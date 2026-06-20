<link rel="stylesheet" href="<?= url('public/css/company_merge.css') ?>">

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Gộp Công ty</h2>
<p class="title-wrapper__description">Chọn công ty đích để gộp vào và chọn các trường dữ liệu muốn giữ lại.</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/companies') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i> Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<form id="merge-form" action="<?= url('admin/companies/' . $company->id . '/merge') ?>" method="POST">
  <?= csrf_field() ?>
  <input type="hidden" name="target_id" id="target_id" value="">

  <div id="mst-warning" class="company-merge-warning hidden">
    <div class="flex gap-3">
      <i class="fa-solid fa-triangle-exclamation mt-1"></i>
      <div>
        <h4 class="font-semibold">Cảnh báo: Mã số thuế khác nhau</h4>
        <p class="text-sm mt-1">Hai công ty này có mã số thuế khác nhau. Việc gộp chỉ nên thực hiện khi bạn chắc chắn
          đây là cùng 1 công ty (VD: công ty đổi MST, nhập sai MST).</p>
        <label class="flex gap-2 mt-2 text-sm">
          <input type="checkbox" id="confirm_diff_mst" class="tm-checkbox">
          Tôi xác nhận đây là cùng một công ty
        </label>
      </div>
    </div>
  </div>

  <div class="card p-0">
    <div class="company-merge-layout">
      <!-- Cột Trái: Nguồn -->
      <div class="company-merge-layout__source">
        <h3 class="font-semibold mb-2">Công ty nguồn (Sẽ bị xóa)</h3>
        <div class="mt-2">
          <span>#<?= $company->id ?></span> - <?= htmlspecialchars($company->name) ?>
        </div>
      </div>

      <!-- Cột Phải: Đích -->
      <div class="company-merge-layout__target">
        <div class="relative">
          <h3 class="font-semibold mb-2">Công ty đích (Giữ lại)</h3>
          <div class="w-full">
            <input type="text" id="target_search" class="field__input" placeholder="Tìm theo tên hoặc MST..."
              autocomplete="off">
            <div id="target_suggestions" class="company-merge-search__suggestions shadow shadow-sm hidden"></div>
          </div>
          <div id="target_selected_info" class="mt-2 hidden">
            <span id="target_id_display"></span> - <span id="target_name_display"></span>
            <button type="button" id="btn_clear_target" class="company-merge-search__clear" title="Chọn lại"><i
                class="fa-solid fa-xmark"></i></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lưới chọn trường dữ liệu khi gộp -->
    <div class="merge-grid">
      <div class="merge-row header">
        <div class="merge-cell">Trường dữ liệu</div>
        <div class="merge-cell merge-cell--center">Giá trị Nguồn</div>
        <div class="merge-cell merge-cell--center">Giá trị Đích</div>
      </div>

      <?php
      $fields = [
        'name' => 'Tên công ty',
        'tax_code' => 'Mã số thuế',
        'address' => 'Địa chỉ',
        'phone' => 'Số điện thoại',
        'email' => 'Email',
        'website' => 'Website'
      ];
      foreach ($fields as $key => $label):
        ?>
        <div class="merge-row field-row" data-field="<?= $key ?>">
          <div class="merge-cell merge-cell--label"><?= $label ?></div>

          <div class="merge-cell merge-cell--center">
            <label class="merge-choice-label">
              <input type="radio" name="_<?= $key ?>_choice" value="source" class="mr-2" checked>
              <span class="val-source"><?= htmlspecialchars($company->$key ?? '--') ?></span>
            </label>
          </div>

          <div class="merge-cell merge-cell--center">
            <label class="merge-choice-label merge-choice-label--disabled target-label">
              <input type="radio" name="_<?= $key ?>_choice" value="target" class="mr-2" disabled>
              <span class="val-target merge-choice-value--empty">Chưa chọn đích</span>
            </label>
            <input type="hidden" name="<?= $key ?>" id="<?= $key ?>_input"
              value="<?= htmlspecialchars($company->$key ?? '') ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="company-merge-impact">
      <h4 class="company-merge-impact__title">
        <i class="fa-solid fa-circle-info"></i> Ảnh hưởng khi gộp
      </h4>
      <ul class="company-merge-impact__list">
        <li><strong><?= $counts['students'] ?? 0 ?></strong> sinh viên đang tham chiếu công ty nguồn</li>
        <li><strong><?= $counts['referral_letters'] ?? 0 ?></strong> giấy giới thiệu đang tham chiếu công ty nguồn</li>
      </ul>
      <p class="company-merge-impact__note">Tất cả sẽ được chuyển sang tham chiếu đến công ty đích sau khi gộp thành
        công.</p>
    </div>
  </div>

  <div class="mt-2 flex justify-end">
    <button type="submit" id="btn_submit_merge" class="btn" data-variant="primary" data-size="lg" disabled>
      <i class="fa-solid fa-code-merge"></i> Thực hiện Gộp
    </button>
  </div>
</form>

<?php $layout->end() ?>

<script>
  window.API_BASE_URL = <?= json_encode(url('api/v1')) ?>;
  window.MERGE_SOURCE_ID = <?= json_encode($company->id) ?>;
  window.MERGE_PREFILL_QUERY = <?= json_encode(request()->query('q') ?? '') ?>;
</script>
<script type="module" src="<?= url('public/js/pages/company_merge.js') ?>"></script>
