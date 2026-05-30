<?php
namespace App\Editor;

use App\Editor\Blocks\AbstractBlockRenderer;
use App\Editor\Blocks\HeadingRenderer;
use App\Editor\Blocks\ImageRenderer;
use App\Editor\Blocks\ListRenderer;
use App\Editor\Blocks\ParagraphRenderer;
use App\Editor\Blocks\QuoteRenderer;
use App\Editor\Blocks\TableRenderer;

final class BlockRenderer
{
    /**
     * Registry: block type → renderer instance.
     * Lazy-init lần đầu gọi, sau đó reuse.
     *
     * @var array<string, AbstractBlockRenderer>|null
     */
    private static ?array $registry = null;

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Render từ JSON string (raw từ DB).
     *
     * @param  string|null  $json          — Giá trị json từ DB (Phải có blocks, meta)
     * @param  array        $options       — Tùy chọn render (xem renderBlocks())
     * @return string                      — HTML string, safe to echo
     */
    public static function fromJson(?string $contentJson, array $options = []): string
    {
        if ($contentJson === null || $contentJson === '') {
          return '';
        }

        $payload = json_decode($contentJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        return self::fromArray($payload, $options);
    }

    /**
     * Render từ array đã decode (dùng khi đã json_decode trước đó).
     *
     * @param  array|null  $payload
     * @param  array       $options
     * @return string
     */
    public static function fromArray(?array $payload, array $options = []): string
    {
      if (empty($payload)) {
        return '';
      }

      try {
        $normalized = BlockValidator::validatePayload($payload);
      } catch (BlockValidationException $e) {
        return '';
      }

      return self::renderBlocks($normalized['blocks'], $options);
    }

    // ─── Core render pipeline ─────────────────────────────────────────────────

    /**
     * Render mảng blocks đã normalized thành HTML.
     *
     * Options:
     *   'wrapper'       => string|false   — CSS class của wrapper div (false = không wrap)
     *   'eager_first'   => bool           — Render ảnh đầu tiên với loading="eager" (LCP)
     *
     * @param  array  $blocks    Normalized blocks từ BlockValidator
     * @param  array  $options
     */
    private static function renderBlocks(array $blocks, array $options = []): string
    {
      $registry = self::getRegistry();
      $html     = '';

      foreach ($blocks as $block) {
        $type     = $block['type'];
        $renderer = $registry[$type] ?? null;

        if ($renderer === null) {
          // Type không có renderer → bỏ qua (forward compatible)
          continue;
        }

        try {
          $html .= $renderer->render($block);
        } catch (\Throwable $e) {
          // Một block lỗi không được phá cả trang
          continue;
        }
      }

      // Wrap nếu cần
      $wrapperClass = $options['wrapper'] ?? 'be-content';
      if ($wrapperClass !== false && $wrapperClass !== '') {
        $safeClass = htmlspecialchars((string) $wrapperClass, ENT_QUOTES, 'UTF-8');
        $html = "<div class=\"{$safeClass}\">{$html}</div>";
      }

      return $html;
    }

    // ─── Registry ─────────────────────────────────────────────────────────────

    /**
     * Build registry lần đầu, reuse từ lần sau.
     * Không dùng DI container để giữ class này zero-dependency.
     *
     * @return array<string, AbstractBlockRenderer>
     */
    private static function getRegistry(): array
    {
      if (self::$registry !== null) {
        return self::$registry;
      }

      self::$registry = [
        'blocks/heading'   => new HeadingRenderer(),
        'blocks/paragraph' => new ParagraphRenderer(),
        'blocks/quote'     => new QuoteRenderer(),
        'blocks/list'      => new ListRenderer(),
        'blocks/image'     => new ImageRenderer(),
        'blocks/table'     => new TableRenderer(),
      ];

      return self::$registry;
    }

    /**
     * Đăng ký thêm renderer tùy chỉnh (dùng cho extension/plugin).
     * Gọi trước lần render đầu tiên (ví dụ trong AppServiceProvider).
     *
     * @param  string                 $type      Ví dụ 'blocks/video'
     * @param  AbstractBlockRenderer  $renderer
     */
    public static function register(string $type, AbstractBlockRenderer $renderer): void
    {
      self::getRegistry(); // Đảm bảo registry đã init
      self::$registry[$type] = $renderer;
    }
}
