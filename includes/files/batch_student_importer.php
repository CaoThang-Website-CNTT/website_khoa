<?php

namespace App\Core\Files;

use Shuchkin\SimpleXLS;

/**
 * Cấu hình cho việc import danh sách sinh viên đợt thực tập.
 * Áp dụng cho định dạng file .xls xuất từ phần mềm quản lý điểm.
 */
class BatchStudentImportConfig
{
  /**
   * Dòng chứa metadata lớp học (tên lớp).
   * Cột D (index 3) chứa: "CĐ TH 22WEBC-Đồ án lập trình web"
   */
  public const CLASS_NAME_ROW = 1; // 0-indexed: dòng 2 trong Excel

  /**
   * Index cột chứa tên lớp (0-indexed, cột D = index 3).
   */
  public const CLASS_NAME_COL = 3;

  /**
   * Dòng bắt đầu chứa dữ liệu sinh viên (0-indexed).
   * Dòng 6 trong Excel = index 5.
   */
  public const DATA_START_ROW = 5;

  /**
   * Giới hạn số lượng bản ghi có thể xử lý trong một lần import.
   */
  public const MAX_ROWS = 1000;

  /**
   * Chỉ số cột (0-indexed) cho từng trường thông tin.
   * File template:
   * A (0): STT
   * B (1): Mã SV
   * C (2): Họ
   * D (3): Tên
   * E (4): Ngày sinh (dd/mm/yyyy)
   * F (5): Tổng Kết (bỏ qua)
   * G (6): Ghi Chú
   */
  public const COL_STT       = 0;
  public const COL_MSSV      = 1;
  public const COL_HO        = 2;
  public const COL_TEN       = 3;
  public const COL_NGAY_SINH = 4;
  public const COL_GHI_CHU  = 6;
}

/**
 * Lớp xử lý đọc file danh sách sinh viên đợt thực tập.
 * Hỗ trợ định dạng .xls (xuất từ phần mềm quản lý điểm của trường).
 */
class BatchStudentImporter
{
  /**
   * Đọc file XLS và trả về mảng danh sách sinh viên.
   *
   * @param string $filePath Đường dẫn tuyệt đối tới file .xls
   * @return array Danh sách sinh viên đã parse
   * @throws \RuntimeException Lỗi khi đọc file hoặc vượt quá giới hạn
   */
  public static function import(string $filePath): array
  {
    require_once BASE_PATH . '/includes/files/simplexls.php';

    $xls = SimpleXLS::parse($filePath);

    if (!$xls) {
      throw new \RuntimeException('Không thể đọc file XLS: ' . SimpleXLS::parseError());
    }

    $allRows = $xls->rows(0);

    return self::readStudents($allRows);
  }

  /**
   * Trích xuất tên lớp mặc định từ dòng metadata (dòng 2, cột D).
   * VD: "CĐ TH 22WEBC-Đồ án lập trình web" → "CĐ TH 22WEBC"
   */
  private static function extractDefaultClassroomName(array $allRows): string
  {
    $rawCell = trim((string) ($allRows[BatchStudentImportConfig::CLASS_NAME_ROW][BatchStudentImportConfig::CLASS_NAME_COL] ?? ''));

    if ($rawCell === '') {
      return '';
    }

    // Tách bằng dấu '-', lấy phần đầu tiên là short_name
    $parts = explode('-', $rawCell, 2);
    return trim($parts[0]);
  }

  /**
   * Trích xuất tên lớp từ cột Ghi Chú nếu sinh viên học ghép (HG-).
   * VD: "HG-CÐTH21WEBC-ĐA-LTWEB" → "CÐTH21WEBC"
   * Trả về null nếu không phải học ghép.
   */
  private static function extractGhepClassroomName(string $ghiChu): ?string
  {
    $trimmed = trim($ghiChu);

    // Kiểm tra prefix HG- (không phân biệt hoa thường)
    if (stripos($trimmed, 'HG-') !== 0) {
      return null;
    }

    // Bỏ prefix "HG-", tách thành các phần bằng '-'
    $withoutHG = substr($trimmed, 3);
    $parts     = explode('-', $withoutHG, 2);

    return trim($parts[0]);
  }

