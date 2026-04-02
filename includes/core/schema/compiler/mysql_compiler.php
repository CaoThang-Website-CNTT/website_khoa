<?php
namespace App\Core\Schema\Compiler;

use App\Core\Schema\ColumnDefinition;
use App\Core\Schema\Compiler\BaseSQLCompiler;

class MySQLCompiler extends BaseSQLCompiler
{
  protected function wrap(string $value): string
  {
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
  public function compileColumn(ColumnDefinition $column): string
  {
    $attr = $column->attributes;
    $sql = sprintf("%s %s", $this->wrap($column->name), $this->getType($column));

    if ($attr['unsigned'])
      $sql .= " UNSIGNED";

    $sql .= ($attr['nullable']) ? " NULL" : " NOT NULL";

    if ($attr['default'] !== null) {
      $sql .= " DEFAULT " . $this->formatDefaultValue($attr['default']);
    }

    if ($attr['auto_increment'])
      $sql .= " AUTO_INCREMENT";
    if ($attr['primary'])
      $sql .= " PRIMARY KEY";
    if ($attr['unique'])
      $sql .= " UNIQUE";
    if ($attr['comment'] !== null) {
      $comment = str_replace("'", "''", $attr['comment']);
      $sql .= " COMMENT '" . $comment . "'";
    }

    return $sql;
  }

  protected function getType(ColumnDefinition $column): string
  {
    $type = strtolower($column->type);
    $len = $column->attributes['length'] ?? 255;
    $p = $column->attributes['precision'] ?? 8;
    $s = $column->attributes['scale'] ?? 2;

    return match ($type) {
      'varchar' => "VARCHAR($len)",
      'char' => "CHAR($len)",
      'decimal' => "DECIMAL($p,$s)",
      'tinyint' => "TINYINT($len)",
      'int', 'bigint', 'mediumint', 'smallint',
      'text', 'longtext', 'mediumtext', 'json',
      'date', 'datetime', 'timestamp', 'time' => strtoupper($type),
      'enum' => "ENUM('" . implode("','", $column->attributes['allowed']) . "')",
      default => "TEXT",
    };
  }

  public function compileCommand(mixed $command): ?string
  {
    if ($command instanceof ForeignDefinition) {
      return sprintf(
        "CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s",
        $this->wrap("fk_" . $command->getColumn()),
        $this->wrap($command->getColumn()),
        $this->wrap($command->getOnTable()),
        $this->wrap($command->getReferences()),
        $command->getOnDelete(),
        $command->getOnUpdate()
      );
    }

    if (is_array($command)) {
      $wrappedCols = array_map([$this, 'wrap'], $command['columns']);
      $cols = implode(', ', $wrappedCols);

      return match ($command['type']) {
        'index' => "INDEX ($cols)",
        'unique' => "UNIQUE INDEX ($cols)",
        default => null
      };
    }

    return null;
  }
}