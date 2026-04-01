<?php
namespace App\Console\Commands;

abstract class BaseCommand
{
  protected string $name;
  protected string $paramsDescription;
  protected string $description;

  // Every command must implement this
  abstract public function handle(array $args): void;

  public function getName(): string
  {
    return $this->name;
  }
  public function getParamsDescription(): string
  {
    return $this->paramsDescription;
  }
  public function getDescription(): string
  {
    return $this->description;
  }
}