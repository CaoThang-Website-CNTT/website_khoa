<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{AccountService};

class AccountController extends Controller
{
  private AccountService $_accountService;
  public function __construct(
    AccountService $accountService,
  ) {
    $this->_accountService = $accountService;
  }

  public function index(Request $request)
  {
    $page = (int) $request->query('page', 1);
    $perPage = (int) $request->query('limit', 15);
    $search = $request->query("search", '%');

    $data = $this->_accountService->getAccounts($page, $perPage, $search);

    $this->render('admin/accounts/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render('admin/accounts/create', [], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'email' => ['required', 'email', 'max:255'],
      'password' => ['required', 'max:255'],
      'password_confirmation' => ['required', 'max:255', 'same:password'],
      'role' => ['required', 'in:admin,editor,student,teacher']
    ];

    if ($validator->validate($data, $rules)) {
      if (!$this->_accountService->isEmailUnique($data['email'])) {
        $validator->addError('email', 'Email này đã được sử dụng.');
      }
    }

    if ($validator->hasErrors()) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/accounts/create');
    }

    try {
      $newAccount = $this->_accountService->createAccount($data['email'], $data['password'], $data['role']);

      if ($newAccount) {
        $request->session()->flashNotify(
          'success',
          'Tạo account thành công!',
          "Account \"{$data['email']}\" đã được tạo."
        );
        return $this->redirect('admin/accounts');
      } else {
        throw new \RuntimeException("Không thể tạo account.");
      }
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/accounts/create');
    }
  }

  public function edit($account_id)
  {
    $account = $this->_accountService->getAccount($account_id);

    if (!$account) {
      $this->abort(404);
    }

    $this->render('admin/accounts/edit', [
      'account' => $account,
    ], layout: 'dashboard_layout');
  }

  public function update($account_id, Request $request)
  {
    $account = $this->_accountService->getAccount($account_id);

    if (!$account) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'email' => ['required', 'email', 'max:255'],
      'password' => ['nullable', 'max:255'],
      'password_confirmation' => ['nullable', 'max:255'],
      'role' => ['required', 'in:admin,editor,student,teacher']
    ];

    if (trim((string) ($data['password'] ?? '')) !== '') {
      $rules['password_confirmation'] = ['required', 'max:255', 'same:password'];
    }

    if ($validator->validate($data, $rules)) {
      if (!$this->_accountService->isEmailUnique($data['email'], $account->id)) {
        $validator->addError('email', 'Email này đã được sử dụng.');
      }
    }

    if ($validator->hasErrors()) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/accounts/' . $account_id);
    }

    try {
      $this->_accountService->updateAccount($account_id, $data['email'], $data['password'] ?? null, $data['role']);
      $request->session()->flashNotify(
        'success',
        "Cập nhật account #" . $account_id . " thành công!"
      );
      return $this->redirect('admin/accounts');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/accounts/' . $account_id);
    }
  }

  public function destroy($account_id, Request $request)
  {
    $account = $this->_accountService->getAccount($account_id);

    if (!$account) {
      $this->abort(404);
    }

    try {
      $this->_accountService->deactivateAccount($account_id);
      $request->session()->flashNotify('success', 'Xóa account #' . $account_id . ' thành công!');
      return $this->redirect('admin/accounts');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/accounts/' . $account_id);
    }
  }
}
