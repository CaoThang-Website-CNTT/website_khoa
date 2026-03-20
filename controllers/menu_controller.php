<?php

namespace App\Controllers;

require_once BASE_PATH . '/includes/core/controller.php';
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/menu.php';
require_once BASE_PATH . '/models/menu_item.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\MenuService;

class MenuController extends Controller
{
  private $_menuService;

  public function __construct(MenuService $menuService)
  {
    $this->_menuService = $menuService;
  }

  // ============================================================================
  // Menus
  // ============================================================================

  public function index()
  {
    $menus = $this->_menuService->getAllMenus();

    $this->render('admin/menus/index', [
      'menus' => $menus,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render('admin/menus/create', [], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'key' => ['required', 'max:60'],
      'label' => ['required', 'max:100'],
      'description' => ['max:255'],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/create');
    }

    if (!$this->_menuService->isKeyUnique($data['key'])) {
      $validator->addError('key', 'Key này đã tồn tại, vui lòng chọn key khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/create');
    }

    $newId = $this->_menuService->createMenu([
      'key' => $data['key'],
      'label' => $data['label'],
      'description' => $data['description'] ?? null,
      'type' => 'custom',
      'sort_order' => !empty($data['sort_order']) ? (int) $data['sort_order'] : 0,
    ]);

    if ($newId) {
      $request->flash('success', 'Tạo nhóm menu thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus');
  }

  public function edit(string $id)
  {
    $menu = $this->_menuService->getMenuById((int) $id);

    if (!$menu) {
      $this->abort(404);
    }

    $items = $this->_menuService->getItemsTree((int) $id);

    $this->render('admin/menus/edit', [
      'menu' => $menu,
      'items' => $items,
    ], layout: 'dashboard_layout');
  }

  public function update(string $id, Request $request)
  {
    $menu = $this->_menuService->getMenuById((int) $id);

    if (!$menu) {
      $this->abort(404);
    }

    if (!$menu->isEditable()) {
      $request->flash('error', 'Menu này do hệ thống định nghĩa, không thể chỉnh sửa.');
      return $this->redirect('admin/menus');
    }

    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'key' => ['required', 'max:60'],
      'label' => ['required', 'max:100'],
      'description' => ['max:255'],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $id);
    }

    if (!$this->_menuService->isKeyUnique($data['key'], (int) $id)) {
      $validator->addError('key', 'Key này đã tồn tại, vui lòng chọn key khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $id);
    }

    $isSuccess = $this->_menuService->updateMenu((int) $id, [
      'key' => $data['key'],
      'label' => $data['label'],
      'description' => $data['description'] ?? null,
      'sort_order' => !empty($data['sort_order']) ? (int) $data['sort_order'] : 0,
    ]);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật nhóm menu thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $id);
  }

  public function destroy(string $id, Request $request)
  {
    $menu = $this->_menuService->getMenuById((int) $id);

    if (!$menu) {
      $this->abort(404);
    }

    if (!$menu->isEditable()) {
      $request->flash('error', 'Menu này do hệ thống định nghĩa, không thể xóa.');
      return $this->redirect('admin/menus');
    }

    $isSuccess = $this->_menuService->deleteMenu((int) $id);

    if ($isSuccess) {
      $request->flash('success', 'Xóa nhóm menu thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus');
  }

  // ============================================================================
  // Menu Items
  // ============================================================================

  public function createItem(string $menuId)
  {
    $menu = $this->_menuService->getMenuById((int) $menuId);

    if (!$menu) {
      $this->abort(404);
    }

    $items = $this->_menuService->getItemsFlat((int) $menuId);

    $this->render('admin/menus/items/create', [
      'menu' => $menu,
      'items' => $items,
    ], layout: 'dashboard_layout');
  }

  public function storeItem(string $menuId, Request $request)
  {
    $menu = $this->_menuService->getMenuById((int) $menuId);

    if (!$menu) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'label' => ['required', 'max:150'],
      'url' => ['required', 'max:500'],
      'parent_id' => [],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $menuId . '/items/create');
    }

    $newId = $this->_menuService->createItem([
      'menu_id' => (int) $menuId,
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
      'label' => $data['label'],
      'url' => $data['url'],
      'sort_order' => !empty($data['sort_order']) ? (int) $data['sort_order'] : null,
    ]);

    if ($newId) {
      $request->flash('success', 'Thêm mục menu thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $menuId);
  }

  public function editItem(string $menuId, string $itemId)
  {
    $menu = $this->_menuService->getMenuById((int) $menuId);

    if (!$menu) {
      $this->abort(404);
    }

    $item = $this->_menuService->getItemById((int) $itemId);

    if (!$item) {
      $this->abort(404);
    }

    // Loại trừ chính item đang edit khỏi danh sách parent — tránh circular reference
    $items = array_values(array_filter(
      $this->_menuService->getItemsFlat((int) $menuId),
      fn($i) => $i->id !== $item->id
    ));

    $this->render('admin/menus/items/edit', [
      'menu' => $menu,
      'item' => $item,
      'items' => $items,
    ], layout: 'dashboard_layout');
  }

  public function updateItem(string $menuId, string $itemId, Request $request)
  {
    $menu = $this->_menuService->getMenuById((int) $menuId);

    if (!$menu) {
      $this->abort(404);
    }

    $item = $this->_menuService->getItemById((int) $itemId);

    if (!$item) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'label' => ['required', 'max:150'],
      'url' => ['required', 'max:500'],
      'parent_id' => [],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $menuId . '/items/' . $itemId);
    }

    $isSuccess = $this->_menuService->updateItem((int) $itemId, [
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
      'label' => $data['label'],
      'url' => $data['url'],
      'sort_order' => !empty($data['sort_order']) ? (int) $data['sort_order'] : 0,
    ]);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật mục menu thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $menuId . '/items/' . $itemId);
  }

  public function destroyItem(string $menuId, string $itemId, Request $request)
  {
    $item = $this->_menuService->getItemById((int) $itemId);

    if (!$item) {
      $this->abort(404);
    }

    $isSuccess = $this->_menuService->deleteItem((int) $itemId);

    if ($isSuccess) {
      $request->flash('success', 'Xóa mục menu thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $menuId);
  }

  public function reorderItems(string $menuId, Request $request)
  {
    $data = $request->all();
    $moveId = !empty($data['move_id']) ? (int) $data['move_id'] : null;
    $direction = $data['direction'] ?? null;

    if (!$moveId || !in_array($direction, ['up', 'down'])) {
      $request->flash('error', 'Dữ liệu sắp xếp không hợp lệ.');
      return $this->redirect('admin/menus/' . $menuId);
    }

    $isSuccess = $this->_menuService->reorderItems($moveId, $direction);

    if ($isSuccess) {
      $request->flash('success', 'Đã cập nhật thứ tự menu.');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $menuId);
  }
}