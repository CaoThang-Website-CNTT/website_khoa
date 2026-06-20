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

      <div class="news-detail-layout">
        <!-- TOC -->
        <?php if ($detail->hasToc()): ?>
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
        <?php endif; ?>

        <!-- MAIN CONTENT -->
        <div class="news-detail">
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

        </div>


        <!-- Related Articles -->
        <?php if (!empty($relatedNews)): ?>
          <div class="news-detail-related card" id="related-articles-block" data-post-id="<?= $news->id ?>" data-api-url="<?= url('api/v1/posts/' . $news->id . '/related') ?>" data-base-url="<?= url('tin-tuc/') ?>">
            <h3 class="news-detail-related__title">Bài viết liên quan</h3>
            <div class="news-detail-related__list" id="related-articles-list">
              <?php foreach ($relatedNews as $index => $related): ?>
                <?php
                $relatedCategory = !empty($related->categories) ? $related->categories[0]->name : 'Tin tức';
                $relatedDate = $related->published_at ?? $related->created_at;
                $relatedArray = $related->toArray();
                ?>
                <article class="news-detail-related__item">
                  <a href="<?= url('tin-tuc/' . $related->slug) ?>" class="news-detail-related__link">
                    <div class="news-detail-related__thumb">
                      <img class="news-detail-related__image" src="<?= htmlspecialchars($relatedArray['image_url']) ?>"
                        alt="<?= htmlspecialchars($related->title) ?>" loading="lazy">
                    </div>
                    <div class="news-detail-related__content">
                      <span class="badge" data-variant="outline"><?= htmlspecialchars($relatedCategory) ?></span>
                      <h4 class="news-detail-related__heading">
                        <?= htmlspecialchars($related->title) ?>
                      </h4>
                      <time class="news-detail-related__date" datetime="<?= date('Y-m-d', strtotime($relatedDate)) ?>">
                        <?= date('d/m/Y', strtotime($relatedDate)) ?>
                      </time>
                    </div>
                  </a>
                </article>
                <?php if ($index < count($relatedNews) - 1): ?>
                  <hr class="separator" aria-hidden="true">
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <?php if ($hasMoreRelated): ?>
              <button class="news-detail-related__more btn" id="load-more-related-btn" data-variant="outline">
                <span>Xem thêm</span>
                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
              </button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php $layout->start("scripts") ?>
<script src="<?= url('public/js/pages/site/news/detail.js') ?>" type="module"></script>
<?php $layout->end() ?>