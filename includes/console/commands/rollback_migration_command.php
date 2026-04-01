<?php

namespace App\Console\Commands;

use App\Core\Migration\MigrationRunner;
use App\Core\Schema\Compiler\MySQLCompiler;
use App\Console\ConsoleColor;

class RollbackMigrationCommand extends BaseCommand
{
  protected string $name = 'rollback-migration';
  protected string $paramsDescription = "[--step=n]";
  protected string $description = 'Hoàn tác các đợt migration gần nhất hoặc theo số bước chỉ định';

  public function handle(array $args): void
  {
    // 1. Phân tích tham số --step
    $steps = 0;
    foreach ($args as $arg) {
      if (str_starts_with($arg, '--step=')) {
        $steps = (int) str_replace('--step=', '', $arg);
      }
    }

    echo ConsoleColor::colorText("Đang rollback migrations...\n", ConsoleColor::YELLOW);

    $runner = new MigrationRunner(new MySQLCompiler());

    try {
      $count = $runner->back($steps);

      if ($count === 0) {
        echo "  " . ConsoleColor::colorText("!", ConsoleColor::YELLOW) . " Nothing to rollback.\n";
      } else {
        echo ConsoleColor::colorText("\nRollback hoàn tất.\n", ConsoleColor::GREEN);
      }
    } catch (\Throwable $e) {
      echo ConsoleColor::error("\nRollback thất bại: " . $e->getMessage()) . "\n";
    }
  }
}