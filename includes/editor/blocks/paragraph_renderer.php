<?php
namespace App\Editor\Blocks;

/**
 * Render block type: blocks/paragraph
 *
 * Meta:
 *   align : string ('left'|'center'|'right', default 'left')
 *
 * Output: <p class="be-paragraph ...">...rich_text...</p>
 */
final class ParagraphRenderer extends AbstractBlockRenderer
{
    private const ALLOWED_ALIGNS = ['left', 'center', 'right'];

    public function render(array $block): string
    {
        $content = $this->renderRichText($block);

        // Paragraph rỗng: render tag rỗng để giữ spacing layout đúng
        // (Trình soạn thảo có thể có paragraph rỗng dùng làm spacer)
        $align = (string) $this->meta($block, 'align', 'left');
        $align = in_array($align, self::ALLOWED_ALIGNS, true) ? $align : 'left';

        $class = $this->classAttr([
            'be-paragraph',
            $align !== 'left' ? "be-paragraph--{$align}" : '',
        ]);

        return "<p class=\"{$class}\">{$content}</p>";
    }
}
