<?php
namespace App\Console\Commands;

use App\Console\Commands\BaseCommand;
use App\Console\ConsoleColor;
use App\Migration\MigrationRunner;
use App\Schema\Compiler\MySQLCompiler;

class MigrateCommand extends BaseCommand
{
  protected string $name = 'migrate';
  protected string $paramsDescription = "[--all|<migration_file_name>]";
  protected string $description = 'Chạy các file migration';

  public function handle(array $args): void
  {
    $target = $args[0] ?? '--all';
    $compiler = new MySQLCompiler();
    $runner = new MigrationRunner($compiler);

    ConsoleColor::logLabel('MIGRATE', "Khởi động quy trình migration...", ConsoleColor::BG_BLUE);

    try {
      if ($target === '--all') {
        $runner->forward();
      } else {
        // Chạy một file cụ thể
        $runner->forward($target);
      }

      ConsoleColor::success("Tất cả các thay đổi đã được cập nhật vào Database.");
    } catch (\Exception $e) {
      ConsoleColor::error("Lỗi khi chạy migration: " . $e->getMessage());
    }
  }
}