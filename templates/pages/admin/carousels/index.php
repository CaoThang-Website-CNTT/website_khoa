<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">
        Carousel
        <span class="badge" data-variant="primary">
          <?= count($data ?? []); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/carousels/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
<div class="grid grid-cols-2 gap-2">
  <?php if (!empty($data)): ?>
    <?php foreach ($data as $index => $carousel): ?>
      <div class="carousel-card card shadow">
        <?= renderCarousel($carousel->id, $carousel->slides); ?>
        <div class="card__header"></div>
        <div class="card__content">
          <div class="card__title">
            <?= $carousel->name ?>
          </div>
          <div class="card__description">
            <?= $carousel->slug ?>
          </div>
        </div>
        <div class="card__footer">
          <div class="flex justify-between">
            <?= $carousel->updated_at ?>
            <?php if ($carousel->is_active): ?>
              <span class="badge" data-variant="primary">Đang hoạt động</span>
            <?php else: ?>
              <span class="badge" data-variant="desctructive">Ngừng hoạt động</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php
function renderCarousel(string $id, array $carouselSlides): void
{
  if (empty($carouselSlides)) {
    return;
  }
  ?>
  <div class="carousel" id=<?= "carousel-" . $id ?>>
    <a href="<?= url("admin/carousels/" . $id) ?>" class="carousel__inner" id=<?= "carouselInner-" . $id ?>>
      <?php foreach ($carouselSlides as $index => $slide): ?>
        <?php
        // Bỏ qua các slide không được kích hoạt
        if (!$slide->isActive())
          continue;
        ?>
        <div class="carousel__item">

          <div class="carousel__content">
            <div class="carousel__title">
              <?= $slide->title ?>
            </div>
            <p class="carousel__description">
              <?= $slide->description ?>
            </p>
          </div>
          <div class="carousel__image-wrapper">
            <img src="<?= htmlspecialchars($slide->image_path) ?>"
              alt="<?= htmlspecialchars($slide->image_alt ?: $slide->title) ?>" class="image carousel__image">
          </div>
        </div>
      <?php endforeach; ?>
    </a>

    <button class="carousel__control carousel__control--prev">
      <i class="fa-solid fa-angle-left"></i>
    </button>
    <button class="carousel__control carousel__control--next">
      <i class="fa-solid fa-angle-right"></i>
    </button>

    <div class="carousel__indicators">
    </div>
  </div>
  <?php
}
?>