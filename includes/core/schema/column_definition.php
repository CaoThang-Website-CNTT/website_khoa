<?php
namespace App\Core\Schema;

class ColumnDefinition
{
  public string $name;
  public string $type;
  public array $attributes = [
    'length' => null,
    'precision' => null,
    'scale' => null,
    'nullable' => false,
    'default' => null,
    'unsigned' => false,
    'auto_increment' => false,
    'primary' => false,
    'unique' => false,
    'comment' => null,
    'allowed' => [] // Cho ENUMs
  ];
  protected ?string $onUpdate = null;

  public function __construct(string $name, string $type, array $params = [])
  {
    $this->name = $name;
    $this->type = $type;
    $this->attributes = array_merge($this->attributes, $params);
  }
  public function comment(string $text): self
  {
    $this->attributes['comment'] = $text;
    return $this;
  }
  public function nullable(bool $value = true): self
  {
    $this->attributes['nullable'] = $value;
    return $this;
  }

  public function default(mixed $value): self
  {
    $this->attributes['default'] = $value;
    return $this;
  }

  public function unsigned(): self
  {
    $this->attributes['unsigned'] = true;
    return $this;
  }

  public function unique(): self
  {
    $this->attributes['unique'] = true;
    return $this;
  }

  public function onUpdate(string $value): self
  {
    $this->onUpdate = $value;
    return $this;
  }

  public function getOnUpdate(): ?string
  {
    return $this->onUpdate;
  }
}