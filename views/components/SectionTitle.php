<?php

namespace App\Views\Components;

use App\Core\ViewComponent;

class SectionTitle extends ViewComponent
{
  private string $title;
  private string $sub_title;
  public function __construct(string $title = 'title', string $sub_title = 'subtitle')
  {
    $this->title = $title;
    $this->sub_title = $sub_title;
  }
  /** Main Render */
  public function render(): string
  {
    return <<<HTML
      <div class="flex flex-col justify-center items-center gap-4">
        <h2 class="text-4xl font-semibold text-center text-foreground">{$this->title}</h2>
        <p class="font-normal text-base text-center text-muted-foreground mb-12">{$this->sub_title}</p>
      </div>
    HTML;
  }
}
