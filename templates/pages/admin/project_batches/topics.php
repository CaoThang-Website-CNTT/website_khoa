<?php

use App\Models\ProjectBatch;
use App\Enums\ProjectTopicStatus;

$batchObj = (object) $batch;
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Danh sách Đề tài
</h2>
<p>Đợt: <?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}") ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<?php
$topicFilterUrl = function (string $filterKey) use ($batchObj): string {
  $query = ['filter' => $filterKey];
  if (isset($_GET['limit']) && $_GET['limit'] !== '') {
    $query['limit'] = (int) $_GET['limit'];
  }
  return url("admin/project_batches/{$batchObj->id}/topics?" . http_build_query($query));
};

$tabsMode = 'navigation';
$tabsId = 'topic-status-tabs';
$activeTab = $filter ?? 'all';
$tabs = [
  [
    'key' => 'all',
    'label' => 'Tất cả',
    'href' => $topicFilterUrl('all'),
  ],
  [
    'key' => ProjectTopicStatus::PENDING,
    'label' => 'Chờ duyệt',
    'href' => $topicFilterUrl(ProjectTopicStatus::PENDING),
    'badge' => $pendingCount ?? 0,
    'badgeVariant' => 'warning',
  ],
  [
    'key' => ProjectTopicStatus::APPROVED,
    'label' => 'Đã duyệt',
    'href' => $topicFilterUrl(ProjectTopicStatus::APPROVED),
  ],
  [
    'key' => ProjectTopicStatus::REJECTED,
    'label' => 'Từ chối',
    'href' => $topicFilterUrl(ProjectTopicStatus::REJECTED),
  ],
  [
    'key' => ProjectTopicStatus::DRAFT,
    'label' => 'Bản nháp',
    'href' => $topicFilterUrl(ProjectTopicStatus::DRAFT),
  ]
];
?>

<?php $layout->start('content') ?>

<div class="tabs mb-4" data-tabs data-tabs-id="<?= htmlspecialchars($tabsId) ?>"
  data-tabs-mode="<?= htmlspecialchars($tabsMode) ?>" data-tabs-panel-active="<?= htmlspecialchars($activeTab) ?>">
  <div class="tabs__list" role="tablist">
    <?php foreach ($tabs as $tab): ?>
      <?php
      $isActive = ($tab['key'] === $activeTab);
      $badge = $tab['badge'] ?? null;
      ?>
      <a href="<?= htmlspecialchars($tab['href']) ?>" role="tab" aria-selected="<?= $isActive ? 'true' : 'false' ?>"
        data-tabs-trigger="<?= htmlspecialchars($tab['key']) ?>"
        data-tabs-trigger-state="<?= $isActive ? 'active' : 'idle' ?>" tabindex="<?= $isActive ? '0' : '-1' ?>"
        class="tabs__trigger">
        <?= htmlspecialchars($tab['label']) ?>
        <?php if ($badge !== null && $badge > 0): ?>
          <span class="badge" data-variant="<?= htmlspecialchars($tab['badgeVariant'] ?? 'outline') ?>">
            <?= htmlspecialchars((string) $badge) ?>
          </span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>



