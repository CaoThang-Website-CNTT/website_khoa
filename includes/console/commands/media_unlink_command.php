<?php

namespace App\Console\Commands;

use App\Console\ConsoleColor;

class MediaUnlinkCommand extends BaseCommand
{
  protected string $name = 'media-unlink';
  protected string $paramsDescription = '[public_path]';
  protected string $description = 'Remove a media link created for storage/media';

  public function handle(array $args): void
  {
    $storagePath = 'storage/media';
    $publicPath = $args[0] ?? 'public/media';

    if (in_array($publicPath, ['-h', '--help', 'help'], true)) {
      $this->showUsage();
      return;
    }

    $target = $this->normalizePath(BASE_PATH, $storagePath);
    $link = $this->normalizePath(BASE_PATH, $publicPath);

    if (!file_exists($link) && !is_link($link)) {
      ConsoleColor::info("No media link exists at {$publicPath}.");
      return;
    }

    $resolvedTarget = realpath($target);
    $resolvedLink = realpath($link);
    $pointsToMedia = $resolvedTarget !== false && $resolvedLink !== false && $resolvedTarget === $resolvedLink;

    if (!is_link($link) && !$pointsToMedia) {
      ConsoleColor::error("Refusing to remove {$publicPath} because it is not a link to {$storagePath}.");
      return;
    }

    if ($this->removeLink($link)) {
      ConsoleColor::success("Removed media link at {$publicPath}.");
      return;
    }

    ConsoleColor::error("Could not remove media link at {$publicPath}.");
  }

  private function showUsage(): void
  {
    echo "\n";
    ConsoleColor::logLabel('MEDIA-UNLINK', 'Remove a media link created for storage/media.', ConsoleColor::BG_BLUE);
    echo "\n  " . ConsoleColor::colorText('USAGE:', ConsoleColor::YELLOW) . "\n";
    echo "    php ctsdk.php media-unlink [public_path]\n\n";
    echo "  " . ConsoleColor::colorText('EXAMPLES:', ConsoleColor::YELLOW) . "\n";
    echo "    php ctsdk.php media-unlink\n";
    echo "    php ctsdk.php media-unlink public/media\n";
    echo "    php ctsdk.php media-unlink ../public_html/public\n\n";
  }

  private function removeLink(string $link): bool
  {
    if (is_link($link) || is_file($link)) {
      return @unlink($link);
    }

    if (is_dir($link)) {
      return @rmdir($link);
    }

    return false;
  }

  private function normalizePath(string $basePath, string $relativePath): string
  {
    if (str_starts_with($relativePath, '/') ||
        (strlen($relativePath) > 1 && $relativePath[1] === ':')) {
      return $relativePath;
    }

    $absolute = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    $absolute = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $absolute);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $absolute), fn($p) => $p !== '' && $p !== '.');

    $resolved = [];
    foreach ($parts as $part) {
      if ($part === '..') {
        array_pop($resolved);
      } else {
        $resolved[] = $part;
      }
    }

    $result = implode(DIRECTORY_SEPARATOR, $resolved);

    if (strlen($result) > 1 && $result[1] === ':') {
      return $result;
    }

    return (PHP_OS_FAMILY === 'Windows' ? '' : DIRECTORY_SEPARATOR) . $result;
  }
}
