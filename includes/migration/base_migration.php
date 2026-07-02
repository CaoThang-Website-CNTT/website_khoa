<?php
namespace App\Migration;

use App\Core\Schema\TableBuilder;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use InvalidArgumentException;
use Database;

abstract class BaseMigration
{
  abstract public function forward(TableBuilder $builder): void;

  abstract public function back(TableBuilder $builder): void;

  /**
   * Run a parameterized equality-only data update from a migration.
   * Schema changes must still be expressed through TableBuilder/AlterBuilder.
   */
  protected function updateData(string $table, array $values, array $conditions): int
  {
    [$sql, $bindings] = $this->compileDataUpdate($table, $values, $conditions);
    $stmt = Database::getInstance()->getConnection()->prepare($sql);
    $stmt->execute($bindings);
    return $stmt->rowCount();
  }

  /** @return array{0:string,1:array} */
  protected function compileDataUpdate(string $table, array $values, array $conditions): array
  {
    $identifier = '/^[A-Za-z_][A-Za-z0-9_]*$/';
    if (!preg_match($identifier, $table)) {
      throw new InvalidArgumentException('Invalid migration table identifier.');
    }
    if ($values === [] || $conditions === []) {
      throw new InvalidArgumentException('Migration data updates require values and conditions.');
    }
    foreach (array_merge(array_keys($values), array_keys($conditions)) as $column) {
      if (!is_string($column) || !preg_match($identifier, $column)) {
        throw new InvalidArgumentException('Invalid migration column identifier.');
      }
    }

    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from($table)
      ->update($values);
    foreach ($conditions as $column => $value) {
      $query->eq($column, $value);
    }

    return [$query->toSql(), $query->getBindings()];
  }
}
