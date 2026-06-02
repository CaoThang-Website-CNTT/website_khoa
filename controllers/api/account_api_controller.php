<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use Exception;
use App\Services\{AccountService};
class AccountApiController extends Controller
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

    try {
      $pageable = $this->_accountService->getAccounts($page, $perPage, $search);
      return $this->json($pageable, 200);
    } catch (Exception $e) {
      error_log('Lỗi lấy dữ liệu tài khoản: ' . $e->getMessage());
      return $this->json(['message' => 'Không tìm thấy dữ liệu yêu cầu.'], 404);
    }
  }
}
?>