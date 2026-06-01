<?php
namespace App\Editor\Blocks;

use App\Editor\RichTextRenderer;
use App\Editor\TocEntry;

/**
 * Mỗi block renderer:
 *  - Nhận một block array đã qua BlockValidator (đảm bảo schema đúng).
 *  - Trả về chuỗi HTML hoàn chỉnh (safe, không cần escape thêm).
 */
abstract class AbstractBlockRenderer
{
  /**
   * Render block → HTML string.
   *
   * @param  array  $block  Normalized block từ BlockValidator
   * @return string
   */
  abstract public function render(array $block): string;

  /**
   * Trích xuất TocEntry.
   *
   * Mặc định trả về null - chỉ HeadingRenderer override.
   * @param  array  $block  Normalized block từ BlockValidator
   */
  public function extractTocEntry(array $block): ?TocEntry
  {
    return null;
  }

  /**
   * Render rich_text field của block.
   */
  protected function renderRichText(array $block): string
  {
    return RichTextRenderer::render($block['data']['rich_text'] ?? []);
  }

  /**
   * Lấy giá trị meta với fallback.
   */
  protected function meta(array $block, string $key, mixed $default = null): mixed
  {
    return $block['data']['meta'][$key] ?? $default;
  }

  /**
   * Tạo class string từ mảng - lọc falsy value, join bằng space.
   *
   * @param  string[]  $classes
   */
  protected function classAttr(array $classes): string
  {
    $filtered = array_filter($classes, fn($c) => is_string($c) && $c !== '');
    return implode(' ', $filtered);
  }
  protected function esc(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}