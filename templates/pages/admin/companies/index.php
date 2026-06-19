<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Công ty
  <span class="badge" data-variant="primary">
    <?= $data->count(); ?>
  </span>
</h2>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/companies/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm
</a>
<?php $layout->end() ?>
<div class="tm-container" data-tm="companies_table" data-tm-mode="client" data-tm-searchable>

  <!-- Cột ID -->
  <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

  <!-- Cột Tên -->
  <template data-tm-col="name" data-tm-label="Tên công ty" data-tm-sortable data-tm-filter-type="text">
    <a href="<?= url('admin/companies/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
  </template>

  <!-- Cột Mã số thuế -->
  <template data-tm-col="tax_code" data-tm-label="MST" data-tm-sortable data-tm-filter-type="text"></template>

  <!-- Cột Địa chỉ -->
  <template data-tm-col="address" data-tm-label="Địa chỉ" data-tm-filter-type="text"></template>

  <!-- Cột Số điện thoại -->
  <template data-tm-col="phone" data-tm-label="SĐT" data-tm-filter-type="text"></template>

  <!-- Cột Website -->
  <template data-tm-col="website" data-tm-label="Website">
    <a href="{{ value }}" target="_blank">{{ value }}</a>
  </template>

  <template data-tm-pagination></template>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="companies_table">
  <?= json_encode([
    'rows' => array_map(function ($company) {
        return [
          'id' => $company->id,
          'name' => $company->name ?? 'N/A',
          'tax_code' => $company->tax_code ?? 'N/A',
          'address' => $company->address ?? 'N/A',
          'phone' => $company->phone ?? 'N/A',
          'website' => $company->website ?? 'N/A'
        ];
      }, $data->getItems()),
    'total' => $data->count(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>