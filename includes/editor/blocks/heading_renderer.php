<?php
namespace App\Editor\Blocks;

/**
 * Render block type: blocks/heading
 *
 * Meta:
 *   level : int   (2–4, default 2) — JS map: display H1→level 2, H2→3, H3→4
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

        // Nếu không có nội dung, không render tag rỗng (tốt cho SEO)
        if ($content === '') {
            return '';
        }

        $class = $this->classAttr([
            'be-heading',
            $align !== 'left' ? "be-heading--{$align}" : '',
        ]);

        $anchorId = $this->esc($block['data']['meta']['anchor_id'] ?? '');
        $idAttr   = $anchorId !== '' ? " id=\"{$anchorId}\"" : '';

        return "<h{$level} class=\"{$class}\"{$idAttr}>{$content}</h{$level}>";
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
