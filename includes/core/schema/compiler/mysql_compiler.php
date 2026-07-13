<?php
namespace App\Core\Schema\Compiler;

use App\Core\Schema\ColumnDefinition;
use App\Core\Schema\ForeignDefinition;
use App\Core\Schema\AlterBuilder;
use App\Core\Schema\AlterOperation;

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

    // Hỗ trợ alias với cú pháp "column AS alias" hoặc "table.column AS alias"
    if (stripos($value, ' as ') !== false) {
      $segments = preg_split('/\s+as\s+/i', $value);
      return $this->wrap($segments[0]) . ' AS ' . $this->wrap($segments[1]);
    }

    // JSON
    // Toán tử "->>": lấy text
    if (str_contains($value, '->>')) {
      $parts = explode('->>', $value);
      $column = $this->wrap(trim($parts[0])); // Đệ quy wrap tên cột
      $path = "$." . ltrim(trim($parts[1]), '$.'); // Đảm bảo luôn bắt đầu bằng $.

      return "JSON_UNQUOTE(JSON_EXTRACT({$column}, '{$path}'))";
    }

    // Toán tử "->": lấy JSON thô
    if (str_contains($value, '->')) {
      $parts = explode('->', $value);
      $column = $this->wrap(trim($parts[0]));
      $path = "$." . ltrim(trim($parts[1]), '$.');

      return "JSON_EXTRACT({$column}, '{$path}')";
    }

    // Handle 'table.column'
    if (str_contains($value, '.')) {
      $parts = explode('.', $value);
      return implode('.', array_map(
        fn($part) => $part === '*' ? '*' : "`$part`",
        $parts
      ));
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

  public function compileAlter(AlterBuilder $builder): string
  {
    $table = $builder->getTable();
    $operations = $builder->getOperations();

    if (empty($operations)) {
      return ''; // Không có operation nào
    }

    // Kiểm tra nếu có RENAME_TABLE - phải là operation duy nhất
    foreach ($operations as $op) {
      if ($op->type === AlterOperation::RENAME_TABLE) {
        return sprintf(
          "ALTER TABLE %s RENAME TO %s;",
          $this->wrap($table),
          $this->wrap($op->payload)
        );
      }
    }

    // Compile các operation thành clauses
    $clauses = [];
    foreach ($operations as $op) {
      $clause = $this->compileAlterOperation($op, $table);
      if ($clause) {
        $clauses[] = $clause;
      }
    }

    if (empty($clauses)) {
      return '';
    }

    return sprintf(
      "ALTER TABLE %s %s;",
      $this->wrap($table),
      implode(", ", $clauses)
    );
  }

  public function compileAlterOperation(AlterOperation $op, string $table): string
  {
    $type = $op->type;
    $payload = $op->payload;

    return match ($type) {
      AlterOperation::ADD_COLUMN => $this->compileAddColumn($payload),
      AlterOperation::MODIFY_COLUMN => $this->compileModifyColumn($payload),
      AlterOperation::RENAME_COLUMN => $this->compileRenameColumn($payload),
      AlterOperation::DROP_COLUMN => $this->compileDropColumn($payload),
      AlterOperation::ADD_INDEX => $this->compileAddIndex($payload),
      AlterOperation::DROP_INDEX => $this->compileDropIndex($payload),
      AlterOperation::ADD_UNIQUE => $this->compileAddUnique($payload),
      AlterOperation::ADD_FOREIGN => $this->compileAddForeignKey($payload, $table),
      AlterOperation::DROP_FOREIGN => $this->compileDropForeignKey($payload),
      default => ''
    };
  }

  protected function compileAddColumn(ColumnDefinition $column): string
  {
    return sprintf(
      "ADD COLUMN %s",
      $this->compileColumn($column)
    );
  }

  protected function compileModifyColumn(ColumnDefinition $column): string
  {
    return sprintf(
      "MODIFY COLUMN %s",
      $this->compileColumn($column)
    );
  }

  protected function compileRenameColumn(array $payload): string
  {
    $from = $payload['from'];
    $to = $payload['to'];
    $definition = $payload['definition'];

    // MySQL: CHANGE COLUMN `old` `new` <type>
    return sprintf(
      "CHANGE COLUMN %s %s",
      $this->wrap($from),
      $this->compileColumn($definition)
    );
  }

  protected function compileDropColumn(string $column): string
  {
    return sprintf(
      "DROP COLUMN %s",
      $this->wrap($column)
    );
  }

  protected function compileAddIndex(array $payload): string
  {
    $columns = $payload['columns'];
    $name = $payload['name'];

    $cols = implode(', ', array_map([$this, 'wrap'], $columns));

    // Generate name nếu không cung cấp
    if (!$name) {
      $name = 'idx_' . implode('_', $columns);
    }

    return sprintf(
      "ADD INDEX %s (%s)",
      $this->wrap($name),
      $cols
    );
  }

  protected function compileDropIndex(string $name): string
  {
    return sprintf(
      "DROP INDEX %s",
      $this->wrap($name)
    );
  }

  protected function compileAddUnique(array $payload): string
  {
    $columns = $payload['columns'];
    $name = $payload['name'];

    $cols = implode(', ', array_map([$this, 'wrap'], $columns));

    // Generate name nếu không cung cấp
    if (!$name) {
      $name = 'uniq_' . implode('_', $columns);
    }

    return sprintf(
      "ADD UNIQUE INDEX %s (%s)",
      $this->wrap($name),
      $cols
    );
  }

  protected function compileAddForeignKey(ForeignDefinition $fk, string $table): string
  {
    $constraintName = "fk_" . $table . "_" . $fk->getColumn();

    return sprintf(
      "ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s",
      $this->wrap($constraintName),
      $this->wrap($fk->getColumn()),
      $this->wrap($fk->getOnTable()),
      $this->wrap($fk->getReferences()),
      $fk->getOnDelete(),
      $fk->getOnUpdate()
    );
  }

  protected function compileDropForeignKey(string $constraintName): string
  {
    return sprintf(
      "DROP FOREIGN KEY %s",
      $this->wrap($constraintName)
    );
  }
}
