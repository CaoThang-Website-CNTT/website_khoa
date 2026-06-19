<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{CompanyService};
use Exception;

class CompanyController extends Controller
{
  private CompanyService $_companyService;

  public function __construct(
    CompanyService $companyService
  ) {
    $this->_companyService = $companyService;
  }

  public function index(Request $request)
  {
    $currentPage = (int)($request->query('page') ?? 1);
    $limit = (int)($request->query('limit') ?? 15);
    $filter = $request->query('filter') ?? 'all';

    $data = $this->_companyService->getCompanies($currentPage, $limit, $filter);
    $pendingCount = $this->_companyService->getCountByVerified(0);

    $this->render("admin/companies/index", [
      'data' => $data,
      'filter' => $filter,
      'pendingCount' => $pendingCount
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    $this->render("admin/companies/create", [], layout: "dashboard_layout");
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'company_name' => ['required', 'max:255'],
      'tax_code' => ['required', 'max:50'],
      'phone' => ['nullable', 'phone', 'max:15'],
      'address' => ['required'],
      'email' => ['nullable', 'email', 'max:255'],
      'website' => ['nullable', 'max:255'],
      'note' => ['nullable'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/companies/create');
    }

    // Kiểm tra MST unique
    $taxCode = trim($data['tax_code'] ?? '');
    if ($taxCode !== '') {
      $existing = $this->_companyService->findByTaxCode($taxCode);
      if ($existing) {
        $validator->addError('tax_code', 'Mã số thuế này đã tồn tại trong hệ thống.');
        $request->flashOldInputs();
        $request->session()->flashErrors($validator->getErrors());
        return $this->redirect('admin/companies/create');
      }
    }

    try {
      $newCompanyId = $this->_companyService->createManual([
        'name' => $data['company_name'],
        'tax_code' => $taxCode ?: null,
        'phone' => $data['phone'] ?? null,
        'address' => $data['address'],
        'email' => $data['email'] ?? null,
        'website' => $data['website'] ?? null,
        'note' => $data['note'] ?? null,
        'is_verified' => 1,
      ]);

      $request->session()->flashNotify(
        'success',
        'Tạo mới công ty thành công!',
        'Công ty #' . $newCompanyId . ' đã được thêm vào hệ thống.'
      );
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra', $e->getMessage());
      return $this->redirect('admin/companies/create');
    }

    $this->redirect('admin/companies');
    exit;
  }

  public function edit($id, Request $request)
  {
    $company = $this->_companyService->getCompanyById($id);
    if (!$company) {
      $request->session()->flashNotify(
        'error',
        'Không tìm thấy Công ty #' . $id . '!',
        'Hãy thử lại sau.'
      );
      $this->redirect('admin/companies');
    }

    $this->render("admin/companies/edit", [
      'company' => $company,
    ], layout: "dashboard_layout");
  }

  public function update($id, Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'company_name' => ['required', 'max:255'],
      'tax_code' => ['nullable', 'max:50'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required', 'max:500'],
      'email' => ['nullable', 'email', 'max:255'],
      'website' => ['nullable', 'max:255'],
      'description' => ['nullable'],
      'notes' => ['nullable'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/companies/' . $id);
    }

    $isSuccess = $this->_companyService->updateCompany($id, $data);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật công ty thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    $this->redirect('admin/companies/' . $id);
    exit;
  }

  public function destroy($id, Request $request)
  {
    try {
      $isSuccess = $this->_companyService->deleteCompany($id);
      if ($isSuccess) {
        $request->session()->flashNotify('success', 'Xoá công ty thành công!');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi xóa công ty', $e->getMessage());
    }

    return $this->redirect('admin/companies');
  }

  public function approve($id, Request $request)
  {
    try {
      $this->_companyService->approve($id);
      $request->session()->flashNotify('success', 'Đã xác thực công ty thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra', $e->getMessage());
    }
    return $this->redirect('admin/companies');
  }

  public function bulkApprove(Request $request)
  {
    $ids = $request->input('ids');
    if (!is_array($ids) || empty($ids)) {
      $request->session()->flashNotify('error', 'Chưa chọn công ty nào để xác thực.');
      return $this->redirect('admin/companies');
    }

    try {
      $count = $this->_companyService->bulkApprove($ids);
      $request->session()->flashNotify('success', "Đã xác thực thành công $count công ty!");
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra', $e->getMessage());
    }
    return $this->redirect('admin/companies');
  }

  public function duplicates(Request $request)
  {
    $groups = $this->_companyService->getGroupedDuplicates();

    $this->render("admin/companies/duplicates", [
      'groups' => $groups
    ], layout: "dashboard_layout");
  }

  public function quickMerge(Request $request)
  {
    $sourceId = $request->input('source_id');
    $targetId = $request->input('target_id');

    if (!$sourceId || !$targetId) {
      $request->session()->flashNotify('error', 'Thiếu ID công ty nguồn hoặc đích.');
      return $this->redirect('admin/companies/duplicates');
    }

    try {
      $this->_companyService->quickMerge((int)$sourceId, (int)$targetId);
      $request->session()->flashNotify('success', 'Gộp nhanh công ty thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra', $e->getMessage());
    }

    return $this->redirect('admin/companies/duplicates');
  }

  public function bulkQuickMerge(Request $request)
  {
    $sourceIds = $request->input('source_ids');
    $targetId = $request->input('target_id');

    if (empty($sourceIds) || !is_array($sourceIds) || !$targetId) {
      $request->session()->flashNotify('error', 'Thiếu dữ liệu gộp.');
      return $this->redirect('admin/companies/duplicates');
    }

    $results = $this->_companyService->bulkQuickMerge($sourceIds, (int)$targetId);

    if ($results['failed'] > 0) {
      $request->session()->flashNotify('warning', "Gộp thành công {$results['success']} công ty. Thất bại {$results['failed']}.");
    } else {
      $request->session()->flashNotify('success', "Đã gộp nhanh toàn bộ {$results['success']} công ty thành công!");
    }

    return $this->redirect('admin/companies/duplicates');
  }

  public function mergeForm($id, Request $request)
  {
    $company = $this->_companyService->getCompanyById($id);
    if (!$company) {
      $request->session()->flashNotify('error', 'Không tìm thấy công ty!');
      return $this->redirect('admin/companies');
    }

    $counts = $this->_companyService->getRelatedCounts($id);

    $this->render("admin/companies/merge", [
      'company' => $company,
      'counts' => $counts
    ], layout: "dashboard_layout");
  }

  public function merge($id, Request $request)
  {
    $data = $request->all();
    $targetId = $data['target_id'] ?? null;

    if (!$targetId) {
      $request->session()->flashNotify('error', 'Chưa chọn công ty đích để gộp.');
      return $this->redirect('admin/companies/' . $id . '/merge');
    }

    $selectedFields = [
      'name' => $data['name'] ?? '',
      'tax_code' => $data['tax_code'] ?? '',
      'address' => $data['address'] ?? '',
      'phone' => $data['phone'] ?? '',
      'email' => $data['email'] ?? '',
      'website' => $data['website'] ?? ''
    ];

    try {
      $this->_companyService->merge($id, $targetId, $selectedFields);
      $request->session()->flashNotify('success', 'Gộp công ty thành công!');
      return $this->redirect('admin/companies');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra', $e->getMessage());
      return $this->redirect('admin/companies/' . $id . '/merge');
    }
  }
}
