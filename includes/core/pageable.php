<?php
namespace App\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Pageable implements IteratorAggregate, Countable, JsonSerializable
{
  protected array $items;
  protected int $total;
  protected int $per_page;
  protected int $current_page;

  public function __construct(array $items, int $total, int $per_page, int $current_page)
  {
    $this->items = $items;
    $this->total = $total;
    $this->per_page = $per_page;
    $this->current_page = max(1, $current_page);
  }

  public function getItems(): array
  {
    return $this->items;
  }

  public function getTotalPages(): int
  {
    return (int) max(ceil($this->total / $this->per_page), 1);
  }

  public function hasPages(): bool
  {
    return $this->getTotalPages() > 1;
  }

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

  public function getElements(): array
  {
    $totalPages = $this->getTotalPages();
    if ($totalPages < 1)
      return [];

    if ($totalPages <= 7) {
      return range(1, $totalPages);
    }

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

  public function getTotal(): int
  {
    return $this->total;
  }

  public function getPerPage(): int
  {
    return $this->per_page;
  }

  public function getCurrentPage(): int
  {
    return $this->current_page;
  }

  public function getIterator(): Traversable
  {
    return new ArrayIterator($this->items);
  }

  public function count(): int
  {
    return count($this->items);
  }

  public function jsonSerialize(): mixed
  {
    return [
      'data' => $this->items,
      'meta' => [
        'current_page' => $this->current_page,
        'per_page' => $this->per_page,
        'total' => $this->total,
        'last_page' => $this->getTotalPages(),
      ],
      'links' => [
        'prev' => $this->prevPageUrl(),
        'next' => $this->nextPageUrl(),
      ]
    ];
  }
}
?>