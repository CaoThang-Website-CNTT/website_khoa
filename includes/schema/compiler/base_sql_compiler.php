<?php
namespace App\Schema\Compiler;

use App\Schema\{TableBuilder, ColumnDefinition, ForeignDefinition};

interface ISQLCompiler
{
  public function compile(TableBuilder $builder): string;
  public function compileColumn(ColumnDefinition $column): string;
  public function compileCommand(array|ForeignDefinition $command): ?string;
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
      $line = $this->compileCommand($command);
      if ($line)
        $lines[] = $line;
    }

    return $this->buildCreateTableString(
      $builder->getTable(),
      implode(",\n  ", array_filter($lines))
    );
  }
  protected function formatDefaultValue(mixed $value): string
  {
    if (is_bool($value))
      return $value ? '1' : '0';
    if (is_null($value))
      return 'NULL';
    return is_numeric($value) ? (string) $value : "'$value'";
  }
  abstract protected function wrap(string $value): string;
  abstract protected function buildCreateTableString(string $table, string $body): string;
}