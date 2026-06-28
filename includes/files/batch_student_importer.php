<?php

namespace App\Core\Files;

require_once BASE_PATH . '/includes/files/xlsx_reader.php';

use App\Core\Files\XlsxReader;

/**
 * Cấu hình cho việc import danh sách sinh viên đợt thực tập.
 */
class BatchStudentImportConfig
{
  /**
   * Dòng bắt đầu chứa dữ liệu (Bỏ qua dòng 1 là tiêu đề).
   */
  public const DATA_START_ROW = 2;

  /**
   * Giới hạn số lượng bản ghi có thể xử lý trong một lần import.
   */
  public const MAX_ROWS = 1000;

  /**
   * Chỉ số cột (bắt đầu từ 1) cho từng trường thông tin.
   * File template:
   * A: STT
   * B: MSSV
   * C: Họ và tên đệm
   * D: Tên
   * E: Ngày sinh (dd/mm/yyyy)
   * F: Lớp (Mã viết tắt, vd: CĐCNTT23A)
   */
  public const COL_STT = 1;
  public const COL_MSSV = 2;
  public const COL_HO_DEM = 3;
  public const COL_TEN = 4;
  public const COL_NGAY_SINH = 5;
  public const COL_LOP = 6;
  public const COL_CCCD = 7;
}

/**
 * Lớp xử lý đọc file danh sách sinh viên đợt thực tập (*.xlsx).
 */
class BatchStudentImporter
{
  /**
   * Đọc file XLSX và trả về mảng danh sách sinh viên.
   *
   * @param string $filePath Đường dẫn tuyệt đối tới file .xlsx
   * @return array Danh sách sinh viên đã parse
   * @throws \RuntimeException Lỗi khi đọc file hoặc vượt quá giới hạn
   */
  public static function import(string $filePath): array
  {
    $reader = XlsxReader::open($filePath);
    return self::readStudents($reader);
  }

  /**
   * Trích xuất và chuẩn hóa dữ liệu từ các hàng trong file Excel.
   */
  private static function readStudents(XlsxReader $reader): array
  {
    $students = [];
    $rowCount = 0;

    foreach ($reader->rows(BatchStudentImportConfig::DATA_START_ROW) as $rowIdx => $row) {
      $stt = $row[BatchStudentImportConfig::COL_STT] ?? null;

      // Bỏ qua dòng trống hoặc không có số thứ tự hợp lệ
      if (!is_numeric($stt) || (int) $stt <= 0) {
        continue;
      }

      $rowCount++;
      if ($rowCount > BatchStudentImportConfig::MAX_ROWS) {
        throw new \RuntimeException(
          "Vượt quá giới hạn " . BatchStudentImportConfig::MAX_ROWS . " sinh viên trong một file. Vui lòng chia nhỏ danh sách."
        );
      }

      // Extract & trim data
      $mssv = trim((string) ($row[BatchStudentImportConfig::COL_MSSV] ?? ''));

      // Bỏ qua nếu MSSV rỗng dù có STT
      if ($mssv === '') {
        continue;
      }

      $hoDem = trim((string) ($row[BatchStudentImportConfig::COL_HO_DEM] ?? ''));
      $ten = trim((string) ($row[BatchStudentImportConfig::COL_TEN] ?? ''));

      // Build full name
      $fullName = self::joinName($hoDem, $ten);

      // Lưu ý: ngày sinh lúc này đang ở dạng Raw (dd/mm/yyyy string hoặc excel date number)
      // Hàm XlsxReader tự động parse nếu là số hoặc date string chuẩn của xlsx, nhưng 
      // để hiển thị và lưu DB chuẩn, tạm thời sẽ giữ string và chuẩn hóa ở Service/Client.
      $ngaySinh = trim((string) ($row[BatchStudentImportConfig::COL_NGAY_SINH] ?? ''));
      $lop = trim((string) ($row[BatchStudentImportConfig::COL_LOP] ?? ''));
      $cccd = trim((string) ($row[BatchStudentImportConfig::COL_CCCD] ?? ''));

      if ($cccd === '') {
        throw new \RuntimeException("Dòng " . ($rowIdx) . ": Thiếu số CCCD.");
      }

      $students[] = [
        'stt' => (int) $stt,
        'student_code' => $mssv,
        'full_name' => $fullName,
        'dob' => $ngaySinh,
        'classroom_name' => $lop, // Tên viết tắt của lớp (short_name)
        'national_id' => $cccd,
      ];
    }

    return $students;
  }

  /**
   * Gộp phần "Họ đệm" và "Tên" thành một chuỗi "Họ Tên" phân cách bằng dấu cách.
   */
  private static function joinName(string $hoDem, string $ten): string
  {
    return $ten !== '' ? "{$hoDem} {$ten}" : $hoDem;
  }
}
