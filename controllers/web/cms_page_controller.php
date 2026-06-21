<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CmsPageService;

class CmsPageController extends Controller
{
  public function __construct(
    private CmsPageService $_cmsPageService,
  ) {
  }

  public function index(Request $request): void
  {
    $currentPage = (int) $request->query('page', 1);
    $limit = (int) $request->query('limit', 15);

    $data = $this->_cmsPageService->getPages($currentPage, $limit, [
      'search' => $request->query('search', ''),
      'status' => $request->query('status', ''),
    ]);

    $this->render('admin/cms_pages/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function edit(string $slug, Request $request): void
  {
    try {
      $payload = $this->_cmsPageService->getPageForEditing($slug);
    } catch (\InvalidArgumentException $e) {
      $request->session()->flashNotify('error', 'CMS page not found', $e->getMessage());
      $this->redirect('admin/cms-pages');
    }

    $this->render('admin/cms_pages/edit', $payload, layout: 'cms_layout');
  }

  public function update(string $slug, Request $request): void
  {
    try {
      $payload = $this->decodeEditorPayload($request);
      $action = (string) ($request->input('action', $payload['action'] ?? 'draft'));

      $page = $action === 'publish'
        ? $this->_cmsPageService->publish($slug, $payload)
        : $this->_cmsPageService->saveDraft($slug, $payload);

      $request->session()->flashNotify(
        'success',
        $action === 'publish' ? 'Published CMS page' : 'Saved CMS draft',
        "CMS page '{$page->title}' has been updated."
      );
    } catch (\InvalidArgumentException | \RuntimeException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify('error', 'Unable to save CMS page', $e->getMessage());
    }

    $this->redirect("admin/cms-pages/{$slug}");
  }

  public function publish(string $slug, Request $request): void
  {
    try {
      $payload = $this->decodeEditorPayload($request);
      $page = $this->_cmsPageService->publish($slug, $payload);
      $request->session()->flashNotify('success', 'Published CMS page', "CMS page '{$page->title}' is now public.");
    } catch (\InvalidArgumentException | \RuntimeException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify('error', 'Unable to publish CMS page', $e->getMessage());
    }

    $this->redirect("admin/cms-pages/{$slug}");
  }

  private function decodeEditorPayload(Request $request): array
  {
    $raw = $request->input('editor_data');

    if (is_array($raw)) {
      return $raw;
    }

    if (!is_string($raw) || trim($raw) === '') {
      return $request->json() ?: [];
    }

    $decoded = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
      throw new \InvalidArgumentException('CMS editor payload must be valid JSON.');
    }

    return $decoded;
  }
}
