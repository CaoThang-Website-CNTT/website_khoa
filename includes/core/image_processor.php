<?php

/**
 * =============================================================================
 * PHÂN TÍCH: CÁC ĐIỂM CÒN THIẾU TRONG MEDIA HIỆN TẠI
 * =============================================================================
 *
 * 1. EXTERNAL URL
 * ---------------
 * Hiện tại Media model và MediaStore không có bất kỳ khái niệm nào về
 * "external URL". Toàn bộ flow upload đều giả định file đến từ $_FILES
 * và sẽ được move vào storage vật lý.
 *
 * Vấn đề: trong content_json của Post, một image block có thể có dạng:
 *   { "mediaId": null, "url": "https://placehold.co/600x400", ... }
 *
 * Với trường hợp này, PostService đã xử lý đúng: extractInternalMediaIds()
 * bỏ qua mọi block có mediaId === null. Tức là external URL chỉ tồn tại
 * trong JSON, không có record trong bảng media — đây là thiết kế đúng.
 *
 * KẾT LUẬN: Media hiện tại KHÔNG cần thay đổi gì cho external URL.
 * PostService đã đảm nhận việc phân biệt nội bộ vs ngoại bộ tại tầng parse.
 *
 *
 * 2. IMAGE VARIANTS & COMPRESSION
 * --------------------------------
 * MediaService.upload() hiện tại chỉ move file thô vào storage, không có
 * bất kỳ xử lý nào về:
 *   - Resize thành các kích thước variants (thumb, medium, large)
 *   - Convert sang WebP hoặc AVIF
 *   - Nén để giảm dung lượng
 *
 * Đây là gap lớn về performance. Cần khai báo contract (interface) ngay bây giờ
 * và implement khi có yêu cầu cụ thể về kích thước/chất lượng.
 *
 * Lý do tách thành interface riêng thay vì nhét vào MediaService:
 *   - Single Responsibility: MediaService quản lý lifecycle của media record,
 *     không nên biết chi tiết về image processing pipeline.
 *   - Có thể swap implementation: GD, Imagick, hay future WASM-based processor
 *     mà không động đến MediaService.
 *   - Có thể chạy async trong tương lai (queue job) mà không refactor service.
 */

namespace App\Core\Image;

// =============================================================================
// CONTRACT
// =============================================================================

/**
 * Đại diện cho một variant đã được xử lý.
 * Immutable value object — không extend Model vì không lưu riêng vào DB,
 * variants được embed vào media record qua một JSON column (nếu cần sau này).
 */
class ImageVariant
{
  public function __construct(
    public string $name,         // 'thumb' | 'medium' | 'large' | 'original'
    public string $relativePath, // đường dẫn tương đối trong storage
    public int $width,
    public int $height,
    public int $fileSize,     // bytes sau khi xử lý
    public string $mimeType,     // 'image/webp' | 'image/avif' | 'image/jpeg' | ...
  ) {
  }
}

/**
 * IImageProcessor — contract cho tầng xử lý ảnh.
 *
 * Implement bằng GD (built-in PHP) là lựa chọn zero-dependency.
 * Imagick cho chất lượng cao hơn nhưng cần extension.
 *
 * Không inject vào MediaService trực tiếp — MediaService gọi qua interface
 * để tránh coupling với implementation cụ thể.
 */
interface IImageProcessor
{
  /**
   * Nhận đường dẫn tuyệt đối của file gốc, trả về danh sách variants đã tạo.
   * Mỗi variant được lưu vào storage tại $outputDir với tên file tự động.
   *
   * Các variant mặc định:
   *   - thumb:    320px wide, WebP, quality 75
   *   - medium:   800px wide, WebP, quality 80
   *   - large:    1600px wide, WebP, quality 85
   *   - original: giữ kích thước, chỉ nén + convert sang WebP
   *
   * Nếu ảnh gốc nhỏ hơn kích thước target thì bỏ qua variant đó
   * (không upscale — upscale làm ảnh vỡ và tăng dung lượng vô ích).
   *
   * @param  string $absoluteSourcePath  Đường dẫn tuyệt đối tới file gốc
   * @param  string $outputDir           Thư mục output tuyệt đối
   * @param  string $baseFilename        Tên file không có extension (dùng để đặt tên variant)
   * @return ImageVariant[]
   */
  public function process(string $absoluteSourcePath, string $outputDir, string $baseFilename): array;

  /**
   * Kiểm tra file tại đường dẫn có phải ảnh xử lý được không.
   * Dựa trên MIME type thực tế (magic bytes), không phải extension.
   */
  public function supports(string $absolutePath): bool;
}

// =============================================================================
// GD IMPLEMENTATION (zero external dependency)
// =============================================================================

/**
 * GdImageProcessor
 *
 * Dùng ext-gd (baked vào PHP mặc định) để:
 *   1. Decode ảnh gốc (JPEG, PNG, GIF, WebP)
 *   2. Resize về các kích thước target với imagescale() — giữ tỉ lệ
 *   3. Export sang WebP với chất lượng cấu hình được
 *
 * Giới hạn của GD:
 *   - Không hỗ trợ AVIF encode (chỉ decode từ PHP 8.1 nếu libavif có)
 *   - Chất lượng resize thấp hơn Imagick (dùng bilinear thay vì Lanczos)
 *   - Không giữ ICC color profile
 *
 * Nếu cần AVIF hoặc ICC profile, swap sang ImagickImageProcessor.
 */