  /**
   * Trích xuất và chuẩn hóa dữ liệu từ các hàng trong file Excel.
   *
   * @param array $allRows Toàn bộ dữ liệu rows từ SimpleXLS
   * @return array Danh sách sinh viên đã parse
   */
  private static function readStudents(array $allRows): array
  {
    $students = [];
    $rowCount = 0;

    $defaultClassroomName = self::extractDefaultClassroomName($allRows);

    foreach ($allRows as $rowIdx => $row) {
      // Bỏ qua các dòng trước dòng dữ liệu (header, tên bảng, v.v.)
      if ($rowIdx < BatchStudentImportConfig::DATA_START_ROW) {
        continue;
      }

      $stt = $row[BatchStudentImportConfig::COL_STT] ?? null;

      // Bỏ qua dòng trống hoặc không có số thứ tự hợp lệ
      if (!is_numeric($stt) || (int) $stt <= 0) {
        continue;
      }

      $rowCount++;
      if ($rowCount > BatchStudentImportConfig::MAX_ROWS) {
        throw new \RuntimeException(
          'Vượt quá giới hạn ' . BatchStudentImportConfig::MAX_ROWS . ' sinh viên trong một file. Vui lòng chia nhỏ danh sách.'
        );
      }

      $mssv = trim((string) ($row[BatchStudentImportConfig::COL_MSSV] ?? ''));

      // Bỏ qua nếu MSSV rỗng dù có STT
      if ($mssv === '') {
        continue;
      }

      $ho  = trim((string) ($row[BatchStudentImportConfig::COL_HO] ?? ''));
      $ten = trim((string) ($row[BatchStudentImportConfig::COL_TEN] ?? ''));

      $fullName = self::joinName($ho, $ten);

      $ngaySinh = trim((string) ($row[BatchStudentImportConfig::COL_NGAY_SINH] ?? ''));
      $ghiChu   = trim((string) ($row[BatchStudentImportConfig::COL_GHI_CHU] ?? ''));

      // Xác định tên lớp: ưu tiên lớp từ Ghi Chú (học ghép), nếu không thì dùng lớp mặc định
      $ghepClassroom   = self::extractGhepClassroomName($ghiChu);
      $classroomName   = $ghepClassroom ?? $defaultClassroomName;

      $students[] = [
        'stt'            => (int) $stt,
        'student_code'   => $mssv,
        'full_name'      => $fullName,
        'dob'            => $ngaySinh,
        'classroom_name' => $classroomName,
        'national_id'    => '',
      ];
    }

    return $students;
  }

  /**
   * Gộp "Họ" và "Tên" thành một chuỗi "Họ Tên" phân cách bằng dấu cách.
   */
  private static function joinName(string $ho, string $ten): string
  {
    $parts = array_filter([$ho, $ten], fn($p) => $p !== '');
    return implode(' ', $parts);
  }

  /**
   * Chuẩn hóa tên lớp để so sánh chính xác giữa Excel và Database:
   * 1. Chuyển thành in hoa.
   * 2. Đồng nhất ký tự 'Đ' (thay thế Latin Eth 'Ð' bằng D-stroke 'Đ').
   * 3. Xóa bỏ toàn bộ khoảng trắng (bao gồm cả non-breaking space).
   */
  public static function normalizeClassroomName(string $name): string
  {
    $name = mb_strtoupper($name, 'UTF-8');
    // Thay thế 'Ð' (U+00D0 - Latin Eth) thành 'Đ' (U+0110 - D with stroke)
    $name = str_replace("\xC3\x90", "\xC4\x90", $name);
    // Xóa tất cả khoảng trắng (dấu cách, tab, non-breaking space...)
    $name = preg_replace('/\s+/u', '', $name);
    return $name;
  }
}
