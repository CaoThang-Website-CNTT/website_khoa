<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\RequestValidator;
use App\Services\{AccountService, GoogleOAuthService, StudentService, TeacherService, WebSettingsService};

class AuthController extends Controller
{
  public const SESSION_OAUTH_PENDING = 'oauth_pending';

  private AccountService $_accountService;
  private GoogleOAuthService $_oauthService;
  private WebSettingsService $_settingService;
  private StudentService $_studentService;
  private TeacherService $_teacherService;

  /**
   * Settings được load một lần tại constructor và tái sử dụng cho mọi method.
   * Key là setting key, value là cast_value đã được service xử lý.
   *
   * Chỉ load các group cần thiết cho public site: general, contact, seo, social.
   * Đây là substitute cho cache - khi có cache layer thật thì
   * chỉ cần thay getByGroup() bằng getAutoloaded() ở service layer.
   *
   * @var array<string, mixed>
   */
  private array $_settings = [];

  /**
   * Groups cần load cho public site.
   * Thêm/bớt group tại đây khi site mở rộng.
   */
  private const PRELOAD_GROUPS = ['general', 'contact', 'social'];

  public function __construct(
    AccountService $accountService,
    GoogleOAuthService $oauthService,
    WebSettingsService $settingService,
    StudentService $studentService,
    TeacherService $teacherService,
  ) {
    $this->_accountService = $accountService;
    $this->_oauthService = $oauthService;
    $this->_settingService = $settingService;
    $this->_studentService = $studentService;
    $this->_teacherService = $teacherService;

    $this->_loadSettings();

  }
  public function show(Request $request)
  {
    $loginUrl = $this->_oauthService->getAuthUrl($request->session());

    return $this->render('auth/login', [
      'loginUrl' => $loginUrl,
      'settings' => $this->_settings,
    ], "auth_layout");
  }
  public function onboard(Request $request)
  {
    $pending = $request->session()->get(self::SESSION_OAUTH_PENDING);
    if (!is_array($pending) || empty($pending['email']) || empty($pending['role'])) {
      $request->session()->flashNotify('error', 'Phiên đăng nhập không hợp lệ.', 'Vui lòng đăng nhập lại bằng Google.');
      return $this->redirect('login');
    }

    $role = (string) $pending['role'];
    if (!in_array($role, ['student', 'teacher'], true)) {
      $request->session()->flashNotify('error', 'Không thể hoàn tất đăng ký.', 'Tài khoản không thuộc đối tượng sinh viên hoặc giảng viên.');
      $request->session()->forget(self::SESSION_OAUTH_PENDING);
      return $this->redirect('login');
    }

    $email = (string) $pending['email'];
    $studentIdFromEmail = null;
    if ($role === 'student') {
      $local = strstr($email, '@', true);
      if (strlen($local) === 10 && ctype_digit($local)) {
        $studentIdFromEmail = $local;
      } else {
        $request->session()->forget(self::SESSION_OAUTH_PENDING);
        $request->session()->flashNotify('error', 'Email sinh viên không hợp lệ.', 'MSSV trong email phải gồm đúng 10 chữ số.');
        return $this->redirect('login');
      }
    }

    $classrooms = $role === 'student' ? $this->_studentService->getAllClassrooms() : [];

    return $this->render('auth/onboard', [
      'settings' => $this->_settings,
      'role' => $role,
      'pendingEmail' => $email,
      'googleDisplayName' => (string) ($pending['name'] ?? ''),
      'studentIdFromEmail' => $studentIdFromEmail,
      'classrooms' => $classrooms,
    ], 'auth_layout');
  }

