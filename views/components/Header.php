<?php

namespace App\Views\Components;

use App\Types\IBaseViewComponent;

class Header implements IBaseViewComponent
{
  public function render(): string
  {
    return <<<HTML
    <div>
      A better world without the cruelty!!
    </div>
    HTML;
  }
}
