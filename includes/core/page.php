<?php

namespace App\Core;

class Page
{
  protected int $total;
  protected int $per_page;
  protected int $current_page;

  public function __construct(int $total, int $per_page, int $current_page)
  {
    $this->total = $total;
    $this->per_page = $per_page;
    $this->current_page = $current_page ?: 1;
  }

  public function getTotalPages(): int
  {
    return (int) max(ceil($this->total / $this->per_page), 1);
  }

  public function hasPages(): bool
  {
    return $this->getTotalPages() > 1;
  }

  /**
   * Generate a URL for a specific page number, keeping existing query strings (like ?search=)
   */
  public function url(int $page): string
  {
    if ($page <= 0 || $page > $this->getTotalPages()) {
      return '#';
    }

    $request = request();
    $query = $request->query();
    $query['page'] = $page;

    return $request->uri() . '?' . http_build_query($query);
  }

  public function nextPageUrl(): ?string
  {
    return $this->current_page < $this->getTotalPages() ? $this->url($this->current_page + 1) : null;
  }

  public function prevPageUrl(): ?string
  {
    return $this->current_page > 1 ? $this->url($this->current_page - 1) : null;
  }

  /**
   * Calculates the "Sliding Window" of links (e.g., 1 2 ... 5 6 7 ... 10)
   */
  public function getElements(): array
  {
    $totalPages = $this->getTotalPages();
    if ($totalPages < 1)
      return [];

    // If total pages are 7 or less, just show all of them
    if ($totalPages <= 7) {
      return range(1, $totalPages);
    }

    // Sliding window logic
    if ($this->current_page <= 4) {
      return [1, 2, 3, 4, 5, '...', $totalPages];
    }

    if ($this->current_page >= $totalPages - 3) {
      return [1, '...', $totalPages - 4, $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];
    }

    return [
      1,
      '...',
      $this->current_page - 1,
      $this->current_page,
      $this->current_page + 1,
      '...',
      $totalPages
    ];
  }
  public function getTotal(): ?string
  {
    return $this->total;
  }

  public function getPerPage(): ?int
  {
    return $this->per_page;
  }
  public function getCurrentPage(): ?int
  {
    return $this->current_page;
  }
}