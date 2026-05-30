<?php
namespace App\Editor\Blocks;

/**
 * Render block type: blocks/quote
 *
 * Meta:
 *   citation : string (plain text, không phải rich text — sync trực tiếp từ JS)
 *
 * Output:
 *   <blockquote class="be-quote">
 *     <p class="be-quote-content">...rich_text...</p>
 *     <cite class="be-quote-citation">...citation...</cite>   (nếu có)
 *   </blockquote>
 */
final class QuoteRenderer extends AbstractBlockRenderer
{
    public function render(array $block): string
    {
        $content  = $this->renderRichText($block);
        $citation = trim((string) $this->meta($block, 'citation', ''));

        $citationHtml = '';
        if ($citation !== '') {
            $citationHtml = '<cite class="be-quote-citation">'
                . $this->esc($citation)
                . '</cite>';
        }

        return <<<HTML
            <blockquote class="be-quote">
                <p class="be-quote-content">{$content}</p>
                {$citationHtml}
            </blockquote>
            HTML;
    }
}
