<?php
namespace App\Core\Schema\Compiler;

use App\Core\Schema\ColumnDefinition;
use App\Core\Schema\ForeignDefinition;

class MySQLCompiler extends BaseSQLCompiler
{
  public function wrap(string $value): string
  {
    if ($value === '*')
      return '*';

    // Đừng wrap nếu là SQL function thô (chứa dấu ngoặc đơn mở/đóng, ví dụ: COUNT(*))
    if (str_contains($value, '(') && str_contains($value, ')')) {
      return $value;
    }

    // Handle 'table.column'
    if (str_contains($value, '.')) {
      return implode('.', array_map(fn($p) => "`$p`", explode('.', $value)));
    }

    return "`$value`";
  }

  protected function buildCreateTableString(string $table, string $body): string
  {
    return sprintf(
      "CREATE TABLE %s (\n  %s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
      $this->wrap($table),
      $body
    );
  }

  protected function compileLimit(int $limit, ?int $offset): string
  {
    return $offset !== null ? "LIMIT $offset, $limit" : "LIMIT $limit";
  }

  public function compileColumn(ColumnDefinition $column): string
  {
    $attr = $column->attributes;
    $sql = sprintf("%s %s", $this->wrap($column->name), $this->getType($column));

    if ($attr['unsigned'] ?? false)
      $sql .= " UNSIGNED";

    $sql .= ($attr['nullable'] ?? false) ? " NULL" : " NOT NULL";

    if (($attr['default'] ?? null) !== null) {
      $sql .= " DEFAULT " . $this->formatDefaultValue($attr['default']);
    }

    if ($column->getOnUpdate() !== null) {
      $sql .= " ON UPDATE " . $column->getOnUpdate();
    }

    if ($attr['auto_increment'] ?? false)
      $sql .= " AUTO_INCREMENT";
    if ($attr['primary'] ?? false)
      $sql .= " PRIMARY KEY";
    if ($attr['unique'] ?? false)
      $sql .= " UNIQUE";

    if (($attr['comment'] ?? null) !== null) {
      $comment = str_replace("'", "''", $attr['comment']);
      $sql .= " COMMENT '" . $comment . "'";
    }
    // echo "\n" . $sql . "\n";

    return $sql;
  }

  protected function getType(ColumnDefinition $column): string
  {
    $type = strtolower($column->type);
    $attr = $column->attributes;
    $len = $attr['length'] ?? 255;

    return match ($type) {
      'varchar' => "VARCHAR($len)",
      'char' => "CHAR($len)",
      'decimal' => "DECIMAL(" . ($attr['precision'] ?? 8) . "," . ($attr['scale'] ?? 2) . ")",
      'tinyint' => "TINYINT($len)",
      'int', 'bigint', 'mediumint', 'smallint',
      'text', 'longtext', 'mediumtext', 'json',
      'date', 'datetime', 'timestamp', 'time' => strtoupper($type),
      'enum' => "ENUM('" . implode("','", $attr['allowed'] ?? []) . "')",
      default => "TEXT",
    };
  }

  public function compileCommand(mixed $command, string $fromTable): ?string
  {
    if ($command instanceof ForeignDefinition) {
      $constraintName = "fk_" . $fromTable . "_" . $command->getColumn();
      return sprintf(
        "CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s",
        $this->wrap($constraintName),
        $this->wrap($command->getColumn()),
        $this->wrap($command->getOnTable()),
        $this->wrap($command->getReferences()),
        $command->getOnDelete(),
        $command->getOnUpdate()
      );
    }

    if (is_array($command)) {
      $cols = implode(', ', array_map([$this, 'wrap'], $command['columns']));
      return match ($command['type']) {
        'index' => "INDEX ($cols)",
        'unique' => "UNIQUE INDEX ($cols)",
        'primary' => "PRIMARY KEY ($cols)",
        default => null
      };
    }

    return null;
  }
}