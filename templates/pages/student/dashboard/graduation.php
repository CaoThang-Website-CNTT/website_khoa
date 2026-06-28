<?php

/**
 * View: Đồ án tốt nghiệp sinh viên
 * Route: /student/graduation
 * SEO: Thông tin về đợt đồ án tốt nghiệp của sinh viên
 */
$student = $student ?? null;
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Đồ án tốt nghiệp</h2>
  <p class="title-wrapper__description">Theo dõi tiến độ và kết quả thực hiện đồ án tốt nghiệp.</p>
  <?php $layout->end() ?>
  <div class="card shadow py-12 text-center">
    <div class="card__content flex flex-col items-center">
      <div class="text-5xl mb-6">
        <i class="fa-solid fa-lock"></i>
      </div>
      <h2 class="text-xl font-semibold mb-2">Chưa có thông tin đồ án</h2>
      <p class="max-w-sm mx-auto">
        Bạn hiện chưa tham gia vào đợt làm đồ án tốt nghiệp nào.
        Thông tin sẽ hiển thị khi bạn bắt đầu đợt mới.
      </p>
    </div>
  </div>
