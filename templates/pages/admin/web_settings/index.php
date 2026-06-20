<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Cài đặt hệ thống</h2>
<?php $layout->end() ?>

<?php
$initialTab = $_GET['tab'] ?? (!empty($data) ? $data[0]['name'] : '');
$tabs = [];
$tabPanels = [];

if (!empty($data)) {
  foreach ($data as $index => $group) {
    $groupKey = $group['name'];
    $tabs[] = [
      'key' => $groupKey,
      'label' => htmlspecialchars($group['group_label'] ?? $groupKey),
    ];

    ob_start(); ?>

    <div class="card shadow rounded-md">
      <form action="<?= url('admin/web_settings/' . $groupKey . '/batch-update') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="card__content">
          <div class="field-group">
            <?php if (!empty($group['settings'])): ?>
              <?php foreach ($group['settings'] as $setting): ?>
                <?php
                $inputId = 'setting_' . $setting['id'];
                $inputName = 'settings[' . $setting['id'] . '][value]';
                $inputClass = 'field__input';
                $val = $setting['value'] ?? $setting['default_value'] ?? '';
                $isDisabled = !empty($setting['is_locked']);
                $disabledAttr = $isDisabled ? 'disabled readonly' : '';
                ?>

                <div>
                  <div class="field" data-orientation="horizontal">
                    <div class="field__content">
                      <label class="field__label" for="<?= $inputId ?>">
                        <?= htmlspecialchars($setting['label']) ?>
                        <?php if ($isDisabled): ?>
                          <span class="badge" data-variant="secondary">Hệ thống</span>
                        <?php endif; ?>
                      </label>

                      <?php if (!empty($setting['description'])): ?>
                        <p class="field__description"><?= htmlspecialchars($setting['description']) ?></p>
                      <?php endif; ?>
                    </div>

                    <?php switch ($setting['type']):
                      case 'bool': ?>
                        <button class="switch" type="button" role="switch">
                          <span class="switch__thumb"></span>
                        </button>
                        <?php break;

                      case 'text':
                      case 'json': ?>
                        <textarea class="<?= $inputClass ?>" id="<?= $inputId ?>" name="<?= $inputName ?>" rows="3"
                          class="form-control" <?= $disabledAttr ?>><?= htmlspecialchars((string) $val) ?></textarea>
                        <?php break;

                      case 'int':
                      case 'float': ?>
                        <input class="<?= $inputClass ?>" type="number" id="<?= $inputId ?>" name="<?= $inputName ?>"
                          value="<?= htmlspecialchars((string) $val) ?>" class="form-control" <?= $disabledAttr ?>>
                        <?php break;

                      case 'email':
                      case 'url': ?>
                        <input class="<?= $inputClass ?>" type="<?= $setting['type'] ?>" id="<?= $inputId ?>"
                          name="<?= $inputName ?>" value="<?= htmlspecialchars((string) $val) ?>" class="form-control"
                          <?= $disabledAttr ?>>
                        <?php break;

                      default: ?>
                        <input class="<?= $inputClass ?>" type="text" id="<?= $inputId ?>" name="<?= $inputName ?>"
                          value="<?= htmlspecialchars((string) $val) ?>" class="form-control" <?= $disabledAttr ?>>
                        <?php break;
                    endswitch; ?>

                    <?php if (!empty($setting['default_value']) && $val != $setting['default_value']): ?>
                      <div>
                        Mặc định: <i><?= htmlspecialchars((string) $setting['default_value']) ?></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Nhóm này chưa có cài đặt nào.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="card__footer">
          <div class="flex justify-end">
            <?php if (!empty($group['settings'])): ?>
              <button type="submit" class="btn" data-variant="primary" data-size="lg">
                <i class="fa-solid fa-save"></i>
                Lưu cài đặt
              </button>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>
    <?php
    $tabPanels[$groupKey] = ob_get_clean();
  }
} else {
  $tabs[] = ['key' => 'empty', 'label' => 'Không có dữ liệu'];
  ob_start(); ?>
  <div class="card shadow rounded-md">
    <p>Chưa có cài đặt nào trong hệ thống.</p>
  </div>
  <?php $tabPanels['empty'] = ob_get_clean();
}

$tabsId = 'tab';
$activeTab = $initialTab;
?>

<div class="tabs" data-tabs data-tabs-id="<?= htmlspecialchars($tabsId) ?>"
  data-tabs-panel-active="<?= htmlspecialchars($activeTab) ?>">
  <div class="tabs__list" role="tablist">
    <?php foreach ($tabs as $tab): ?>
      <?php $isActive = $tab['key'] === $activeTab; ?>
      <a href="#<?= htmlspecialchars($tabsId) ?>:<?= htmlspecialchars($tab['key']) ?>" role="tab"
        aria-selected="<?= $isActive ? 'true' : 'false' ?>"
        aria-controls="<?= htmlspecialchars($tabsId) ?>-panel-<?= htmlspecialchars($tab['key']) ?>"
        data-tabs-trigger="<?= htmlspecialchars($tab['key']) ?>" data-tabs-trigger-state="<?= $isActive ? 'active' : 'idle' ?>"
        tabindex="<?= $isActive ? '0' : '-1' ?>" class="tabs__trigger">
        <?= htmlspecialchars($tab['label']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php foreach ($tabs as $tab): ?>
    <?php $isActive = $tab['key'] === $activeTab; ?>
    <div id="<?= htmlspecialchars($tabsId) ?>-panel-<?= htmlspecialchars($tab['key']) ?>" role="tabpanel"
      data-tabs-panel="<?= htmlspecialchars($tab['key']) ?>" data-tabs-panel-state="<?= $isActive ? 'active' : 'idle' ?>"
      class="tabs__panel">
      <?= $tabPanels[$tab['key']] ?? '' ?>
    </div>
  <?php endforeach; ?>
</div>
