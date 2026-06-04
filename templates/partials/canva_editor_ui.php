<?php
$old_input = request()->session()->getOldInputs() ?? [];
$oldEditorData = $old_input['editor_data'] ?? null;
$initialPayload = null;

if ($oldEditorData) {
  try {
    $initialPayload = json_decode($oldEditorData, true);
  } catch (\Exception $e) {
  }
} else if (isset($post)) {
  // Nếu là Edit, map dữ liệu của bài viết sang payload dạng JSON
  $initialPayload = [
    'meta' => json_decode($post->settings_json ?? '{}', true),
    'blocks' => json_decode($post->content_json ?? '[]', true)
  ];
}

$oldTitle = $initialPayload['meta']['title'] ?? '';
$isEdit = isset($post); // Biến cờ xác định chế độ
$current_user = request()->session()->authUser() ?? ['account_id' => null];
?>

<!-- ======== Canva Main =========== -->
<div id="be-topbar">
  <div id="be-topbar-left">
    <a href="<?= url('admin/posts') ?>" class="btn" data-size="md" data-variant="outline">
      <i class="fa-solid fa-chevron-left"></i> Quay lại
    </a>
    <button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-left"
      title="Ẩn/hiện panel block">
      <i class="fa-solid fa-cube"></i> Blocks
    </button>
  </div>

  <div id="be-topbar-center">
    <input id="be-title-input" class="field__input be-post-title-input" type="text" placeholder="Tiêu đề bài viết..."
      value="<?= htmlspecialchars($oldTitle) ?>" autocomplete="off" data-be-meta-key="title">
    <span class="badge"
      data-variant="<?= ($initialPayload['meta']['status'] ?? 'draft') === 'published' ? 'primary' : 'secondary' ?>"
      id="be-status-badge" data-be-meta-preview="status">
    </span>
  </div>

  <div id="be-topbar-right">
    <button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-right"
      title="Ẩn/hiện panel settings">
      <i class="fa-solid fa-table-columns"></i> Cấu Hình
    </button>
    <button type="button" class="btn" data-variant="primary" data-size="md" id="be-publish-btn"
      data-modal-trigger="#confirm-modal">
      Lưu
    </button>
  </div>
</div>

