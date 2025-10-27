<?php

namespace App\Views\Components;

use App\Types\IBaseViewComponent;

class Footer implements IBaseViewComponent
{
  public function render(): string
  {
    return <<<HTML
    <div>
      This is the end of the world
    </div>
    HTML;
  }
}
