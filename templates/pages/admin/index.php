<?php
$overview = $overview ?? ['updated_at' => null, 'kpis' => [], 'cards' => []];
$kpis = array_values(array_filter($overview['kpis'] ?? [], fn($item) => ($item['is_valid'] ?? false)));
$cards = array_values(array_filter($overview['cards'] ?? [], fn($item) => ($item['is_valid'] ?? false)));

$formatDate = static function (?string $value): string {
  if (!$value) {
    return '';
  }

  $time = strtotime($value);
  return $time ? date('d/m/Y', $time) : '';
};

$formatDateTime = static function (?string $value): string {
  if (!$value) {
    return '';
  }

  $time = strtotime($value);
  return $time ? date('H:i d/m/Y', $time) : '';
};
?>

<?php $layout->start('head') ?>
<link rel="stylesheet" href="<?= url('public/css/admin_overview_dashboard.css') ?>">
<?php $layout->end() ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Tổng quan</h2>
<?php $layout->end() ?>

<?php if (!empty($overview['updated_at'])): ?>
  <?php $layout->start('actions') ?>
  <span class="overview-updated">
    <i class="fa-regular fa-clock" aria-hidden="true"></i>
    Cập nhật lúc <?= htmlspecialchars($overview['updated_at']) ?>
  </span>
  <?php $layout->end() ?>
<?php endif; ?>

