<link rel="stylesheet" href="<?= url('public/css/company_index.css') ?>">

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Công ty
  <span class="badge" data-variant="primary">
    <?= $data->getTotal(); ?>
  </span>
</h2>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/companies/duplicates') ?>" data-variant="destructive" data-size="md" class="btn">
  <i class="fa-solid fa-code-compare"></i>
  Nghi trùng lặp
</a>
<a href="<?= url('admin/companies/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm
</a>
<?php $layout->end() ?>

<div class="dashboard-tabs">
  <a href="<?= url('admin/companies?filter=all') ?>" class="dashboard-tabs__item <?= (!isset($filter) || $filter === 'all') ? 'dashboard-tabs__item--active' : '' ?>">
    Tất cả
  </a>
  <a href="<?= url('admin/companies?filter=pending') ?>" class="dashboard-tabs__item <?= (isset($filter) && $filter === 'pending') ? 'dashboard-tabs__item--active' : '' ?>">
    Chưa xác thực <span class="badge" data-variant="warning" data-size="sm"><?= $pendingCount ?? 0 ?></span>
  </a>
  <a href="<?= url('admin/companies?filter=verified') ?>" class="dashboard-tabs__item <?= (isset($filter) && $filter === 'verified') ? 'dashboard-tabs__item--active' : '' ?>">
    Đã xác thực
  </a>
</div>

<div class="card relative">
  <div class="tm-container" id="companies_table" data-tm="companies_table" data-tm-mode="server" data-tm-searchable <?= (isset($filter) && $filter === 'pending') ? 'data-tm-selectable="true" data-tm-id-key="id"' : '' ?>>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên -->
    <template data-tm-col="name" data-tm-label="Tên công ty" data-tm-sortable>
      <a href="<?= url('admin/companies/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
    </template>

    <!-- Cột Mã số thuế -->
    <template data-tm-col="tax_code" data-tm-label="MST" data-tm-sortable></template>

    <!-- Cột Địa chỉ -->
    <template data-tm-col="address" data-tm-label="Địa chỉ"></template>

    <!-- Cột Số điện thoại -->
    <template data-tm-col="phone" data-tm-label="SĐT"></template></template>

    <!-- Cột Trạng thái -->
    <template data-tm-col="status" data-tm-label="Trạng thái">
      <div class="flex flex-col gap-2">
        <span class="badge" data-variant="{{ row.is_verified ? 'primary' : 'warning' }}">
          {{ row.is_verified ? 'Đã xác thực' : 'Chưa xác thực' }}
        </span>
        <span class="badge" data-variant="outline" data-size="sm">
          {{ row.source === 'api' ? 'API' : 'Nhập thủ công' }}
        </span>
      </div>
    </template>

    <!-- Cột Hành động -->
    <template data-tm-col="_actions" data-tm-label="Hành động" data-tm-width="80px">
      <a href="<?= url('admin/companies/') ?>{{ row.id }}" class="btn" data-variant="outline" data-size="sm">
        Sửa
      </a>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<?php if (isset($filter) && $filter === 'pending'): ?>
  <!-- Bulk Action Bar -->
  <form id="bulk-approve-form" action="<?= url('admin/companies/bulk-approve') ?>" method="POST">
    <div id="bulk-action-bar" class="company-action-bar hidden" data-state="closed">
      <div class="company-action-bar__container">
        <div class="company-action-bar__stats">
          <span class="badge" data-variant="primary" id="selected-count">Đã chọn: 0</span>
        </div>
        <div class="company-action-bar__inputs" id="bulk-inputs-container">
          <!-- Hidden inputs appended here -->
        </div>
        <div class="company-action-bar__actions">
          <button type="button" class="btn" data-variant="outline" data-size="md" id="btn-cancel-selection">Hủy chọn</button>
          <button type="submit" class="btn" data-variant="primary" data-size="md">
            <i class="fa-solid fa-check"></i> Xác thực đã chọn
          </button>
        </div>
      </div>
    </div>
  </form>

  <script>
    document.addEventListener('tm-selection-changed', function(e) {
      if (e.detail.tmId !== 'companies_table') return;

      const selectedIds = e.detail.selectedIds;
      const actionBar = document.getElementById('bulk-action-bar');
      const countSpan = document.getElementById('selected-count');
      const container = document.getElementById('bulk-inputs-container');

      if (selectedIds.length > 0) {
        actionBar.classList.remove('hidden');
        actionBar.setAttribute('data-state', 'open');
        countSpan.textContent = `Đã chọn: ${selectedIds.length}`;

        container.innerHTML = '';
        selectedIds.forEach(id => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = id;
          container.appendChild(input);
        });
      } else {
        actionBar.classList.add('hidden');
        actionBar.setAttribute('data-state', 'closed');
        container.innerHTML = '';
      }
    });

    document.getElementById('btn-cancel-selection')?.addEventListener('click', function() {
      document.dispatchEvent(new CustomEvent('tm-clear-selection', {
        detail: {
          tmId: 'companies_table'
        }
      }));
    });
  </script>
<?php endif; ?>

<!-- Bootstrap Data Source -->
<?php $layout->start("scripts"); ?>
<script type="module">
  import {
    TableManager
  } from '<?= url("public/js/table/table_manager.js") ?>';

  const tm = TableManager.get("companies_table");

  tm.root.addEventListener("tm:pagination:change", (e) => {
    const {
      page,
      limit
    } = e.detail;
    const filter = '<?= isset($filter) ? $filter : 'all' ?>';
    window.location.href = `<?= url("admin/companies") ?>?filter=${filter}&page=${page}&limit=${limit}`;
  });

  tm.loadData(<?= json_encode([
                'rows' => array_map(function ($company) {
                  return [
                    'id' => $company->id,
                    'name' => $company->name ?: 'Chưa có',
                    'tax_code' => $company->tax_code ?: 'Chưa có',
                    'address' => $company->address ?: 'Chưa có',
                    'phone' => $company->phone ?: 'Chưa có',
                    'website' => $company->website ?: 'Chưa có',
                    'is_verified' => $company->is_verified,
                    'source' => $company->source,
                    'status' => $company->is_verified ? 'Đã xác thực' : 'Chưa xác thực'
                  ];
                }, $data->getItems()),
                'total' => $data->getTotal(),
                'page' => $data->getCurrentPage(),
                'limit' => $data->getPerPage()
              ]) ?>);
</script>
<?php $layout->end(); ?>