<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
<?php if ($flash = request()->getFlash()): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Giảng Viên
        <span class="badge" data-variant="primary">
          <?= (int) count($teachers ?? []) ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">

      <div>
        <a href="<?= url('admin/teachers/create') ?>" data-variant="primary" data-size="md" class="btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
            <path
              d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
          </svg>
          Thêm
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<table id="teacher-table" class="data-table tm">
  <thead>
    <tr>
      <th data-sort="account_id">
        <h6>ID</h6>
      </th>
      <th data-sort="full_name">
        <h6>Họ tên</h6>
      </th>
      <th data-sort="email">
        <h6>Email</h6>
      </th>
      <th data-sort="degree">
        <h6>Học hàm/học vị</h6>
      </th>
      <th data-sort="title">
        <h6>Chức danh</h6>
      </th>
      <th data-sort="department">
        <h6>Bộ môn</h6>
      </th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

<nav role="navigation" aria-label="pagination" class="pagination">
  <ul class="pagination-content" id="teacher-pagination"></ul>
</nav>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const tm = new TableManager({
      tableSelector: '#teacher-table',
      paginationSelector: '#teacher-pagination',
      apiUrl: '<?= url("api/teachers") ?>',
      defaultSort: 'account_id',
      defaultDir: 'ASC',
      renderRow: function(teacher) {
        const detailUrl = `<?= url('admin/teachers/') ?>${teacher.account_id}`;

        return `
      <tr class="tm__row tm__row--clickable" onclick="window.location.href='${detailUrl}'">
        <td class="data-table__id">#${teacher.account_id || 'N/A'}</td>
        <td>${teacher.full_name || 'N/A'}</td>
        <td>${teacher.account?.email || 'N/A'}</td>
        <td>${teacher.degree || 'N/A'}</td>
        <td>${teacher.title || 'N/A'}</td>
        <td>${teacher.department || 'N/A'}</td>
      </tr>
      `;
      }
    });
    tm.init('Tìm tên, chức danh, bộ môn...');
  });
</script>