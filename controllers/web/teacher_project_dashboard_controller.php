<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\TeacherService;
use App\Services\ProjectBatchService;
use App\Services\ProjectTopicService;
use App\Services\ProjectGroupService;
use App\Enums\ProjectTopicStatus;
use App\Enums\ProjectBatchStatus;
use App\Models\ProjectBatch;
use Exception;

class TeacherProjectDashboardController extends Controller
{
    private TeacherService $_teacherService;
    private ProjectBatchService $_batchService;
    private ProjectTopicService $_topicService;
    private ProjectGroupService $_groupService;

    public function __construct(
        TeacherService $teacherService,
        ProjectBatchService $batchService,
        ProjectTopicService $topicService,
        ProjectGroupService $groupService
    ) {
        $this->_teacherService = $teacherService;
        $this->_batchService = $batchService;
        $this->_topicService = $topicService;
        $this->_groupService = $groupService;
    }

    public function index(Request $request)
    {
        $authUser = $request->session()->authUser();
        if (!$authUser) return $this->redirect('/login');

        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
        if (!$teacher) return $this->redirect('/');

        $page = (int)($request->query('page') ?? 1);
        $data = $this->_batchService->getBatchesByTeacherId($teacher->id, $page, 15);

        return $this->render('teacher/project_batches/index', [
            'data' => $data,
            'title' => 'Quản lý đồ án tốt nghiệp'
        ], layout: 'dashboard_layout');
    }

    public function show(Request $request, int $id)
    {
        $authUser = $request->session()->authUser();
        if (!$authUser) return $this->redirect('/login');

        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
        if (!$teacher) return $this->redirect('/');

        $batch = $this->_batchService->getBatchById($id);
        if (!$batch) {
            $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án.');
            return $this->redirect('/teacher/project_batches');
        }

        if (!in_array($batch['status'], [ProjectBatchStatus::PUBLISHED, ProjectBatchStatus::CLOSED], true)) {
            $request->session()->flashNotify('error', 'Đợt đồ án chưa được công bố.');
            return $this->redirect('/teacher/project_batches');
        }
        
        if (!$this->_batchService->isTeacherAssigned($id, $teacher->id)) {
            $request->session()->flashNotify('error', 'Bạn không được phân công phụ trách đợt đồ án này.');
            return $this->redirect('/teacher/project_batches');
        }

        $topics = $this->_topicService->getTopicsByTeacher($id, $teacher->id);
        
        $batchModel = new ProjectBatch(
            status: $batch['status'] ?? 'draft',
            topic_proposal_start: $batch['topic_proposal_start'] ?? null,
            topic_proposal_end: $batch['topic_proposal_end'] ?? null,
            registration_start: $batch['registration_start'] ?? null,
            registration_end: $batch['registration_end'] ?? null,
            allocation_published_at: $batch['allocation_published_at'] ?? null
        );
        $isAllocationPublished = $batchModel->isAllocationPublished();

        $groups = $isAllocationPublished ? $this->_groupService->getAssignedGroupsByTeacher($id, $teacher->id) : [];

        return $this->render('teacher/project_batches/show', [
            'batch' => $batch,
            'topics' => $topics,
            'groups' => $groups,
            'isAllocationPublished' => $isAllocationPublished,
            'title' => 'Chi tiết đợt đồ án: ' . $batch['title']
        ], layout: 'dashboard_layout');
    }

    public function previewRegistrationForms(Request $request, int $id)
    {
        $authUser = $request->session()->authUser();
        $teacher = $authUser ? $this->_teacherService->getTeacherByAccountId($authUser['account_id']) : null;
        if (!$teacher) return $this->redirect('/login');

        $batch = $this->_batchService->getBatchById($id);
        $groupIds = (array)$request->input('group_ids', []);
        if (!$batch || !$groupIds) {
            $request->session()->flashNotify('error', 'Vui lòng chọn ít nhất một nhóm để in.');
            return $this->redirect("/teacher/project_batches/{$id}");
        }

        $groups = $this->_groupService->getAssignedGroupsForPrint($id, $teacher->id, $groupIds);
        if (count($groups) !== count(array_unique(array_map('intval', $groupIds)))) {
            $request->session()->flashNotify('error', 'Một hoặc nhiều nhóm không tồn tại hoặc không thuộc quyền hướng dẫn của bạn.');
            return $this->redirect("/teacher/project_batches/{$id}");
        }

        $this->render('teacher/project_batches/registration_form_print', [
            'batch' => $batch,
            'groups' => $groups,
        ], layout: 'document_editor_layout');
    }

