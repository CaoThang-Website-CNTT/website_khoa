<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ProjectTopicService;
use App\Enums\ProjectTopicStatus;
use Exception;

class ProjectTopicApiController extends Controller
{
    private ProjectTopicService $_topicService;

    public function __construct(ProjectTopicService $topicService)
    {
        $this->_topicService = $topicService;
    }

    public function indexByBatch(Request $request, $id)
    {
        $page = (int)$request->query('page', 1);
        $limit = (int)$request->query('limit', 15);
        
        $filters = [];
        if ($request->query('status')) {
            $filters['status'] = $request->query('status');
        }
        if ($request->query('search')) {
            $filters['search'] = $request->query('search');
        }

        try {
            $pageable = $this->_topicService->getPaginatedByBatch((int)$id, $page, $limit, $filters);
            return $this->json([
                'success' => true,
                'data' => $pageable->getItems(),
                'meta' => [
                    'total' => $pageable->getTotal(),
                    'page' => $pageable->getCurrentPage(),
                    'last_page' => $pageable->getTotalPages(),
                    'per_page' => $pageable->getPerPage()
                ]
            ]);
        } catch (Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function approve(Request $request, $id)
    {
        $authUser = $request->session()->authUser();
        $adminId = (int) ($authUser['account_id'] ?? 0);

        try {
            $this->_topicService->reviewTopic((int)$id, ProjectTopicStatus::APPROVED, null, $adminId);
            return $this->json(null, 200, 'Đã duyệt đề tài thành công.');
        } catch (Exception $e) {
            return $this->json(null, 400, $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $authUser = $request->session()->authUser();
        $adminId = (int) ($authUser['account_id'] ?? 0);
        
        $data = $request->json();
        $reason = is_string($data['reason'] ?? null) ? trim($data['reason']) : '';

        try {
            $this->_topicService->reviewTopic((int)$id, ProjectTopicStatus::REJECTED, $reason, $adminId);
            return $this->json(null, 200, 'Đã từ chối đề tài.');
        } catch (Exception $e) {
            return $this->json(null, 400, $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $authUser = $request->session()->authUser();
        $adminId = (int) ($authUser['account_id'] ?? 0);

        $data = $request->json();
        $topicIds = $data['topic_ids'] ?? [];

        if (empty($topicIds) || !is_array($topicIds)) {
            return $this->json(null, 400, 'Danh sách đề tài không hợp lệ.');
        }

        $successCount = 0;
        $errors = [];

        foreach ($topicIds as $id) {
            try {
                $this->_topicService->reviewTopic((int)$id, ProjectTopicStatus::APPROVED, null, $adminId);
                $successCount++;
            } catch (Exception $e) {
                $errors[] = "Đề tài #$id: " . $e->getMessage();
            }
        }

        return $this->json([
            'approved_count' => $successCount,
            'errors' => $errors
        ], 200, "Đã duyệt thành công $successCount đề tài.");
    }
}
