<?php

require_once __DIR__ . '/xlsx_reader.php';

/**
 * Config cho import "Danh Sách Sinh Viên".
 * Chứa thông tin về vị trí ô, chỉ số cột và dòng bắt đầu để có trích xuất dữ liệu chính xác
 */
class StudentImportConfig
{
  /**
   * Vị trí ô chứa giá trị tên lớp (mặc định là E4, nếu merge E4->L4 thì chỉ đọc E4)
   */
  public const CLASS_NAME_REF = 'E4';

  /**
   * Dòng đầu tiên chứa dữ liệu sinh viên (bỏ qua 6 dòng đầu là tiêu đề).
   */
  public const DATA_START_ROW = 7;

  /**
   * Chỉ số cột (bắt đầu từ 1) cho từng trường thông tin.
   *
   * Lưu ý: "Họ tên" trong file bị chia làm 2 phần:
   * - Cột G (merge G:J): Họ + tên đệm
   * - Cột K (merge K:M): Tên
   * Hai cột này sẽ được gộp chung thành trường 'ho_ten' khi xuất ra.
   */
  public const COL_STT = 1;  // A - Số thứ tự (dùng để kiểm tra dòng hợp lệ)
  public const COL_SBD = 4;  // D - Số báo danh
  public const COL_MA_SV = 6;  // F - Mã sinh viên
  public const COL_HO_TEN_1 = 7;  // G - Họ và tên đệm
  public const COL_HO_TEN_2 = 11; // K - Tên
  public const COL_NGAY_SINH = 14; // N - Ngày sinh
  public const COL_NOI_SINH = 16; // P - Nơi sinh
  public const COL_GHI_CHU = 17; // Q - Ghi chú
}

/**
 * Lớp xử lý trích xuất dữ liệu sinh viên từ file XLSX.
 * * Kết hợp giữa thư viện XlsxReader và cấu hình StudentImportConfig.
 */
class StudentImporter
{
  /**
   * Đọc file XLSX và trả về tên lớp cùng danh sách sinh viên.
   *
   * @param string $filePath Đường dẫn tuyệt đối tới file .xlsx.
   * @return array Mảng chứa 'class_name' và 'students'.
   * @throws RuntimeException Báo lỗi từ XlsxReader nếu file sai định dạng.
   */
  public static function import(string $filePath): array
  {
    $reader = XlsxReader::open($filePath);

    return [
      'class_name' => self::readClassName($reader),
      'students' => self::readStudents($reader),
    ];
  }

  /**
   * Lấy tên lớp từ ô cấu hình sẵn.
   */
  private static function readClassName(XlsxReader $reader): string
  {
    return (string) $reader->cellByRef(StudentImportConfig::CLASS_NAME_REF, '');
  }

  /**
   * Đọc và chuẩn hóa danh sách sinh viên.
   */
  private static function readStudents(XlsxReader $reader): array
  {
    $students = [];

    foreach ($reader->rows(StudentImportConfig::DATA_START_ROW) as $rowIdx => $row) {
      $stt = $row[StudentImportConfig::COL_STT] ?? null;

      // Dòng sinh viên hợp lệ phải có STT là số nguyên dương.
      // Các dòng trống, chân trang hoặc chữ ký sẽ bị bỏ qua.
      if (!is_numeric($stt) || (int) $stt <= 0) {
        continue;
      }

      $students[] = [
        'stt' => (int) $stt,
        'sbd' => (string) ($row[StudentImportConfig::COL_SBD] ?? ''),
        'ma_sv' => (string) ($row[StudentImportConfig::COL_MA_SV] ?? ''),
        'ho_ten' => self::joinName(
          (string) ($row[StudentImportConfig::COL_HO_TEN_1] ?? ''),
          (string) ($row[StudentImportConfig::COL_HO_TEN_2] ?? '')
        ),
        'ngay_sinh' => (string) ($row[StudentImportConfig::COL_NGAY_SINH] ?? ''),
        'noi_sinh' => (string) ($row[StudentImportConfig::COL_NOI_SINH] ?? ''),
        'ghi_chu' => (string) ($row[StudentImportConfig::COL_GHI_CHU] ?? ''),
      ];
    }

    return $students;
  }

  /**
   * Gộp phần "Họ + Tên đệm" và "Tên" thành một chuỗi hoàn chỉnh.
   * Nếu phần tên (part2) trống (ví dụ: tên chỉ có 1 chữ), sẽ chỉ lấy phần 1.
   */
  private static function joinName(string $part1, string $part2): string
  {
    $part1 = trim($part1);
    $part2 = trim($part2);

    return $part2 !== '' ? "{$part1} {$part2}" : $part1;
  }
}
?>