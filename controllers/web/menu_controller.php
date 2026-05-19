<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\MenuService;

class MenuController extends Controller
{
  private MenuService $_menuService;

  public function __construct(MenuService $menuService)
  {
    $this->_menuService = $menuService;
  }

  // ============================================================================
  // Menus
  // ============================================================================

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $data = $this->_menuService->getMenus($currentPage, 15);

    $this->render('admin/menus/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render('admin/menus/create', [], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'key' => ['required', 'max:60'],
      'label' => ['required', 'max:100'],
      'description' => ['max:255'],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/create');
    }

    if (!$this->_menuService->isKeyUnique($data['key'])) {
      $validator->addError('key', 'Key này đã tồn tại, vui lòng chọn key khác.');
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/create');
    }

    $rawItems = is_array($data['items'] ?? null) ? $data['items'] : [];
    foreach ($rawItems as $i => $item) {
      if (empty($item['label'])) {
        $validator->addError("items.{$i}.label", 'Mục ' . ($i + 1) . ': nhãn không được để trống.');
      }
      if (empty($item['url'])) {
        $validator->addError("items.{$i}.url", 'Mục ' . ($i + 1) . ': URL không được để trống.');
      }
    }

    if ($validator->hasErrors()) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/create');
    }

    try {
      $newMenu = $this->_menuService->create([
        'key' => $data['key'],
        'label' => $data['label'],
        'description' => $data['description'] ?? null,
        'sort_order' => $data['sort_order'] ?? 0,
        'items' => $rawItems,
      ]);

      $itemCount = count($newMenu->items);
      $request->session()->flashNotify(
        'success',
        'Tạo nhóm menu thành công!',
        "Menu \"{$newMenu->label}\" đã được tạo" . ($itemCount ? " với {$itemCount} mục." : '.')
      );
      return $this->redirect('admin/menus/' . $newMenu->id);
    } catch (\Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
      return $this->redirect('admin/menus/create');
    }
  }

  public function edit($id)
  {
    $menu = $this->_menuService->getMenuWithItems($id);

    if (!$menu) {
      $this->abort(404);
    }

    $this->render('admin/menus/edit', [
      'menu' => $menu,
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $menu = $this->_menuService->getMenuWithItems($id);

    if (!$menu) {
      $this->abort(404);
    }

    if (!$menu->isEditable()) {
      $request->session()->flashNotify('error', 'Menu này do hệ thống định nghĩa, không thể chỉnh sửa.');
      return $this->redirect('admin/menus');
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'key' => ['required', 'max:60'],
      'label' => ['required', 'max:100'],
      'description' => ['max:255'],
      'sort_order' => [],
      'reorder' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $id);
    }

    try {
      $isSuccess = $this->_menuService->updateMenu((int) $id, $data);
    } catch (\InvalidArgumentException $e) {
      $validator->addError('key', 'Key này đã tồn tại, vui lòng chọn key khác.');
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $id);
    }

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật nhóm menu thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $id);
  }

  public function destroy($id, Request $request)
  {
    $menu = $this->_menuService->getMenuWithItems($id);

    if (!$menu) {
      $this->abort(404);
    }

    if (!$menu->isEditable()) {
      $request->session()->flashNotify('error', 'Menu này do hệ thống định nghĩa, không thể xóa.');
      return $this->redirect('admin/menus');
    }

    $isSuccess = $this->_menuService->deleteMenu($id);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Xóa nhóm menu thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus');
  }

  // ============================================================================
  // Menu Items
  // ============================================================================

  public function createItem($menuId)
  {
    $menu = $this->_menuService->getMenuWithItems($menuId);

    if (!$menu) {
      $this->abort(404);
    }

    $this->render('admin/menus-items/create', [
      'menu' => $menu,
    ], layout: 'dashboard_layout');
  }

  public function storeItem(string $menuId, Request $request)
  {
    $menu = $this->_menuService->getMenuById((int) $menuId);

    if (!$menu) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'label' => ['required', 'max:150'],
      'url' => ['required', 'max:500'],
      'parent_id' => [],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $menuId);
    }

    try {
      $newId = $this->_menuService->addItem((int) $menuId, $data);
    } catch (\InvalidArgumentException $e) {
      $validator->addError('key', 'Key này đã tồn tại, vui lòng chọn key khác.');
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $menuId);
    }

    if ($newId) {
      $request->session()->flashNotify('success', 'Thêm mục menu thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $menuId);
  }

  public function editItem($itemId)
  {
    $item = $this->_menuService->getItemById($itemId);

    if (!$item) {
      $this->abort(404);
    }

    $this->render('admin/menus-items/edit', [
      'item' => $item,
    ], layout: 'dashboard_layout');
  }

  public function updateItem($itemId, Request $request)
  {
    $item = $this->_menuService->getItemById($itemId);

    if (!$item) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'label' => ['required', 'max:150'],
      'url' => ['required', 'max:500'],
      'parent_id' => [],
      'sort_order' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/menus/' . $item->menu_id);
    }

    $isSuccess = $this->_menuService->updateItem((int) $itemId, [
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
      'label' => $data['label'],
      'url' => $data['url'],
      'sort_order' => !empty($data['sort_order']) ? (int) $data['sort_order'] : 0,
    ]);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật mục menu thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $item->menu_id);
  }

  public function destroyItem($itemId, Request $request)
  {
    $item = $this->_menuService->getItemById($itemId);

    if (!$item) {
      $this->abort(404);
    }

    $isSuccess = $this->_menuService->removeItem($itemId);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Xóa menu item thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $item->menu_id);
  }

  public function reorderItems($menuId, Request $request)
  {
    $data = $request->all();

    $orderMap = $data['order_map'] ?? [];

    if (empty($orderMap) || !is_array($orderMap)) {
      $request->session()->flashNotify('error', 'Dữ liệu sắp xếp không hợp lệ.');
      return $this->redirect('admin/menus/' . $menuId);
    }

    $cleanOrderMap = [];
    foreach ($orderMap as $itemId => $sortOrder) {
      $cleanOrderMap[(int) $itemId] = (int) $sortOrder;
    }

    $isSuccess = $this->_menuService->reorderItems($cleanOrderMap);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Đã cập nhật thứ tự menu.');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/menus/' . $menuId);
  }
}