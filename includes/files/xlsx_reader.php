<?php

/**
 * XlsxReader
 *
 * Low-level, pure-PHP reader for .xlsx files.
 * Parses the OOXML ZIP container (sharedStrings, workbook rels, sheet XML)
 * and exposes a simple cell-access API to callers.
 *
 * No external dependencies — requires only ext-zip + ext-simplexml (both
 * are bundled with every standard PHP 7.4+ distribution).
 *
 * This class knows nothing about the business meaning of the data; it only
 * understands the XLSX file format.
 */
class XlsxReader
{
  // -------------------------------------------------------------------------
  // State
  // -------------------------------------------------------------------------

  /** @var array<string, scalar>  "row:col" (1-based) → scalar cell value */
  private array $cells = [];

  /** @var int  Highest row index found in the sheet */
  private int $maxRow = 0;

  /** @var int  Highest column index found in the sheet */
  private int $maxCol = 0;

  // -------------------------------------------------------------------------
  // Factory
  // -------------------------------------------------------------------------

  /**
   * Open an XLSX file and return a ready-to-query XlsxReader instance.
   *
   * @throws RuntimeException on missing file, corrupt ZIP, or missing XML parts.
   */
  public static function open(string $filePath): self
  {
    $reader = new self();
    $reader->load($filePath);
    return $reader;
  }

  // -------------------------------------------------------------------------
  // Public query API
  // -------------------------------------------------------------------------

  /**
   * Return the scalar value of a single cell, or $default if the cell is
   * empty / out of range.
   *
   * @param int   $row      1-based row index
   * @param int   $col      1-based column index
   * @param mixed $default  Returned when the cell has no value
   * @return scalar|mixed
   */
  public function cell(int $row, int $col, mixed $default = null): mixed
  {
    return $this->cells["{$row}:{$col}"] ?? $default;
  }

  /**
   * Return the scalar value of a cell addressed by its A1-style reference
   * (e.g. "E4", "N7"), or $default if empty.
   *
   * @param string $ref     Cell reference like "A1", "E4", "AA10"
   * @param mixed  $default
   * @return scalar|mixed
   */
  public function cellByRef(string $ref, mixed $default = null): mixed
  {
    [$row, $col] = self::refToRowCol($ref);
    return $this->cell($row, $col, $default);
  }

  /**
   * Iterate over all non-empty rows in the given range.
   *
   * Yields: int $rowIndex => array<int colIndex, scalar value>
   * Only columns that actually have values are included per row.
   *
   * @param int      $startRow  First row to include (1-based, default 1)
   * @param int|null $endRow    Last row to include (null = last row in sheet)
   * @return iterable<int, array<int, scalar>>
   */
  public function rows(int $startRow = 1, ?int $endRow = null): iterable
  {
    $endRow ??= $this->maxRow;

    for ($r = $startRow; $r <= $endRow; $r++) {
      $row = [];
      for ($c = 1; $c <= $this->maxCol; $c++) {
        $key = "{$r}:{$c}";
        if (isset($this->cells[$key])) {
          $row[$c] = $this->cells[$key];
        }
      }
      if (!empty($row)) {
        yield $r => $row;
      }
    }
  }

  /**
   * Return the index of the last row that contains any data.
   */
  public function maxRow(): int
  {
    return $this->maxRow;
  }

  /**
   * Return the index of the last column that contains any data.
   */
  public function maxCol(): int
  {
    return $this->maxCol;
  }

  // -------------------------------------------------------------------------
  // Static helpers (public so callers can reuse them)
  // -------------------------------------------------------------------------

  /**
   * Convert an A1-style cell reference to [row, col] (both 1-based).
   *
   * Examples:  "A1" → [1, 1],  "E4" → [4, 5],  "AA10" → [10, 27]
   *
   * @return array{0: int, 1: int}
   */
  public static function refToRowCol(string $ref): array
  {
    if (!preg_match('/^([A-Za-z]+)(\d+)$/', $ref, $m)) {
      throw new InvalidArgumentException("Invalid cell reference: {$ref}");
    }
    return [(int) $m[2], self::colLettersToIndex(strtoupper($m[1]))];
  }

  /**
   * Convert column letter(s) to a 1-based column index.
   *
   * A=1, B=2 … Z=26, AA=27, AB=28 …
   */
  public static function colLettersToIndex(string $letters): int
  {
    $letters = strtoupper($letters);
    $col = 0;
    $len = strlen($letters);
    for ($i = 0; $i < $len; $i++) {
      $col = $col * 26 + (ord($letters[$i]) - ord('A') + 1);
    }
    return $col;
  }

  // -------------------------------------------------------------------------
  // Private loading pipeline
  // -------------------------------------------------------------------------

  private function load(string $filePath): void
  {
    if (!file_exists($filePath)) {
      throw new RuntimeException("File not found: {$filePath}");
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
      throw new RuntimeException("Cannot open XLSX (not a valid ZIP): {$filePath}");
    }

    try {
      $sharedStrings = $this->loadSharedStrings($zip);
      $sheetXml = $this->loadFirstSheetXml($zip);
    } finally {
      $zip->close();
    }

    $this->cells = $this->parseSheetXml($sheetXml, $sharedStrings);
  }

  // ·····················································
  // Step 1: shared strings
  // ·····················································

