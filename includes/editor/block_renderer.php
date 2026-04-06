<?php

namespace App\Editor;

/**
 * BlockRenderer
 * 
 * Nhận mảng blocks từ DB (đã json_decode), render từng block
 * bằng cách dispatch sang file template riêng theo type + version.
 * 
 * Pattern: Strategy dispatch qua filesystem.
 * Mỗi block type có thư mục riêng, mỗi version có file riêng:
 *   templates/blocks/heading/v1.php
 *   templates/blocks/paragraph/v1.php
 *   ...
 * 
 * Lợi ích: thêm block type mới không đụng vào class này.
 * Migration: v2.php ra đời mà v1.php vẫn serve bài viết cũ.
 */
class BlockRenderer
{
  private string $templateBase;

  public function __construct(string $templateBase)
  {
    // VD: BASE_PATH . '/templates/blocks'
    $this->templateBase = rtrim($templateBase, '/');
  }

  /**
   * Render toàn bộ mảng blocks thành HTML string.
   * Dùng cho SSR + file cache.
   * 
   * @param  array  $blocks   Mảng block đã json_decode
   * @return string           HTML đã render
   */
  public function render(array $blocks): string
  {
    // Đảm bảo thứ tự theo field 'order' trước khi render
    usort($blocks, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

    return implode("\n", array_map(
      fn($block) => $this->renderBlock($block),
      $blocks
    ));
  }

  /**
   * Render một block đơn lẻ.
   * Trả về empty string nếu template không tồn tại — graceful degradation.
   */
  private function renderBlock(array $block): string
  {
    $type = $block['type'] ?? '';
    $version = (int) ($block['version'] ?? 1);
    $data = $block['data'] ?? [];

    $tpl = "{$this->templateBase}/{$type}/v{$version}.php";

    if (!file_exists($tpl)) {
      // Log để biết có block type chưa có template, nhưng không crash trang
      error_log("[BlockRenderer] Template không tồn tại: {$tpl}");
      return '';
    }

    ob_start();
    // extract() đưa các key của $data thành biến cục bộ cho template
    // heading: $text, $level
    // image:   $url, $caption, $alt
    extract($data, EXTR_SKIP);
    require $tpl;
    return ob_get_clean();
  }
}
