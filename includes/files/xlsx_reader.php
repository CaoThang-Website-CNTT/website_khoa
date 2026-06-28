<?php

namespace App\Core\Files;

/**
 * Trình đọc file .xlsx cơ bản bằng PHP thuần.
 * Phân tích cấu trúc file ZIP của định dạng OOXML (sharedStrings, workbook rels, sheet XML) và cung cấp các hàm đơn giản để lấy dữ liệu từng ô.
 */
class XlsxReader
{
  /** Lưu trữ dữ liệu ô dưới dạng "dòng:cột" (bắt đầu từ 1) => giá trị 
   * @var array<string, scalar> 
   */
  private array $cells = [];

  /** Dòng tối đa có chứa dữ liệu
   * @var int
   */
  private int $maxRow = 0;

  /** Cột tối đa có chứa dữ liệu 
   * @var int
   */
  private int $maxCol = 0;

  /**
   * Mở file XLSX và trả về đối tượng XlsxReader đã sẵn sàng để truy vấn.
   *
   * @throws \RuntimeException Nếu không tìm thấy file, file ZIP lỗi hoặc thiếu XML.
   */
  public static function open(string $filePath): self
  {
    $reader = new self();
    $reader->load($filePath);
    return $reader;
  }

  /**
   * Lấy giá trị của một ô dựa vào chỉ số dòng và cột. 
   * Trả về $default nếu ô trống hoặc ngoài vùng dữ liệu.
   *
   * @param int   $row     Chỉ số dòng (bắt đầu từ 1)
   * @param int   $col     Chỉ số cột (bắt đầu từ 1)
   * @param mixed $default Giá trị mặc định nếu ô rỗng
   */
  public function cell(int $row, int $col, mixed $default = null): mixed
  {
    return $this->cells["{$row}:{$col}"] ?? $default;
  }

  /**
   * Lấy giá trị của một ô dựa vào tọa độ (ví dụ: "E4", "N7").
   *
   * @param string $ref     Tọa độ ô (vd: "A1", "E4", "AA10")
   * @param mixed  $default Giá trị mặc định
   */
  public function cellByRef(string $ref, mixed $default = null): mixed
  {
    [$row, $col] = self::refToRowCol($ref);
    return $this->cell($row, $col, $default);
  }

  /**
   * Lặp qua các dòng có dữ liệu trong khoảng chỉ định.
   * Chỉ trả về những cột thực sự có giá trị trong dòng đó.
   *
   * @param int      $startRow Dòng bắt đầu (mặc định là 1)
   * @param int|null $endRow   Dòng kết thúc (null = dòng cuối cùng của sheet)
   * @return iterable Trả về danh sách dạng: dòng => [cột => giá trị]
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
   * Trả về chỉ số của dòng cuối cùng có chứa dữ liệu.
   */
  public function maxRow(): int
  {
    return $this->maxRow;
  }

  /**
   * Trả về chỉ số của cột cuối cùng có chứa dữ liệu.
   */
  public function maxCol(): int
  {
    return $this->maxCol;
  }

  /**
   * Chuyển đổi tọa độ ô thành mảng [dòng, cột] (đều bắt đầu từ 1).
   * Ví dụ: "A1" -> [1, 1], "E4" -> [4, 5], "AA10" -> [10, 27]
   */
  public static function refToRowCol(string $ref): array
  {
    if (!preg_match('/^([A-Za-z]+)(\d+)$/', $ref, $m)) {
      throw new \InvalidArgumentException("Tọa độ ô không hợp lệ: {$ref}");
    }
    return [(int) $m[2], self::colLettersToIndex(strtoupper($m[1]))];
  }

  /**
   * Chuyển đổi chữ cái của cột thành số thứ tự.
   * Ví dụ: A=1, B=2 ... Z=26, AA=27, AB=28 ...
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

  private function load(string $filePath): void
  {
    if (!file_exists($filePath)) {
      throw new \RuntimeException("Không tìm thấy file: {$filePath}");
    }

    if (!class_exists(\ZipArchive::class)) {
      throw new \RuntimeException('PHP ZIP extension is required to read XLSX files.');
    }

    $zip = new \ZipArchive();
    if ($zip->open($filePath) !== true) {
      throw new \RuntimeException("Không thể mở file XLSX (không phải định dạng ZIP hợp lệ): {$filePath}");
    }

    try {
      $sharedStrings = $this->loadSharedStrings($zip);
      $sheetXml = $this->loadFirstSheetXml($zip);
    } finally {
      $zip->close();
    }

    $this->cells = $this->parseSheetXml($sheetXml, $sharedStrings);
  }

  /**
   * Đọc file xl/sharedStrings.xml thành một mảng đơn giản.
   * Xử lý cả văn bản thường và văn bản có định dạng (rich-text).
   * Trả về mảng rỗng nếu file không có sharedStrings (vd: file chỉ chứa số).
   */
  private function loadSharedStrings(\ZipArchive $zip): array
  {
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml === false) {
      return []; // sharedStrings.xml là không bắt buộc
    }

