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
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/accounts/create');
    }

    try {
      $newAccount = "";

      $request->session()->flashNotify(
        'success',
        'Tạo account thành công!',
        "Account \"{$newAccount->email}\" đã được tạo."
      );
      return $this->redirect('admin/accounts');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/accounts/create');
    }
  }

  public function edit($id)
  {
    $account = $this->_accountService->getAccount($id);

    if (!$account) {
      $this->abort(404);
    }

    $this->render('admin/accounts/edit', [
      'account' => $account,
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $account = $this->_accountService->getAccount($id);

    if (!$account) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
    ];

    try {
      $request->session()->flashNotify('success', 'Cập nhật account #' . $id . 'thành công!');
      return $this->redirect('admin/accounts');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/accounts/' . $id);
    }
  }

  public function destroy($id, Request $request)
  {
    $account = $this->_accountService->getAccount($id);

    if (!$account) {
      $this->abort(404);
    }

    try {
      $request->session()->flashNotify('success', 'Xóa account #' . $id . ' thành công!');
      return $this->redirect('admin/accounts');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/accounts/' . $id);
    }
  }
}