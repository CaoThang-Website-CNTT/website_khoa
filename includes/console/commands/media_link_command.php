<?php

namespace App\Console\Commands;

use App\Console\ConsoleColor;

class MediaLinkCommand extends BaseCommand
{
  protected string $name = 'media-link';
  protected string $paramsDescription = '[public_path]';
  protected string $description = 'Create a media link from storage/media to a public path';

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

    $resolvedTarget = realpath($target);
    $resolvedLink = realpath($link);
    if ($resolvedTarget !== false && $resolvedLink !== false && $resolvedTarget === $resolvedLink) {
      ConsoleColor::success("{$publicPath} is already linked to {$storagePath}.");
      return;
    }

    (new StorageLinkCommand())->handle([$storagePath, $publicPath]);
  }

  private function showUsage(): void
  {
    echo "\n";
    ConsoleColor::logLabel('MEDIA-LINK', 'Create a media link from storage/media to a public path.', ConsoleColor::BG_BLUE);
    echo "\n  " . ConsoleColor::colorText('USAGE:', ConsoleColor::YELLOW) . "\n";
    echo "    php ctsdk.php media-link [public_path]\n\n";
    echo "  " . ConsoleColor::colorText('EXAMPLES:', ConsoleColor::YELLOW) . "\n";
    echo "    php ctsdk.php media-link\n";
    echo "    php ctsdk.php media-link public/media\n";
    echo "    php ctsdk.php media-link ../public_html/public\n\n";
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
