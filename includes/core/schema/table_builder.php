<?php
namespace App\Core\Schema;

interface ITableBuilder
{
  /**
   * Primary Key & Identity
   */
  public function id(string $name = 'id'): ColumnDefinition;
  public function primary(string|array $columns): self;

  /**
   * Numeric Types
   */
  public function tinyInt(string $name): ColumnDefinition;
  public function smallInt(string $name): ColumnDefinition;
  public function mediumInt(string $name): ColumnDefinition;
  public function int(string $name): ColumnDefinition;
  public function bigInt(string $name): ColumnDefinition;
  public function decimal(string $name, int $precision = 8, int $scale = 2): ColumnDefinition;
  public function float(string $name): ColumnDefinition;
  public function double(string $name): ColumnDefinition;

  /**
   * String & Character Types
   */
  public function char(string $name, int $length): ColumnDefinition;
  public function varchar(string $name, int $length = 255): ColumnDefinition;
  public function text(string $name): ColumnDefinition;
  public function mediumText(string $name): ColumnDefinition;
  public function longText(string $name): ColumnDefinition;
  public function blob(string $name): ColumnDefinition;

  /**
   * Date & Time Types
   */
  public function date(string $name): ColumnDefinition;
  public function dateTime(string $name): ColumnDefinition;
  public function timestamp(string $name): ColumnDefinition;
  public function time(string $name): ColumnDefinition;
  public function year(string $name): ColumnDefinition;
  public function timestamps(): self;

  /**
   * Specialized Types
   */
  public function boolean(string $name): ColumnDefinition;
  public function json(string $name): ColumnDefinition;
  public function enum(string $name, array $allowed): ColumnDefinition;
  public function softDeletes(string $name = "deleted_at"): ColumnDefinition;

  /**
   * Constraints & Indexes
   */
  public function index(string|array $columns, ?string $name = null): self;
  public function unique(string|array $columns, ?string $name = null): self;
  public function foreign(string $column): ForeignDefinition;

  /**
   * Helpers
   */
  public function disableForeignKeys(): self;
  public function enableForeignKeys(): self;
  public function drop(string $name): self;
  public function getColumns(): array;
  public function getTable(): string;
  public function getTablesToCreate(): array;
}

class TableBuilder implements ITableBuilder
{
  /**
   * Lưu các TableBuilder con
   * @var TableBuilder[]
   */
  protected array $tablesToCreate = [];
  protected string $tableName;
  protected array $columns = [];
  protected array $commands = []; // Hậu xử lý sau khi tạo bảng như tạo constraint, etc

  public function __construct(string $tableName = "")
  {
    $this->tableName = $tableName;
  }

  public function create(string $tableName, callable $callback): void
  {
    $childBuilder = new self($tableName);
    $callback($childBuilder);
    $this->tablesToCreate[] = $childBuilder;
  }

  protected function addColumn(string $type, string $name, array $params = []): ColumnDefinition
  {
    $col = new ColumnDefinition($name, $type, $params);
    $this->columns[] = $col;
    return $col;
  }

  /**
   * Primary Key & Identity
   */
  public function id(string $name = 'id'): ColumnDefinition
  {
    return $this->addColumn('bigint', $name, [
      'unsigned' => true,
      'auto_increment' => true,
      'primary' => true
    ]);
  }

  /**
   * Numeric Types
   */
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
    return $this->addColumn('decimal', $name, ['precision' => $precision, 'scale' => $scale]);
  }

  public function float(string $name): ColumnDefinition
  {
    return $this->addColumn('float', $name);
  }
  public function double(string $name): ColumnDefinition
  {
    return $this->addColumn('double', $name);
  }

  /**
   * String & Character Types
   */
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
  /**
   * Date & Time Types
   */
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

  public function timestamps(): self
  {
    $this->timestamp('created_at')->nullable();
    $this->timestamp('updated_at')->nullable();
    return $this;
  }

  /**
   * Specialized Types
   */
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
  public function softDeletes(string $name = 'deleted_at'): ColumnDefinition
  {
    return $this->dateTime($name)->nullable();
  }

  /**
   * Constraints & Indexes
   */
  public function primary(string|array $columns): self
  {
    $this->commands[] = ['type' => 'primary', 'columns' => (array) $columns];
    return $this;
  }

  public function index(string|array $columns, ?string $name = null): self
  {
    $this->commands[] = ['type' => 'index', 'columns' => (array) $columns, 'name' => $name];
    return $this;
  }

  public function unique(string|array $columns, ?string $name = null): self
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

  public function disableForeignKeys(): self
  {
    $this->commands[] = "SET FOREIGN_KEY_CHECKS = 0;";
    return $this;
  }

  public function enableForeignKeys(): self
  {
    $this->commands[] = "SET FOREIGN_KEY_CHECKS = 1;";
    return $this;
  }

  public function drop(string $tableNameName): self
  {
    $this->commands[] = "DROP TABLE IF EXISTS `$tableNameName`;";
    return $this;
  }

  public function getCommands(): array
  {
    return $this->commands;
  }

  public function getColumns(): array
  {
    return $this->columns;
  }
  public function getTablesToCreate(): array
  {
    return $this->tablesToCreate;
  }
  public function getTable(): string
  {
    return $this->tableName;
  }
}