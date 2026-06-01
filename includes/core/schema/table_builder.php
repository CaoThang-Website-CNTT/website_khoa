<?php
namespace App\Core\Schema;

interface ITableBuilder
{
  /**
   * DDL - Tạo / chỉnh sửa bảng
   */
  public function create(string $tableName, callable $callback): void;
  public function alter(string $tableName, callable $callback): void;

  /**
   * Constraints & Indexes
   * 
   * @example
   * $table->index(['mime_type', 'created_at'], 'idx_media_mime_type_created_at');
   */
  public function primary(string|array $columns): static;
  public function index(string|array $columns, ?string $name = null): static;
  public function unique(string|array $columns, ?string $name = null): static;
  public function foreign(string $column): ForeignDefinition;

  /**
   * Helpers
   */
  public function disableForeignKeys(): static;
  public function enableForeignKeys(): static;
  public function drop(string $name): static;

  public function getColumns(): array;
  public function getCommands(): array;
  public function getTable(): string;
  public function getTablesToCreate(): array;
  public function getTablesToAlter(): array;
}

class TableBuilder implements ITableBuilder
{
  use ColumnTypeTrait;

  /** *@var TableBuilder[] - Lưu các TableBuilder con */
  protected array $tablesToCreate = [];
  /** @var AlterBuilder[] — các ALTER TABLE builder con */
  protected array $tablesToAlter = [];
  protected string $tableName;
  protected array $columns = [];
  protected array $commands = []; // Hậu xử lý sau khi tạo bảng như tạo constraint, etc

  public function __construct(string $tableName = "")
  {
    $this->tableName = $tableName;
  }

  protected function addColumn(string $type, string $name, array $params = []): ColumnDefinition
  {
    $col = new ColumnDefinition($name, $type, $params);
    $this->columns[] = $col;
    return $col;
  }

  public function create(string $tableName, callable $callback): void
  {
    $childBuilder = new self($tableName);
    $callback($childBuilder);
    $this->tablesToCreate[] = $childBuilder;
  }

  public function alter(string $tableName, callable $callback): void
  {
    $childBuilder = new AlterBuilder($tableName);
    $callback($childBuilder);
    $this->tablesToAlter[] = $childBuilder;
  }

  public function timestamps(): static
  {
    $this->timestamp('created_at')->default('CURRENT_TIMESTAMP');
    $this->timestamp('updated_at')
      ->default('CURRENT_TIMESTAMP')
      ->onUpdate('CURRENT_TIMESTAMP');
    return $this;
  }

  /**
   * Constraints & Indexes
   */
  public function primary(string|array $columns): static
  {
    $this->commands[] = ['type' => 'primary', 'columns' => (array) $columns];
    return $this;
  }

  public function index(string|array $columns, ?string $name = null): static
  {
    $this->commands[] = ['type' => 'index', 'columns' => (array) $columns, 'name' => $name];
    return $this;
  }

  public function unique(string|array $columns, ?string $name = null): static
  {
    $this->commands[] = ['type' => 'unique', 'columns' => (array) $columns, 'name' => $name];
    return $this;
  }

  public function foreign(string $column): ForeignDefinition
  {
    $spec = new ForeignDefinition($column);
    $this->commands[] = $spec;
    return $spec;
  }

  public function disableForeignKeys(): static
  {
    $this->commands[] = "SET FOREIGN_KEY_CHECKS = 0;";
    return $this;
  }

  public function enableForeignKeys(): static
  {
    $this->commands[] = "SET FOREIGN_KEY_CHECKS = 1;";
    return $this;
  }

  public function drop(string $tableNameName): static
  {
    $this->commands[] = "DROP TABLE IF EXISTS `$tableNameName`;";
    return $this;
  }

  public function getColumns(): array
  {
    return $this->columns;
  }

  public function getCommands(): array
  {
    return $this->commands;
  }

  public function getTable(): string
  {
    return $this->tableName;
  }

  /** @return TableBuilder[] */
  public function getTablesToCreate(): array
  {
    return $this->tablesToCreate;
  }

  /** @return AlterBuilder[] */
  public function getTablesToAlter(): array
  {
    return $this->tablesToAlter;
  }
}