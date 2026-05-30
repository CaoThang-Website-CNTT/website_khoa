<?php
namespace App\Editor\Blocks;

use App\Editor\RichTextRenderer;

/**
 * Render block type: blocks/table
 *
 * Meta:
 *   hasHeader : bool    (default true)
 *   rows      : array   Mảng 2 chiều — rows[rowIndex][colIndex] = RichSegment[]|string
 *
 * Output (mirror #buildTable() trong table.js):
 *   <div class="be-table-wrapper">
 *     <div class="be-table-scroll">
 *       <table class="be-table">
 *         <thead>...</thead>   (nếu hasHeader = true)
 *         <tbody>...</tbody>
 *       </table>
 *     </div>
 *   </div>
 *
 * SEO notes:
 *   - <thead>/<th> giúp crawler hiểu cấu trúc bảng
 *   - scope="col" trên <th> cho screen reader
 */
final class TableRenderer extends AbstractBlockRenderer
{
    public function render(array $block): string
    {
        $rows      = $this->meta($block, 'rows', []);
        $hasHeader = (bool) $this->meta($block, 'hasHeader', true);

        if (!is_array($rows) || empty($rows)) {
            return '';
        }

        $thead = '';
        $tbody = '';

        if ($hasHeader && !empty($rows[0])) {
            $thead = '<thead>' . $this->buildRow($rows[0], 'th') . '</thead>';
            $bodyRows = array_slice($rows, 1);
        } else {
            $bodyRows = $rows;
        }

        foreach ($bodyRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $tbody .= $this->buildRow($row, 'td');
        }

        if ($tbody !== '') {
            $tbody = "<tbody>{$tbody}</tbody>";
        }

        $tableHtml = "<table class=\"be-table\">{$thead}{$tbody}</table>";

        return <<<HTML
            <div class="be-table-wrapper">
                <div class="be-table-scroll">
                    {$tableHtml}
                </div>
            </div>
            HTML;
    }

    /**
     * Build một <tr> từ mảng cell data.
     *
     * @param  array   $cellsData  Mảng các cell — mỗi cell là RichSegment[] hoặc string
     * @param  string  $cellTag    'th' hoặc 'td'
     */
    private function buildRow(array $cellsData, string $cellTag): string
    {
        $cells = '';

        foreach ($cellsData as $cellValue) {
            $content = $this->renderCell($cellValue);
            $scopeAttr = $cellTag === 'th' ? ' scope="col"' : '';
            $cells .= "<{$cellTag}{$scopeAttr}>{$content}</{$cellTag}>";
        }

        return "<tr>{$cells}</tr>";
    }

    /**
     * Render cell content — hỗ trợ cả RichSegment[] (v2) lẫn string (legacy).
     *
     * @param  mixed  $cellValue
     */
    private function renderCell(mixed $cellValue): string
    {
        if (is_array($cellValue)) {
            // RichSegment[] — render inline HTML
            return RichTextRenderer::render($cellValue);
        }

        if (is_string($cellValue)) {
            return $this->esc($cellValue);
        }

        return '';
    }
}
