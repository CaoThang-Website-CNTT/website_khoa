<?php
namespace App\Console;

use App\Console\Commands\BaseCommand;

class Kernel
{
  private string $version = "v0.5";
  protected array $commands = [];

  public function register(BaseCommand $command): void
  {
    $this->commands[$command->getName()] = $command;
  }

  public function handle(array $argv): void
  {
    $fullCommand = $argv[1] ?? '';

    if ($fullCommand === '') {
      $this->showInvalidCommand();
      return;
    }

    if ($fullCommand === 'help') {
      $this->showHelp();
      return;
    }

    if (isset($this->commands[$fullCommand])) {
      $args = array_slice($argv, 2);
      $this->commands[$fullCommand]->handle($args);
      return;
    }

    $this->showInvalidCommand();
  }

  protected function showInvalidCommand(): void
  {
    echo "\n";
    ConsoleColor::logLabel('ERROR', "Cú pháp không xác định!", ConsoleColor::BG_RED);
    echo "  " . ConsoleColor::colorText("-> ", ConsoleColor::CYAN);
    echo "Thử lại với: " . ConsoleColor::colorText("php ctsdk.php help", ConsoleColor::PURPLE) . "\n\n";
  }

  protected function showHelp(): void
  {
    echo "\n" . ConsoleColor::BOLD . ConsoleColor::BG_BLUE . " CAO THANG IT SDK {$this->version} " . ConsoleColor::RESET . "\n\n";

    echo ConsoleColor::colorText("DANH SÁCH LỆNH:", ConsoleColor::YELLOW) . "\n";

    foreach ($this->commands as $cmd) {
      echo
        ConsoleColor::colorText("*", ConsoleColor::PURPLE) .
        " php ctsdk.php " .
        ConsoleColor::colorText(str_pad($cmd->getName(), 15), ConsoleColor::PURPLE) .
        ' ' .
        ConsoleColor::colorText(str_pad($cmd->getParamsDescription(), 30), ConsoleColor::YELLOW) .
        "| " . $cmd->getDescription() . "\n";
    }
    echo "\n";
  }
}
class ConsoleColor
{
  // Reset
  const RESET = "\033[0m";

  // Foreground
  const RED = "\033[31m";
  const GREEN = "\033[32m";
  const YELLOW = "\033[33m";
  const BLUE = "\e[1;94m";
  const PURPLE = "\e[1;95m";
  const CYAN = "\033[36m";
  const WHITE = "\033[37m";
  const GRAY = "\033[90m";
  const L_BLUE = "\033[94m";
  const L_CYAN = "\033[96m";

  // Background
  const BG_RED = "\033[41m";
  const BG_GREEN = "\033[42m";
  const BG_YELLOW = "\033[43m";
  const BG_PURPLE = "\e[0;105m";
  const BG_CYAN = "\033[46m";
  const BG_WHITE = "\033[47m";
  const BG_BLUE = "\033[48;5;26m";

  // Styles
  const BOLD = "\033[1m";
  public static function colorText(string $text, string $color = self::WHITE): string
  {
    return $color . $text . self::RESET;
  }
  public static function logLabel(string $label, string $message, string $bgColor = self::BG_BLUE): void
  {
    echo "\n" . self::BOLD . $bgColor . self::WHITE . " $label " . self::RESET . " $message\n";
  }

  public static function success(string $message): void
  {
    self::logLabel('SUCCESS', $message, self::BG_GREEN);
  }

  public static function error(string $message): void
  {
    self::logLabel('ERROR', $message, self::BG_RED);
  }

  public static function info(string $message): void
  {
    self::logLabel('INFO', $message, self::BG_CYAN);
  }
}