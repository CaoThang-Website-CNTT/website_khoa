<?php

namespace App\Services;

use App\Core\Pageable;
use App\Stores\InternshipWeeklyReportStore;
use DateTime;
use Exception;

interface IInternshipWeeklyReportService
{
  public function calculateWeeks(string $startAt, string $endAt): array;
  public function submitWeeklyReport(int $batchStudentId, int $weekNumber, ?string $content, bool $isExempt, array $imagesData, string $startAt, string $endAt): int;
  public function getStudentWeeklySummary(int $batchStudentId, string $startAt, string $endAt): array;
  public function getStudentWeeklyData(int $batchStudentId, string $startAt, string $endAt): array;
  public function getTeacherWeeklyOverview(int $batchId, int $teacherId, int $weekNumber, int $page = 1, int $limit = 15): Pageable;
  public function getStudentWeeklyTimeline(int $batchStudentId, string $startAt, string $endAt): array;
}

class InternshipWeeklyReportService implements IInternshipWeeklyReportService
{
  private InternshipWeeklyReportStore $_store;
  private WebSettingsService $_webSettingsService;

  public function __construct(
    InternshipWeeklyReportStore $store,
    WebSettingsService $webSettingsService
  ) {
    $this->_store = $store;
    $this->_webSettingsService = $webSettingsService;
  }

  /**
   * Tính toán danh sách tuần thực tập
   * Quy tắc: Tuần lịch (Thứ 2 -> CN), bắt đầu từ tuần chứa ngày startAt
   */
  public function calculateWeeks(string $startAt, string $endAt): array
  {
    $weeks = [];
    $startDt = new DateTime($startAt);

    // Tìm Thứ 2 của tuần chứa startAt
    $currentWeekStart = clone $startDt;
    $dayOfWeek = (int)$currentWeekStart->format('N'); // 1 = Mon, 7 = Sun
    if ($dayOfWeek > 1) {
      $currentWeekStart->modify('-' . ($dayOfWeek - 1) . ' days');
    }

    $endDt = new DateTime($endAt);
    // Cộng thêm buffer time theo setting
    $submissionDays = (int) $this->_webSettingsService->getValue('internship_report_submission_days', 14);
    $limitDt = (clone $endDt)->modify("+{$submissionDays} days");

    $now = new DateTime();
    $maxDt = min($limitDt, $now);

    $weekNumber = 1;

    while ($currentWeekStart <= $maxDt || $currentWeekStart <= $endDt) {
      $currentWeekEnd = clone $currentWeekStart;
      $currentWeekEnd->modify('+6 days');

      // Sinh danh sách tất cả các tuần từ start_at đến end_at để SV thấy được toàn bộ lộ trình.
      // Nếu hiện tại đang trong thời gian nộp bù (buffer time sau end_at), không sinh thêm tuần mới ngoài lộ trình chính thức.
      $weeks[] = [
        'week_number' => $weekNumber,
        'start' => $currentWeekStart->format('Y-m-d'),
        'end' => $currentWeekEnd->format('Y-m-d'),
      ];

      $currentWeekStart->modify('+7 days');
      $weekNumber++;

      if ($currentWeekStart > $endDt && $currentWeekStart > $now) {
        break;
      }
    }

    return $weeks;
  }

  public function submitWeeklyReport(int $batchStudentId, int $weekNumber, ?string $content, bool $isExempt, array $imagesData, string $startAt, string $endAt): int
  {
    $weeks = $this->calculateWeeks($startAt, $endAt);
    $targetWeek = null;
    foreach ($weeks as $w) {
      if ($w['week_number'] === $weekNumber) {
        $targetWeek = $w;
        break;
      }
    }

    if (!$targetWeek) {
      throw new Exception("Tuần báo cáo không hợp lệ.");
    }

    if (!$isExempt && empty(trim((string)$content))) {
      throw new Exception("Vui lòng nhập nội dung công việc.");
    }

    $now = new DateTime();
    $weekEndDt = new DateTime($targetWeek['end']);
    // Chuyển weekEndDt đến cuối ngày CN
    $weekEndDt->setTime(23, 59, 59);

    $isLate = $now > $weekEndDt;

    $this->_store->beginTransaction();
    try {
      // Đặt is_latest = 0 cho bản ghi cũ
      $this->_store->resetLatest($batchStudentId, $weekNumber);

      $reportData = [
        'batch_student_id' => $batchStudentId,
        'week_number' => $weekNumber,
        'week_start' => $targetWeek['start'],
        'week_end' => $targetWeek['end'],
        'content' => $isExempt ? null : $content,
        'is_exempt' => $isExempt ? 1 : 0,
        'is_late' => $isLate ? 1 : 0,
        'is_latest' => 1
      ];

      $reportId = $this->_store->create($reportData);

      foreach ($imagesData as $img) {
        $img['weekly_report_id'] = $reportId;
        $this->_store->addImage($img);
      }

      $this->_store->commit();
      return $reportId;
    } catch (\Throwable $e) {
      $this->_store->rollBack();
      throw $e;
    }
  }

