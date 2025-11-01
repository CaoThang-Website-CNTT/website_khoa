<?php

namespace App\Views\Layouts\Sections;

use App\Core\ViewComponent;

class BaseSection extends ViewComponent
{
  private string $id;
  private string $title;
  private string $sub_title;
  private string $badge;
  private ViewComponent $children;
  public function __construct(array $props = [
    'id' => 'id',
    'title' => 'title',
    'sub_title' => 'sub_title',
    'badge' => '',
    'children' => null
  ])
  {
    $this->id = $props['id'];
    $this->title = $props['title'];
    $this->sub_title = $props['sub_title'];
    $this->badge = $props['badge'] ?? '';
    $this->children = $props['children'];
  }
  /** Main Render */
  public function render(): string
  {
    $badgeHtml = trim($this->badge) === '' ? '' : "
      <div class='section-badge px-4 py-2 rounded-2xl text-sm text-center mb-4'>{$this->e($this->badge)}</div>
    ";
    $titleHtml = trim($this->title) === '' ? '' : "
      <h2 class='text-4xl font-semibold text-center text-foreground'>{$this->e($this->title)}</h2>
    ";
    $subTitleHtml = trim($this->sub_title) === '' ? '' : "
      <p class='font-normal text-base text-center text-muted-foreground'>{$this->e($this->sub_title)}</p>
    ";

    return <<<HTML
    <section class='relative container py-16' id='{$this->id}'>
      <div class="container-wrapper">
        <div class="flex flex-col justify-center items-center gap-4 mb-12">
          {$badgeHtml}
          {$titleHtml}
          {$subTitleHtml}
        </div>
        {$this->children->render()}
      </div>
    </section>
    HTML;
  }
}
