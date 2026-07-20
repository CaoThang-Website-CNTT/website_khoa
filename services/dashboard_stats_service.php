<?php

namespace App\Services;

use App\Stores\DashboardStatsStore;

interface IDashboardStatsService
{
  public function getOverview(): array;
}

class DashboardStatsService implements IDashboardStatsService
{
  public function __construct(private DashboardStatsStore $store = new DashboardStatsStore())
  {
  }

  public function getOverview(): array
  {
    $cms = $this->store->getCmsOverview();
    $internship = $this->store->getInternshipOverview();
    $project = $this->store->getProjectOverview();
    $attentionItems = $this->buildAttentionItems($internship, $project);

    $kpis = array_values(array_filter([
      $this->buildKpi('cms_published', 'Bài viết đã công bố', $cms['published'] ?? null, 'Tin đang hiển thị', 'fa-solid fa-newspaper', 'admin/posts', $cms !== null),
      $this->buildKpi('internship_active', 'Đợt TTTN mở', $internship['active_count'] ?? null, 'Đang hoạt động', 'fa-solid fa-house-laptop', 'admin/internship_batches', $internship !== null),
      $this->buildKpi('project_active', 'Đợt DATN mở', $project['active_count'] ?? null, 'Đang hoạt động', 'fa-solid fa-graduation-cap', 'admin/project_batches', $project !== null),
      $this->buildKpi('attention_total', 'Cần xử lý', count($attentionItems) > 0 ? array_sum(array_column($attentionItems, 'count')) : null, 'Mục cần xem', 'fa-solid fa-list-check', null, count($attentionItems) > 0),
    ]));

    $cards = array_values(array_filter([
      $this->buildAttentionCard($attentionItems),
      $this->buildCmsDistributionCard($cms),
      $this->buildInternshipProgressCard($internship),
      $this->buildTimelineCard($internship, $project),
      $this->buildProjectProgressCard($project),
      $this->buildRecentActivityCard($this->store->getRecentActivity()),
    ]));

    return [
      'updated_at' => (!empty($kpis) || !empty($cards)) ? date('H:i d/m/Y') : null,
      'kpis' => $kpis,
      'cards' => $cards,
    ];
  }

  private function buildKpi(string $id, string $title, mixed $value, string $caption, string $icon, ?string $url, bool $isValid): ?array
  {
    if (!$isValid || $value === null) {
      return null;
    }

    $numericValue = (int) $value;
    if ($numericValue <= 0 && $id !== 'cms_published') {
      return null;
    }

    return [
      'id' => $id,
      'domain' => $this->domainFromId($id),
      'title' => $title,
      'value' => $numericValue,
      'caption' => $caption,
      'icon' => $icon,
      'url' => $url,
      'size' => 'sm',
      'visual_type' => 'number',
      'visual_data' => [],
      'priority' => 10,
      'is_valid' => true,
    ];
  }

  private function buildAttentionItems(?array $internship, ?array $project): array
  {
    $items = [];

    if ($internship !== null) {
      if (($internship['pending_referrals'] ?? 0) > 0) {
        $items[] = [
          'label' => 'Giấy giới thiệu TTTN chờ duyệt',
          'count' => (int) $internship['pending_referrals'],
          'url' => 'admin/internship_batches',
          'domain' => 'TTTN',
        ];
      }

      $missingCompany = $this->missingCount($internship['students'] ?? null, $internship['with_company'] ?? null);
      if ($missingCompany > 0) {
        $items[] = [
          'label' => 'Sinh viên TTTN chưa khai báo công ty',
          'count' => $missingCompany,
          'url' => 'admin/internship_batches',
          'domain' => 'TTTN',
        ];
      }

      $unassigned = $this->missingCount($internship['students'] ?? null, $internship['assigned'] ?? null);
      if ($unassigned > 0) {
        $items[] = [
          'label' => 'Sinh viên TTTN chưa phân công giảng viên',
          'count' => $unassigned,
          'url' => 'admin/internship_batches',
          'domain' => 'TTTN',
        ];
      }
    }

    if ($project !== null) {
      if (($project['pending_topics'] ?? 0) > 0) {
        $items[] = [
          'label' => 'Đề tài DATN chờ duyệt',
          'count' => (int) $project['pending_topics'],
          'url' => 'admin/project_batches',
          'domain' => 'DATN',
        ];
      }

      $unallocated = $this->missingCount($project['groups'] ?? null, $project['allocated_groups'] ?? null);
      if ($unallocated > 0) {
        $items[] = [
          'label' => 'Nhóm DATN chưa có đề tài',
          'count' => $unallocated,
          'url' => 'admin/project_batches',
          'domain' => 'DATN',
        ];
      }
    }

    return $items;
  }