  public function getStudentWeeklySummary(int $batchStudentId, string $startAt, string $endAt): array
  {
    $weeks = $this->calculateWeeks($startAt, $endAt);
    $totalWeeks = count($weeks);
    $submittedCount = $this->_store->countSubmittedWeeks($batchStudentId);

    $currentWeekNumber = null;
    $currentWeekStatus = 'missing';

    $now = new DateTime();
    $nowStr = $now->format('Y-m-d');

    foreach ($weeks as $week) {
      if ($nowStr >= $week['start'] && $nowStr <= $week['end']) {
        $currentWeekNumber = $week['week_number'];
        $report = $this->_store->getLatestByBatchStudentAndWeek($batchStudentId, $week['week_number']);
        if ($report) {
          $currentWeekStatus = $report['is_exempt'] ? 'exempt' : 'submitted';
        } else {
          $currentWeekStatus = 'current';
        }
        break;
      }
    }

    return [
      'total_weeks' => $totalWeeks,
      'submitted_weeks' => $submittedCount,
      'percentage' => $totalWeeks > 0 ? round(($submittedCount / $totalWeeks) * 100) : 0,
      'current_week' => $currentWeekNumber,
      'current_week_status' => $currentWeekStatus
    ];
  }

  public function getStudentWeeklyData(int $batchStudentId, string $startAt, string $endAt): array
  {
    $weeks = $this->calculateWeeks($startAt, $endAt);
    $reports = $this->_store->getLatestByBatchStudent($batchStudentId);

    // Đưa reports vào hash map
    $reportsMap = [];
    foreach ($reports as $report) {
      // Lấy thêm ảnh
      $report['images'] = $this->_store->getImagesByReportId($report['id']);
      $reportsMap[$report['week_number']] = $report;
    }

    $now = new DateTime();
    $nowStr = $now->format('Y-m-d');

    foreach ($weeks as &$week) {
      $wn = $week['week_number'];
      $week['report'] = $reportsMap[$wn] ?? null;

      if (isset($reportsMap[$wn])) {
        $r = $reportsMap[$wn];
        if ($r['is_exempt']) {
          $week['status'] = 'exempt';
        } elseif ($r['is_late']) {
          $week['status'] = 'late';
        } else {
          $week['status'] = 'submitted';
        }
      } else {
        if ($nowStr >= $week['start'] && $nowStr <= $week['end']) {
          $week['status'] = 'current';
        } elseif ($nowStr < $week['start']) {
          $week['status'] = 'future';
        } else {
          $week['status'] = 'missing';
        }
      }
    }

    return $weeks;
  }

  public function getTeacherWeeklyOverview(int $batchId, int $teacherId, int $weekNumber, int $page = 1, int $limit = 15): Pageable
  {
    $items = $this->_store->getPaginatedByBatchAndTeacher($batchId, $teacherId, $weekNumber, $page, $limit);
    $total = $this->_store->countByBatchAndTeacher($batchId, $teacherId, $weekNumber);
    return new Pageable($items, $total, $limit, $page);
  }

  public function getStudentWeeklyTimeline(int $batchStudentId, string $startAt, string $endAt): array
  {
    $weeksData = $this->getStudentWeeklyData($batchStudentId, $startAt, $endAt);

    $totalWeeks = count($weeksData);
    $submitted = 0;
    $late = 0;
    $missing = 0;
    $exempt = 0;

    foreach ($weeksData as $w) {
      if ($w['status'] === 'submitted') $submitted++;
      elseif ($w['status'] === 'late') {
        $submitted++;
        $late++;
      } elseif ($w['status'] === 'exempt') {
        $submitted++;
        $exempt++;
      } elseif ($w['status'] === 'missing') $missing++;
    }

    return [
      'weeks' => $weeksData,
      'summary' => [
        'total_weeks' => $totalWeeks,
        'submitted' => $submitted,
        'late' => $late,
        'exempt' => $exempt,
        'missing' => $missing,
        'percentage' => $totalWeeks > 0 ? round(($submitted / $totalWeeks) * 100) : 0
      ]
    ];
  }
}
