<?php
namespace App\Editor;

use App\Editor\Blocks\AbstractBlockRenderer;
use App\Editor\Blocks\HeadingRenderer;
use App\Editor\Blocks\ImageRenderer;
use App\Editor\Blocks\ListRenderer;
use App\Editor\Blocks\ParagraphRenderer;
use App\Editor\Blocks\QuoteRenderer;
use App\Editor\Blocks\TableRenderer;

/**
 * Entry point duy nhất cho việc biên dịch content_json → HTML + ToC.
 *
 * ─── Hai mode sử dụng ─────────────────────────────────────────────────────
 *
 *   // Mode 1: Chỉ cần HTML (backward compatible)
 *   {!! BlockRenderer::fromJson($post->content_json) !!}
 *
 *   // Mode 2: Cần cả HTML lẫn ToC - một lần pass duy nhất
 *   $result = BlockRenderer::compile($post->content_json);
 *   // $result->html        → string, safe to echo
 *   // $result->entries()   → TocEntry[]
 *   // $result->hasToc()    → bool
 *   // $result->baseLevel() → int, để tính indent
 *
 * ─── Luồng xử lý (compile) ────────────────────────────────────────────────
 *
 *   JSON string
 *      ↓ json_decode
 *      ↓ BlockValidator::validatePayload()
 *      ↓ foreach block:
 *           renderer->render($block)          → cộng dồn vào $html
 *           renderer->extractTocEntry($block) → collect vào $tocEntries[]
 *      ↓ RenderResult($html, $tocEntries)
 *
 * Loop chỉ chạy một lần - không parse lại để lấy ToC.
 */
final class BlockRenderer
{
  /** @var array<string, AbstractBlockRenderer>|null */
  private static ?array $registry = null;

  // ─── Public API ───────────────────────────────────────────────────────────

  /**
   * Compile content_json → RenderResult (HTML + ToC).
   * Đây là method chính - fromJson/fromArray là shortcut trên nó.
   *
   * @param  string|null  $json
   * @param  array        $options  Xem processBlocks()
   */
  public static function compile(?string $json, array $options = []): RenderResult
  {
    if ($json === null || $json === '') {
      return new RenderResult('', []);
    }

    $payload = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      return new RenderResult('', []);
    }

