<?php

namespace App\Views\Components;

use App\Core\ViewComponent;

class Footer extends ViewComponent
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