<div id="be-body">
  <div class="be-panel__wrapper">
    <div class="be-panel__gap"></div>
    <div id="be-left" class="be-panel" data-tabs data-tabs-id="be-left-panel" data-tabs-panel-active="be-blocks-panel"
      data-tabs-sync="false">
      <div class="tabs__list be-panel__tabs-list" role="tablist">
        <button type="button" class="be-panel__tabs-trigger be-left-tab active"
          data-tabs-trigger="be-blocks-panel">Blocks</button>
        <button type="button" class="be-panel__tabs-trigger be-left-tab" data-tabs-trigger="be-list-view-panel">Cấu
          trúc</button>
      </div>
      <div class="be-panel__content">
        <div id="be-blocks-menu-panel" class="tabs__panel" data-tabs-panel="be-blocks-panel" role="tabpanel"></div>
        <div id="be-list-view-panel" class="tabs__panel" data-tabs-panel="be-list-view-panel" role="tabpanel">
          <div id="be-list-view-panel-empty-hint" class="empty">
            <div class="empty__header">
              <div class="empty__media"><i class="fa-solid fa-cubes"></i></div>
              <div class="empty__title">Chưa có tiêu đề nào trong bài viết.</div>
              <div class="empty__description">Nhấn "Thêm tiêu đề" để tạo cấu trúc mới.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="be-canvas-wrap">
    <div id="be-canvas">
      <div class="be-canvas__content-wrapper">
        <div class="be-canvas__content be-canvas-title-area">
          <h1 contenteditable="true" id="be-canvas-title" class="be-canvas-title" data-be-meta-key="title">
            <?= !empty($initialPayload['meta']['title']) ? htmlspecialchars($initialPayload['meta']['title']) : 'Tiêu đề bài viết' ?>
          </h1>
          <div class="be-canvas-meta">
            <div class="be-canvas-meta__info" data-be-meta-preview="settings.show_author"
              data-be-preview-action="toggle">
              <i class="fa-regular fa-user"></i> <span id="be-author-name-preview" class="be-canvas-meta__text"></span>
            </div>
            <div class="be-canvas-meta__info" data-be-meta-preview="settings.show_date" data-be-preview-action="toggle">
              <i class="fa-regular fa-calendar"></i> <span class="be-canvas-meta__text">
                <?= date('d/m/Y') ?>
              </span>
            </div>
            <div class="be-canvas-meta__info" data-be-meta-preview="settings.show_view_count"
              data-be-preview-action="toggle">
              <i class="fa-regular fa-eye"></i>
              <span class="be-canvas-meta__text">
                <span class="be-canvas-meta__value" data-be-meta-preview="init_view_count" data-be-preview-action="text"
                  data-preview-default="0">0</span> lượt xem
              </span>
            </div>
          </div>
        </div>
      </div>
      <hr class="separator">
      <div class="be-canvas__content-wrapper">
        <div id="be-block-list" class="be-canvas__content">
          <div id="be-canvas-empty-hint" class="empty">
            <div class="empty__header">
              <div class="empty__media"><i class="fa-solid fa-feather"></i></div>
              <div class="empty__title">Bắt đầu soạn thảo</div>
              <div class="empty__description">Chọn block từ panel bên trái để thêm nội dung.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="be-panel__wrapper">
    <div class="be-panel__gap"></div>
    <div id="be-right" class="be-panel" data-tabs data-tabs-id="be-right-panel"
      data-tabs-panel-active="be-post-settings-panel" data-tabs-sync="false">
      <div class="tabs__list be-panel__tabs-list" role="tablist">
        <button type="button" class="be-panel__tabs-trigger be-right-tab" data-tabs-trigger="be-post-settings-panel">Bài
          viết</button>
        <button type="button" class="be-panel__tabs-trigger be-right-tab active"
          data-tabs-trigger="be-block-settings-panel">Block</button>
      </div>
      <div class="be-panel__content">
        <div class="tabs__panel" data-tabs-panel="be-block-settings-panel" role="tabpanel" id="be-block-settings-panel">
          <div id="be-block-settings-panel-empty-hint" class="empty">
            <div class="empty__header">
              <div class="empty__media"><i class="fa-regular fa-square"></i></div>
              <div class="empty__title">Chưa chọn block</div>
              <div class="empty__description">Chọn một block trong canvas để chỉnh sửa thuộc tính.</div>
            </div>
          </div>
        </div>

        <div class="tabs__panel" data-tabs-panel="be-post-settings-panel" role="tabpanel" id="be-post-settings-panel">
          <div class="field-group">
            <div class="field">
              <span class="field__label">Tiêu đề</span>
              <div id="be-post-title" data-be-meta-preview="title">
                <?= !empty($initialPayload['meta']['title']) ? htmlspecialchars($initialPayload['meta']['title']) : 'Tiêu đề bài viết' ?>
              </div>
            </div>

            <div class="field">
              <span class="field__label">Tác giả</span>
              <button type="button" class="select" data-select-id="be-author-select" data-select-searchable
                data-select-placeholder="Chọn tác giả" name="author_id" data-be-meta-key="author_id" role="listbox"
                data-select-default-value="<?= $initialPayload['meta']['author_id'] ?? $current_user['account_id'] ?>">
                <div class="select__content">
                  <?php foreach (($authors ?? []) as $author): ?>
                    <div class="select__item" data-select-value="<?= $author->id ?>">
                      <?= htmlspecialchars($author->email) ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </button>
            </div>

            <div class="field">
              <span class="field__label">Trạng thái</span>
              <button type="button" class="select" data-select-id="be-status-select" name="status"
                data-be-meta-key="status" role="listbox"
                data-select-default-value="<?= $initialPayload['meta']['status'] ?? 'draft' ?>">
                <div class="select__content">
                  <div class="select__item" data-select-value="draft">Nháp</div>
                  <div class="select__item" data-select-value="published">Xuất bản</div>
                  <div class="select__item" data-select-value="archived">Lưu trữ</div>
                </div>
              </button>
            </div>

            <div class="field">
              <span class="field__label">Slug</span>
              <input type="text" class="field__input" id="be-slug-input" name="slug"
                value="<?= htmlspecialchars($initialPayload['meta']['slug'] ?? '') ?>" placeholder="duong-dan-bai-viet"
                data-be-meta-key="slug">
            </div>

            <div class="field">
              <span class="field__label">Danh mục</span>
              <button type="button" class="select" data-select-id="be-categories-select" data-select-multiple
                data-select-searchable data-select-placeholder="Chọn danh mục..." name="category_ids"
                data-be-meta-key="category_ids" role="listbox" <?= empty($categories) ? 'data-select-disabled' : '' ?>
                <?php $selectedCats = $initialPayload['meta']['category_ids'] ?? [];
                if (!empty($selectedCats)): ?>
                  data-select-default-value="
                <?= implode(',', array_map('intval', $selectedCats)) ?>" <?php endif; ?>>
                <div class="select__content">
                  <?php if (!empty($categories)):
                    foreach ($categories as $cat): ?>
                      <div class="select__item" data-select-value="<?= $cat->id ?>" <?= ($cat->disabled ?? false) ? 'data-select-disabled' : '' ?>>
                        <?= htmlspecialchars($cat->name) ?>
                      </div>
                    <?php endforeach; else: ?>
                    <div class="select__item" data-select-value="" data-select-disabled>Chưa có danh mục nào</div>
                  <?php endif; ?>
                </div>
              </button>
            </div>

            <div class="field">
              <span class="field__label">Mô tả</span>
              <textarea id="be-excerpt-input" class="field__input" rows="3" name="seo_desc"
                placeholder="Để trống: tự lấy từ đoạn văn đầu tiên" maxlength="500"
                data-be-meta-key="excerpt"><?= htmlspecialchars($initialPayload['meta']['excerpt'] ?? '') ?></textarea>
            </div>

            <div class="field" data-orientation="horizontal">
              <div class="field__content"><span class="field__label">Hiển thị Tác giả</span></div>
              <button class="switch" type="button" role="switch" name="settings.show_author"
                data-switch-default-state="<?= ($initialPayload['meta']['settings']['show_author'] ?? true) ? 'checked' : '' ?>"
                data-be-meta-key="settings.show_author"><span class="switch__thumb"></span></button>
            </div>

            <div class="field" data-orientation="horizontal">
              <div class="field__content"><span class="field__label">Hiển thị Ngày xuất bản</span></div>
              <button class="switch" type="button" role="switch" name="settings.show_date"
                data-switch-default-state="<?= ($initialPayload['meta']['settings']['show_date'] ?? true) ? 'checked' : '' ?>"
                data-be-meta-key="settings.show_date"><span class="switch__thumb"></span></button>
            </div>

            <div class="field" data-orientation="horizontal">
              <div class="field__content"><span class="field__label">Hiển thị Lượt xem</span></div>
              <button class="switch" type="button" role="switch" name="settings.show_view_count"
                data-switch-default-state="<?= ($initialPayload['meta']['settings']['show_view_count'] ?? true) ? 'checked' : '' ?>"
                data-be-meta-key="settings.show_view_count"><span class="switch__thumb"></span></button>
            </div>

            <div class="field" data-be-meta-preview="settings.show_view_count" data-be-preview-action="toggle">
              <span class="field__label">Lượt xem khởi tạo</span>
              <input type="number" class="field__input" data-be-meta-key="init_view_count"
                value="<?= $initialPayload['meta']['init_view_count'] ?? 0 ?>" placeholder="VD: 500" min="0">
            </div>

            <div class="field" data-orientation="horizontal">
              <div class="field__content">
                <span class="field__label">Bài viết nổi bật</span>
                <div class="field__description">Bài viết sẽ được hiển thị ở vị trí nổi bật trên trang chủ</div>
              </div>
              <button class="switch" type="button" role="switch" name="settings.is_featured"
                data-switch-default-state="<?= ($initialPayload['meta']['settings']['is_featured'] ?? false) ? 'checked' : '' ?>"
                data-be-meta-key="settings.is_featured"><span class="switch__thumb"></span></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<form id="be-post-form" method="POST" action="<?= $isEdit ? url("admin/posts/{$post->id}") : url("admin/posts"); ?>"
  class="hidden">
  <?= csrf_field() ?>
  <?php if ($isEdit): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>
  <input type="hidden" id="be-editor-data" name="editor_data" />
