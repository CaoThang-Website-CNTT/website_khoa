<?php
$teachers = [
  ['name' => 'TS. Nguyễn Thị Lan', 'role' => 'Phó Giáo sư, Trí tuệ nhân tạo', 'phone' => '028 3821 2360', 'email' => 'lan.nguyen@faculty.edu.vn'],
  ['name' => 'ThS. Trần Minh Quân', 'role' => 'Giảng viên, Kỹ thuật phần mềm', 'phone' => '028 3821 2361', 'email' => 'quan.tran@faculty.edu.vn'],
  ['name' => 'TS. Lê Hoàng Anh', 'role' => 'Trưởng bộ môn, Khoa học dữ liệu', 'phone' => '028 3821 2362', 'email' => 'anh.le@faculty.edu.vn'],
  ['name' => 'ThS. Phạm Thu Hà', 'role' => 'Giảng viên, Hệ thống thông tin', 'phone' => '028 3821 2363', 'email' => 'ha.pham@faculty.edu.vn'],
  ['name' => 'TS. Võ Quốc Bảo', 'role' => 'Giảng viên chính, An toàn thông tin', 'phone' => '028 3821 2364', 'email' => 'bao.vo@faculty.edu.vn'],
  ['name' => 'ThS. Đặng Ngọc Mai', 'role' => 'Giảng viên, Mạng máy tính', 'phone' => '028 3821 2365', 'email' => 'mai.dang@faculty.edu.vn'],
];

$layout->start('head');
?>
<link rel="stylesheet"
  href="<?= url('public/css/pages/faculty.css') ?>?v=<?= filemtime(BASE_PATH . '/public/css/pages/faculty.css') ?>">
<?php
$layout->end();
$layout->start('content');
?>
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
      include_once BASE_PATH . '/templates/components/breadcrumb.php';
      renderBreadcrumb([
        ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
        ['url' => url('/gioi-thieu'), 'title' => 'Giới thiệu'],
        ['url' => url('/giang-vien'), 'title' => 'Đội ngũ giảng viên'],
      ]);
      ?>
    </div>
  </div>
</section>

<section class="page-thumbnail relative">
  <div class="page-thumbnail__media"><img class="w-full h-full object-cover" src="<?= url('public/img/about.jpg') ?>"
      alt=""></div>
  <div class="page-thumbnail__overlay absolute inset-0 flex justify-center items-center">
    <div class="container">
      <div class="container-wrapper">
        <div class="page-thumbnail__content flex flex-col justify-center items-center gap-6 text-center">
          <span class="badge" data-variant="primary">Đội ngũ giảng viên</span>
          <h1 class="page-thumbnail__title">Khoa Công nghệ Thông tin</h1>
          <p class="page-thumbnail__subtitle">Giàu kinh nghiệm, vững chuyên môn và luôn tiên phong đổi mới,<br>
            đồng hành cùng sinh viên làm chủ công nghệ tương lai.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="faculty-directory py-12">
  <div class="container">
    <div class="container-wrapper">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-faculty-grid>
        <?php foreach ($teachers as $teacher): ?>
          <article class="card faculty-card overflow-hidden" data-faculty-card tabindex="0" role="button"
            aria-expanded="false">
            <div class="card__header faculty-card__portrait">
              <img class="w-full h-full object-cover" src="<?= url('public/img/about.jpg') ?>"
                alt="Ảnh chân dung <?= htmlspecialchars($teacher['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
            </div>
            <div class="card__content faculty-card__content">
              <h3 class="card__title text-xl"><?= htmlspecialchars($teacher['name'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="card__description mt-1"><?= htmlspecialchars($teacher['role'], ENT_QUOTES, 'UTF-8') ?></p>
              <div class="faculty-card__contact mt-4" data-faculty-contact aria-hidden="true">
                <p class="flex items-center gap-2"><i class="fa-solid fa-phone"
                    aria-hidden="true"></i><span><?= htmlspecialchars($teacher['phone'], ENT_QUOTES, 'UTF-8') ?></span>
                </p>
                <p class="flex items-center gap-2 mt-2"><i class="fa-regular fa-envelope"
                    aria-hidden="true"></i><span><?= htmlspecialchars($teacher['email'], ENT_QUOTES, 'UTF-8') ?></span>
                </p>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script src="<?= url('public/js/pages/site/faculty.js') ?>" type="module"></script>
<?php $layout->end(); ?>