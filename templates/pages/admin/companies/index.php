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
    Có dấu hiệu trùng lặp
  </a>
  <a href="<?= url('admin/companies/create') ?>" data-variant="primary" data-size="md" class="btn">
    <i class="fa-solid fa-plus"></i>
    Thêm
  </a>
  <?php $layout->end() ?>

  <?php $layout->start("content") ?>

  <?php
  $companyFilterUrl = function (string $filterKey): string {
    $query = ['filter' => $filterKey];

    if (isset($_GET['limit']) && $_GET['limit'] !== '') {
      $query['limit'] = (int) $_GET['limit'];
    }

    return url('admin/companies?' . http_build_query($query));
  };

  $tabsMode = 'navigation';
  $tabsId = 'companies-filter-tabs';
  $activeTab = $filter ?? 'all';
  $tabs = [
    [
      'key' => 'all',
      'label' => 'Tất cả',
      'href' => $companyFilterUrl('all'),
    ],
    [
      'key' => 'pending',
      'label' => 'Chưa xác thực',
      'href' => $companyFilterUrl('pending'),
      'badge' => $pendingCount ?? 0,
      'badgeVariant' => 'warning',
    ],
    [
      'key' => 'verified',
      'label' => 'Đã xác thực',
      'href' => $companyFilterUrl('verified'),
    ],
  ];
  ?>

  <div class="tabs" data-tabs data-tabs-id="<?= htmlspecialchars($tabsId) ?>"
    data-tabs-mode="<?= htmlspecialchars($tabsMode) ?>" data-tabs-panel-active="<?= htmlspecialchars($activeTab) ?>">
    <div class="tabs__list" role="tablist">
      <?php foreach ($tabs as $tab): ?>
        <?php
        $isActive = ($tab['active'] ?? ($tab['key'] === $activeTab));
        $badge = $tab['badge'] ?? null;
        ?>
        <a href="<?= htmlspecialchars($tab['href']) ?>" role="tab" aria-selected="<?= $isActive ? 'true' : 'false' ?>"
          data-tabs-trigger="<?= htmlspecialchars($tab['key']) ?>"
          data-tabs-trigger-state="<?= $isActive ? 'active' : 'idle' ?>" tabindex="<?= $isActive ? '0' : '-1' ?>"
          class="tabs__trigger">
          <?= htmlspecialchars($tab['label']) ?>
          <?php if ($badge !== null): ?>
            <span class="badge" data-variant="<?= htmlspecialchars($tab['badgeVariant'] ?? 'outline') ?>">
              <?= htmlspecialchars((string) $badge) ?>
            </span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php unset($tabs, $tabsMode, $tabsId, $activeTab, $companyFilterUrl); ?>

  <div class="tm-container" id="companies_table" data-tm="companies_table" data-tm-mode="server" data-tm-searchable
    data-server-table-url="<?= url('api/v1/companies') ?>" data-server-table-filter="<?= htmlspecialchars($filter ?? 'all') ?>"
    <?= (isset($filter) && $filter === 'pending') ? 'data-tm-selectable="true" data-tm-id-key="id"' : '' ?>>

    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <template data-tm-col="name" data-tm-label="Tên công ty" data-tm-sortable>
      <a href="<?= url('admin/companies/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
    </template>

    <template data-tm-col="tax_code" data-tm-label="MST" data-tm-sortable></template>

    <template data-tm-col="address" data-tm-label="Địa chỉ"></template>

    <template data-tm-col="phone" data-tm-label="SĐT"></template>

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

    <template data-tm-col="_actions" data-tm-label="Hành động" data-tm-width="80px">
      <a href="<?= url('admin/companies/') ?>{{ row.id }}" class="btn" data-variant="outline" data-size="sm">
        Sửa
      </a>
    </template>

    <template data-tm-pagination></template>
  </div>

  <?php $layout->end() ?>

  <?php $layout->start("scripts"); ?>
  <script type="module">
    import {
      TableManager
    } from '<?= url("public/js/table/index.js") ?>';

    const tm = TableManager.get("companies_table");
    const bulkApproveUrl = '<?= url('admin/companies/bulk-approve') ?>';
    const isPendingFilter = <?= json_encode(isset($filter) && $filter === 'pending') ?>;

    if (isPendingFilter) {
      TableManager.registerBulkActions("companies_table", {
        countLabel: count => `Đã chọn: ${count}`,
        actions: [
          {
            id: "approve",
            label: "Xác thực đã chọn",
            icon: "fa-solid fa-check",
            variant: "primary",
            confirm: {
              message: "Xác thực các công ty đã chọn?"
            },
            onClick: ({ selectedIds }) => {
              const form = document.createElement("form");
              form.method = "POST";
              form.action = bulkApproveUrl;
              form.hidden = true;

              selectedIds.forEach(id => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "ids[]";
                input.value = id;
                form.appendChild(input);
              });

              document.body.appendChild(form);
              form.submit();
            },
          },
        ],
      });
    }

    tm.root.addEventListener("tm:pagination:change", (e) => {
      const {
        page,
        limit
      } = e.detail;
      const filter = '<?= isset($filter) ? $filter : 'all' ?>';
      window.history.replaceState({}, '', `<?= url("admin/companies") ?>?filter=${filter}&page=${page}&limit=${limit}`);
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
  <script type="module" src="<?= url('public/js/pages/admin/server_table.js') ?>"></script>
  <?php $layout->end(); ?>
