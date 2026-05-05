<?php
/**
 * View: Đồ án tốt nghiệp sinh viên
 * Route: /student/graduation
 * SEO: Thông tin về đợt đồ án tốt nghiệp của sinh viên
 */
$student = $student ?? null;
?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h1 class="title text-2xl font-semibold">Đồ án tốt nghiệp</h1>
      <p>Theo dõi tiến độ và kết quả thực hiện đồ án tốt nghiệp.</p>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="card shadow py-12 text-center">
  <div class="card__content flex flex-col items-center">
    <div class="text-5xl text-muted-foreground/20 mb-6">
      <i class="fa-solid fa-lock"></i>
    </div>
    <h2 class="text-xl font-semibold mb-2">Chưa có thông tin đồ án</h2>
    <p class="text-muted-foreground max-w-sm mx-auto">
      Bạn hiện chưa tham gia vào đợt làm đồ án tốt nghiệp nào. 
      Thông tin sẽ hiển thị khi bạn bắt đầu đợt mới.
    </p>
  </div>
</div>
