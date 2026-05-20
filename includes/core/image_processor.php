<?php
namespace App\Core\Image;

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
   * @param  string $absoluteSourcePath  Đường dẫn tuyệt đối tới file gốc
   * @param  string $outputDir           Thư mục output tuyệt đối
   * @param  string $baseFilename        Tên file không có extension (dùng để đặt tên variant)
   * @return ImageVariant[]
   */
  public function process(string $absoluteSourcePath, string $outputDir, string $baseFilename): array;

  /**
   * Nhận đường dẫn tuyệt đối của file gốc, nén và thay đổi kích thước theo chế độ nén cụ thể.
   * Trả về đối tượng ImageVariant đã tạo hoặc null nếu thất bại.
   * Chế độ nén: 'thumbnail' (thumb), 'standard' (medium), 'banner' (large), 'lossless' (original).
   *
   * @param  string $absoluteSourcePath  Đường dẫn tuyệt đối tới file gốc
   * @param  string $outputDir           Thư mục output tuyệt đối
   * @param  string $baseFilename        Tên file không có extension (dùng để đặt tên variant)
   * @param  string $compressMode        Chế độ nén hình ảnh
   * @return ImageVariant|null
   */
  public function processSingle(string $absoluteSourcePath, string $outputDir, string $baseFilename, string $compressMode): ?ImageVariant;

  /**
   * Kiểm tra file tại đường dẫn có phải ảnh xử lý được không.
   * Dựa trên MIME type thực tế (magic bytes), không phải extension.
   */
  public function supports(string $absolutePath): bool;
}

/**
 * ImageProcessor
 *
 * Dùng ext-gd (baked vào PHP mặc định) để:
 *   1. Decode ảnh gốc (JPEG, PNG, GIF, WebP)
 *   2. Resize về các kích thước target với imagescale() — giữ tỉ lệ
 *   3. Export sang WebP với chất lượng cấu hình được
 */
class ImageProcessor implements IImageProcessor
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

      if ($targetWidth > 0 && $sourceWidth <= $targetWidth) {
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

  public function processSingle(string $absoluteSourcePath, string $outputDir, string $baseFilename, string $compressMode): ?ImageVariant
  {
    if (!$this->supports($absoluteSourcePath)) {
      return null;
    }

    // Map compress mode to variants
    $variantName = match ($compressMode) {
      'thumbnail' => 'thumb',
      'standard' => 'medium',
      'banner' => 'large',
      'lossless', 'original' => 'original',
      default => 'original'
    };

    $config = $this->_variantConfig[$variantName] ?? $this->_variantConfig['original'];
    $targetWidth = $config['width'];
    $quality = $config['quality'];

    $sourceImage = $this->loadImage($absoluteSourcePath);
    if ($sourceImage === false) {
      return null;
    }

    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);

    // Không upscale nếu ảnh nhỏ hơn mục tiêu
    if ($targetWidth > 0 && $sourceWidth <= $targetWidth) {
      $variantName = 'original';
      $targetWidth = 0;
    }

    $outputImage = $targetWidth === 0
      ? $this->cloneImage($sourceImage, $sourceWidth, $sourceHeight)
      : $this->resizeImage($sourceImage, $sourceWidth, $sourceHeight, $targetWidth);

    if ($outputImage === false) {
      imagedestroy($sourceImage);
      return null;
    }

    $outputFilename = "{$baseFilename}_{$variantName}.webp";
    $outputPath = rtrim($outputDir, '/') . '/' . $outputFilename;

    $relativeParts = explode('/storage/', $outputPath);
    $relativePath = 'storage/' . ($relativeParts[1] ?? $outputFilename);

    $success = imagewebp($outputImage, $outputPath, $quality);
    imagedestroy($outputImage);
    imagedestroy($sourceImage);

    if (!$success) {
      return null;
    }

    $actualWidth = $targetWidth === 0 ? $sourceWidth : $this->calcScaledWidth($sourceWidth, $sourceHeight, $targetWidth);
    $actualHeight = $targetWidth === 0 ? $sourceHeight : $this->calcScaledHeight($sourceWidth, $sourceHeight, $targetWidth);

    return new ImageVariant(
      name: $variantName,
      relativePath: $relativePath,
      width: $actualWidth,
      height: $actualHeight,
      fileSize: (int) filesize($outputPath),
      mimeType: 'image/webp',
    );
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

  private function resizeImage(
    \GdImage $source,
    int $srcWidth,
    int $srcHeight,
    int $targetWidth
  ): \GdImage|false {
    $scaledWidth = $targetWidth;
    $scaledHeight = $this->calcScaledHeight($srcWidth, $srcHeight, $targetWidth);

    $dest = imagecreatetruecolor($scaledWidth, $scaledHeight);

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