<?php $layout->start("content") ?>
<div class="overview-dashboard">
  <?php if (!empty($kpis)): ?>
    <div class="stats-grid">
      <?php foreach ($kpis as $kpi): ?>
        <?php
        $tag = !empty($kpi['url']) ? 'a' : 'div';
        $href = !empty($kpi['url']) ? ' href="' . url($kpi['url']) . '"' : '';
        ?>
        <<?= $tag ?><?= $href ?> class="card stats-card hover:bg-muted/50 transition-colors">
          <div class="card__header">
            <div class="flex justify-between gap-2">
              <span class="stats-card__label"><?= htmlspecialchars($kpi['title']) ?></span>
              <?php if (!empty($kpi['url'])): ?>
                <i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i>
              <?php elseif (!empty($kpi['icon'])): ?>
                <i class="<?= htmlspecialchars($kpi['icon']) ?>" aria-hidden="true"></i>
              <?php endif; ?>
            </div>
            <span class="stats-card__value"><?= number_format((int) $kpi['value']) ?></span>
          </div>
          <div class="card__footer">
            <?= htmlspecialchars($kpi['caption']) ?>
          </div>
        </<?= $tag ?>>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($cards)): ?>
    <div class="overview-grid">
      <?php foreach ($cards as $card): ?>
        <?php
        $sizeClass = '';
        foreach (explode(' ', (string) ($card['size'] ?? '')) as $size) {
          if ($size === 'span-2') {
            $sizeClass .= ' overview-card--span-2';
          }
          if ($size === 'row-span-2') {
            $sizeClass .= ' overview-card--row-span-2';
          }
        }
        ?>
        <section class="card overview-card<?= $sizeClass ?>">
          <div class="card__header">
            <div class="overview-card__title-row">
              <?php if (!empty($card['icon'])): ?>
                <i class="<?= htmlspecialchars($card['icon']) ?>" aria-hidden="true"></i>
              <?php endif; ?>
              <h3 class="card__title"><?= htmlspecialchars($card['title']) ?></h3>
            </div>
          </div>
          <div class="card__content">
            <?php if ($card['visual_type'] === 'attention'): ?>
              <ul class="overview-list">
                <?php foreach (($card['visual_data']['items'] ?? []) as $item): ?>
                  <li class="overview-list__item">
                    <a href="<?= url($item['url']) ?>" class="overview-list__link">
                      <span class="overview-list__main">
                        <span class="badge" data-variant="outline"><?= htmlspecialchars($item['domain']) ?></span>
                        <span title="<?= htmlspecialchars($item['label']) ?>"><?= htmlspecialchars($item['label']) ?></span>
                      </span>
                      <span class="overview-list__value"><?= number_format((int) $item['count']) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>

            <?php elseif ($card['visual_type'] === 'bars'): ?>
              <div class="stat-bars">
                <?php foreach (($card['visual_data']['items'] ?? []) as $index => $item): ?>
                  <div class="stat-bars__item" style="--stat-color: var(--chart-<?= ($index % 5) + 1 ?>); --stat-value: <?= (int) $item['percent'] ?>%;">
                    <div class="stat-bars__meta">
                      <span><?= htmlspecialchars($item['label']) ?></span>
                      <strong><?= number_format((int) $item['value']) ?></strong>
                    </div>
                    <div class="stat-bars__track" aria-hidden="true"><span></span></div>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if (!empty($card['visual_data']['detail_groups'])): ?>
                <div class="overview-mini-groups">
                  <?php foreach ($card['visual_data']['detail_groups'] as $group): ?>
                    <section class="overview-mini-group">
                      <h4><?= htmlspecialchars($group['title']) ?></h4>
                      <ul class="overview-mini-list">
                        <?php foreach (($group['items'] ?? []) as $detail): ?>
                          <li>
                            <a href="<?= url($detail['url']) ?>">
                              <span>
                                <strong title="<?= htmlspecialchars($detail['title']) ?>"><?= htmlspecialchars($detail['title']) ?></strong>
                              </span>
                              <?php if (!empty($detail['meta'])): ?>
                                <em><?= htmlspecialchars($detail['meta']) ?></em>
                              <?php endif; ?>
                            </a>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    </section>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            <?php elseif ($card['visual_type'] === 'progress'): ?>
              <div class="stat-progress">
                <?php foreach (($card['visual_data']['items'] ?? []) as $index => $item): ?>
                  <div class="stat-progress__item" style="--stat-color: var(--chart-<?= ($index % 5) + 1 ?>); --stat-value: <?= (int) $item['percent'] ?>%;">
                    <div class="stat-progress__meta">
                      <span><?= htmlspecialchars($item['label']) ?></span>
                      <strong><?= (int) $item['percent'] ?>%</strong>
                    </div>
                    <div class="stat-progress__track" aria-hidden="true"><span></span></div>
                    <p><?= number_format((int) $item['value']) ?> / <?= number_format((int) $item['total']) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>

            <?php elseif ($card['visual_type'] === 'quota'): ?>
              <?php $quotaItems = $card['visual_data']['items'] ?? []; ?>
              <div class="quota-progress">
                <div class="quota-progress__bar" aria-hidden="true">
                  <?php foreach ($quotaItems as $index => $item): ?>
                    <span style="--stat-color: var(--chart-<?= ($index % 5) + 1 ?>); --stat-value: <?= (int) $item['percent'] ?>%;"></span>
                  <?php endforeach; ?>
                </div>
                <div class="quota-progress__summary">
                  <span>Tổng quan</span>
                  <strong><?= number_format((int) ($card['visual_data']['total'] ?? 0)) ?></strong>
                </div>
                <ul class="quota-progress__legend">
                  <?php foreach ($quotaItems as $index => $item): ?>
                    <li style="--stat-color: var(--chart-<?= ($index % 5) + 1 ?>);">
                      <span class="quota-progress__dot" aria-hidden="true"></span>
                      <span class="quota-progress__label"><?= htmlspecialchars($item['label']) ?></span>
                      <strong><?= (int) $item['percent'] ?>%</strong>
                      <em><?= number_format((int) $item['value']) ?> / <?= number_format((int) $item['total']) ?></em>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>

            <?php elseif ($card['visual_type'] === 'timeline'): ?>
              <ul class="overview-list overview-list--compact">
                <?php foreach (($card['visual_data']['items'] ?? []) as $item): ?>
                  <li class="overview-list__item">
                    <a href="<?= url($item['url']) ?>" class="overview-list__link">
                      <span class="overview-list__main">
                        <span class="badge" data-variant="secondary"><?= htmlspecialchars($item['domain']) ?></span>
                        <span title="<?= htmlspecialchars($item['label']) ?>"><?= htmlspecialchars($item['label']) ?></span>
                      </span>
                      <span class="overview-list__side">
                        <span class="badge" data-variant="outline"><?= htmlspecialchars($item['badge']) ?></span>
                        <span><?= htmlspecialchars($formatDate($item['date'] ?? null)) ?></span>
                      </span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>

            <?php elseif ($card['visual_type'] === 'recent'): ?>
              <ul class="overview-list overview-list--compact">
                <?php foreach (($card['visual_data']['items'] ?? []) as $item): ?>
                  <li class="overview-list__item">
                    <a href="<?= url($item['url']) ?>" class="overview-list__link">
                      <span class="overview-list__main">
                        <span class="badge" data-variant="outline"><?= htmlspecialchars($item['domain']) ?></span>
                        <span title="<?= htmlspecialchars($item['label']) ?>"><?= htmlspecialchars($item['label']) ?></span>
                      </span>
                      <span class="overview-list__side"><?= htmlspecialchars($formatDateTime($item['happened_at'] ?? null)) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php $layout->end() ?>