class GdImageProcessor implements IImageProcessor
{
  /** @var array<string, array{width: int, quality: int}> */
  private array $_variantConfig;

  public function __construct()
  {
    // Cấu hình variants: key = tên, value = [width tối đa, chất lượng WebP 0-100]
    // 'original' width = 0 nghĩa là không resize, chỉ convert + nén
    $this->_variantConfig = [
      'thumb' => ['width' => 320, 'quality' => 75],
      'medium' => ['width' => 800, 'quality' => 80],
      'large' => ['width' => 1600, 'quality' => 85],
      'original' => ['width' => 0, 'quality' => 85],
    ];
  }

  public function supports(string $absolutePath): bool
  {
    if (!file_exists($absolutePath)) {
      return false;
    }

    $supportedMimes = [
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/webp',
    ];

    // mime_content_type dùng magic bytes, không trust extension
    $mime = mime_content_type($absolutePath);
    return in_array($mime, $supportedMimes, true);
  }

  public function process(string $absoluteSourcePath, string $outputDir, string $baseFilename): array
  {
    if (!$this->supports($absoluteSourcePath)) {
      return [];
    }

    $sourceImage = $this->loadImage($absoluteSourcePath);
    if ($sourceImage === false) {
      return [];
    }

    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);

    $variants = [];

    foreach ($this->_variantConfig as $variantName => $config) {
      $targetWidth = $config['width'];
      $quality = $config['quality'];

      // Bỏ qua variant nếu ảnh gốc đã nhỏ hơn target (không upscale)
      if ($targetWidth > 0 && $sourceWidth <= $targetWidth) {
        // Vẫn xử lý 'original' ngay cả khi nhỏ — để đảm bảo convert sang WebP
        if ($variantName !== 'original') {
          continue;
        }
      }

      $outputImage = $variantName === 'original'
        ? $this->cloneImage($sourceImage, $sourceWidth, $sourceHeight)
        : $this->resizeImage($sourceImage, $sourceWidth, $sourceHeight, $targetWidth);

      if ($outputImage === false) {
        continue;
      }

      $outputFilename = "{$baseFilename}_{$variantName}.webp";
      $outputPath = rtrim($outputDir, '/') . '/' . $outputFilename;

      $relativeParts = explode('/storage/', $outputPath);
      $relativePath = 'storage/' . ($relativeParts[1] ?? $outputFilename);

      $success = imagewebp($outputImage, $outputPath, $quality);
      imagedestroy($outputImage);

      if (!$success) {
        continue;
      }

      $actualWidth = $variantName === 'original' ? $sourceWidth : $this->calcScaledWidth($sourceWidth, $sourceHeight, $targetWidth);
      $actualHeight = $variantName === 'original' ? $sourceHeight : $this->calcScaledHeight($sourceWidth, $sourceHeight, $targetWidth);

      $variants[] = new ImageVariant(
        name: $variantName,
        relativePath: $relativePath,
        width: $actualWidth,
        height: $actualHeight,
        fileSize: (int) filesize($outputPath),
        mimeType: 'image/webp',
      );
    }

    imagedestroy($sourceImage);

    return $variants;
  }

  // ---------------------------------------------------------------------------
  // Private helpers
  // ---------------------------------------------------------------------------

  private function loadImage(string $path): \GdImage|false
  {
    $mime = mime_content_type($path);

    return match ($mime) {
      'image/jpeg' => imagecreatefromjpeg($path),
      'image/png' => imagecreatefrompng($path),
      'image/gif' => imagecreatefromgif($path),
      'image/webp' => imagecreatefromwebp($path),
      default => false,
    };
  }

  /**
   * Resize giữ tỉ lệ — không bao giờ upscale.
   */
  private function resizeImage(
    \GdImage $source,
    int $srcWidth,
    int $srcHeight,
    int $targetWidth
  ): \GdImage|false {
    $scaledWidth = $targetWidth;
    $scaledHeight = $this->calcScaledHeight($srcWidth, $srcHeight, $targetWidth);

    $dest = imagecreatetruecolor($scaledWidth, $scaledHeight);

    // Bảo toàn alpha channel cho PNG/WebP có transparency
    imagealphablending($dest, false);
    imagesavealpha($dest, true);

    $success = imagecopyresampled(
      $dest,
      $source,
      0,
      0,
      0,
      0,
      $scaledWidth,
      $scaledHeight,
      $srcWidth,
      $srcHeight
    );

    return $success ? $dest : false;
  }

  private function cloneImage(\GdImage $source, int $width, int $height): \GdImage|false
  {
    $dest = imagecreatetruecolor($width, $height);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);

    $success = imagecopy($dest, $source, 0, 0, 0, 0, $width, $height);
    return $success ? $dest : false;
  }

  private function calcScaledHeight(int $srcWidth, int $srcHeight, int $targetWidth): int
  {
    if ($srcWidth === 0)
      return 0;
    return (int) round($srcHeight * ($targetWidth / $srcWidth));
  }

  private function calcScaledWidth(int $srcWidth, int $srcHeight, int $targetWidth): int
  {
    return min($targetWidth, $srcWidth);
  }
}