  public function completeOnboarding(Request $request)
  {
    $pending = $request->session()->get(self::SESSION_OAUTH_PENDING);
    if (!is_array($pending) || empty($pending['email']) || empty($pending['role'])) {
      $request->session()->flashNotify('error', 'Phiên đăng nhập không hợp lệ.', 'Vui lòng đăng nhập lại bằng Google.');
      return $this->redirect('login');
    }

    $role = (string) $pending['role'];
    $email = (string) $pending['email'];
    $data = $request->all();
    unset($data['_token']);

    if ($role === 'student') {
      return $this->_completeStudentOnboarding($request, $email, $data);
    }

    if ($role === 'teacher') {
      return $this->_completeTeacherOnboarding($request, $email, $data);
    }

    $request->session()->forget(self::SESSION_OAUTH_PENDING);
    $request->session()->flashNotify('error', 'Không thể hoàn tất đăng ký.');
    return $this->redirect('login');
  }

  public function googleOAuthCallback(Request $request)
  {
    $state = $request->query('state');
    $savedState = $request->session()->get(Session::KEY_OAUTH_STATE);
    if (
      !is_string($state) || $state === ''
      || !is_string($savedState) || $savedState === ''
      || !hash_equals($savedState, $state)
    ) {
      $request->session()->flashNotify(
        'error',
        'Xác thực OAuth không hợp lệ.',
        'Phiên đăng nhập không khớp hoặc đã hết hạn. Vui lòng thử đăng nhập lại.'
      );
      return $this->redirect('login');
    }
    $request->session()->forget(Session::KEY_OAUTH_STATE);

    if (empty($request->query('code'))) {
      $request->session()->flashNotify('error', 'Thiếu mã xác thực.', 'Không nhận được authorization code từ Google.');
      return $this->redirect('login');
    }

    $googleUser = $this->_oauthService->authenticate($request->query('code'));

    if (!$googleUser || !isset($googleUser['email'])) {
      $request->session()->flashNotify('error', 'Đăng nhập Google thất bại.', 'Không lấy được email từ Google.');
      return $this->redirect('login');
    }

    try {
      $result = $this->_accountService->authenticateOAuthUser($googleUser);
    } catch (\Throwable $e) {
      $request->session()->flashNotify('error', 'Không thể xác thực tài khoản.', $e->getMessage());
      return $this->redirect('login');
    }

    $role = $result['role'];
    $is_new = $result['is_new'];

    if (!$is_new) {
      $account = $result['user'] ?? null;
      if ($account === null) {
        $request->session()->flashNotify('error', 'Không thể đăng nhập.', 'Không tìm thấy tài khoản.');
        return $this->redirect('login');
      }
      $request->session()->loginUser($account->id, $account->email, $account->role);
      return $this->redirect('/');
    }

    if ($is_new) {
      if (!in_array($role, ['student', 'teacher'], true)) {
        $request->session()->flashNotify(
          'error',
          'Không thể đăng ký tự động.',
          'Email không thuộc định dạng sinh viên hoặc giảng viên của trường.'
        );
        return $this->redirect('login');
      }
      $request->session()->put(self::SESSION_OAUTH_PENDING, [
        'email' => $googleUser['email'],
        'name' => $googleUser['name'] ?? '',
        'sub' => $googleUser['sub'] ?? '',
        'role' => $role,
      ]);
      return $this->redirect('onboarding?role=' . rawurlencode($role));
    }

    $request->session()->flashNotify('error', 'Không thể hoàn tất đăng nhập.');
    return $this->redirect('login');
  }

