<?php
namespace App\Core\Schema;

trait ColumnTypeTrait
{
  // ─── Primary Key & Identity ───────────────────────────────────────────────

  public function id(string $name = 'id'): ColumnDefinition
  {
    return $this->addColumn('bigint', $name, [
      'unsigned' => true,
      'auto_increment' => true,
      'primary' => true,
    ]);
  }

  // ─── Numeric Types ────────────────────────────────────────────────────────

  public function tinyInt(string $name): ColumnDefinition
  {
    return $this->addColumn('tinyint', $name);
  }

  public function smallInt(string $name): ColumnDefinition
  {
    return $this->addColumn('smallint', $name);
  }

  public function mediumInt(string $name): ColumnDefinition
  {
    return $this->addColumn('mediumint', $name);
  }

  public function int(string $name): ColumnDefinition
  {
    return $this->addColumn('int', $name);
  }

  public function bigInt(string $name): ColumnDefinition
  {
    return $this->addColumn('bigint', $name);
  }

  public function decimal(string $name, int $precision = 8, int $scale = 2): ColumnDefinition
  {
    return $this->addColumn('decimal', $name, [
      'precision' => $precision,
      'scale' => $scale,
    ]);
  }

  public function float(string $name): ColumnDefinition
  {
    return $this->addColumn('float', $name);
  }

  public function double(string $name): ColumnDefinition
  {
    return $this->addColumn('double', $name);
  }

  // ─── String & Character Types ─────────────────────────────────────────────

  public function char(string $name, int $length): ColumnDefinition
  {
    return $this->addColumn('char', $name, ['length' => $length]);
  }

  public function varchar(string $name, int $length = 255): ColumnDefinition
  {
    return $this->addColumn('varchar', $name, ['length' => $length]);
  }

  public function text(string $name): ColumnDefinition
  {
    return $this->addColumn('text', $name);
  }

  public function mediumText(string $name): ColumnDefinition
  {
    return $this->addColumn('mediumtext', $name);
  }

  public function longText(string $name): ColumnDefinition
  {
    return $this->addColumn('longtext', $name);
  }

  public function blob(string $name): ColumnDefinition
  {
    return $this->addColumn('blob', $name);
  }

  // ─── Date & Time Types ────────────────────────────────────────────────────

  public function date(string $name): ColumnDefinition
  {
    return $this->addColumn('date', $name);
  }

  public function dateTime(string $name): ColumnDefinition
  {
    return $this->addColumn('datetime', $name);
  }

  public function timestamp(string $name): ColumnDefinition
  {
    return $this->addColumn('timestamp', $name);
  }

  public function time(string $name): ColumnDefinition
  {
    return $this->addColumn('time', $name);
  }

  public function year(string $name): ColumnDefinition
  {
    return $this->addColumn('year', $name);
  }

  // ─── Specialized Types ────────────────────────────────────────────────────

  public function boolean(string $name): ColumnDefinition
  {
    return $this->addColumn('tinyint', $name, ['length' => 1]);
  }

  public function json(string $name): ColumnDefinition
  {
    return $this->addColumn('json', $name);
  }

  public function enum(string $name, array $allowed): ColumnDefinition
  {
    return $this->addColumn('enum', $name, ['allowed' => $allowed]);
  }

  // ─── Convenience helpers ──────────────────────────────────────────────────

  public function softDeletes(string $name = 'deleted_at'): ColumnDefinition
  {
    return $this->dateTime($name)->nullable();
  }
}