  private function buildAttentionCard(array $items): ?array
  {
    if (empty($items)) {
      return null;
    }

    return [
      'id' => 'attention',
      'title' => 'Việc cần xử lý',
      'icon' => 'fa-solid fa-list-check',
      'size' => 'span-2 row-span-2',
      'visual_type' => 'attention',
      'visual_data' => ['items' => $items],
      'is_valid' => true,
    ];
  }

  private function buildCmsDistributionCard(?array $cms): ?array
  {
    if ($cms === null || ($cms['total'] ?? 0) <= 0) {
      return null;
    }

    $items = [];
    foreach ([
      'published' => 'Đã công bố',
      'draft' => 'Bản nháp',
    ] as $key => $label) {
      $count = (int) ($cms[$key] ?? 0);
      if ($count > 0) {
        $items[] = [
          'label' => $label,
          'value' => $count,
          'percent' => $this->percent($count, (int) $cms['total']),
        ];
      }
    }

    $detailGroups = array_values(array_filter([
      $this->postDetailGroup('Mới xuất bản', $cms['newest_posts'] ?? [], 'published_at'),
      $this->postDetailGroup('Xem nhiều nhất', $cms['most_viewed_posts'] ?? [], 'view_count'),
    ], fn(array $group) => !empty($group['items'])));

    if (empty($items) && empty($detailGroups)) {
      return null;
    }

    return [
      'id' => 'cms_distribution',
      'title' => 'Tình trạng bài viết',
      'icon' => 'fa-solid fa-newspaper',
      'size' => '',
      'visual_type' => 'bars',
      'visual_data' => ['items' => $items, 'detail_groups' => $detailGroups],
      'is_valid' => true,
    ];
  }

  private function postDetailGroup(string $title, array $posts, string $type): array
  {
    return [
      'title' => $title,
      'items' => array_values(array_filter(array_map(
        fn(array $post) => $this->postDetailItem($title, $post, $type),
        array_slice($posts, 0, 3)
      ))),
    ];
  }

  private function postDetailItem(string $label, ?array $post, string $type): ?array
  {
    if (!$post || empty($post['title'])) {
      return null;
    }

    $meta = '';
    if ($type === 'view_count') {
      $meta = number_format((int) ($post['view_count'] ?? 0)) . ' lượt xem';
    } elseif (!empty($post['published_at'])) {
      $time = strtotime((string) $post['published_at']);
      $meta = $time ? date('d/m/Y', $time) : '';
    }

    return [
      'label' => $label,
      'title' => (string) $post['title'],
      'meta' => $meta,
      'url' => 'admin/posts/' . (int) $post['id'],
    ];
  }

  private function buildInternshipProgressCard(?array $internship): ?array
  {
    if ($internship === null || ($internship['students'] ?? 0) <= 0) {
      return null;
    }

    $total = (int) $internship['students'];
    $items = array_values(array_filter([
      $this->progressItem('Khai báo công ty', $internship['with_company'] ?? null, $total),
      $this->progressItem('Phân công giảng viên', $internship['assigned'] ?? null, $total),
      $this->progressItem('Nộp hồ sơ', $internship['submitted'] ?? null, $total),
      $this->progressItem('Chốt điểm', $internship['graded'] ?? null, $total),
    ]));

    if (empty($items)) {
      return null;
    }

    return [
      'id' => 'internship_progress',
      'title' => 'Tiến độ TTTN',
      'icon' => 'fa-solid fa-house-laptop',
      'size' => 'span-2',
      'visual_type' => 'quota',
      'visual_data' => $this->quotaVisualData($items, $total),
      'is_valid' => true,
    ];
  }

  private function buildProjectProgressCard(?array $project): ?array
  {
    if ($project === null) {
      return null;
    }

    $items = array_values(array_filter([
      $this->progressItem('Đề tài được duyệt', $project['approved_topics'] ?? null, $project['total_topics'] ?? null),
      $this->progressItem('Nhóm đã có đề tài', $project['allocated_groups'] ?? null, $project['groups'] ?? null),
      $this->progressItem('Công bố phân công', $project['published_allocations'] ?? null, $project['active_count'] ?? null),
    ]));

    if (empty($items)) {
      return null;
    }

    return [
      'id' => 'project_progress',
      'title' => 'Tiến độ DATN',
      'icon' => 'fa-solid fa-graduation-cap',
      'size' => 'span-2',
      'visual_type' => $this->hasSharedTotal($items) ? 'quota' : 'progress',
      'visual_data' => $this->hasSharedTotal($items) ? $this->quotaVisualData($items, (int) $items[0]['total']) : ['items' => $items],
      'is_valid' => true,
    ];
  }

