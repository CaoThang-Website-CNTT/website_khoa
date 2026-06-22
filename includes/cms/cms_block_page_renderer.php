<?php

namespace App\Cms;

use App\Editor\RichTextRenderer;

final class CmsBlockPageRenderer
{
  public function __construct(private array $context = [])
  {
  }

  public function render(array $document): string
  {
    $html = '';

    foreach (($document['blocks'] ?? []) as $block) {
      if (!is_array($block)) {
        continue;
      }

      $blockHtml = $this->renderBlock($block);
      if ($blockHtml === '') {
        continue;
      }

      $id = $this->e($block['id'] ?? '');
      $type = $this->e($block['type'] ?? '');
      $html .= "<div class=\"cms-block\" data-cms-block-id=\"{$id}\" data-cms-block-type=\"{$type}\">{$blockHtml}</div>";
    }

    return $html;
  }

  private function renderBlock(array $block): string
  {
    $type = (string) ($block['type'] ?? '');
    $data = is_array($block['data'] ?? null) ? $block['data'] : [];
    $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];

    return match ($type) {
      'cms/heading' => $this->renderHeading($data, $meta),
      'cms/paragraph' => $this->renderParagraph($data, $meta),
      'cms/image' => $this->renderImage($meta),
      'cms/button' => $this->renderButton($meta),
      'cms/button_group' => $this->renderButtonGroup($meta),
      'cms/spacer' => $this->renderSpacer($meta),
      'cms/columns' => $this->renderColumns($meta),
      'cms/card_grid' => $this->renderCardGrid($meta),
      'cms/stat_grid' => $this->renderStatGrid($meta),
      'cms/quote' => $this->renderQuote($data, $meta),
      'cms/carousel' => $this->renderCarousel(),
      'cms/newsfeed' => $this->renderNewsfeed($meta),
      default => '',
    };
  }

  private function renderHeading(array $data, array $meta): string
  {
    $level = (int) ($meta['level'] ?? 2);
    $level = min(3, max(1, $level));
    $align = $this->choice($meta['align'] ?? 'left', ['left', 'center', 'right'], 'left');
    $variant = $this->choice($meta['variant'] ?? 'section', ['display', 'section', 'eyebrow'], 'section');
    $content = RichTextRenderer::render($data['rich_text'] ?? []);
    return "<section class=\"cms-block-section cms-text-align-{$align}\"><h{$level} class=\"cms-heading cms-heading--{$variant}\">{$content}</h{$level}></section>";
  }

  private function renderParagraph(array $data, array $meta): string
  {
    $align = $this->choice($meta['align'] ?? 'left', ['left', 'center', 'right'], 'left');
    $variant = $this->choice($meta['variant'] ?? 'body', ['body', 'lead', 'muted'], 'body');
    $content = RichTextRenderer::render($data['rich_text'] ?? []);
    return "<section class=\"cms-block-section cms-text-align-{$align}\"><p class=\"cms-paragraph cms-paragraph--{$variant}\">{$content}</p></section>";
  }

  private function renderImage(array $meta): string
  {
    $url = $this->asset($meta['url'] ?? '');
    if ($url === '') {
      return '';
    }
    $alt = $this->e($meta['alt'] ?? '');
    $ratio = $this->choice($meta['ratio'] ?? 'wide', ['wide', 'square', 'banner'], 'wide');
    $variant = $this->choice($meta['variant'] ?? 'rounded', ['rounded', 'plain'], 'rounded');
    $caption = trim((string) ($meta['caption'] ?? ''));
    return '<section class="cms-block-section"><figure class="cms-image cms-image--' . $ratio . ' cms-image--' . $variant . '">'
      . '<img src="' . $this->e($url) . '" alt="' . $alt . '" loading="lazy">'
      . ($caption !== '' ? '<figcaption>' . $this->e($caption) . '</figcaption>' : '')
      . '</figure></section>';
  }

  private function renderButton(array $meta): string
  {
    $label = trim((string) ($meta['label'] ?? ''));
    if ($label === '') {
      return '';
    }
    return '<section class="cms-block-section cms-text-align-center">' . $this->button($meta) . '</section>';
  }

  private function renderButtonGroup(array $meta): string
  {
    $buttons = is_array($meta['buttons'] ?? null) ? $meta['buttons'] : [];
    $html = '';
    foreach ($buttons as $button) {
      if (is_array($button)) {
        $html .= $this->button($button);
      }
    }
    return $html === '' ? '' : '<section class="cms-block-section"><div class="cms-button-group">' . $html . '</div></section>';
  }

  private function renderSpacer(array $meta): string
  {
    $size = $this->choice($meta['size'] ?? 'md', ['sm', 'md', 'lg'], 'md');
    return "<div class=\"cms-spacer cms-spacer--{$size}\" aria-hidden=\"true\"></div>";
  }

  private function renderColumns(array $meta): string
  {
    $columns = is_array($meta['columns'] ?? null) ? $meta['columns'] : [];
    if (empty($columns)) {
      return '';
    }
    $html = '';
    foreach ($columns as $column) {
      if (!is_array($column)) {
        continue;
      }
      $html .= '<article class="cms-column-card"><h3>' . $this->e($column['title'] ?? '') . '</h3><p>' . nl2br($this->e($column['body'] ?? '')) . '</p></article>';
    }
    return '<section class="cms-block-section"><div class="cms-columns">' . $html . '</div></section>';
  }

  private function renderCardGrid(array $meta): string
  {
    $items = is_array($meta['items'] ?? null) ? $meta['items'] : [];
    if (empty($items)) {
      return '';
    }
    $columns = (int) ($meta['columns'] ?? 3);
    $columns = min(4, max(2, $columns));
    $html = '';
    foreach ($items as $item) {
      if (!is_array($item)) {
        continue;
      }
      $html .= '<article class="cms-card"><h3>' . $this->e($item['title'] ?? '') . '</h3><p>' . $this->e($item['description'] ?? '') . '</p></article>';
    }
    return "<section class=\"cms-block-section\"><div class=\"cms-card-grid cms-grid-cols-{$columns}\">{$html}</div></section>";
  }

  private function renderStatGrid(array $meta): string
  {
    $items = is_array($meta['items'] ?? null) ? $meta['items'] : [];
    if (empty($items)) {
      return '';
    }
    $columns = (int) ($meta['columns'] ?? 4);
    $columns = min(4, max(2, $columns));
    $html = '';
    foreach ($items as $item) {
      if (!is_array($item)) {
        continue;
      }
      $html .= '<article class="cms-stat"><strong>' . $this->e($item['number'] ?? '') . '</strong><h3>' . $this->e($item['label'] ?? '') . '</h3><p>' . $this->e($item['description'] ?? '') . '</p></article>';
    }
    return "<section class=\"cms-block-section\"><div class=\"cms-stat-grid cms-grid-cols-{$columns}\">{$html}</div></section>";
  }

  private function renderQuote(array $data, array $meta): string
  {
    $content = RichTextRenderer::render($data['rich_text'] ?? []);
    $citation = trim((string) ($meta['citation'] ?? ''));
    return '<section class="cms-block-section"><blockquote class="cms-quote"><p>' . $content . '</p>' . ($citation !== '' ? '<cite>' . $this->e($citation) . '</cite>' : '') . '</blockquote></section>';
  }

  private function renderCarousel(): string
  {
    $carouselSlides = $this->context['carouselSlides'] ?? [];
    if (empty($carouselSlides)) {
      return '';
    }
    ob_start();
    ?>
    <section class="relative" id="hero-section">
      <div class="container">
        <div class="carousel" id="landingCarousel">
          <div class="carousel__inner" id="carouselInner">
            <?php foreach ($carouselSlides as $slide): ?>
              <?php if (!$slide->isActive()) continue; ?>
              <div class="carousel__item <?= $slide->isCustom() ? 'carousel__item--custom' : 'carousel__item--standard' ?> flex justify-between items-center">
                <?php if ($slide->isCustom()): ?>
                  <?= $slide->custom_html ?>
                <?php else: ?>
                  <div class="carousel__content flex flex-col">
                    <h2 class="carousel__title font-normal"><?= $this->e($slide->title) ?><?php if (!empty($slide->title_highlight)): ?><span><?= $this->e($slide->title_highlight) ?></span><?php endif; ?></h2>
                    <?php if (!empty($slide->description)): ?><p class="carousel__description"><?= nl2br($this->e($slide->description)) ?></p><?php endif; ?>
                    <?php if ($slide->hasCta()): ?><div class="carousel__cta"><a href="<?= $this->e(url($slide->cta_url)) ?>" data-variant="<?= $this->e($slide->cta_variant) ?>" class="btn px-8 py-2 rounded-3xl bouncy-btn"><?= $this->e($slide->cta_label) ?></a></div><?php endif; ?>
                  </div>
                  <div class="image-wrapper carousel__image-wrapper rounded-3xl">
                    <img src="<?= $this->e(url('public/media/' . $slide->media->file_path)) ?>" alt="<?= $this->e($slide->media->alt_text ?: $slide->title) ?>" class="image carousel__image">
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel__control carousel__control--prev"><i class="fa-solid fa-angle-left"></i></button>
          <button class="carousel__control carousel__control--next"><i class="fa-solid fa-angle-right"></i></button>
          <div class="carousel__indicators"></div>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
  }

  private function renderNewsfeed(array $meta): string
  {
    include_once BASE_PATH . '/templates/components/news_card.php';
    $featuredNews = array_slice($this->context['featuredNews'] ?? [], 0, (int) ($meta['featured_count'] ?? 4));
    $latestNewsItems = array_slice($this->context['latestNewsItems'] ?? [], 0, (int) ($meta['latest_count'] ?? 3));

    ob_start();
    ?>
    <section class="relative container py-16" id="newsfeed-section">
      <div class="container-wrapper">
        <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <h2 class="section__title">Tin tức &amp; Sự kiện</h2>
          <p class="section__sub-title">Cập nhật những tin tức mới nhất về hoạt động của khoa.</p>
        </div>
        <div class="newsfeed__content-wrapper flex flex-col gap-16">
          <div class="flex flex-col gap-3 md:gap-6">
            <?php if (!empty($featuredNews)): $featured = $featuredNews[0]; ?>
              <?php renderLandingNewsCard($featured, ['variant' => 'featured', 'category_label' => 'Nổi bật', 'show_read_more' => true, 'class' => 'super-landing-featured-news landing-featured-news relative overflow-hidden rounded-3xl']); ?>
            <?php endif; ?>
            <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
              <?php for ($i = 1, $count = count($featuredNews); $i < 4 && $i < $count; $i++): $news = $featuredNews[$i]; ?>
                <?php renderLandingNewsCard($news, ['variant' => 'secondary', 'category_label' => $news->categories[0]->name ?? 'Tin tức', 'class' => 'landing-sub-featured-news flex-1']); ?>
              <?php endfor; ?>
            </div>
          </div>
          <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
            <?php foreach ($latestNewsItems as $news): ?>
              <?php renderLandingNewsCard($news, ['variant' => 'standard', 'category_label' => $news->categories[0]->name ?? 'Tin tức']); ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
  }

  private function button(array $meta): string
  {
    $label = trim((string) ($meta['label'] ?? ''));
    if ($label === '') {
      return '';
    }
    $url = $this->safeUrl($meta['url'] ?? '#');
    $variant = $this->choice($meta['variant'] ?? 'primary', ['primary', 'outline', 'secondary'], 'primary');
    return '<a class="btn cms-button" data-variant="' . $this->e($variant) . '" href="' . $this->e($url) . '">' . $this->e($label) . '</a>';
  }

  private function asset(mixed $value): string
  {
    $src = trim((string) $value);
    if ($src === '') {
      return '';
    }
    if (preg_match('/^(https?:)?\/\//', $src) || str_starts_with($src, 'data:') || str_starts_with($src, '/')) {
      return $src;
    }
    $normalized = ltrim(preg_replace('/^\.\//', '', $src), '/');
    if (str_starts_with($normalized, 'public/')) {
      return url($normalized);
    }
    if (str_starts_with($normalized, 'media/')) {
      return url('public/' . $normalized);
    }
    return url('public/media/' . $normalized);
  }

  private function safeUrl(mixed $value): string
  {
    $url = trim((string) $value);
    if ($url === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) && !preg_match('#^(https?://|mailto:)#i', $url)) {
      return '#';
    }

    return preg_match('#^(https?://|mailto:|/|#|[a-zA-Z0-9_-])#', $url) ? $url : '#';
  }

  private function choice(mixed $value, array $allowed, string $fallback): string
  {
    return in_array($value, $allowed, true) ? (string) $value : $fallback;
  }

  private function e(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}
