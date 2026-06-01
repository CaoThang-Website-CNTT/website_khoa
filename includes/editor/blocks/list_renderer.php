<?php
namespace App\Editor\Blocks;

use App\Editor\RichTextRenderer;

/**
 * Render block type: blocks/list
 *
 * Meta:
 *   style : 'bullet'|'ordered'  (default 'bullet')
 *   items : ListNode[]
 *
 * ListNode schema:
 *   { rich_text: RichSegment[], children: ListNode[] }
 *
 * Output: <ul>/<ol> với <li> đệ quy - mirror #renderTree() trong list.js.
 * Độ sâu tối đa: 4 cấp (mirror #handleIndent depth >= 3 guard trong JS).
 */
final class ListRenderer extends AbstractBlockRenderer
{
  private const MAX_DEPTH = 4;

  public function render(array $block): string
  {
    $style = (string) $this->meta($block, 'style', 'bullet');
    $items = $this->meta($block, 'items', []);

    if (!is_array($items) || empty($items)) {
      return '';
    }

    $tag = $style === 'ordered' ? 'ol' : 'ul';

    return $this->renderTree($items, $tag, 0);
  }

  /**
   * Đệ quy render cây ListNode[] - logic tương đương #renderTree() trong JS.
   *
   * @param  array   $nodes
   * @param  string  $tag    'ul' hoặc 'ol'
   * @param  int     $depth  Độ sâu hiện tại (0-indexed)
   */
  private function renderTree(array $nodes, string $tag, int $depth): string
  {
    if ($depth >= self::MAX_DEPTH) {
      return '';
    }

    $class = 'be-list' . ($depth > 0 ? " be-list--depth-{$depth}" : '');
    $items = '';

    foreach ($nodes as $node) {
      if (!is_array($node)) {
        continue;
      }

      $richText = $node['rich_text'] ?? [];
      $children = $node['children'] ?? [];

      $text = is_array($richText)
        ? RichTextRenderer::render($richText)
        : $this->esc((string) $richText);

      $childrenHtml = '';
      if (is_array($children) && !empty($children)) {
        $childrenHtml = $this->renderTree($children, $tag, $depth + 1);
      }

      $items .= "<li>{$text}{$childrenHtml}</li>";
    }

    if ($items === '') {
      return '';
    }

    return "<{$tag} class=\"{$class}\">{$items}</{$tag}>";
  }
}