<!-- Table Container -->
<div class="tm-container" id="topics_table" data-tm="topics_table" data-tm-mode="server" data-tm-searchable
  <?= (isset($filter) && $filter === ProjectTopicStatus::PENDING) ? 'data-tm-selectable="true" data-tm-id-key="id"' : '' ?>>

  <template data-tm-col="title" data-tm-label="Đề tài" data-tm-sortable>
    <div class="font-medium">{{ value }}</div>
  </template>

  <template data-tm-col="description" data-tm-label="Mô tả" data-tm-sortable>
    <div class="font-medium line-clamp-2 mt-1" style="white-space: pre-line;" title="{{ row.description }}">{{ row.description }}</div>
  </template>

  <template data-tm-col="teacher" data-tm-label="Giảng viên" data-tm-sortable data-tm-filter-type="text">
    <div class="font-medium text-sm">{{ value.name }}</div>
  </template>

  <template data-tm-col="max_students" data-tm-label="Số SV tối đa" data-tm-sortable></template>

  <template data-tm-col="status" data-tm-label="Trạng thái">
    <span class="badge" data-variant="{{ value.variant }}">{{ value.label }}</span>
  </template>

  <template data-tm-col="_actions" data-tm-label="Hành động" data-tm-width="150px">
    <div class="space-x-1" data-id="{{ row.id }}">
      <a href="{{ row.pdf_file_url ? row.pdf_file_url : 'javascript:void(0)' }}"
        target="{{ row.pdf_file_url ? '_blank' : '_self' }}"
        class="btn"
        data-size="md"
        data-variant="outline"
        title="{{ row.pdf_file_url ? 'Xem file mô tả' : 'Chưa có file mô tả' }}"
        style="{{ !row.pdf_file_url ? 'opacity: 0.5; pointer-events: none;' : '' }}">
        <i class="fa-solid fa-file-pdf"></i>
      </a>
      <button type="button" class="btn btn-approve {{ row.status.value !== 'pending' ? 'hidden' : '' }}" data-size="md" data-variant="primary" data-id="{{ row.id }}" title="Duyệt">
        <i class="fa-solid fa-check"></i>
      </button>
      <button type="button" class="btn btn-reject {{ row.status.value !== 'pending' ? 'hidden' : '' }}" data-size="md" data-id="{{ row.id }}" data-variant="destructive" title="Từ chối">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <span class="text-xs {{ row.status.value !== 'rejected' ? 'hidden' : '' }}" title="{{ row.reject_reason }}">Xem lý do</span>
    </div>
  </template>

  <template data-tm-pagination></template>
</div>

<!-- Reject Modal -->
<div class="modal" id="reject-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Từ chối Đề tài</h3>
    <p class="modal__description">Vui lòng nhập lý do từ chối để giảng viên có thể chỉnh sửa.</p>
  </div>
  <div class="modal__content space-y-4">
    <input type="hidden" id="reject-topic-id">
    <div class="field" data-field-required>
      <label class="field__label" for="reject-reason">Lý do từ chối</label>
      <textarea id="reject-reason" class="field__input" rows="3" required></textarea>
    </div>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button id="confirm-reject-btn" data-variant="destructive" class="btn" data-size="lg" type="button">Từ chối</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<?php $layout->end() ?>

<?php $layout->start('scripts') ?>
<script>
  window.BATCH_ID = <?= $batchObj->id ?>;
  window.TOPIC_STATUSES = {
    '<?= ProjectTopicStatus::DRAFT ?>': {
      label: 'Bản nháp',
      variant: 'secondary'
    },
    '<?= ProjectTopicStatus::PENDING ?>': {
      label: 'Chờ duyệt',
      variant: 'warning'
    },
    '<?= ProjectTopicStatus::APPROVED ?>': {
      label: 'Đã duyệt',
      variant: 'success'
    },
    '<?= ProjectTopicStatus::REJECTED ?>': {
      label: 'Từ chối',
      variant: 'destructive'
    },
  };
</script>

