<?php
namespace App\Migration;

use App\Schema\TableBuilder;

abstract class BaseMigration
{
  abstract public function forward(TableBuilder $builder): void;

  abstract public function back(TableBuilder $builder): void;
}