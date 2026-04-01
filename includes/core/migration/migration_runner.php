<?php
namespace App\Core\Migration;

use App\Core\Schema\TableBuilder;
use App\Core\Schema\Compiler\ISQLCompiler;
use App\Console\ConsoleColor;
use Database;

class MigrationRunner
{
  protected ISQLCompiler $compiler;
  protected MigrationHistoryTracker $tracker;
  protected Database $db;

  public function __construct(ISQLCompiler $compiler)
  {
    $this->db = Database::getInstance();
    $this->compiler = $compiler;

    $this->tracker = new MigrationHistoryTracker();
    $this->tracker->ensureTableExists();
  }

  public function forward(?string $specificFile = null): void
  {
    $allFiles = glob(BASE_PATH . '/database/migrations/*.php');
    $ran = $this->tracker->getRanMigrations();
    $batch = $this->tracker->getNextBatchNumber();

    foreach ($allFiles as $file) {
      $fileName = basename($file, '.php');

      // Nếu chỉ định file hoặc chạy tất cả file chưa chạy
      if (($specificFile && $fileName !== $specificFile) || in_array($fileName, $ran)) {
        continue;
      }

      $this->executeMigration($file, $fileName, $batch, 'forward');
    }
  }

  protected function executeMigration(string $file, string $name, int $batch, string $method): void
  {
    $migration = require_once $file;
    $mainBuilder = new TableBuilder();

    try {
      $migration->$method($mainBuilder);
      $conn = $this->db->getConnection();

      foreach ($mainBuilder->getTablesToCreate() as $subBuilder) {
        if ($method === 'forward') {
          $sql = $this->compiler->compile($subBuilder);
          $conn->exec($sql);
        }

        foreach ($subBuilder->getCommands() as $cmd) {
          if (is_string($cmd))
            $conn->exec($cmd);
        }
      }

      foreach ($mainBuilder->getCommands() as $cmd) {
        if (is_string($cmd))
          $conn->exec($cmd);
      }

      $statusLabel = ($method === 'forward') ? 'MIGRATED' : 'ROLLBACK';
      $color = ($method === 'forward') ? ConsoleColor::CYAN : ConsoleColor::PURPLE;

      echo "  " . ConsoleColor::colorText("-> ", $color) . str_pad($name, 50) . " " . ConsoleColor::colorText("[$statusLabel]", $color) . "\n";

      if ($method === 'forward') {
        $this->tracker->log($name, $batch);
      }
    } catch (\Throwable $e) {
      ConsoleColor::error("Lỗi tại $name ($method): " . $e->getMessage());
    }
  }
  public function back(int $steps = 0): int
  {
    // Lấy danh sách file cần rollback từ Tracker
    $migrations = $this->tracker->getMigrationsToRollback($steps);
    $count = 0;

    foreach ($migrations as $item) {
      $name = $item['migration'];
      $file = BASE_PATH . "/database/migrations/{$name}.php";

      if (file_exists($file)) {
        $this->executeMigration($file, $name, $item['batch'], 'back');

        $this->tracker->remove($name);
        $count++;
      }
    }

    return $count;
  }
}