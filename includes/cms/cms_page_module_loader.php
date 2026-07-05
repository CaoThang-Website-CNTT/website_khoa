<?php

namespace App\Cms;

final class CmsPageModuleLoader
{
  public static function load(string $directory): array
  {
    $pages = [];
    foreach (glob(rtrim($directory, '/\\') . '/*.php') ?: [] as $file) {
      $page = require $file;
      if (!is_array($page) || trim((string) ($page['slug'] ?? '')) === '') {
        throw new \RuntimeException("Trang CMS module không hợp lệ: {$file}");
      }
      $slug = (string) $page['slug'];
      if (isset($pages[$slug]))
        throw new \RuntimeException("Slug trang CMS bị trùng lặp: {$slug}");
      $pages[$slug] = $page;
    }
    return $pages;
  }
}
