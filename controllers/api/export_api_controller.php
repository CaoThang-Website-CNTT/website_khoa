<?php

namespace App\Controllers\Api;

use App\Core\Controller;

use App\Services\InternshipBatchService;
use App\Services\ProjectGroupService;
use App\Core\Files\XlsxWriter;

class ExportApiController extends Controller
{
  private InternshipBatchService $_internshipBatchService;
  private ProjectGroupService $_projectGroupService;

  public function __construct(
    InternshipBatchService $internshipBatchService,
    ProjectGroupService $projectGroupService
  ) {
    $this->_internshipBatchService = $internshipBatchService;
    $this->_projectGroupService = $projectGroupService;
  }

  /**
   * POST /api/v1/export
   * Body:
   * {
   *   "source": "batch_students",
   *   "source_id": 123,
   *   "mode": "current_view" | "all" | "selected",
   *   "columns": { "student_code": "MSSV", "student_name": "Họ và Tên", ... },
   *   "export_columns": ["student_code", "student_name"],  // Column Selection (optional)
   *   "metadata": ["Tiêu đề báo cáo", "Ngày xuất: ..."],  // Sheet Metadata (optional)
   *   "filters": [...],
   *   "sort": { "col": "...", "dir": "..." },
   *   "selected_ids": [1, 2, 3],
   *   "filename": "danh-sach-..."
   * }
   */
  public function export()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return $this->json(null, 405, 'Method not allowed')->send();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
      return $this->json(null, 400, 'Invalid JSON payload')->send();
    }

    $source = $input['source'] ?? '';
    $sourceId = (int)($input['source_id'] ?? 0);
    $mode = $input['mode'] ?? 'all';
    $columnsMap = $input['columns'] ?? [];
    $exportColumns = $input['export_columns'] ?? [];
    $metadata = $input['metadata'] ?? [];
    $filters = $input['filters'] ?? [];
    $sort = $input['sort'] ?? null;
    $selectedIds = $input['selected_ids'] ?? [];
    $filename = $input['filename'] ?? 'export_data';

    if (empty($source) || empty($columnsMap)) {
      return $this->json(null, 400, 'Missing required fields: source, columns')->send();
    }

    // Nếu client gửi export_columns, chỉ giữ lại các cột đó
    if (!empty($exportColumns)) {
      $columnsMap = array_intersect_key($columnsMap, array_flip($exportColumns));
    }

    if (empty($columnsMap)) {
      return $this->json(null, 400, 'No columns selected for export')->send();
    }

    $data = [];

    // Dispatch based on source
    switch ($source) {
      case 'batch_students':
        if ($sourceId <= 0) {
          return $this->json(null, 400, 'Missing source_id for batch_students')->send();
        }
        $data = $this->_internshipBatchService->getExportBatchStudents(
          $sourceId,
          $mode === 'all' ? [] : $filters,
          $mode === 'all' ? null : $sort,
          $mode === 'selected' ? $selectedIds : []
        );
        break;

      case 'project_allocations':
        if ($sourceId <= 0) {
          return $this->json(null, 400, 'Missing source_id for project_allocations')->send();
        }
        $data = $this->_projectGroupService->getExportAllocations(
          $sourceId,
          $mode === 'all' ? [] : $filters,
          $mode === 'all' ? null : $sort,
          $mode === 'selected' ? $selectedIds : []
        );
        break;

      default:
        return $this->json(null, 400, "Unknown source: $source")->send();
    }

    // Format data for XlsxWriter
    $writer = new XlsxWriter();

    // Sheet Metadata
    if (!empty($metadata)) {
      $writer->setMetadata($metadata);
    }

    // Set headers
    $headers = array_merge(['STT'], array_values($columnsMap));
    $writer->setColumns($headers);

    // Default styling
    $writer->setHeaderStyle('F4F4F5', '000000', true);

    // Map rows to array based on columnsMap keys
    $rows = [];
    $columnKeys = array_keys($columnsMap);
    $stt = 1;

    foreach ($data as $record) {
      $row = [$stt++];
      foreach ($columnKeys as $key) {
        $row[] = $record[$key] ?? '';
      }
      $rows[] = $row;
    }

    $writer->addRows($rows);

    // Generate output
    $binaryContent = $writer->output();
    if (empty($binaryContent)) {
      return $this->json(null, 500, 'Failed to generate Excel file')->send();
    }

    // Send binary response
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Content-Length: ' . strlen($binaryContent));

    echo $binaryContent;
    exit;
  }
}
