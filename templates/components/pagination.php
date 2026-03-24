<nav role="navigation" aria-label="pagination" class="pagination">
  <ul class="pagination-content">

    <?php
    $isPrevDisabled = $page->getCurrentPage() <= 1;
    $prevTag = $isPrevDisabled ? 'span' : 'a';
    $prevHref = $isPrevDisabled ? '' : 'href="' . $page->prevPageUrl() . '"';
    $prevDisabled = $isPrevDisabled ? 'data-disabled' : '';
    $prevAria = $isPrevDisabled ? 'aria-disabled="true"' : 'aria-label="Go to previous page"';
    ?>
    <li class="pagination-item">
      <<?= $prevTag ?> class="pagination-link pagination-prev" <?= $prevDisabled ?> <?= $prevHref ?> <?= $prevAria ?>>
        <i class="fa-solid fa-chevron-left"></i>
        <span>Trước</span>
      </<?= $prevTag ?>>
    </li>

    <?php foreach ($page->getElements() as $element): ?>

      <?php if (is_string($element)): // Nếu là chuỗi '...' ?>
        <li class="pagination-item pagination-item--ellipsis" aria-hidden="true">
          <span class="pagination-ellipsis">
            <i class="fa-solid fa-ellipsis"></i>
            <span class="sr-only">More pages</span>
          </span>
        </li>

      <?php else: // Nếu là số trang ?>
        <?php
        $isActive = $element == $page->getCurrentPage();
        $tag = $isActive ? 'span' : 'a';
        $active = $isActive ? 'data-active' : '';
        $href = $isActive ? '' : 'href="' . $page->url($element) . '"';
        $ariaCur = $isActive ? ' aria-current="page"' : 'aria-label="Go to page ' . $element . '"';
        ?>
        <li class="pagination-item">
          <<?= $tag ?> class="pagination-link" <?= $href ?>     <?= $active ?>     <?= $ariaCur ?>><?= $element ?></<?= $tag ?>>
        </li>
      <?php endif; ?>

    <?php endforeach; ?>

    <?php
    $isNextDisabled = $page->getCurrentPage() >= $page->getTotalPages();
    $nextTag = $isNextDisabled ? 'span' : 'a';
    $nextHref = $isNextDisabled ? '' : 'href="' . $page->nextPageUrl() . '"';
    $nextDisabled = $isNextDisabled ? 'data-disabled' : '';
    $nextAria = $isNextDisabled ? 'aria-disabled="true"' : 'aria-label="Go to next page"';
    ?>
    <li class="pagination-item">
      <<?= $nextTag ?> class="pagination-link pagination-next" <?= $nextDisabled ?> <?= $nextHref ?> <?= $nextAria ?>>
        <span>Sau</span>
        <i class="fa-solid fa-chevron-right"></i>
      </<?= $nextTag ?>>
    </li>

  </ul>
</nav>