</form>

<div class="modal detail-modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
      <?php if ($isEdit): ?>
        Những thay đổi sẽ được áp dụng vào bài viết `<span class="text-sm font-medium"
          data-be-meta-preview="title"></span>`
      <?php else: ?>
        Nội dung bạn đã nhập sẽ được lưu thành bài viết `<span class="text-sm font-medium"
          data-be-meta-preview="title"></span>`
      <?php endif; ?>
    </p>
  </div>

  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<?php $layout->start('scripts'); ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const authors = <?= json_encode($authors ?? []) ?> ?? [];
    const authorSelect = document.querySelector("[name='author_id']");

    authorSelect.addEventListener('select:change', (e) => {
      const { value } = e.detail;
      const author = authors.find(a => Number(a.id) === Number(value));
      document.getElementById('be-author-name-preview').textContent = author?.email ?? '';
    });

    const form = document.querySelector('#be-post-form');
    const initialPayload = <?= json_encode($initialPayload) ?>;

    if (initialPayload && initialPayload.blocks && initialPayload.blocks.length > 0) {
      window.BeEditor.importPayload(initialPayload);
    }

    document.querySelector('#confirm-modal-btn')?.addEventListener('click', () => {
      const payload = window.BeEditor.getPayload();
      document.querySelector('#be-editor-data').value = JSON.stringify(payload);
      form.submit();
    });
  });
</script>
<?php $layout->end(); ?>