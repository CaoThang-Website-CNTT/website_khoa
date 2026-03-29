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
        Sinh Viên
        <span class="badge" data-variant="primary">
          <?= (int) count($students ?? []) ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/students/create') ?>" data-variant="primary" data-size="md" class="btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
            <path
              d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
          </svg>
          Thêm
        </a>
        <a href="<?= url('admin/students/import') ?>" data-variant="outline" data-size="md" class="btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
            <path
              d="M64 0C28.7 0 0 28.7 0 64l0 240 182.1 0-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l72 72c9.4 9.4 9.4 24.6 0 33.9l-72 72c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-182.1 0 0 96c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-277.5c0-17-6.7-33.3-18.7-45.3L258.7 18.7C246.7 6.7 230.5 0 213.5 0L64 0zM325.5 176L232 176c-13.3 0-24-10.7-24-24L208 58.5 325.5 176z" />
          </svg>
          Nhập
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<table id="student-table" class="data-table tm">
  <thead>
    <tr>
      <th data-sort="account_id">ID</th>
      <th data-sort="full_name">Họ Tên</th>
      <th data-sort="email">Email</th>
      <th data-sort="gender">Giới tính</th>
      <th data-sort="phone">Điện thoại</th>
      <th data-sort="classroom_name">Lớp</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

<nav role="navigation" aria-label="pagination" class="pagination">
  <ul class="pagination-content" id="student-pagination"></ul>
</nav>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const tm = new TableManager({
      tableSelector: '#student-table',
      paginationSelector: '#student-pagination',
      apiUrl: '<?= url("api/students") ?>',
      defaultSort: 'account_id',
      defaultDir: 'ASC',
      filters: [{
        key: 'classroom_id',
        label: 'Lớp học',
        type: 'api',
        url: '<?= url("api/classrooms") ?>',
        required: true, // Bắt buộc phải chọn (nếu rỗng = không có dữ liệu)
        autoSelectFirst: true // Tự động chọn option đầu tiên
      }],
      renderRow: function(student) {
        const detailUrl = `<?= url('admin/students/') ?>${student.account_id}`;

        return `
      <tr class="tm__row tm__row--clickable" onclick="window.location.href='${detailUrl}'">
        <td class="data-table__id">#${student.account_id || 'N/A'}</td>
        <td>${student.full_name || 'N/A'}</td>
        <td>${student.account?.email || 'N/A'}</td>
        <td>${student.gender || 'N/A'}</td>
        <td>${student.phone || 'N/A'}</td>
        <td>
          <span class="badge" data-variant="secondary">${student?.classroom?.short_name || 'N/A'}</span>
        </td>
      </tr>
      `;
      }
    });
    tm.init('Tìm tên, MSSV, Email...');
  });
</script>