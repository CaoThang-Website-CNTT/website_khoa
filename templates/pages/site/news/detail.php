<!-- Breadcrumbs -->
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
      include_once BASE_PATH . '/templates/components/breadcrumb.php';
      renderBreadcrumb([
        ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
        ['url' => url('/tin-tuc'), 'title' => 'Tin tức & Sự kiện'],
        ['url' => url('tin-tuc/' . $news->slug), 'title' => $news->title],
      ]);
      ?>
    </div>
  </div>
</section>

<!-- News Detail -->
<section class="py-12">
  <div class="container">
    <div class="container-wrapper">
      <?php
      $categories = $news->categories;
      $publishDate = $news->published_at ?? $news->created_at;

      $showAuthor = $newsSettings['show_author'] ?? false;
      $showDate = $newsSettings['show_date'] ?? true;
      $showViewCount = $newsSettings['show_view_count'] ?? false;
      ?>

      <div class="relative grid <?= $detail->hasToc() ? 'grid-cols-3' : 'grid-cols-1' ?> gap-8">
        <!-- LEFT: MAIN -->
        <div class="news-detail col-span-2">
          <!-- News Header -->
          <div class="news-detail-header">
            <!-- News Category -->
            <?php if (!empty($categories)): ?>
              <span class="badge" data-variant="primary">
                <?= htmlspecialchars($categories[0]->name) ?>
              </span>
            <?php endif; ?>

            <!-- News Title -->
            <h1 class="news-detail-header__title">
              <?= htmlspecialchars($news->title); ?>
            </h1>

            <!-- Metadata Line -->
            <div class="news-detail-header__meta">
              <?php if ($showAuthor && $news->author): ?>
                <span class="news-detail-header__meta-item">
                  <i class="fa-regular fa-user"></i>
                  <?= htmlspecialchars($news->author->email); ?>
                </span>
              <?php endif; ?>

              <?php if ($showDate && !empty($publishDate)): ?>
                <span class="news-detail-header__meta-item">
                  <i class="fa-regular fa-calendar"></i>
                  <?= date('d/m/Y', strtotime($publishDate)); ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Social Share -->
            <div class="news-detail-header__social">
              <span class="text-sm text-muted-foreground mr-2">Chia sẻ:</span>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']); ?>"
                target="_blank" class="btn" data-size="lg" data-variant="outline" title="Chia sẻ trên Facebook">
                <i class="fa-brands fa-facebook-f"></i>
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']); ?>" target="_blank"
                class="btn" data-size="lg" data-variant="outline" title="Chia sẻ trên Twitter">
                <i class="fa-brands fa-twitter"></i>
              </a>
              <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($_SERVER['REQUEST_URI']); ?>"
                target="_blank" class="btn" data-size="lg" data-variant="outline" title="Chia sẻ trên LinkedIn">
                <i class="fa-brands fa-linkedin-in"></i>
              </a>
              <button class="btn" data-size="lg" data-variant="outline" title="Sao chép liên kết"
                onclick="copyToClipboard()">
                <i class="fa-solid fa-link"></i>
              </button>
            </div>
          </div>

          <hr class="separator">

          <!-- Featured Image -->

          <!-- News Body -->
          <article class="news-detail-content be-content">
            <?= $detail->html; ?>
          </article>

          <hr class="separator">

          <!-- News Footer -->
          <div>
            <!-- News Category Tags -->
            <?php if (!empty($categories)): ?>
              <div class="news-detail-footer__tags">
                <div>
                  <i class="fa-solid fa-tag"></i>
                  <span class="sr-only">Danh mục:</span>
                </div>
                <?php foreach ($categories as $category): ?>
                  <a href="/tin-tuc?category=<?= urlencode($category->slug) ?>" class="badge news-detail-footer__tag"
                    data-variant="outline">
                    <?= htmlspecialchars($category->name) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- News Author -->
        </div>
        <?php if ($detail->hasToc()): ?>
          <!-- RIGHT: SIDEBAR -->
          <div class="news-sidebar col-span-1">
            <div class="news-sidebar-wrapper">
              <!-- News TOC -->
              <div class="news-toc card">
                <div class="card__header">
                  <h3 class="card__title">Nội dung bài viết</h3>
                  <hr class="separator">
                </div>
                <div class="card__content">
                  <ul class="news-toc-list">
                    <?php foreach ($detail->entries() as $entry): ?>
                      <li class="new-toc-list__item" style="padding-left: <?= ($entry->level - $detail->baseLevel()) ?>rem">
                        <?php if ($entry->anchorId !== ''): ?>
                          <a class="link-hover--standout" href="#<?= htmlspecialchars($entry->anchorId) ?>">
                            <?= htmlspecialchars($entry->plainText) ?>
                          </a>
                        <?php else: ?>
                          <span>
                            <?= htmlspecialchars($entry->plainText) ?>
                          </span>
                        <?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php $layout->start("scripts") ?>
<script src="<?= url('public/js/pages/site/news/detail.js') ?>" type="module"></script>
<?php $layout->end() ?>
