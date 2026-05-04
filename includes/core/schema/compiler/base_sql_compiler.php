<?php
namespace App\Core\Schema\Compiler;

use App\Core\Schema\{TableBuilder, ColumnDefinition, ForeignDefinition};

interface ISQLCompiler
{
  // Schema (DDL) methods
  public function compile(TableBuilder $builder): string;
  public function compileColumn(ColumnDefinition $column): string;
  public function compileCommand(array|ForeignDefinition $command, string $fromTable): ?string;

  // Query (DML) methods
  public function compileSelect(string $table, array $columns, array $wheres, array $joins, array $orders, ?int $limit, ?int $offset): string;
  public function compileInsert(string $table, array $data): string;
  public function compileUpdate(string $table, array $data, array $wheres): string;
  public function compileDelete(string $table, array $wheres): string;
}

abstract class BaseSQLCompiler implements ISQLCompiler
{
  public function compile(TableBuilder $builder): string
  {
    $lines = [];

    foreach ($builder->getColumns() as $column) {
      $lines[] = $this->compileColumn($column);
    }

    foreach ($builder->getCommands() as $command) {
      $line = $this->compileCommand($command, $builder->getTable());
      if ($line)
        $lines[] = $line;
    }

    return $this->buildCreateTableString(
      $builder->getTable(),
      implode(",\n  ", array_filter($lines))
    );
  }
  public function compileSelect(string $table, array $columns, array $wheres, array $joins, array $orders, ?int $limit, ?int $offset): string
  {
    $sql = [];

    $sql[] = "SELECT " . implode(', ', array_map([$this, 'wrap'], $columns));
    $sql[] = "FROM " . $this->wrap($table);

    if (!empty($joins)) {
      foreach ($joins as $join) {
        $sql[] = strtoupper($join['type']) . " JOIN " . $this->wrap($join['table']) .
          " ON " . $this->wrap($join['first']) . " " . $join['operator'] . " " . $this->wrap($join['second']);
      }
    }

    if (!empty($wheres)) {
      $sql[] = "WHERE " . $this->compileWheres($wheres);
    }

    if (!empty($orders)) {
      $orderParts = array_map(fn($o) => $this->wrap($o['column']) . " " . $o['direction'], $orders);
      $sql[] = "ORDER BY " . implode(', ', $orderParts);
    }

    if ($limit !== null) {
      $sql[] = $this->compileLimit($limit, $offset);
    }

    return implode(' ', $sql);
  }
  protected function compileWheres(array $wheres): string
  {
    $sql = [];
    foreach ($wheres as $i => $w) {
      $lead = ($i === 0) ? '' : ($w['boolean'] ?? 'AND') . ' ';

      if (($w['type'] ?? 'Basic') === 'Null') {
        $sql[] = $lead . $this->wrap($w['column']) . " " . $w['operator'];
      } else {
        $sql[] = $lead . $this->wrap($w['column']) . " " . $w['operator'] . " ?";
      }
    }
    return implode(' ', $sql);
  }

  public function compileInsert(string $table, array $data): string
  {
    $columns = implode(', ', array_map([$this, 'wrap'], array_keys($data)));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));

    // Kiểm tra nếu là Bulk Insert
    if (isset($data[0]) && is_array($data[0])) {
      $columns = implode(', ', array_map([$this, 'wrap'], array_keys($data[0])));

      $rows = [];
      foreach ($data as $row) {
        $rows[] = "(" . implode(', ', array_fill(0, count($row), '?')) . ")";
      }
      $placeholders = implode(', ', $rows);
    } else {
      // Single Insert cũ
      $columns = implode(', ', array_map([$this, 'wrap'], array_keys($data)));
      $placeholders = "(" . implode(', ', array_fill(0, count($data), '?')) . ")";
    }

    return sprintf(
      "INSERT INTO %s (%s) VALUES %s",
      $this->wrap($table),
      $columns,
      $placeholders
    );
  }

  public function compileUpdate(string $table, array $data, array $wheres): string
  {
    $sets = implode(', ', array_map(
      fn($col) => $this->wrap($col) . ' = ?',
      array_keys($data)
    ));

    $sql = sprintf("UPDATE %s SET %s", $this->wrap($table), $sets);

    if (!empty($wheres)) {
      $sql .= ' WHERE ' . $this->compileWheres($wheres);
    }

    return $sql;
  }

  public function compileDelete(string $table, array $wheres): string
  {
    $sql = sprintf("DELETE FROM %s", $this->wrap($table));

    if (!empty($wheres)) {
      $sql .= ' WHERE ' . $this->compileWheres($wheres);
    }

    return $sql;
  }

  protected function formatDefaultValue(mixed $value): string
  {
    if (is_bool($value))
      return $value ? '1' : '0';
    if (is_null($value))
      return 'NULL';
    if (is_string($value) && $value === 'CURRENT_TIMESTAMP')
      return $value;
    return is_numeric($value) ? (string) $value : "'$value'";
  }

  abstract public function wrap(string $value): string;
  abstract protected function buildCreateTableString(string $table, string $body): string;
  abstract protected function compileLimit(int $limit, int $offset): string;
}