<script type="module">
  import {
    TableManager
  } from '<?= url("public/js/table/index.js") ?>';

  window.BATCH_ID = <?= $batchObj->id ?>;
  const STATUSES = {
    '<?= ProjectTopicStatus::DRAFT ?>': {
      label: 'Bản nháp',
      variant: 'secondary'
    },
    '<?= ProjectTopicStatus::PENDING ?>': {
      label: 'Chờ duyệt',
      variant: 'warning'
    },
    '<?= ProjectTopicStatus::APPROVED ?>': {
      label: 'Đã duyệt',
      variant: 'success'
    },
    '<?= ProjectTopicStatus::REJECTED ?>': {
      label: 'Từ chối',
      variant: 'destructive'
    },
  };

  const tm = TableManager.get("topics_table");
  const isPendingFilter = <?= json_encode(isset($filter) && $filter === ProjectTopicStatus::PENDING) ?>;
  const bulkApproveUrl = window.appUrl + '/api/v1/project_topics/bulk-approve';
  const filterKey = '<?= isset($filter) ? $filter : 'all' ?>';
  const searchVal = '<?= isset($search) ? $search : '' ?>';

  if (isPendingFilter) {
    TableManager.registerBulkActions("topics_table", {
      countLabel: count => `Đã chọn: ${count}`,
      actions: [{
        id: "approve",
        label: "Duyệt đã chọn",
        icon: "fa-solid fa-check",
        variant: "primary",
        confirm: {
          message: "Duyệt các đề tài đã chọn?"
        },
        onClick: ({
          selectedIds
        }) => {
          fetch(bulkApproveUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                topic_ids: selectedIds
              })
            })
            .then(response => response.json())
            .then(res => {
              if (res.success) {
                Toast.success(res.message);
                window.location.reload();
              } else {
                Toast.error(res.message);
              }
            })
            .catch(err => {
              console.error(err);
              Toast.error('Đã xảy ra lỗi hệ thống.');
            });
        },
      }, ],
    });
  }

  tm.root.addEventListener("tm:pagination:change", (e) => {
    const {
      page,
      limit
    } = e.detail;
    let url = `<?= url("admin/project_batches/{$batchObj->id}/topics") ?>?filter=${filterKey}&page=${page}&limit=${limit}`;
    if (searchVal) url += `&search=${searchVal}`;
    window.location.href = url;
  });

  tm.root.addEventListener("tm:search", (e) => {
    const term = e.detail.term;
    let url = `<?= url("admin/project_batches/{$batchObj->id}/topics") ?>?filter=${filterKey}`;
    if (term) url += `&search=${term}`;
    window.location.href = url;
  });

  // Since tm handles rendering rows, we bind events using event delegation
  document.addEventListener('click', function(e) {
    const approveBtn = e.target.closest('.btn-approve');
    if (approveBtn) {
      if (!confirm('Bạn có chắc chắn muốn duyệt đề tài này?')) return;
      fetch(window.appUrl + '/api/v1/project_topics/' + approveBtn.dataset.id + '/approve', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(res => {
          if (res.success) {
            Toast.success(res.message);
            window.location.reload();
          } else {
            Toast.error(res.message);
          }
        })
        .catch(err => {
          Toast.error('Đã xảy ra lỗi hệ thống.');
        });
      return;
    }

    const rejectBtn = e.target.closest('.btn-reject');
    if (rejectBtn) {
      document.getElementById('reject-topic-id').value = rejectBtn.dataset.id;
      document.getElementById('reject-reason').value = '';
      document.getElementById('reject-modal').setAttribute('data-state', 'open');
      return;
    }
  });

  document.getElementById('confirm-reject-btn').addEventListener('click', () => {
    const id = document.getElementById('reject-topic-id').value;
    const reason = document.getElementById('reject-reason').value.trim();
    if (!reason) {
      Toast.error('Vui lòng nhập lý do từ chối.');
      return;
    }

    fetch(window.appUrl + '/api/v1/project_topics/' + id + '/reject', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          reason
        })
      })
      .then(response => response.json())
      .then(res => {
        if (res.success) {
          Toast.success(res.message);
          window.location.reload();
        } else {
          Toast.error(res.message);
        }
      })
      .catch(err => {
        Toast.error('Đã xảy ra lỗi hệ thống.');
      });
  });

  // Pre-fill search if it exists
  const searchInput = tm.root.querySelector('input[type="search"]');
  if (searchInput && searchVal) {
    searchInput.value = searchVal;
  }

  // Load Data
  tm.loadData(<?= json_encode([
                'rows' => array_map(function ($topic) {
                  return [
                    'id' => $topic['id'],
                    'title' => $topic['title'],
                    'description' => $topic['description'] ?? '',
                    'teacher' => [
                      'name' => $topic['teacher_name'],
                      'department' => $topic['department_name'] ?? ''
                    ],
                    'max_students' => $topic['max_students'],
                    'status' => [
                      'value' => $topic['status'],
                      'label' => ProjectTopicStatus::getLabel($topic['status']),
                      'variant' => ProjectTopicStatus::getVariant($topic['status'])
                    ],
                    'pdf_file_url' => !empty($topic['pdf_file_path']) ? url('/public/media/' . $topic['pdf_file_path']) : null,
                    'reject_reason' => $topic['reject_reason'] ?? ''
                  ];
                }, $data->getItems()),
                'total' => $data->getTotal(),
                'page' => $data->getCurrentPage(),
                'limit' => $data->getPerPage()
              ]) ?>);
</script>
<?php $layout->end() ?>