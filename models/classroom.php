<?php

namespace App\Models;

class Classroom
{
  public function __construct(
    public int $id,
    public string $name,
    public string $description,
  ) {
  }
}