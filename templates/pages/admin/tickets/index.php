<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Tickets
  <span class="badge" data-variant="primary"><?= $data->getTotal(); ?></span>
</h2>
<?php $layout->end() ?>

<?php $layout->start("content") ?>
<div class="tm-container" data-tm="tickets_table" data-tm-mode="server" data-tm-searchable
  data-server-table-url="<?= url('api/v1/tickets') ?>">
  <!-- Cột ID -->
  <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

  <!-- Cột Tiêu đề với Link edit -->
  <template data-tm-col="title" data-tm-label="Tiêu đề">
    <a href="<?= url('admin/tickets/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
  </template>

  <!-- Cột Loại -->
  <template data-tm-col="type" data-tm-label="Loại"></template>

  <!-- Cột Trạng thái -->
  <template data-tm-col="status" data-tm-label="Trạng thái">
    <span class="badge" data-variant="primary">{{ value }}</span>
  </template>

  <!-- Cột Reporter Email -->
  <template data-tm-col="reporter_email" data-tm-label="Reporter"></template>

  <!-- Cột Created At -->
  <template data-tm-col="created_at" data-tm-label="Ngày tạo"></template>

  <!-- Phân trang -->
  <template data-tm-pagination></template>
</div>
<?php $layout->end() ?>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="tickets_table">
  <?= json_encode([
    'rows' => array_map(fn($ticket) => [
      'id' => $ticket->id,
      'title' => $ticket->title,
      'type' => $ticket->type,
      'status' => $ticket->status,
      'reporter_email' => $ticket->reporter_email,
      'created_at' => $ticket->created_at,
    ], $data->getItems()),
    'total' => $data->count(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage(),
  ]) ?>
</script>
<script type="module" src="<?= url('public/js/pages/admin/server_table.js') ?>"></script>
<?php $layout->end() ?>
