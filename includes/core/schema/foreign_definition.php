<?php
namespace App\Core\Schema;

class ForeignDefinition
{
  protected string $column;
  protected string $onTable;
  protected string $referencesColumn;
  protected string $onDelete = 'RESTRICT';
  protected string $onUpdate = 'RESTRICT';

  public function __construct(string $column)
  {
    $this->column = $column;
  }

  public function on(string $table): self
  {
    $this->onTable = $table;
    return $this;
  }

  public function references(string $column): self
  {
    $this->referencesColumn = $column;
    return $this;
  }
  /**
   * [cascade, set null, restrict]
   * @param string $action
   * @return ForeignDefinition
   */
  public function onDelete(string $action): self
  {
    $this->onDelete = strtoupper($action); // CASCADE, SET NULL, RESTRICT
    return $this;
  }

  public function onUpdate(string $action): self
  {
    $this->onUpdate = strtoupper($action);
    return $this;
  }

  // Getters for the MySQL Compiler
  public function getColumn(): string
  {
    return $this->column;
  }
  public function getOnTable(): string
  {
    return $this->onTable;
  }
  public function getReferences(): string
  {
    return $this->referencesColumn;
  }
  public function getOnDelete(): string
  {
    return $this->onDelete;
  }
  public function getOnUpdate(): string
  {
    return $this->onUpdate;
  }
}