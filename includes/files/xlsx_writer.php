<?php

namespace App\Core\Files;

use ZipArchive;

/**
 * Trình tạo file .xlsx cơ bản bằng PHP thuần.
 * Hỗ trợ export dữ liệu bảng, định dạng header (in đậm, màu nền), đóng băng dòng đầu, 
 * sheet metadata, và tự động nhận diện kiểu dữ liệu (số, chuỗi).
 */
class XlsxWriter
{
  private array $columns = [];
  private array $rows = [];
  private string $sheetName = 'Data';
  private array $metadata = [];
  private array $styles = [
    'header_bg' => 'FF6C757D',
    'header_color' => 'FFFFFFFF',
    'border' => true
  ];

  public function setColumns(array $columns): self
  {
    $this->columns = $columns;
    return $this;
  }

  public function addRows(array $rows): self
  {
    $this->rows = array_merge($this->rows, $rows);
    return $this;
  }

  /**
   * Đặt metadata hiển thị phía trên bảng dữ liệu.
   * @param array $metadata Mảng các dòng metadata, mỗi phần tử là một chuỗi.
   *   Ví dụ: ['Danh sách sinh viên đợt 5', 'Ngày xuất: 12/06/2026', 'Bộ lọc: Lớp = CNTT1']
   */
  public function setMetadata(array $metadata): self
  {
    $this->metadata = $metadata;
    return $this;
  }

  public function setHeaderStyle(string $bgHex, string $colorHex = 'FFFFFFFF', bool $border = true): self
  {
    $this->styles['header_bg'] = ltrim($bgHex, '#');
    $this->styles['header_color'] = ltrim($colorHex, '#');
    $this->styles['border'] = $border;
    return $this;
  }

  public function save(string $filename): bool
  {
    $zip = new ZipArchive();
    if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
      return false;
    }

    $zip->addFromString('[Content_Types].xml', $this->buildContentTypes());
    $zip->addFromString('_rels/.rels', $this->buildRels());
    $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());
    $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());
    $zip->addFromString('xl/styles.xml', $this->buildStyles());
    $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildSheet());
    $zip->close();

    return true;
  }

  public function output(): string
  {
    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
    if ($this->save($tmpFile)) {
      $content = file_get_contents($tmpFile);
      unlink($tmpFile);
      return $content;
    }
    return '';
  }

  private function buildContentTypes(): string
  {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
  }

  private function buildRels(): string
  {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
  }

  private function buildWorkbook(): string
  {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . htmlspecialchars($this->sheetName) . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
  }

  private function buildWorkbookRels(): string
  {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
  }

  /**
   * Build stylesheet XML.
   * Style indexes:
   *   0 = default cell
   *   1 = table header (bold, bg color, border)
   *   2 = bordered data cell
   *   3 = metadata title (bold, larger font, no border)
   *   4 = metadata info (italic, muted color, no border)
   */
  private function buildStyles(): string
  {
    $bg = $this->styles['header_bg'];
    $color = $this->styles['header_color'];
    $borderXml = $this->styles['border'] ? '<border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/></border>' : '<border><left/><right/><top/><bottom/></border>';

    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="4">
    <font><sz val="11"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="' . $color . '"/><name val="Arial"/></font>
    <font><b/><sz val="14"/><name val="Arial"/></font>
    <font><i/><sz val="10"/><color rgb="FF888888"/><name val="Arial"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF' . $bg . '"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/></border>
    ' . $borderXml . '
  </borders>
  <cellXfs count="5">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1">
      <alignment vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    <xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>
  </cellXfs>
</styleSheet>';
  }

  private function buildSheet(): string
  {
    // Tính số dòng metadata (nếu có) + 1 dòng trống phân cách
    $metaRowCount = !empty($this->metadata) ? count($this->metadata) + 1 : 0;

    // Dòng header nằm ở vị trí metaRowCount + 1
    $headerRow = $metaRowCount + 1;
    $freezeRow = $headerRow + 1; // Freeze ngay dưới header
    $freezeCell = 'A' . $freezeRow;

    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetViews>
    <sheetView tabSelected="1" workbookViewId="0">
      <pane ySplit="' . $headerRow . '" topLeftCell="' . $freezeCell . '" activePane="bottomLeft" state="frozen"/>
    </sheetView>
  </sheetViews>';

    // Auto-width columns approximation
    if (!empty($this->columns)) {
      $xml .= '<cols>';
      $colIndex = 1;
      foreach ($this->columns as $col) {
        $width = min(50, max(15, mb_strlen((string)$col) * 1.5));
        $xml .= '<col min="' . $colIndex . '" max="' . $colIndex . '" width="' . $width . '" customWidth="1"/>';
        $colIndex++;
      }
      $xml .= '</cols>';
    }

    $xml .= '<sheetData>';

    $rowIndex = 1;

    // Build Metadata rows
    if (!empty($this->metadata)) {
      $isTitle = true;
      foreach ($this->metadata as $metaLine) {
        $cellRef = $this->getCellRef(1, $rowIndex);
        $styleId = $isTitle ? '3' : '4'; // 3 = title bold, 4 = info italic
        $xml .= '<row r="' . $rowIndex . '">';
        $xml .= '<c r="' . $cellRef . '" t="inlineStr" s="' . $styleId . '"><is><t>' . htmlspecialchars((string)$metaLine) . '</t></is></c>';
        $xml .= '</row>';
        $rowIndex++;
        $isTitle = false;
      }

      // Dòng trống phân cách
      $xml .= '<row r="' . $rowIndex . '"/>';
      $rowIndex++;
    }

    // Build Header
    if (!empty($this->columns)) {
      $xml .= '<row r="' . $rowIndex . '" customHeight="1" ht="25">';
      $colIndex = 1;
      foreach ($this->columns as $col) {
        $cellRef = $this->getCellRef($colIndex, $rowIndex);
        $xml .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t>' . htmlspecialchars((string)$col) . '</t></is></c>';
        $colIndex++;
      }
      $xml .= '</row>';
      $rowIndex++;
    }

    // Build Rows
    foreach ($this->rows as $row) {
      $xml .= '<row r="' . $rowIndex . '">';
      $colIndex = 1;
      foreach ($row as $val) {
        $cellRef = $this->getCellRef($colIndex, $rowIndex);
        $styleId = $this->styles['border'] ? '2' : '0';

        if (is_numeric($val) && !is_string($val)) {
          $xml .= '<c r="' . $cellRef . '" s="' . $styleId . '"><v>' . $val . '</v></c>';
        } elseif ($val === null || $val === '') {
          $xml .= '<c r="' . $cellRef . '" s="' . $styleId . '"/>';
        } else {
          $xml .= '<c r="' . $cellRef . '" t="inlineStr" s="' . $styleId . '"><is><t>' . htmlspecialchars((string)$val) . '</t></is></c>';
        }
        $colIndex++;
      }
      $xml .= '</row>';
      $rowIndex++;
    }

    $xml .= '</sheetData></worksheet>';
    return $xml;
  }

  private function getCellRef(int $colIndex, int $rowIndex): string
  {
    $letters = '';
    while ($colIndex > 0) {
      $mod = ($colIndex - 1) % 26;
      $letters = chr(65 + $mod) . $letters;
      $colIndex = (int)(($colIndex - $mod) / 26);
    }
    return $letters . $rowIndex;
  }
}
