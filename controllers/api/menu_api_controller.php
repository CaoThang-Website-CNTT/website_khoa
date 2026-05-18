<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\MenuService;

class MenuApiController extends Controller
{
    protected MenuService $_menuService;

    public function __construct(MenuService $menuService)
    {
        $this->_menuService = $menuService;
    }

    public function sortItems(Request $request, int $menu_id)
    {
        $items = $request->input('items');
        if (!is_array($items)) {
            return $this->json(data: null, message: 'Dữ liệu không hợp lệ!', status: 400);
        }

        $success = $this->_menuService->sortItems($items);
        if ($success) {
            return $this->json(data: null, message: 'Cấu trúc menu đã được cập nhật thành công!', status: 200);
        }
        return $this->json(data: null, message: 'Có lỗi xảy ra khi lưu cấu trúc menu.', status: 500);
    }
}
?>
