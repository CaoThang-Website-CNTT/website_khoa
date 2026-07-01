<?php
$layout->start('content');
include_once BASE_PATH . '/templates/components/breadcrumb.php';
?>
<section class="partners-page py-12">
  <div class="container"><div class="container-wrapper">
    <?php renderBreadcrumb([
      ['url' => url('/'), 'title' => 'Trang chủ'],
      ['url' => url('/viec-lam/doanh-nghiep'), 'title' => 'Doanh nghiệp đối tác'],
    ]); ?>
    <header class="partners-page__header">
      <span class="badge" data-variant="primary">Kết nối doanh nghiệp</span>
      <h1>Doanh nghiệp đối tác</h1>
      <p>Các đơn vị đồng hành cùng Khoa Công nghệ thông tin trong đào tạo, thực tập và cơ hội nghề nghiệp.</p>
    </header>

    <?php if ($partners): ?>
      <div class="partners-slider" data-partners-slider>
        <button class="btn partners-slider__control" data-partners-prev aria-label="Đối tác trước"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="partners-slider__track" data-partners-track tabindex="0">
          <?php foreach ($partners as $partner): ?>
            <?php $image = $partner->media?->file_path ? url('public/media/' . $partner->media->file_path) : url('public/img/default-post-thumb.jpg'); ?>
            <button class="card partner-card" type="button" data-partner
              data-title="<?= htmlspecialchars($partner->title) ?>"
              data-description="<?= htmlspecialchars($partner->description ?? '') ?>"
              data-url="<?= htmlspecialchars($partner->cta_url ?? '') ?>"
              data-image="<?= htmlspecialchars($image) ?>">
              <div class="partner-card__logo"><img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($partner->media?->alt_text ?: $partner->title) ?>"></div>
              <div class="partner-card__content"><h2><?= htmlspecialchars($partner->title) ?></h2><p><?= htmlspecialchars($partner->description ?? '') ?></p><span>Xem thông tin <i class="fa-solid fa-arrow-right"></i></span></div>
            </button>
          <?php endforeach; ?>
        </div>
        <button class="btn partners-slider__control" data-partners-next aria-label="Đối tác tiếp theo"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    <?php else: ?>
      <div class="empty partners-page__empty"><i class="fa-solid fa-building"></i><h2>Thông tin đang được cập nhật</h2><p>Danh sách doanh nghiệp đối tác sẽ sớm được công bố.</p></div>
    <?php endif; ?>
  </div></div>
</section>

<dialog class="partner-dialog" data-partner-dialog aria-labelledby="partner-dialog-title">
  <button class="btn partner-dialog__close" type="button" data-partner-close aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button>
  <img data-partner-dialog-image alt="">
  <h2 id="partner-dialog-title" data-partner-dialog-title></h2>
  <p data-partner-dialog-description></p>
  <a class="btn" data-variant="primary" data-partner-dialog-link target="_blank" rel="noopener">Truy cập website</a>
</dialog>
<script src="<?= url('public/js/pages/site/partners.js') ?>" type="module"></script>
<?php $layout->end(); ?>
