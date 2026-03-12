<?php

require_once __DIR__ . '/xlsx_reader.php';

/**
 * StudentImportConfig
 *
 * All template-specific knowledge for the "Danh Sách Sinh Viên Đầu Khóa"
 * report format lives here — cell addresses, column indices, and the row
 * validation rule.
 *
 * To adapt to a different report layout, only this file needs to change.
 * XlsxReader remains untouched.
 */
class StudentImportConfig
{
  // =========================================================================
  // Template configuration
  // Change these constants whenever the school updates the report format.
  // =========================================================================

  /**
   * A1-style reference of the cell that contains the class name.
   * Current layout: "LỚP:" label in C4, value merged across E4:L4 → read E4.
   */
  public const CLASS_NAME_REF = 'E4';

  /**
   * First row that contains student data (1-based).
   * Rows 1–6 are the title block and header row.
   */
  public const DATA_START_ROW = 7;

  /**
   * Column indices (1-based) for each student field.
   *
   * Quick reference: A=1 B=2 C=3 D=4 E=5 F=6 G=7 … K=11 … N=14 P=16 Q=17
   *
   * Note: "Họ tên" is split across two merged regions in the template:
   *   - COL_HO_TEN_1 (col G, merged G:J)  → họ + tên đệm
   *   - COL_HO_TEN_2 (col K, merged K:M)  → tên
   * Both parts are concatenated into a single 'ho_ten' field on output.
   */
  public const COL_STT = 1;   // A — số thứ tự (used to detect valid rows)
  public const COL_SBD = 4;   // D — số báo danh
  public const COL_MA_SV = 6;   // F — mã sinh viên
  public const COL_HO_TEN_1 = 7;   // G — họ + tên đệm  (merged G:J)
  public const COL_HO_TEN_2 = 11;  // K — tên            (merged K:M)
  public const COL_NGAY_SINH = 14;  // N — ngày sinh      (merged N:O)
  public const COL_NOI_SINH = 16;  // P — nơi sinh
  public const COL_GHI_CHU = 17;  // Q — ghi chú        (merged Q:S)
}

/**
 * StudentImporter
 *
 * Uses XlsxReader (generic) + StudentImportConfig (domain config) to
 * extract structured student records from the school's XLSX report.
 */
class StudentImporter
{
  // =========================================================================
  // Public API
  // =========================================================================

  /**
   * Parse an XLSX file and return all student records plus the class name.
   *
   * @param  string $filePath  Absolute path to the .xlsx file.
   * @return array{
   *     class_name: string,
   *     students: array<array{
   *         stt: int,
   *         sbd: string,
   *         ma_sv: string,
   *         ho_ten: string,
   *         ngay_sinh: string,
   *         noi_sinh: string,
   *         ghi_chu: string,
   *     }>
   * }
   *
   * @throws RuntimeException  propagated from XlsxReader on format errors.
   */
  public static function import(string $filePath): array
  {
    $reader = XlsxReader::open($filePath);

    return [
      'class_name' => self::readClassName($reader),
      'students' => self::readStudents($reader),
    ];
  }

  // =========================================================================
  // Private extraction helpers
  // =========================================================================

  private static function readClassName(XlsxReader $reader): string
  {
    return (string) $reader->cellByRef(StudentImportConfig::CLASS_NAME_REF, '');
  }

  private static function readStudents(XlsxReader $reader): array
  {
    $students = [];

    foreach ($reader->rows(StudentImportConfig::DATA_START_ROW) as $rowIdx => $row) {
      $stt = $row[StudentImportConfig::COL_STT] ?? null;

      // A valid student row has a positive integer in the STT column.
      // Footer rows, signature lines, and blank rows all fail this check.
      if (!is_numeric($stt) || (int) $stt <= 0) {
        continue;
      }

      $students[] = [
        'stt' => (int) $stt,
        'sbd' => (string) ($row[StudentImportConfig::COL_SBD] ?? ''),
        'ma_sv' => (string) ($row[StudentImportConfig::COL_MA_SV] ?? ''),
        'ho_ten' => self::joinName(
          (string) ($row[StudentImportConfig::COL_HO_TEN_1] ?? ''),
          (string) ($row[StudentImportConfig::COL_HO_TEN_2] ?? ''),
        ),
        'ngay_sinh' => (string) ($row[StudentImportConfig::COL_NGAY_SINH] ?? ''),
        'noi_sinh' => (string) ($row[StudentImportConfig::COL_NOI_SINH] ?? ''),
        'ghi_chu' => (string) ($row[StudentImportConfig::COL_GHI_CHU] ?? ''),
      ];
    }

    return $students;
  }

  /**
   * Join the two name parts, handling cases where one part may be empty.
   *
   * Template splits name across two merged column groups (G:J and K:M).
   * If the second part is empty (single-word names), only the first is used.
   */
  private static function joinName(string $part1, string $part2): string
  {
    $part1 = trim($part1);
    $part2 = trim($part2);
    return $part2 !== '' ? "{$part1} {$part2}" : $part1;
  }
}
?>