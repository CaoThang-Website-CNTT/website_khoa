<?php
namespace App\Editor\Blocks;

use App\Editor\RichTextRenderer;

/**
 * Render block type: blocks/image
 *
 * Meta:
 *   url     : string  (URL ảnh, rỗng = block chưa có ảnh → không render)
 *   alt     : string  (alt text cho SEO/accessibility)
 *   caption : RichSegment[]  (rich text của caption)
 *   align   : 'left'|'center'|'right'  (default 'center')
 *   width   : string  ('25%'|'50%'|'75%'|'100%', default '100%')
 *
 * Output (mirror #renderResolved() trong image.js):
 *   <figure class="be-image be-image-align--{align}">
 *     <div class="be-image-wrapper">
 *       <img src="..." alt="..." loading="lazy" style="width: ...">
 *     </div>
 *     <figcaption class="be-image-caption">...caption rich text...</figcaption>
 *   </figure>
 *
 * SEO notes:
 *   - loading="lazy" cho ảnh không phải ảnh đầu tiên
 *   - Caller có thể truyền $isFirstImage = true để dùng loading="eager"
 *     (above-the-fold LCP optimization)
 */
final class ImageRenderer extends AbstractBlockRenderer
{
    private const ALLOWED_ALIGNS = ['left', 'center', 'right'];

    /** Tập hợp width hợp lệ từ inspector controls bên JS */
    private const ALLOWED_WIDTHS = ['25%', '50%', '75%', '100%'];

    public function render(array $block): string
    {
        $url = trim((string) $this->meta($block, 'url', ''));

        // Block chưa có ảnh (placeholder state) → không render gì cả
        if ($url === '') {
            return '';
        }

        // Validate URL — chỉ render http/https/relative
        if (!$this->isSafeUrl($url)) {
            return '';
        }

        $alt     = $this->esc($this->meta($block, 'alt', ''));
        $align   = $this->resolveAlign($this->meta($block, 'align', 'center'));
        $width   = $this->resolveWidth($this->meta($block, 'width', '100%'));
        $caption = $this->meta($block, 'caption', []);

        $captionHtml = '';
        if (is_array($caption) && !empty($caption)) {
            $captionContent = RichTextRenderer::render($caption);
            if ($captionContent !== '') {
                $captionHtml = "<figcaption class=\"be-image-caption\">{$captionContent}</figcaption>";
            }
        }

        $safeUrl    = $this->esc($url);
        $styleWidth = $this->esc($width);

        // loading strategy: eager cho ảnh đầu (LCP), lazy cho phần còn lại.
        // Mặc định lazy; caller có thể override qua meta hoặc xử lý ở BlockRenderer.
        $loading = 'lazy';

        return <<<HTML
            <figure class="be-image be-image-align--{$align}">
                <div class="be-image-wrapper">
                    <img src="{$safeUrl}" alt="{$alt}" loading="{$loading}" style="width:{$styleWidth}">
                </div>
                {$captionHtml}
            </figure>
            HTML;
    }

    private function resolveAlign(mixed $align): string
    {
        $align = (string) $align;
        return in_array($align, self::ALLOWED_ALIGNS, true) ? $align : 'center';
    }

    private function resolveWidth(mixed $width): string
    {
        $width = (string) $width;

        // Nếu không có % hoặc px suffix, thêm px (mirror JS logic)
        if (!str_contains($width, '%') && !str_contains($width, 'px')) {
            $width .= 'px';
        }

        // Validate whitelist — nếu không khớp, fallback về 100%
        return in_array($width, self::ALLOWED_WIDTHS, true) ? $width : '100%';
    }

    private function isSafeUrl(string $url): bool
    {
        return (bool) preg_match('#^(https?://|/)[^\s]*$#i', $url);
    }
}