  /**
   * @param array<string, mixed> $data
   */
  private function _completeStudentOnboarding(Request $request, string $email, array $data)
  {
    $local = strstr($email, '@', true);
    if (!$local || strlen($local) !== 10 || !ctype_digit($local)) {
      $request->session()->forget(self::SESSION_OAUTH_PENDING);
      $request->session()->flashNotify('error', 'Email sinh viên không hợp lệ.');
      return $this->redirect('login');
    }

    $data['email'] = $email;
    $data['student_id'] = $local;
    $data['status'] = 'Đang học';
    $data['notes'] = $data['notes'] ?? '';

    $validator = new RequestValidator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'birth_place' => ['required', 'max:255'],
      'national_id' => ['required', 'size:12'],
      'gender' => ['required', 'in:male,female'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],
      'classroom_id' => ['required'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->session()->flashOldInputs($data);
      $request->session()->flashErrors($validator->getErrors());
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ', 'Hãy kiểm tra lại form');
      return $this->redirect('onboarding?role=student');
    }

    if (!$this->_studentService->isStudentIdUnique($local)) {
      $request->session()->flashOldInputs($data);
      $request->session()->flashErrors($validator->getErrors());
      $request->session()->flashNotify('error', 'Mã số sinh viên không hợp lệ', 'Mã số sinh viên này đã tồn tại trong hệ thống.');
      return $this->redirect('onboarding?role=student');
    }

    $student = null;
    try {
      $student = $this->_studentService->createStudent($data);
    } catch (\Throwable $e) {
      $request->session()->flashNotify('error', 'Không thể tạo hồ sơ.', $e->getMessage());
      $request->session()->flashOldInputs($data);
      return $this->redirect('onboarding?role=student');
    }

    if (!$student || !$student->account_id) {
      $request->session()->flashNotify('error', 'Không thể tạo hồ sơ.', 'Vui lòng thử lại.');
      $request->session()->flashOldInputs($data);
      return $this->redirect('onboarding?role=student');
    }

    $request->session()->forget(self::SESSION_OAUTH_PENDING);
    $request->session()->loginUser((int) $student->account_id, $email, 'student');
    $request->session()->flashNotify('success', 'Hoàn tất đăng ký!', 'Tài khoản sinh viên đã được tạo.');
    return $this->redirect('/');
  }

  /**
   * @param array<string, mixed> $data
   */
  private function _completeTeacherOnboarding(Request $request, string $email, array $data)
  {
    $data['email'] = $email;
    if (isset($data['staff_code'])) {
      $data['staff_code'] = trim((string) $data['staff_code']);
    }

    $validator = new RequestValidator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'national_id' => ['required', 'size:12'],
      'gender' => ['required', 'in:male,female'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],
      'staff_code' => ['required', 'size:10'],
      'degree' => ['required', 'max:255'],
      'title' => ['nullable', 'max:150'],
      'position' => ['required', 'max:255'],
      'department' => ['required', 'max:255'],
      'contract_type' => ['required', 'in:full_time,part_time,visiting,contract'],
      'start_date' => ['required', 'date'],
      'end_date' => ['required', 'date'],
      'notes' => ['nullable'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->session()->flashOldInputs($data);
      $request->session()->flashErrors($validator->getErrors());
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ', 'Hãy kiểm tra lại form');
      return $this->redirect('onboarding?role=teacher');
    }

    $teacher = null;
    try {
      $teacher = $this->_teacherService->createTeacher($data);
    } catch (\Throwable $e) {
      $request->session()->flashNotify('error', 'Không thể tạo hồ sơ.', $e->getMessage());
      $request->session()->flashOldInputs($data);
      return $this->redirect('onboarding?role=teacher');
    }

    if (!$teacher || !$teacher->account_id) {
      $request->session()->flashNotify('error', 'Không thể tạo hồ sơ.', 'Vui lòng thử lại.');
      $request->session()->flashOldInputs($data);
      return $this->redirect('onboarding?role=teacher');
    }

    $request->session()->forget(self::SESSION_OAUTH_PENDING);
    $request->session()->loginUser((int) $teacher->account_id, $email, 'teacher');
    $request->session()->flashNotify('success', 'Hoàn tất đăng ký!', 'Tài khoản giảng viên đã được tạo.');
    return $this->redirect('/');
  }
  public function logout(Request $request)
  {
    $request->session()->logoutUser();
    $request->session()->flashNotify('success', 'Đăng xuất thành công.');
    return $this->redirect('/');
  }

  /**
   * Load tất cả settings thuộc PRELOAD_GROUPS vào $_settings.
   * Kết quả là flat map: key → cast_value.
   */
  private function _loadSettings()
  {
    foreach (self::PRELOAD_GROUPS as $group) {
      $rows = $this->_settingService->getByGroup($group);
      foreach ($rows as $setting) {
        $this->_settings[$setting->key] = $setting->cast_value;
      }
    }
  }
}