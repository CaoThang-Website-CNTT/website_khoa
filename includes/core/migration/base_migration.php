<?php
namespace App\Core\Migration;

use App\Core\Schema\TableBuilder;

abstract class BaseMigration
{
  abstract public function forward(TableBuilder $builder): void;

  abstract public function back(TableBuilder $builder): void;
}