  private function hasSharedTotal(array $items): bool
  {
    if (count($items) < 2) {
      return false;
    }

    $total = (int) ($items[0]['total'] ?? 0);
    if ($total <= 0) {
      return false;
    }

    foreach ($items as $item) {
      if ((int) ($item['total'] ?? 0) !== $total) {
        return false;
      }
    }

    return true;
  }

  private function quotaVisualData(array $items, int $total): array
  {
    return [
      'total' => $total,
      'items' => array_map(function (array $item): array {
        return [
          'label' => $item['label'],
          'value' => (int) $item['value'],
          'total' => (int) $item['total'],
          'percent' => (int) $item['percent'],
        ];
      }, $items),
    ];
  }

  private function buildTimelineCard(?array $internship, ?array $project): ?array
  {
    $items = [];

    foreach (($internship['active_batches'] ?? []) as $batch) {
      $items[] = [
        'domain' => 'TTTN',
        'label' => $batch['title'],
        'date' => $batch['end_at'] ?? null,
        'badge' => $this->deadlineLabel($batch['end_at'] ?? null),
        'url' => 'admin/internship_batches/' . $batch['id'],
      ];
    }

    foreach (($project['active_batches'] ?? []) as $batch) {
      $items[] = [
        'domain' => 'DATN',
        'label' => $batch['title'],
        'date' => $batch['registration_end'] ?? null,
        'badge' => $this->deadlineLabel($batch['registration_end'] ?? null),
        'url' => 'admin/project_batches/' . $batch['id'],
      ];
    }

    $items = array_values(array_filter($items, fn(array $item) => !empty($item['date'])));
    usort($items, fn(array $a, array $b) => strcmp((string) $a['date'], (string) $b['date']));
    $items = array_slice($items, 0, 5);

    if (empty($items)) {
      return null;
    }

    return [
      'id' => 'timeline',
      'title' => 'Mốc thời gian gần nhất',
      'icon' => 'fa-regular fa-calendar',
      'size' => '',
      'visual_type' => 'timeline',
      'visual_data' => ['items' => $items],
      'is_valid' => true,
    ];
  }

  private function buildRecentActivityCard(array $items): ?array
  {
    if (empty($items)) {
      return null;
    }

    return [
      'id' => 'recent_activity',
      'title' => 'Hoạt động gần đây',
      'icon' => 'fa-solid fa-clock-rotate-left',
      'size' => '',
      'visual_type' => 'recent',
      'visual_data' => ['items' => $items],
      'is_valid' => true,
    ];
  }

  private function progressItem(string $label, ?int $value, ?int $total): ?array
  {
    if ($value === null || $total === null || $total <= 0) {
      return null;
    }

    return [
      'label' => $label,
      'value' => $value,
      'total' => $total,
      'percent' => $this->percent($value, $total),
    ];
  }

  private function percent(int $value, int $total): int
  {
    if ($total <= 0) {
      return 0;
    }

    return max(0, min(100, (int) round(($value / $total) * 100)));
  }

  private function missingCount(?int $total, ?int $done): int
  {
    if ($total === null || $done === null || $total <= 0) {
      return 0;
    }

    return max(0, $total - $done);
  }

  private function deadlineLabel(?string $date): string
  {
    if (!$date) {
      return 'Đang mở';
    }

    $target = strtotime($date);
    if ($target === false) {
      return 'Đang mở';
    }

    $days = (int) floor(($target - time()) / 86400);
    if ($days < 0) {
      return 'Đã quá hạn';
    }

    if ($days === 0) {
      return 'Hôm nay';
    }

    if ($days <= 7) {
      return 'Còn ' . $days . ' ngày';
    }

    return 'Đang mở';
  }

  private function domainFromId(string $id): string
  {
    if (str_starts_with($id, 'cms')) {
      return 'CMS';
    }

    if (str_starts_with($id, 'internship')) {
      return 'TTTN';
    }

    if (str_starts_with($id, 'project')) {
      return 'DATN';
    }

    return 'SYSTEM';
  }
}