    return self::compileArray($payload, $options);
  }

  /**
   * Compile từ array đã decode.
   *
   * @param  array|null  $payload
   * @param  array       $options
   */
  public static function compileArray(?array $payload, array $options = []): RenderResult
  {
    if (empty($payload)) {
      return new RenderResult('', []);
    }

    try {
      $normalized = BlockValidator::validatePayload($payload);
    } catch (BlockValidationException $e) {
      // logger()->warning('[BlockRenderer] Validation failed: ' . $e->getMessage());
      return new RenderResult('', []);
    }

    return self::processBlocks($normalized['blocks'], $options);
  }

  /**
   * Shortcut trả về chỉ HTML string - backward compatible với code cũ.
   *
   * @param  string|null  $json
   * @param  array        $options
   */
  public static function fromJson(?string $json, array $options = []): string
  {
    return self::compile($json, $options)->html;
  }

  /**
   * Shortcut trả về chỉ HTML string từ array.
   *
   * @param  array|null  $payload
   * @param  array       $options
   */
  public static function fromArray(?array $payload, array $options = []): string
  {
    return self::compileArray($payload, $options)->html;
  }

  // ─── Core pipeline ────────────────────────────────────────────────────────

  /**
   * Loop duy nhất: render HTML + collect ToC entries cùng lúc.
   *
   * Options:
   *   'wrapper' => string|false  - CSS class của wrapper div (false = không wrap)
   *
   * @param  array  $blocks   Normalized blocks từ BlockValidator
   * @param  array  $options
   */
  private static function processBlocks(array $blocks, array $options = []): RenderResult
  {
    $registry = self::getRegistry();
    $html = '';
    $tocEntries = [];
    $imageCount = 0;
    $usedAnchorIds = self::collectCustomAnchorIds($blocks);

    foreach ($blocks as $block) {
      $type = $block['type'];
      $renderer = $registry[$type] ?? null;

      if ($renderer === null) {
        continue;
      }

      try {
        if ($type === 'blocks/heading' && trim((string) ($block['data']['meta']['anchor_id'] ?? '')) === '') {
          $block['data']['meta']['anchor_id'] = self::generateHeadingAnchorId($block, $usedAnchorIds);
        }

        // Track first image for LCP optimization
        if ($type === 'blocks/image') {
          $imageCount++;
          if ($imageCount === 1) {
            $block['isFirstImage'] = true;
          }
        }

        // Render HTML
        $blockHtml = $renderer->render($block);

        if ($blockHtml === '') {
          continue;
        }

        $id = htmlspecialchars($block['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $typeAttr = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');

        $html .= "<div class=\"be-block\" data-be-block-id=\"{$id}\" data-be-block-type=\"{$typeAttr}\">"
          . $blockHtml
          . '</div>';

        // Collect ToC entry nếu block này có (chỉ Heading trả về non-null)
        $tocEntry = $renderer->extractTocEntry($block);
        if ($tocEntry !== null) {
          $tocEntries[] = $tocEntry;
        }
      } catch (\Throwable $e) {
        // logger()->error("[BlockRenderer] block type={$type}: " . $e->getMessage());
        continue;
      }
    }

    $wrapperClass = $options['wrapper'] ?? 'be-content';
    if ($wrapperClass !== false && $wrapperClass !== '') {
      $safeClass = htmlspecialchars((string) $wrapperClass, ENT_QUOTES, 'UTF-8');
      $html = "<div class=\"{$safeClass}\">{$html}</div>";
    }

    return new RenderResult($html, $tocEntries);
  }

  /** @return array<string, true> */
  private static function collectCustomAnchorIds(array $blocks): array
  {
    $used = [];

    foreach ($blocks as $block) {
      if (($block['type'] ?? '') !== 'blocks/heading') {
        continue;
      }

      $anchorId = trim((string) ($block['data']['meta']['anchor_id'] ?? ''));
      if ($anchorId !== '') {
        $used[$anchorId] = true;
      }
    }

    return $used;
  }

  /** @param array<string, true> $usedAnchorIds */
  private static function generateHeadingAnchorId(array $block, array &$usedAnchorIds): string
  {
    $plainText = trim(implode('', array_map(
      fn($segment) => is_string($segment['text'] ?? null) ? $segment['text'] : '',
      $block['data']['rich_text'] ?? [],
    )));

    $base = \generateSlug($plainText);

    if ($base === '') {
      $blockId = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) ($block['id'] ?? '')), '-');
      $base = $blockId !== '' ? "heading-{$blockId}" : 'heading';
    }

    $anchorId = $base;
    $suffix = 2;
    while (isset($usedAnchorIds[$anchorId])) {
      $anchorId = "{$base}-{$suffix}";
      $suffix++;
    }

    $usedAnchorIds[$anchorId] = true;
    return $anchorId;
  }

  // ─── Registry ─────────────────────────────────────────────────────────────

  /** @return array<string, AbstractBlockRenderer> */
  private static function getRegistry(): array
  {
    if (self::$registry !== null) {
      return self::$registry;
    }

    self::$registry = [
      'blocks/heading' => new HeadingRenderer(),
      'blocks/paragraph' => new ParagraphRenderer(),
      'blocks/quote' => new QuoteRenderer(),
      'blocks/list' => new ListRenderer(),
      'blocks/image' => new ImageRenderer(),
      'blocks/table' => new TableRenderer(),
    ];

    return self::$registry;
  }

  /**
   * Đăng ký thêm renderer tùy chỉnh.
   * Gọi trong AppServiceProvider::boot().
   */
  public static function register(string $type, AbstractBlockRenderer $renderer): void
  {
    self::getRegistry();
    self::$registry[$type] = $renderer;
  }
}
