<?php
namespace App\Console\Commands;

use App\Console\Commands\BaseCommand;
use App\Console\ConsoleColor;
use App\Migration\MigrationRunner;
use App\Core\Schema\Compiler\MySQLCompiler;

class MigrateCommand extends BaseCommand
{
  protected string $name = 'migrate';
  protected string $paramsDescription = "[--all|<migration_file_name>]";
  protected string $description = 'Chạy các file migration';

  public function handle(array $args): void
  {
    $target = $args[0] ?? '--all';

    ConsoleColor::logLabel('MIGRATE', "Khởi động quy trình migration...", ConsoleColor::BG_BLUE);

    try {
      $compiler = new MySQLCompiler();
      $runner = new MigrationRunner($compiler);
      if ($target === '--all') {
        $runner->forward();
      } else {
        // Chạy một file cụ thể
        $runner->forward($target);
      }

      ConsoleColor::success("Tất cả các thay đổi đã được cập nhật vào Database.");
    } catch (\Throwable $e) {
      $message = "Migration failed:" . PHP_EOL . (string) $e . PHP_EOL;
      if (defined('STDERR')) {
        fwrite(STDERR, $message);
      } else {
        echo $message;
      }
      exit(1);
    }
  }
}
