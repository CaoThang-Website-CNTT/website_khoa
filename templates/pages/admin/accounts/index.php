<?php $layout->start("heading"); ?>
<h2 class="title text-2xl font-semibold">
  Tài khoản
  <span class="badge" data-variant="primary">
    <?= $data->getTotal(); ?>
  </span>
</h2>
<?php $layout->end(); ?>

<?php $layout->start("actions"); ?>
<a href="<?= url('admin/accounts/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm
</a>
<?php $layout->end(); ?>

<div class="tm-container" data-tm="accounts" data-tm-id-key="id" data-tm-mode="server" data-tm-selectable
  data-tm-searchable>

  <!-- Khai báo phân trang -->
  <template data-tm-pagination></template>

  <!-- Cột Id -->
  <template data-tm-col="id" data-tm-label="Id" data-tm-sortable></template>

  <!-- Cột Email -->
  <template data-tm-col="email" data-tm-label="Email" data-tm-sortable>
    <a href="<?= url('admin/accounts/') ?>{{ row.id }}">{{ value }}</a>
  </template>

  <!-- Cột Role -->
  <template data-tm-col="role" data-tm-label="Vai trò" data-tm-sortable data-tm-filter-type="select"
    data-tm-filter-options='[{"label":"Admin","value":"admin"},{"label":"Editor","value":"editor"},{"label":"Student","value":"student"},{"label":"Teacher","value":"teacher"}]'></template>

  <!-- Cột Created At-->
  <template data-tm-col="created_at" data-tm-label="Ngày tạo"></template>

</div>

<!-- Bootstrap Data Source -->
<?php $layout->start("scripts"); ?>
<script type="module">
  import { TableManager } from '<?= url("public/js/table/table_manager.js") ?>';

  const tm = TableManager.get("accounts");

  tm.root.addEventListener("tm:state-change", async (e) => {
    const { reason, state } = e.detail;
    console.log(`State thay đổi: ${reason}`, state);

    if (reason === "search") {
      try {
        const url = new URL("http://localhost/website_khoa/api/v1/accounts");

        if (state.search) {
          url.searchParams.set("search", `%${state.search}%`);
        }
        if (state.page) {
          url.searchParams.set("page", state.page);
        }
        if (state.limit) {
          url.searchParams.set("limit", state.limit);
        }

        const response = await fetch(url);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        tm.loadData({
          rows: result.data.map(account => ({
            id: account.id,
            email: account.email,
            role: account.role,
            created_at: account.created_at,
          })),
          total: result.total,
          page: result.page,
          limit: result.limit
        });
      } catch (error) {
        console.error("Lỗi khi tìm kiếm dữ liệu:", error);
      }
    }
  });

  tm.root.addEventListener("tm:pagination:change", (e) => {
    const { page, limit } = e.detail;
    const searchParams = new URLSearchParams(window.location.search);
    const search = searchParams.get('filter[search]') || '';
    const searchQuery = search ? `&filter[search]=${encodeURIComponent(search)}` : '';
    window.location.href = `<?= url("admin/accounts") ?>?page=${page}&limit=${limit}${searchQuery}`;
  });

  tm.loadData(
    <?= json_encode([
      'rows' => array_map(fn($account) => [
        'id' => $account->id,
        'email' => $account->email,
        'role' => $account->role,
        'created_at' => $account->created_at,
      ], $data->getItems()),
      'total' => $data->getTotal(),
      'page' => $data->getCurrentPage(),
      'limit' => $data->getPerPage()
    ]) ?>
  );
</script>
<?php $layout->end(); ?>