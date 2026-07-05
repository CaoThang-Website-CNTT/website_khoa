<?php
$layout->start('content');
?>
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
      include_once BASE_PATH . '/templates/components/breadcrumb.php';
      renderBreadcrumb([
        ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
        ['url' => url('/viec-lam/doanh-nghiep'), 'title' => 'Doanh nghiệp đối tác'],
      ]);
      ?>
    </div>
  </div>
</section>

<section class="partners-page py-12">
  <div class="container"><div class="container-wrapper">
    <header class="partners-page__header">
      <span class="badge" data-variant="primary">Kết nối doanh nghiệp</span>
      <h1>Doanh nghiệp đối tác</h1>
      <p>Các đơn vị đồng hành cùng Khoa Công nghệ thông tin trong đào tạo, thực tập và cơ hội nghề nghiệp.</p>
    </header>

    <?php if ($partners): ?>
      <?php $partnerPayload = json_encode($partners, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
      <section class="partners-directory gap-10" data-partners-directory>
        <div class="partners-grid gap-4">
          <?php foreach ($partners as $index => $partner): ?>
            <?php $name = $partner['name'] ?? ''; $image = $partner['image'] ?? []; ?>
            <button class="card partner-grid-card<?= $index === 0 ? ' partner-grid-card--active' : '' ?>" type="button"
              data-partner-card data-partner-index="<?= (int) $index ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
              <span class="partner-grid-card__logo">
                <img src="<?= htmlspecialchars($image['src'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($image['alt'] ?? $name, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
              </span>
              <span class="partner-grid-card__name font-semibold text-center"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
            </button>
          <?php endforeach; ?>
        </div>

        <article class="partner-detail" data-partner-detail tabindex="-1">
          <div class="partner-detail__media flex items-center justify-center">
            <img class="w-full object-contain" data-partner-detail-image src="<?= htmlspecialchars($partners[0]['image']['src'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($partners[0]['image']['alt'] ?? $partners[0]['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="partner-detail__content flex flex-col items-start justify-center">
            <p class="partner-detail__eyebrow text-sm font-semibold mb-2">Hồ sơ đối tác</p>
            <h2 data-partner-detail-title><?= htmlspecialchars($partners[0]['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
            <p data-partner-detail-description><?= htmlspecialchars($partners[0]['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <a class="btn partner-detail__cta mt-6" data-size="lg" data-variant="primary" data-partner-detail-link href="<?= htmlspecialchars($partners[0]['url'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Truy cập website <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
          </div>
        </article>

        <script type="application/json" data-partners-json><?= $partnerPayload ?></script>
      </section>
    <?php else: ?>
      <div class="empty partners-page__empty"><i class="fa-solid fa-building"></i><h2>Thông tin đang được cập nhật</h2><p>Danh sách doanh nghiệp đối tác sẽ sớm được công bố.</p></div>
    <?php endif; ?>
  </div></div>
</section>
<script src="<?= url('public/js/pages/site/partners.js') ?>" type="module"></script>
<?php $layout->end(); ?>
