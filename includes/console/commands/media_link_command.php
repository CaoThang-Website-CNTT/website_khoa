<?php

namespace App\Console\Commands;

use App\Console\ConsoleColor;

class MediaLinkCommand extends BaseCommand
{
  protected string $name = 'media-link';
  protected string $paramsDescription = '';
  protected string $description = 'Create public/media link to storage/media';

  public function handle(array $args): void
  {
    $target = BASE_PATH . '/storage/media';
    $link = BASE_PATH . '/public/media';

    $resolvedTarget = realpath($target);
    $resolvedLink = realpath($link);
    if ($resolvedTarget !== false && $resolvedLink !== false && $resolvedTarget === $resolvedLink) {
      ConsoleColor::success('public/media already points to storage/media.');
      return;
    }

    (new StorageLinkCommand())->handle(['storage/media', 'public/media']);
  }
}
