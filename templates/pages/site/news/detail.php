<!-- Breadcrumbs -->
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
      include_once BASE_PATH . '/templates/components/breadcrumb.php';
      renderBreadcrumb([
        ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => '/', 'title' => 'Trang chủ'],
        ['url' => '/tin-tuc', 'title' => 'Tin tức & Sự kiện'],
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
      $payload['blocks'] = json_decode($news->content_json, true);
      $payload['meta'] = json_decode($news->settings_json, true);

      $category = $payload['meta']['category'] ?? 'Uncategorized';
      $publishDate = $news->published_at ?? $news->created_at;
      $readTime = $news->read_time ?? 5;

      $settings = $payload['meta']['settings'] ?? [];
      $showAuthor = $settings['show_author'] ?? false;
      $showDate = $settings['show_date'] ?? true;
      $showReadTime = $settings['show_read_time'] ?? false;
      $showViewCount = $settings['show_view_count'] ?? false;
      ?>

      <!-- Main Content Grid: 2/3 left + 1/3 right -->
      <div class="grid grid-cols-3 gap-8">

        <!-- LEFT COLUMN: Article Content -->
        <div class="col-span-2">

          <!-- Article Metadata Line -->
          <div class="flex items-center gap-3 mb-6 text-sm text-muted-foreground flex-wrap">
            <?php if (!empty($category)): ?>
              <span class="inline-block px-3 py-1 rounded-md bg-primary/10 text-primary font-medium">
                <?php echo htmlspecialchars($category); ?>
              </span>
            <?php endif; ?>

            <?php if ($showDate && !empty($publishDate)): ?>
              <span class="flex items-center gap-1">
                <i class="fa-regular fa-calendar text-xs"></i>
                <?php echo date('d/m/Y', strtotime($publishDate)); ?>
              </span>
            <?php endif; ?>

            <?php if ($showReadTime && $readTime > 0): ?>
              <span class="flex items-center gap-1">
                <i class="fa-regular fa-clock text-xs"></i>
                <?php echo $readTime; ?> phút đọc
              </span>
            <?php endif; ?>

            <!-- Social Share Buttons -->
            <div class="flex items-center gap-2 mb-8">
              <span class="text-sm text-muted-foreground mr-2">Chia sẻ:</span>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                target="_blank" class="btn" data-variant="outline" title="Chia sẻ trên Facebook">
                <i class="fa-brands fa-facebook-f"></i>
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                target="_blank" class="btn" data-variant="outline" title="Chia sẻ trên Twitter">
                <i class="fa-brands fa-twitter"></i>
              </a>
              <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                target="_blank" class="btn" data-variant="outline" title="Chia sẻ trên LinkedIn">
                <i class="fa-brands fa-linkedin-in"></i>
              </a>
              <button class="btn" data-variant="outline" title="Sao chép liên kết" onclick="copyToClipboard()">
                <i class="fa-solid fa-link"></i>
              </button>
            </div>

            <!-- Article Title -->
            <h1 class="text-5xl font-bold mb-8 text-foreground leading-tight">
              <?php echo htmlspecialchars($news->title); ?>
            </h1>

            <!-- Featured Image -->
            <?php if ($news->seo_image_url): ?>
              <div class="mb-8 rounded-lg overflow-hidden h-96">
                <img src="<?php echo htmlspecialchars($news->seo_image_url); ?>"
                  alt="<?php echo htmlspecialchars($news->title); ?>" class="w-full h-full object-cover">
              </div>
            <?php endif; ?>

            <!-- Article Body with Left Border Accent -->
            <div class="border-l-4 border-primary pl-8 py-4">
              <article class="article-body text-base leading-relaxed text-foreground space-y-6">
                <?php echo \App\Editor\BlockRenderer::fromArray($payload); ?>
              </article>
            </div>

          </div>

          <!-- RIGHT COLUMN: Sidebar -->
          <div class="col-span-1">

            <!-- Sidebar Box: Table of Contents -->
            <div class="bg-muted p-6 rounded-lg mb-8 border border-border">
              <h3 class="font-bold text-lg mb-4 text-foreground">Nội dung bài viết</h3>
              <nav class="sidebar-toc space-y-2">
                <ul class="flex flex-col gap-2">
                  <?php
                  // Generate TOC from blocks if available
                  if (!empty($payload['blocks'])) {
                    foreach ($payload['blocks'] as $index => $block) {
                      if ($block['type'] === 'heading' || strpos($block['type'] ?? '', 'heading') !== false):
                        $level = $block['level'] ?? 2;
                        $text = $block['text'] ?? 'Heading ' . ($index + 1);
                        $id = 'heading-' . $index;
                        $indent = ($level - 1) * 0.5;
                        ?>
                        <li style="margin-left: <?php echo $indent; ?>rem;">
                          <a href="#<?php echo $id; ?>"
                            class="text-sm text-muted-foreground hover:text-primary transition-colors">
                            <?php echo htmlspecialchars($text); ?>
                          </a>
                        </li>
                        <?php
                      endif;
                    }
                  }
                  ?>
                </ul>
              </nav>
            </div>

            <!-- Sidebar Box: Author Information -->
            <?php if (false && $news->author): ?>
              <div class="border border-border rounded-lg p-6 mb-8">
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0">
                    <div class="avatar" data-size="lg">
                      <div class="avatar__image">
                        <img
                          src="<?php echo htmlspecialchars($news->author->image_url ?? 'https://via.placeholder.com/80'); ?>"
                          alt="<?php echo htmlspecialchars($news->author->name ?? 'Author'); ?>">
                      </div>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-foreground">
                      <?php echo htmlspecialchars($news->author->name ?? 'Tác giả'); ?>
                    </h4>
                    <?php if (!empty($news->author->title) || !empty($news->author->email)): ?>
                      <p class="text-sm text-muted-foreground mt-1">
                        <?php echo htmlspecialchars($news->author->title ?? $news->author->email ?? ''); ?>
                      </p>
                    <?php endif; ?>
                    <?php if (!empty($news->author->bio)): ?>
                      <p class="text-sm text-muted-foreground mt-2 line-clamp-3">
                        <?php echo htmlspecialchars($news->author->bio); ?>
                      </p>
                    <?php endif; ?>
                    <a href="/tac-gia/<?php echo htmlspecialchars($news->author->id ?? ''); ?>"
                      class="text-sm text-primary hover:text-primary/80 font-medium mt-3 inline-block">
                      Xem thêm bài viết →
                    </a>
                  </div>
                </div>
              </div>
            <?php endif; ?>

          </div>

        </div>
      </div>
    </div>
</section>

<script>
  function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
      alert('Đã sao chép liên kết!');
    }).catch(err => {
      console.error('Failed to copy: ', err);
    });
  }
</script>