<?php

namespace App\Components;

class Pagination
{
  private static function chevronLeft(): string
  {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
  }

  private static function chevronRight(): string
  {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';
  }

  private static function ellipsis(): string
  {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>';
  }

  private static function renderPrev(int $currentPage, string $baseUrl): string
  {
    $icon = self::chevronLeft();
    $isDisabled = $currentPage <= 1;
    $tag = $isDisabled ? 'span' : 'a';
    $href = $isDisabled ? '' : 'href="' . $baseUrl . ($currentPage - 1) . '"';
    $disabled = $isDisabled ? 'data-disabled' : '';
    $ariaDis = $isDisabled ? 'aria-disabled="true"' : 'aria-label="Go to previous page"';

    return <<<HTML
      <li class="pagination-item">
        <{$tag} class="pagination-link pagination-prev" {$disabled} {$href} {$ariaDis}>
          {$icon}
          <span>Trước</span>
        </{$tag}>
      </li>
      HTML;
  }

  private static function renderNext(int $currentPage, int $totalPages, string $baseUrl): string
  {
    $icon = self::chevronRight();
    $isDisabled = $currentPage >= $totalPages;
    $tag = $isDisabled ? 'span' : 'a';
    $href = $isDisabled ? '' : 'href="' . $baseUrl . ($currentPage + 1) . '"';
    $disabled = $isDisabled ? 'data-disabled' : '';
    $ariaDis = $isDisabled ? ' aria-disabled="true"' : 'aria-label="Go to next page"';

    return <<<HTML
      <li class="pagination-item">
        <{$tag} class="pagination-link pagination-next" {$disabled} {$href} {$ariaDis}>
          <span>Sau</span>
          {$icon}
        </{$tag}>
      </li>
      HTML;
  }

  private static function renderPageNumbers(int $currentPage, int $totalPages, string $baseUrl): string
  {
    $delta = 2;
    $html = '';

    for ($i = 1; $i <= $totalPages; $i++) {
      $inRange = $i >= $currentPage - $delta && $i <= $currentPage + $delta;
      $isBoundary = $i == 1 || $i == $totalPages;

      if ($isBoundary || $inRange) {
        $isActive = $i == $currentPage;
        $tag = $isActive ? 'span' : 'a';
        $active = $isActive ? 'data-active' : '';
        $href = $isActive ? '' : 'href="' . $baseUrl . $i . '"';
        $ariaCur = $isActive ? ' aria-current="page"' : 'aria-label="Go to page ' . $i . '"';

        $html .= <<<HTML
          <li class="pagination-item">
            <{$tag} class="pagination-link" {$href} {$active} {$ariaCur}>{$i}</{$tag}>
          </li>
          HTML;

      } elseif ($i == $currentPage - $delta - 1 || $i == $currentPage + $delta + 1) {
        $icon = self::ellipsis();

        $html .= <<<HTML
          <li class="pagination-item pagination-item--ellipsis" aria-hidden="true">
            <span class="pagination-ellipsis">
              {$icon}
              <span class="sr-only">More pages</span>
            </span>
          </li>
          HTML;
      }
    }

    return $html;
  }

  public static function render(int $currentPage, int $totalPages, string $baseUrl = '?page='): string
  {
    if ($totalPages <= 1)
      return '';

    $prev = self::renderPrev($currentPage, $baseUrl);
    $pages = self::renderPageNumbers($currentPage, $totalPages, $baseUrl);
    $next = self::renderNext($currentPage, $totalPages, $baseUrl);

    return <<<HTML
      <nav role="navigation" aria-label="pagination" class="pagination-nav">
        <ul class="pagination-content">
          {$prev}
          {$pages}
          {$next}
        </ul>
      </nav>
      HTML;
  }
}