    $sxe = simplexml_load_string($xml, '\SimpleXMLElement', LIBXML_NOCDATA);
    if ($sxe === false) {
      throw new \RuntimeException("Lỗi phân tích cú pháp xl/sharedStrings.xml.");
    }

    $strings = [];
    foreach ($sxe->si as $si) {
      if (isset($si->t)) {
        // Văn bản thường
        $strings[] = (string) $si->t;
      } else {
        // Văn bản có định dạng: nối tất cả các đoạn <r><t> lại với nhau
        $parts = [];
        foreach ($si->r as $r) {
          $parts[] = (string) $r->t;
        }
        $strings[] = implode('', $parts);
      }
    }

    return $strings;
  }

  /**
   * Tìm và đọc nội dung XML của sheet đầu tiên.
   * Đọc file cấu hình để biết tên chính xác của sheet thay vì tự đoán là "sheet1.xml".
   */
  private function loadFirstSheetXml(\ZipArchive $zip): string
  {
    $path = $this->resolveFirstSheetPath($zip);
    $content = $zip->getFromName($path);
    if ($content === false) {
      throw new \RuntimeException("Không tìm thấy XML của sheet tại: {$path}");
    }
    return $content;
  }

  /**
   * Đọc file workbook.xml.rels để tìm đường dẫn của sheet đầu tiên trong ZIP.
   * Nếu không có file rels, sẽ thử quét các tên phổ biến.
   */
  private function resolveFirstSheetPath(\ZipArchive $zip): string
  {
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

    if ($relsXml === false) {
      // Dự phòng: thử các tên phổ biến
      foreach (['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet0.xml'] as $candidate) {
        if ($zip->getFromName($candidate) !== false) {
          return $candidate;
        }
      }
      throw new \RuntimeException("Không tìm thấy sheet: thiếu file xl/_rels/workbook.xml.rels.");
    }

    $sxe = simplexml_load_string($relsXml, '\SimpleXMLElement', LIBXML_NOCDATA);
    if ($sxe === false) {
      throw new \RuntimeException("Lỗi phân tích cú pháp xl/_rels/workbook.xml.rels.");
    }

    foreach ($sxe->Relationship as $rel) {
      if (str_ends_with((string) $rel['Type'], '/worksheet')) {
        $target = (string) $rel['Target'];
        // Trả về đường dẫn chính xác dựa trên việc target là đường dẫn tuyệt đối hay tương đối
        return str_starts_with($target, '/')
          ? ltrim($target, '/')
          : 'xl/' . $target;
      }
    }

    throw new \RuntimeException("Không tìm thấy liên kết sheet nào trong xl/_rels/workbook.xml.rels.");
  }

  /**
   * Đọc XML của sheet và xây dựng mảng dữ liệu.
   * * Cấu trúc lưu trữ: "dòng:cột" (bắt đầu từ 1) => giá trị
   * Các ô bị merge sẽ chỉ lưu giá trị ở ô góc trên cùng bên trái.
   * * Các kiểu dữ liệu của ô:
   * s         -> Chuỗi dùng chung (tìm trong $sharedStrings)
   * inlineStr -> Chuỗi trực tiếp
   * b         -> Boolean (0 / 1)
   * (trống)   -> Số hoặc ngày tháng
   */
  private function parseSheetXml(string $xml, array $sharedStrings): array
  {
    $sxe = simplexml_load_string($xml, '\SimpleXMLElement', LIBXML_NOCDATA);
    if ($sxe === false) {
      throw new \RuntimeException("Lỗi phân tích cú pháp XML của sheet.");
    }

    $cells = [];
    $maxRow = 0;
    $maxCol = 0;

    foreach ($sxe->sheetData->row as $row) {
      $rowIdx = (int) $row['r'];

      foreach ($row->c as $c) {
        $type = (string) $c['t'];
        if (!isset($c->v) && $type !== 'inlineStr') {
          continue; // Bỏ qua ô trống
        }

        $ref = (string) $c['r'];
        $raw = isset($c->v) ? (string) $c->v : '';
        // Tách lấy chữ cái trong tọa độ để chuyển thành số thứ tự cột
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
