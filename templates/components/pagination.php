<?php

namespace App\Components;

class Pagination
{
  public static function render(int $currentPage, int $totalPages, string $baseUrl = '?page=')
  {
    if ($totalPages <= 1) return '';

    $html = '<nav aria-label="Page navigation" class="pagination-wrapper"><ul class="pagination">';
    // Nút Prev
    if ($currentPage > 1) {
      $html .= '<li class="page-item"><a class="page-numbers prev" href="' . $baseUrl . ($currentPage - 1) . '">Prev</a></li>';
    }

    // Logic hiển thị trang
    $delta = 2; // Số trang hiển thị hai bên trang hiện tại
    for ($i = 1; $i <= $totalPages; $i++) {
      if (
        $i == 1 || $i == $totalPages || // Luôn hiện trang đầu và cuối
        ($i >= $currentPage - $delta && $i <= $currentPage + $delta) // Hiện các trang xung quanh trang hiện tại
      ) {
        if ($i == $currentPage) {
          $html .= '<li class="page-item active"><span aria-current="page" class="page-numbers current">' . $i . '</span></li>';
        } else {
          $html .= '<li class="page-item"><a class="page-numbers" href="' . $baseUrl . $i . '">' . $i . '</a></li>';
        }
      } elseif (
        $i == $currentPage - $delta - 1 ||
        $i == $currentPage + $delta + 1
      ) {
        // Thêm dấu ba chấm
        $html .= '<li class="page-item disabled"><span class="page-numbers dots">...</span></li>';
      }
    }
    // Nút Next
    if ($currentPage < $totalPages) {
      $html .= '<li class="page-item"><a class="page-numbers next" href="' . $baseUrl . ($currentPage + 1) . '">Next</a></li>';
    }
    $html .= '</ul></nav>';

    return $html;
  }
}
