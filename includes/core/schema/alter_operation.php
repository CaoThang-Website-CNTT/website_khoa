<?php
namespace App\Core\Schema;

class AlterOperation
{
  // ─── Operation type constants ─────────────────────────────────────────────

  /** ADD COLUMN - thêm cột mới */
  const ADD_COLUMN = 'ADD_COLUMN';

  /**
   * MODIFY COLUMN - đổi type/constraint, giữ nguyên tên.
   */
  const MODIFY_COLUMN = 'MODIFY_COLUMN';

  /**
   * CHANGE COLUMN - đổi tên cột, đồng thời có thể đổi type.
   */
  const RENAME_COLUMN = 'RENAME_COLUMN';

  /** DROP COLUMN */
  const DROP_COLUMN = 'DROP_COLUMN';

  /** ADD INDEX */
  const ADD_INDEX = 'ADD_INDEX';

  /** DROP INDEX */
  const DROP_INDEX = 'DROP_INDEX';

  /** ADD UNIQUE INDEX */
  const ADD_UNIQUE = 'ADD_UNIQUE';

  /** ADD FOREIGN KEY */
  const ADD_FOREIGN = 'ADD_FOREIGN';

  /** DROP FOREIGN KEY */
  const DROP_FOREIGN = 'DROP_FOREIGN';

  /** RENAME TABLE */
  const RENAME_TABLE = 'RENAME_TABLE';

  // ─── Properties ──────────────────────────────────────────────────────────

  public string $type;

  /**
   * Payload phụ thuộc vào type:
   *
   * ADD_COLUMN    → ColumnDefinition
   * MODIFY_COLUMN → ColumnDefinition
   * RENAME_COLUMN → ['from' => string, 'to' => string, 'definition' => ColumnDefinition]
   * DROP_COLUMN   → string (column name)
   * ADD_INDEX     → ['columns' => string[], 'name' => ?string]
   * DROP_INDEX    → string (index name)
   * ADD_UNIQUE    → ['columns' => string[], 'name' => ?string]
   * ADD_FOREIGN   → ForeignDefinition
   * DROP_FOREIGN  → string (constraint name)
   * RENAME_TABLE  → string (new table name)
   */
  public mixed $payload;

  public function __construct(string $type, mixed $payload)
  {
    $this->type = $type;
    $this->payload = $payload;
  }
}