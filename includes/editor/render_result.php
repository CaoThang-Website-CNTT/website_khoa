<?php
namespace App\Editor;

use App\Models\PostMeta;

/**
 * Chứa cả HTML đã render lẫn ToC — được tạo ra từ một lần
 */
final class RenderResult
{
  /** @param TocEntry[] $tocEntries */
  public function __construct(
    /** HTML đầy đủ của toàn bộ content — safe to {!! echo !!} */
    public string $html,

    /** Danh sách heading entries, theo thứ tự xuất hiện trong document */
    private array $tocEntries,
  ) {
  }

  /**
   * Trả về danh sách TocEntry[], theo thứ tự xuất hiện.
   *
   * @return TocEntry[]
   */
  public function entries(): array
  {
    return $this->tocEntries;
  }

  /**
   * Document có heading nào không.
   */
  public function hasToc(): bool
  {
    return !empty($this->tocEntries);
  }

  /**
   * Heading level nhỏ nhất xuất hiện trong document.
   * Dùng làm base để tính indent: entry->indentFrom($toc->baseLevel()).
   *
   * Ví dụ: document chỉ có H2, H3 → baseLevel = 2
   *   H2 → indent 0, H3 → indent 1
   *
   * Ví dụ: document có H1, H3 → baseLevel = 1
   *   H1 → indent 0, H3 → indent 2
   */
  public function baseLevel(): int
  {
    if (empty($this->tocEntries)) {
      return 2; // default
    }

    return min(array_map(fn(TocEntry $e) => $e->level, $this->tocEntries));
  }

  public function toArray(): array
  {
    return [
      'html' => $this->html,
      'toc' => $this->tocToArray(),
    ];
  }

  public function tocToArray(): array
  {
    return array_map(fn(TocEntry $e) => [
      'level' => $e->level,
      'plainText' => $e->plainText,
      'anchorId' => $e->anchorId,
      'fragment' => $e->fragment(),
    ], $this->tocEntries);
  }
}

/**
 * Một mục trong Table of Contents, trích xuất từ heading block.
 *
 * Dùng trong view:
 *   @foreach ($result->toc as $entry)
 *     <li style="padding-left: {{ ($entry->level - 2) * 16 }}px">
 *       <a href="#{{ $entry->anchorId }}">{{ $entry->plainText }}</a>
 *     </li>
 *   @endforeach
 */
final class TocEntry
{
  public function __construct(
    /** Level của heading (2, 3, 4...) — dùng để indent trong ToC */
    public int $level,

    /** Plain text của heading — không chứa HTML tag, dùng để hiển thị */
    public string $plainText,

    /**
     * anchor_id của block — fragment URL để scroll đến (#section-abc).
     * Rỗng nếu heading không có anchor_id trong data.meta.
     * Khi rỗng, entry vẫn được giữ lại trong ToC nhưng không có href.
     */
    public string $anchorId,

    /** Block ID gốc — dùng khi cần reference ngược lại block */
    public string $blockId,
  ) {
  }

  /**
   * Có thể tạo link đến heading này không.
   * False khi anchor_id rỗng — renderer ToC nên render <span> thay vì <a>.
   */
  public function hasAnchor(): bool
  {
    return $this->anchorId !== '';
  }

  /**
   * Fragment URL (#anchor_id) — dùng trong href attribute.
   * Trả về rỗng nếu không có anchor.
   */
  public function fragment(): string
  {
    return $this->anchorId !== '' ? "#{$this->anchorId}" : '';
  }

  /**
   * Indent level tính từ heading level nhỏ nhất trong document.
   * Cần normalize từ bên ngoài (RenderResult::normalizeToc()).
   *
   * Ví dụ: document chỉ có H2, H3 → indent 0, 1.
   * Document có H1, H2, H3 → indent 0, 1, 2.
   */
  public function indentFrom(int $baseLevel): int
  {
    return max(0, $this->level - $baseLevel);
  }
}