    public function previewRegistrationForm(Request $request, int $id, int $groupId)
    {
        $authUser = $request->session()->authUser();
        $teacher = $authUser ? $this->_teacherService->getTeacherByAccountId($authUser['account_id']) : null;
        if (!$teacher) return $this->redirect('/login');

        $batch = $this->_batchService->getBatchById($id);
        $groups = $batch ? $this->_groupService->getAssignedGroupsForPrint($id, $teacher->id, [$groupId]) : [];
        if (!$batch || count($groups) !== 1) {
            $request->session()->flashNotify('error', 'Nhóm không tồn tại hoặc không thuộc quyền hướng dẫn của bạn.');
            return $this->redirect("/teacher/project_batches/{$id}");
        }
        $this->render('teacher/project_batches/registration_form_print', [
            'batch' => $batch,
            'groups' => $groups,
        ], layout: 'document_editor_layout');
    }

    public function saveRegistrationForms(Request $request, int $id)
    {
        $authUser = $request->session()->authUser();
        $teacher = $authUser ? $this->_teacherService->getTeacherByAccountId($authUser['account_id']) : null;
        if (!$teacher) return $this->json(null, 401, 'Phiên đăng nhập không hợp lệ.');

        $forms = json_decode((string)$request->input('forms', '[]'), true);
        if (!is_array($forms)) return $this->json(null, 422, 'Dữ liệu phiếu không hợp lệ.');

        try {
            $count = $this->_groupService->saveRegistrationForms($id, $teacher->id, $forms);
            return $this->json(['count' => $count], 200, "Đã lưu {$count} phiếu đăng ký.");
        } catch (\InvalidArgumentException $e) {
            return $this->json(null, 422, $e->getMessage());
        } catch (\Throwable $e) {
            return $this->json(null, 403, $e->getMessage());
        }
    }

    public function createTopic(Request $request, int $id)
    {
        $authUser = $request->session()->authUser();
        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);

        $batch = $this->_batchService->getBatchById($id);
        if (!$batch || !$this->_batchService->isTeacherAssigned($id, $teacher->id)) {
            $request->session()->flashNotify('error', 'Bạn không được phân công phụ trách đợt đồ án này.');
            return $this->redirect('/teacher/project_batches');
        }

