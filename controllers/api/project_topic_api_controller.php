<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Enums\ProjectTopicStatus;
use App\Services\ProjectTopicService;
use Exception;

class ProjectTopicApiController extends Controller
{
  private const ALLOWED_STATUSES = [
    ProjectTopicStatus::DRAFT,
    ProjectTopicStatus::PENDING,
    ProjectTopicStatus::APPROVED,
    ProjectTopicStatus::REJECTED,
  ];

  public function __construct(private ProjectTopicService $_topicService)
  {
  }

  public function indexByBatch(Request $request, $id)
  {
    $batchId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($batchId === false) {
      return $this->json(null, 422, 'Đợt đồ án không hợp lệ.');
    }

    $page = max(1, (int) $request->query('page', 1));
    $limit = min(100, max(5, (int) $request->query('limit', 15)));
    $status = trim((string) $request->query('status', 'all'));
    $search = trim((string) $request->query('search', ''));
    $teacherId = filter_var($request->query('teacher_id'), FILTER_VALIDATE_INT);

    if ($status !== 'all' && !in_array($status, self::ALLOWED_STATUSES, true)) {
      return $this->json(null, 422, 'Trạng thái đề tài không hợp lệ.');
    }
    if (mb_strlen($search) > 200) {
      return $this->json(null, 422, 'Từ khóa tìm kiếm không được vượt quá 200 ký tự.');
    }

    $filters = [];
    if ($status !== 'all') $filters['status'] = $status;
    if ($search !== '') $filters['search'] = $search;
    if ($teacherId !== false) $filters['teacher_id'] = $teacherId;

    try {
      $pageable = $this->_topicService->getPaginatedByBatch((int) $batchId, $page, $limit, $filters);
      $startIndex = ($page - 1) * $limit;
      $items = $pageable->getItems();
      $rows = [];
      foreach ($items as $index => $topic) {
        $presented = $this->presentTopic($topic);
        $presented['stt'] = $startIndex + $index + 1;
        $rows[] = $presented;
      }
      $counts = array_merge(array_fill_keys(array_merge(['all'], self::ALLOWED_STATUSES), 0), $this->_topicService->getStatusCountsByBatch((int) $batchId));

      return $this->json([
        'rows' => $rows,
        'counts' => $counts,
        'pagination' => [
          'total' => $pageable->getTotal(),
          'page' => $pageable->getCurrentPage(),
          'last_page' => $pageable->getTotalPages(),
          'limit' => $pageable->getPerPage(),
        ],
      ], 200, 'Đã tải danh sách đề tài.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function approve(Request $request, $id)
  {
    try {
      $this->_topicService->reviewTopic($this->validTopicId($id), ProjectTopicStatus::APPROVED, null, $this->adminId($request));
      return $this->json(null, 200, 'Đã duyệt đề tài thành công.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function reject(Request $request, $id)
  {
    $reason = trim((string) $request->json('reason', ''));
    if ($reason === '') return $this->json(null, 422, 'Vui lòng nhập lý do từ chối.');
    if (mb_strlen($reason) > 1000) return $this->json(null, 422, 'Lý do từ chối không được vượt quá 1000 ký tự.');

    try {
      $this->_topicService->reviewTopic($this->validTopicId($id), ProjectTopicStatus::REJECTED, $reason, $this->adminId($request));
      return $this->json(null, 200, 'Đã từ chối đề tài.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function bulkApprove(Request $request)
  {
    $topicIds = $request->json('topic_ids', []);
    if (!is_array($topicIds) || $topicIds === [] || count($topicIds) > 100) {
      return $this->json(null, 422, 'Danh sách đề tài phải có từ 1 đến 100 mục.');
    }

    $ids = array_values(array_unique(array_filter(array_map('intval', $topicIds), fn(int $id): bool => $id > 0)));
    if ($ids === []) return $this->json(null, 422, 'Danh sách đề tài không hợp lệ.');

    try {
      $adminId = $this->adminId($request);
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }

    $approvedIds = [];
    $skipped = [];
    foreach ($ids as $topicId) {
      try {
        $this->_topicService->reviewTopic($topicId, ProjectTopicStatus::APPROVED, null, $adminId);
        $approvedIds[] = $topicId;
      } catch (Exception $e) {
        $skipped[] = ['id' => $topicId, 'reason' => $e->getMessage()];
      }
    }

    $message = sprintf('Đã duyệt %d đề tài.', count($approvedIds));
    if ($skipped !== []) $message .= sprintf(' Bỏ qua %d đề tài không hợp lệ.', count($skipped));

    return $this->json(['approved_ids' => $approvedIds, 'skipped' => $skipped], 200, $message);
  }

  private function presentTopic(array $topic): array
  {
    return [
      'id' => (int) $topic['id'],
      'title' => (string) $topic['title'],
      'description' => (string) ($topic['description'] ?? ''),
      'teacher' => ['name' => (string) $topic['teacher_name'], 'department' => (string) ($topic['department_name'] ?? '')],
      'max_students' => (int) $topic['max_students'],
      'status' => [
        'value' => (string) $topic['status'],
        'label' => ProjectTopicStatus::getLabel((string) $topic['status']),
        'variant' => ProjectTopicStatus::getVariant((string) $topic['status']),
      ],
      'pdf_file_url' => !empty($topic['pdf_file_path']) ? url('public/storage/' . ltrim($topic['pdf_file_path'], '/')) : null,
      'reject_reason' => (string) ($topic['reject_reason'] ?? ''),
    ];
  }

  private function adminId(Request $request): int
  {
    $adminId = (int) (($request->session()->authUser()['account_id'] ?? 0));
    if ($adminId <= 0) throw new Exception('Phiên đăng nhập không hợp lệ.');
    return $adminId;
  }

  private function validTopicId($id): int
  {
    $topicId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($topicId === false) throw new Exception('Đề tài không hợp lệ.');
    return (int) $topicId;
  }
}