  /**
   * Parse xl/sharedStrings.xml into a flat indexed array.
   *
   * Handles both plain-text (<si><t>…</t></si>) and rich-text
   * (<si><r><t>…</t></r>…</si>) entries.
   *
   * Returns an empty array when the file has no sharedStrings.xml (valid for
   * workbooks that contain only numeric / inline-string data).
   *
   * @return string[]
   */
  private function loadSharedStrings(ZipArchive $zip): array
  {
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml === false) {
      return [];   // sharedStrings.xml is optional
    }

    $sxe = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($sxe === false) {
      throw new RuntimeException("Failed to parse xl/sharedStrings.xml.");
    }

    $strings = [];
    foreach ($sxe->si as $si) {
      if (isset($si->t)) {
        // Plain text
        $strings[] = (string) $si->t;
      } else {
        // Rich text: concatenate all <r><t> runs
        $parts = [];
        foreach ($si->r as $r) {
          $parts[] = (string) $r->t;
        }
        $strings[] = implode('', $parts);
      }
    }

    return $strings;
  }

  // ·····················································
  // Step 2: resolve first sheet
  // ·····················································

  /**
   * Locate and return the raw XML of the workbook's first worksheet.
   *
   * Reads xl/_rels/workbook.xml.rels to discover the canonical path rather
   * than assuming "sheet1.xml" — some tools generate "sheet0.xml" or other
   * names.
   */
  private function loadFirstSheetXml(ZipArchive $zip): string
  {
    $path = $this->resolveFirstSheetPath($zip);
    $content = $zip->getFromName($path);
    if ($content === false) {
      throw new RuntimeException("Worksheet XML not found at resolved path: {$path}");
    }
    return $content;
  }

  /**
   * Return the ZIP-internal path to the first worksheet by reading the
   * workbook relationship file.
   *
   * Falls back to probing common paths when the .rels file is absent.
   */
  private function resolveFirstSheetPath(ZipArchive $zip): string
  {
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

    if ($relsXml === false) {
      // Fallback: probe known common names
      foreach (['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet0.xml'] as $candidate) {
        if ($zip->getFromName($candidate) !== false) {
          return $candidate;
        }
      }
      throw new RuntimeException(
        "Cannot locate worksheet: xl/_rels/workbook.xml.rels is missing."
      );
    }

    $sxe = simplexml_load_string($relsXml, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($sxe === false) {
      throw new RuntimeException("Failed to parse xl/_rels/workbook.xml.rels.");
    }

    foreach ($sxe->Relationship as $rel) {
      if (str_ends_with((string) $rel['Type'], '/worksheet')) {
        // Target can be absolute  (/xl/worksheets/sheet0.xml)
        // or relative             (worksheets/sheet1.xml)
        $target = (string) $rel['Target'];
        return str_starts_with($target, '/')
          ? ltrim($target, '/')          // strip leading slash → ZIP path
          : 'xl/' . $target;             // prepend xl/ for relative targets
      }
    }

    throw new RuntimeException(
      "No worksheet relationship found in xl/_rels/workbook.xml.rels."
    );
  }

  // ·····················································
  // Step 3: parse sheet XML into cell map
  // ·····················································

  /**
   * Parse a worksheet XML string and build the internal cell map.
   *
   * Cell map format:  "rowIndex:colIndex" (both 1-based) → scalar value
   *
   * Merged cells in XLSX only store a value in the top-left anchor cell;
   * the remaining cells in the merged region simply have no <c> element —
   * so no special merge handling is required here.
   *
   * Cell type codes:
   *   s          → shared-string index (look up in $sharedStrings)
   *   inlineStr  → <is><t> inline string
   *   b          → boolean  (0 / 1)
   *   (empty)    → numeric or date serial
   *
   * @param  string   $xml            Raw sheet XML
   * @param  string[] $sharedStrings  Indexed shared-strings table
   * @return array<string, scalar>
   */
  private function parseSheetXml(string $xml, array $sharedStrings): array
  {
    $sxe = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($sxe === false) {
      throw new RuntimeException("Failed to parse worksheet XML.");
    }

    $cells = [];
    $maxRow = 0;
    $maxCol = 0;

    foreach ($sxe->sheetData->row as $row) {
      $rowIdx = (int) $row['r'];

      foreach ($row->c as $c) {
        if (!isset($c->v)) {
          continue;   // empty cell
        }

        $ref = (string) $c['r'];
        $type = (string) $c['t'];
        $raw = (string) $c->v;
        $colIdx = self::colLettersToIndex(
          (string) preg_replace('/[0-9]/', '', $ref)
        );

        $value = match ($type) {
          's' => $sharedStrings[(int) $raw] ?? '',
          'inlineStr' => isset($c->is->t) ? (string) $c->is->t : '',
          'b' => (bool) $raw,
          default => is_numeric($raw) ? $raw + 0 : $raw,
        };

        if ($value !== null && $value !== '') {
          $cells["{$rowIdx}:{$colIdx}"] = $value;

          if ($rowIdx > $maxRow)
            $maxRow = $rowIdx;
          if ($colIdx > $maxCol)
            $maxCol = $colIdx;
        }
      }
    }

    $this->maxRow = $maxRow;
    $this->maxCol = $maxCol;

    return $cells;
  }
}