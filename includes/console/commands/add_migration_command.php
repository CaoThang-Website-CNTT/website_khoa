<?php

namespace App\Console\Commands;

use App\Console\Commands\BaseCommand;
use App\Console\ConsoleColor;

class AddMigrationCommand extends BaseCommand
{
  protected string $name = 'add-migration';
  protected string $paramsDescription = "[<migration_file_name>]";
  protected string $description = 'Tạo một file migration mới vào thư mục database/migrations';
  public function handle(array $args): void
  {
    $migrationName = $args[0] ?? null;

    if (!$migrationName) {
      echo "\n";
      ConsoleColor::logLabel('ERROR', "Vui lòng cung cấp tên migration!", ConsoleColor::BG_RED);
      return;
    }

    // Đảm bảo thư mục migrations tồn tại
    $dir = BASE_PATH . '/database/migrations';
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }

    // Tạo tên file theo định dạng YYYY_MM_DD_HHMMSS_name.php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $timestamp = date('Y_m_d_His');
    $fileName = "{$timestamp}_{$migrationName}.php";
    $fullPath = "$dir/$fileName";

    // Xử lý tên Class và Table từ input
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $migrationName)));

    $tableName = str_replace(['create_', '_table'], '', $migrationName);

    // Lấy nội dung template
    $content = $this->getTemplate($className, $tableName);

    // Tạo & ghi file
    if (file_put_contents($fullPath, $content)) {
      ConsoleColor::logLabel('SUCCESS', 'Đã tạo migration ' . ConsoleColor::colorText($fileName, ConsoleColor::BLUE), ConsoleColor::BG_GREEN);
    } else {
      ConsoleColor::logLabel('ERROR', "Kiểm tra quyền ghi thư mục!", ConsoleColor::BG_RED);
    }
  }

  /**
   * Tạo nội dung mẫu cho file Migration
   */
  private function getTemplate(string $className, string $tableName): string
  {
    return "<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration
{
    /**
     * Chạy migration để tạo bảng
     */
    public function forward(TableBuilder \$schema): void
    {
        \$schema->create('', function (\$table) {
          \$table->id();
        });        
    }
    public function back(TableBuilder \$schema): void
    {
        \$schema->disableForeignKeys();

        \$schema->drop('');

        \$schema->enableForeignKeys();
    }
};
";
  }
}