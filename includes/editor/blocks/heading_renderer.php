<?php
namespace App\Editor\Blocks;

use App\Editor\TocEntry;

/**
 * Render block type: blocks/heading
 *
 * Meta:
 *   level : int   (2–4, default 2) - JS map: display H1→level 2, H2→3, H3→4
 *   align : string ('left'|'center'|'right', default 'left')
 *
 * Output: <h{level} class="be-heading ...">...rich_text...</h{level}>
 */
final class HeadingRenderer extends AbstractBlockRenderer
{
  private const ALLOWED_LEVELS = [1, 2, 3, 4, 5, 6];
  private const ALLOWED_ALIGNS = ['left', 'center', 'right'];

  public function render(array $block): string
  {
    $level = $this->resolveLevel($this->meta($block, 'level', 2));
    $align = $this->resolveAlign($this->meta($block, 'align', 'left'));
    $content = $this->renderRichText($block);

    if ($content === '') {
      return '';
    }

    $class = $this->classAttr([
      'be-heading',
      $align !== 'left' ? "be-heading--{$align}" : '',
    ]);

    $anchorId = $this->esc($block['data']['meta']['anchor_id'] ?? '');
    $idAttr = $anchorId !== '' ? " id=\"{$anchorId}\"" : '';

    return "<h{$level} class=\"{$class}\"{$idAttr}>{$content}</h{$level}>";
  }

  /**
   * Override: Heading có TocEntry.
   *
   * plainText được trích từ rich_text segments - strip toàn bộ marks,
   * chỉ lấy text thuần để hiển thị trong sidebar ToC (không có <strong> hay <a>).
   */
  public function extractTocEntry(array $block): ?TocEntry
  {
    $level = $this->resolveLevel($this->meta($block, 'level', 2));
    $plainText = $this->extractPlainText($block['data']['rich_text'] ?? []);

    // Heading rỗng không xuất hiện trong ToC
    if ($plainText === '') {
      return null;
    }

    $anchorId = trim((string) ($block['data']['meta']['anchor_id'] ?? ''));
    $blockId = $block['id'] ?? '';

    return new TocEntry(
      level: $level,
      plainText: $plainText,
      anchorId: $anchorId,
      blockId: $blockId,
    );
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────

  /**
   * Nối text của tất cả RichSegment[] - bỏ qua mọi marks/formatting.
   * Kết quả dùng để hiển thị trong ToC sidebar, không phải trong content.
   *
   * @param  array<int, array<string, mixed>>  $segments
   */
  private function extractPlainText(array $segments): string
  {
    return trim(implode('', array_map(
      fn($seg) => is_string($seg['text'] ?? null) ? $seg['text'] : '',
      $segments,
    )));
  }

  private function resolveLevel(mixed $level): int
  {
    $level = (int) $level;
    return in_array($level, self::ALLOWED_LEVELS, true) ? $level : 2;
  }

  private function resolveAlign(mixed $align): string
  {
    $align = (string) $align;
    return in_array($align, self::ALLOWED_ALIGNS, true) ? $align : 'left';
  }
}