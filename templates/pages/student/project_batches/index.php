<?php

use App\Enums\ProjectBatchStatus;
use App\Models\ProjectBatch;

/**
 * View: Danh sách đợt đồ án tốt nghiệp
 * Route: /student/project_batches
 */
$student = $student ?? null;
$batches = $batches ?? [];
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Đồ án tốt nghiệp</h2>
<p class="title-wrapper__description">Chọn một đợt đồ án để theo dõi tiến độ, quản lý nhóm và đăng ký nguyện vọng đề tài.</p>
<?php $layout->end() ?>

<div class="grid grid-cols-1 gap-6">
  <?php if (empty($batches)): ?>
    <div class="card shadow py-12 text-center">
      <div class="card__content flex flex-col items-center">
        <div class="text-5xl mb-6" style="color: var(--muted-foreground)">
          <i class="fa-solid fa-folder-open"></i>
        </div>
        <h2 class="text-xl font-semibold mb-2">Không có đợt đồ án nào</h2>
        <p class="mx-auto" style="color: var(--muted-foreground)">
          Hiện tại chưa có đợt đồ án tốt nghiệp nào phù hợp với bạn.
          Vui lòng quay lại sau.
        </p>
      </div>
    </div>
  <?php else: ?>
    <div class="tm-container" data-tm="student_batches_table" data-tm-mode="client" data-tm-searchable>
      <template data-tm-col="title" data-tm-label="Tên đợt" data-tm-sortable data-tm-filter-type="text">
        <a href="{{ row._href }}" class="font-medium" style="text-decoration: none; color: inherit;">{{ value }}</a>
      </template>
      <template data-tm-col="registration_start_label" data-tm-label="Bắt đầu ĐK" data-tm-sortable></template>
      <template data-tm-col="registration_end_label" data-tm-label="Kết thúc ĐK" data-tm-sortable></template>
      <template data-tm-col="effective_status" data-tm-label="Trạng thái" data-tm-filter-type="select"
        data-tm-filter-options='<?= json_encode(ProjectBatchStatus::getEffectiveOptions()) ?>'>
        <span class="badge" data-variant="{{ row.effective_status_variant }}">{{ row.effective_status_label }}</span>
      </template>
      <template data-tm-pagination></template>
    </div>
  <?php endif; ?>
</div>

<?php $layout->start("scripts") ?>
<?php if (!empty($batches)): ?>
  <script type="application/json" data-tm-data="student_batches_table">
    <?= json_encode([
      'rows' => array_map(function ($batch) {
        $model = new ProjectBatch();
        $model->status = $batch['status'] ?? 'draft';
        $model->topic_proposal_start = $batch['topic_proposal_start'] ?? null;
        $model->topic_proposal_end = $batch['topic_proposal_end'] ?? null;
        $model->registration_start = $batch['registration_start'] ?? null;
        $model->registration_end = $batch['registration_end'] ?? null;
        $status = $model->getEffectivePhase();

        return [
          'id' => $batch['id'],
          'title' => $batch['title'] ?? 'N/A',
          'registration_start_label' => !empty($batch['registration_start']) ? date('d/m/Y', strtotime($batch['registration_start'])) : 'Chưa có',
          'registration_end_label' => !empty($batch['registration_end']) ? date('d/m/Y', strtotime($batch['registration_end'])) : 'Chưa có',
          'effective_status' => $status,
          'effective_status_label' => ProjectBatchStatus::getLabel($status),
          'effective_status_variant' => ProjectBatchStatus::getVariant($status),
          '_href' => url('student/project_batches/' . $batch['id']),
          '_label' => 'Xem chi tiết đợt đồ án ' . ($batch['title'] ?? '')
        ];
      }, $batches)
    ]) ?>
  </script>
  <script>
    (() => {
      const root = document.querySelector('[data-tm="student_batches_table"]');
      if (!root) return;

      const enhanceRows = () => {
        root.querySelectorAll('.tm-tbody .tm-tr').forEach((row) => {
          if (row.dataset.rowNavigationReady) return;
          const link = row.querySelector('a[href]');
          if (!link) return;

          row.dataset.rowNavigationReady = 'true';
          row.classList.add('tm-tr--interactive');
          row.tabIndex = 0;
          row.setAttribute('role', 'link');
          row.setAttribute('aria-label', `Xem chi tiết ${link.textContent.trim()}`);

          // Thêm style con trỏ trực tiếp bằng JS
          row.style.cursor = 'pointer';

          row.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, select, textarea')) return;
            window.location.href = link.href;
          });
          row.addEventListener('keydown', (event) => {
            if (event.target !== row || !['Enter', ' '].includes(event.key)) return;
            event.preventDefault();
            window.location.href = link.href;
          });
        });
      };

      root.addEventListener('tm:render', enhanceRows);
      document.addEventListener('DOMContentLoaded', enhanceRows);
    })();
  </script>
<?php endif; ?>
<?php $layout->end() ?>