        if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
            $message = $batch['status'] === ProjectBatchStatus::CLOSED
                ? 'Đợt đồ án đã kết thúc.'
                : 'Đợt đồ án chưa được công bố.';
            $request->session()->flashNotify('error', $message);
            return $this->redirect('/teacher/project_batches');
        }

        return $this->render('teacher/project_batches/topic_form', [
            'batch' => $batch,
            'title' => 'Thêm đề tài mới'
        ], layout: 'dashboard_layout');
    }

    public function storeTopic(Request $request, int $id)
    {
        $authUser = $request->session()->authUser();
        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);

        $batch = $this->_batchService->getBatchById($id);
        if (!$batch || !$this->_batchService->isTeacherAssigned($id, $teacher->id)) {
            $request->session()->flashNotify('error', 'Bạn không được phân công phụ trách đợt đồ án này.');
            return $this->redirect('/teacher/project_batches');
        }
        if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
            $message = $batch['status'] === ProjectBatchStatus::CLOSED
                ? 'Đợt đồ án đã kết thúc.'
                : 'Đợt đồ án chưa được công bố.';
            $request->session()->flashNotify('error', $message);
            return $this->redirect('/teacher/project_batches');
        }

        $data = $request->all();
        $data['batch_id'] = $id;

        // Upload PDF
        $pdfPath = null;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                $request->session()->flashNotify('error', "Lỗi tải file lên máy chủ (Mã lỗi: " . $_FILES['pdf_file']['error'] . ").");
                return $this->redirect("/teacher/project_batches/{$id}/topics/create");
            }

            $file = $_FILES['pdf_file'];
            $maxSizeMb = 10;
            $maxSizeBytes = $maxSizeMb * 1024 * 1024;

            if ($file['size'] > $maxSizeBytes) {
                $request->session()->flashNotify('error', "Dung lượng file vượt quá giới hạn {$maxSizeMb}MB.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/create");
            }

            $mime = mime_content_type($file['tmp_name']);
            if ($mime !== 'application/pdf') {
                $request->session()->flashNotify('error', "Chỉ hỗ trợ upload file định dạng PDF.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/create");
            }

            $subDir = 'project_topics/' . date('Y/m');
            $uploadDir = BASE_PATH . '/storage/' . $subDir . '/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = bin2hex(random_bytes(16)) . '.pdf';
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $pdfPath = $subDir . '/' . $fileName;
            } else {
                $request->session()->flashNotify('error', "Không thể lưu file vào máy chủ.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/create");
            }
        }
        
        if ($pdfPath) {
            $data['pdf_file_path'] = $pdfPath;
        }

        $topicId = null;
        try {
            $action = $data['action'] ?? 'draft';

            // Nếu gửi duyệt -> Bắt buộc phải có file PDF
            if ($action === 'submit' && empty($pdfPath)) {
                $request->session()->flashNotify('error', "Vui lòng tải lên tài liệu mô tả (PDF) trước khi gửi duyệt.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/create");
            }

            $data['status'] = ProjectTopicStatus::DRAFT;

            $topicId = $this->_topicService->createTopic($data, $teacher->id);
            
            if ($action === 'submit') {
                $this->_topicService->submitTopic($topicId, $teacher->id);
            }
            
            $msg = $action === 'submit' ? 'Đã nộp đề tài thành công.' : 'Lưu nháp đề tài thành công.';
            $request->session()->flashNotify('success', $msg);
            return $this->redirect("/teacher/project_batches/{$id}");
        } catch (Exception $e) {
            if ($topicId === null && $pdfPath) {
                $this->deleteProjectTopicFile($pdfPath);
            }
            $request->session()->flashNotify('error', $e->getMessage());
            return $this->redirect("/teacher/project_batches/{$id}/topics/create");
        }
    }

    public function editTopic(Request $request, int $id, int $topicId)
    {
        $authUser = $request->session()->authUser();
        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);

        $batch = $this->_batchService->getBatchById($id);
        $topic = $this->_topicService->getTopicById($topicId);

        if (!$batch || !$topic || $topic['batch_id'] != $id || $topic['teacher_id'] != $teacher->id) {
            return $this->redirect("/teacher/project_batches/{$id}");
        }

        if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
            $message = $batch['status'] === ProjectBatchStatus::CLOSED
                ? 'Đợt đồ án đã kết thúc.'
                : 'Đợt đồ án chưa được công bố.';
            $request->session()->flashNotify('error', $message);
            return $this->redirect('/teacher/project_batches');
        }

        return $this->render('teacher/project_batches/topic_form', [
            'batch' => $batch,
            'topic' => $topic,
            'title' => 'Cập nhật đề tài'
        ], layout: 'dashboard_layout');
    }

    public function updateTopic(Request $request, int $id, int $topicId)
    {
        $authUser = $request->session()->authUser();
        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);

        $data = $request->all();

        $batch = $this->_batchService->getBatchById($id);
        $topic = $this->_topicService->getTopicById($topicId);
        if (!$batch || !$topic || $topic['batch_id'] != $id || $topic['teacher_id'] != $teacher->id) {
            $request->session()->flashNotify('error', 'Đề tài không hợp lệ hoặc không thuộc quyền quản lý của bạn.');
            return $this->redirect("/teacher/project_batches/{$id}");
        }
        if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
            $message = $batch['status'] === ProjectBatchStatus::CLOSED
                ? 'Đợt đồ án đã kết thúc.'
                : 'Đợt đồ án chưa được công bố.';
            $request->session()->flashNotify('error', $message);
            return $this->redirect('/teacher/project_batches');
        }

        $oldPdfPath = $topic['pdf_file_path'] ?? null;
        $pdfPath = $oldPdfPath;
        $uploadedPdfPath = null;
        $topicUpdated = false;

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                $request->session()->flashNotify('error', "Lỗi tải file lên máy chủ (Mã lỗi: " . $_FILES['pdf_file']['error'] . ").");
                return $this->redirect("/teacher/project_batches/{$id}/topics/{$topicId}/edit");
            }

            $file = $_FILES['pdf_file'];
            $maxSizeMb = 10;
            $maxSizeBytes = $maxSizeMb * 1024 * 1024;

            if ($file['size'] > $maxSizeBytes) {
                $request->session()->flashNotify('error', "Dung lượng file vượt quá giới hạn {$maxSizeMb}MB.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/{$topicId}/edit");
            }

            $mime = mime_content_type($file['tmp_name']);
            if ($mime !== 'application/pdf') {
                $request->session()->flashNotify('error', "Chỉ hỗ trợ upload file định dạng PDF.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/{$topicId}/edit");
            }

            $subDir = 'project_topics/' . date('Y/m');
            $uploadDir = BASE_PATH . '/storage/' . $subDir . '/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = bin2hex(random_bytes(16)) . '.pdf';
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $pdfPath = $subDir . '/' . $fileName;
                $uploadedPdfPath = $pdfPath;
            } else {
                $request->session()->flashNotify('error', "Không thể lưu file vào máy chủ.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/{$topicId}/edit");
            }
        }
        
        $data['pdf_file_path'] = $pdfPath;

        try {
            $action = $data['action'] ?? 'draft';

            // Nếu gửi duyệt -> Bắt buộc phải có file PDF
            if ($action === 'submit' && empty($pdfPath)) {
                $request->session()->flashNotify('error', "Vui lòng tải lên tài liệu mô tả (PDF) trước khi gửi duyệt.");
                return $this->redirect("/teacher/project_batches/{$id}/topics/{$topicId}/edit");
            }

            $this->_topicService->updateTopic($topicId, $data, $teacher->id);
            $topicUpdated = true;

            if ($uploadedPdfPath && $oldPdfPath && $oldPdfPath !== $uploadedPdfPath) {
                $this->deleteProjectTopicFile($oldPdfPath);
            }
            
            if ($action === 'submit') {
                $this->_topicService->submitTopic($topicId, $teacher->id);
            }

            $msg = $action === 'submit' ? 'Đã nộp đề tài thành công.' : 'Cập nhật đề tài thành công.';
            $request->session()->flashNotify('success', $msg);
            return $this->redirect("/teacher/project_batches/{$id}");
        } catch (Exception $e) {
            if ($uploadedPdfPath && !$topicUpdated) {
                $this->deleteProjectTopicFile($uploadedPdfPath);
            }
            $request->session()->flashNotify('error', $e->getMessage());
            return $this->redirect("/teacher/project_batches/{$id}/topics/{$topicId}/edit");
        }
    }



    public function submitTopic(Request $request, int $id, int $topicId)
    {
        $authUser = $request->session()->authUser();
        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);

        try {
            $this->_topicService->submitTopic($topicId, $teacher->id);
            $request->session()->flashNotify('success', 'Đã nộp đề tài thành công.');
        } catch (Exception $e) {
            $request->session()->flashNotify('error', $e->getMessage());
        }
        return $this->redirect("/teacher/project_batches/{$id}");
    }

    public function deleteTopic(Request $request, int $id, int $topicId)
    {
        $authUser = $request->session()->authUser();
        $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);

        try {
            $this->_topicService->deleteTopic($topicId, $teacher->id);
            $request->session()->flashNotify('success', 'Đã xóa đề tài thành công.');
        } catch (Exception $e) {
            $request->session()->flashNotify('error', $e->getMessage());
        }
        return $this->redirect("/teacher/project_batches/{$id}");
    }

    private function deleteProjectTopicFile(string $relativePath): void
    {
        $normalizedPath = str_replace('\\', '/', ltrim($relativePath, '/'));
        if (!str_starts_with($normalizedPath, 'project_topics/') || str_contains($normalizedPath, '..')) {
            return;
        }

        $absolutePath = BASE_PATH . '/storage/' . $normalizedPath;
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
