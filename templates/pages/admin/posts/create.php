<?php
/**
 * create.php — Trang tạo bài viết (Block Editor)
 *
 * Layout: 3 cột full-height, dùng canva layout.
 * $categories — mảng category.
 */
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];

$isEdit = isset($post);
$formAction = $isEdit ? url("admin/posts/{$post->id}") : url('admin/posts');

$oldTitle = $old_input['title'] ?? ($post->title ?? '');
$oldSlug = $old_input['slug'] ?? ($post->slug ?? '');
$oldStatus = $old_input['status'] ?? ($post->status ?? 'draft');

$initialBlocksJson = $isEdit
  ? json_encode($post->content_json ?? [], JSON_UNESCAPED_UNICODE)
  : '[]';

$statusLabels = [
  'draft' => 'Nháp',
  'published' => 'Đã xuất bản',
  'archived' => 'Lưu trữ',
];
?>

<script>
  window.__initialBlocks__ = <?= $initialBlocksJson ?>;
  window.__isEdit__ = <?= $isEdit ? 'true' : 'false' ?>;
</script>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>('<?= $flash['title'] ?>', '<?= $flash['desc'] ?>');
    });
  </script>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════
     EDITOR SHELL
════════════════════════════════════════════════════════════ -->
<div id="block-editor-shell">

  <!-- ── TOPBAR ─────────────────────────────────────────────── -->
  <div id="be-topbar">
    <div id="be-topbar-left">
      <a href="<?= request()->previous('admin/posts') ?>" class="btn" data-size="md" data-variant="outline">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>
      <!-- Toggle left panel -->
      <button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-left"
        title="Ẩn/hiện panel block">
        <i class="fa-solid fa-cube"></i>
        Blocks
      </button>
    </div>

    <div id="be-topbar-center">
      <input id="be-title-input" class="field__input be-post-title-input" type="text" placeholder="Tiêu đề bài viết..."
        value="<?= htmlspecialchars($oldTitle) ?>" autocomplete="off">
      <span class="badge" data-variant="<?= $oldStatus === 'published' ? 'primary' : 'secondary' ?>"
        id="be-status-badge">
        <?= $statusLabels[$oldStatus] ?? 'Nháp' ?>
      </span>
    </div>

    <div id="be-topbar-right">
      <!-- Toggle right panel -->
      <button type="button" class="btn" data-size="md" id="be-toggle-right" title="Ẩn/hiện panel settings">
        <i class="fa-solid fa-bars"></i>
      </button>
      <button type="button" class="btn" data-variant="outline" data-size="md" id="be-preview-btn">Xem
        trước</button>
      <button type="button" class="btn" data-variant="secondary" data-size="md" id="be-save-btn">
        <?= $isEdit ? 'Lưu thay đổi' : 'Lưu nháp' ?>
      </button>
      <button type="button" class="btn" d data-variant="primary" data-size="md" id="be-publish-btn">
        Xuất bản
      </button>
    </div>
  </div>
  <!-- ── /TOPBAR ────────────────────────────────────────────── -->

  <!-- ════ START: BODY ════ -->
  <div id="be-body">
    <!-- ════ START: LEFT PANEL ════ -->
    <!-- Tabs -->
    <div id="be-left" class="be-panel" data-tabs data-tabs-id="be-left-panel" data-tabs-panel-active="be-blocks-panel"
      data-tabs-sync="false">
      <div class="tabs__list be-panel__tabs-list" role="tablist">
        <button type="button" class="be-panel__tabs-trigger be-left-tab active"
          data-tabs-trigger="be-blocks-panel">Blocks</button>
        <button type="button" class="be-panel__tabs-trigger be-left-tab" data-tabs-trigger="be-list-view-panel">Cấu
          trúc</button>
      </div>

      <div class="be-panel__content">
        <!-- Tab: Blocks -->
        <div id="be-blocks-menu-panel" class="tabs__panel" data-tabs-panel="be-blocks-panel" role="tabpanel">

          <div class="be-block-group">
            <span class="be-block-group__label">Văn bản</span>

            <?php
            // Render block type buttons từ PHP — thực ra JS sẽ attach addBlock(),
            // nhưng render HTML từ PHP để trang không bị flash khi JS chưa load.
            $blockTypes = [
              ['type' => 'blocks/heading', 'label' => 'Tiêu đề', 'icon' => '<i class="fa-solid fa-heading"></i>', 'desc' => 'H2, H3, H4'],
              ['type' => 'blocks/paragraph', 'label' => 'Đoạn văn', 'icon' => '<i class="fa-solid fa-paragraph"></i>', 'desc' => 'Văn bản thuần'],
              ['type' => 'blocks/quote', 'label' => 'Trích dẫn', 'icon' => '<i class="fa-solid fa-quote-left"></i>', 'desc' => 'Blockquote'],
            ];
            foreach ($blockTypes as $bt):
              ?>
              <button type="button" class="btn be-block-btn" data-variant="outline" data-add-block="<?= $bt['type'] ?>">
                <div class="be-block-btn__icon">
                  <?= $bt['icon'] ?>
                </div>
                <div>
                  <div class="be-block-btn__name">
                    <?= $bt['label'] ?>
                  </div>
                </div>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tab: Cấu trúc -->
        <div id="be-list-view-panel" class="tabs__panel" data-tabs-panel="be-list-view-panel" role="tabpanel">
          <div id="be-list-view-panel-empty-hint" class="empty">
            <div class="empty__header">
              <div class="empty__media">
                <i class="fa-solid fa-cubes"></i>
              </div>
              <div class="empty__title">
                Chưa có tiêu đề nào trong bài viết.
              </div>
              <div class="empty__description">
                Nhấn "Thêm tiêu đề" để tạo cấu trúc mới.
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END: LEFT PANEL -->
    </div>

    <!-- ════ CANVAS ════ -->
    <div id="be-canvas-wrap">
      <div id="be-canvas">

        <!-- Tiêu đề bài viết hiển thị trên canvas như trang public -->
        <div class="be-canvas-title-area">
          <h1 contenteditable="true" id="be-canvas-title" class="be-canvas-title">
            <?= $oldTitle ? htmlspecialchars($oldTitle) : 'Tiêu đề bài viết' ?>
          </h1>
          <div class="be-canvas-meta">
            <div class="be-canvas-meta__info">
              <i class="fa-regular fa-user"></i>
              Ban biên tập
            </div>
            <div class="be-canvas-meta__info">
              <i class="fa-regular fa-calendar"></i>
              <?= date('d/m/Y') ?>
            </div>
            <div class="be-canvas-meta__info">
              <i class="fa-regular fa-clock"></i>
              5 phút đọc
            </div>
            <div class="be-canvas-meta__info">
              <i class="fa-regular fa-eye"></i>
              100 lượt xem
            </div>
          </div>
        </div>

        <hr class="separator">

        <!-- Danh sách block -->
        <div id="be-block-list">
          <!-- Empty state -->
          <div id="be-canvas-empty-hint" class="empty">
            <div class="empty__header">
              <div class="empty__media">
                <i class="fa-solid fa-feather"></i>
              </div>
              <div class="empty__title">
                Bắt đầu soạn thảo
              </div>
              <div class="empty__description">
                Chọn block từ panel bên trái để thêm nội dung.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /be-canvas-wrap -->

    <!-- ════ START: RIGHT PANEL ════ -->
    <!-- Tabs -->
    <div id="be-right" class="be-panel" data-tabs data-tabs-id="be-right-panel"
      data-tabs-panel-active="be-post-settings-panel" data-tabs-sync="false">
      <div class="tabs__list be-panel__tabs-list" role="tablist">
        <button type="button" class="be-panel__tabs-trigger be-right-tab" data-tabs-trigger="be-post-settings-panel">Bài
          viết</button>
        <button type="button" class="be-panel__tabs-trigger be-right-tab active"
          data-tabs-trigger="be-block-settings-panel">Block</button>
      </div>

      <div class="be-panel__content">
        <!-- Tab: Block settings -->
        <div class="tabs__panel" data-tabs-panel="be-block-settings-panel" role="tabpanel" id="be-block-settings-panel">
          <div id="be-block-settings-panel-empty-hint" class="empty">
            <div class="empty__header">
              <div class="empty__media">
                <i class="fa-regular fa-square"></i>
              </div>
              <div class="empty__title">
                Chưa chọn block
              </div>
              <div class="empty__description">
                Chọn một block trong canvas để chỉnh sửa thuộc tính.
              </div>
            </div>
          </div>
        </div>

        <!-- Tab: Post settings -->
        <div class="tabs__panel" data-tabs-panel="be-post-settings-panel" role="tabpanel" id="be-post-settings-panel">
          <div class="field-group">
            <div class="field">
              <span class="field__label">Tiêu đề SEO</span>
              <input type="text" class="be-settings-input" name="seo_title"
                value="<?= htmlspecialchars($post->seo_title ?? '') ?>" placeholder="Để trống: dùng tiêu đề bài viết"
                maxlength="255">
            </div>

            <div class="field">
              <span class="field__label">Tác giả</span>
              <select class="be-settings-select" id="be-status-select" name="">
                <option value="draft" <?= $oldStatus === 'draft' ? 'selected' : '' ?>>Nháp</option>
                <option value="published" <?= $oldStatus === 'published' ? 'selected' : '' ?>>Xuất bản</option>
                <option value="archived" <?= $oldStatus === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
              </select>
            </div>

            <div class="field">
              <span class="field__label">Trạng thái</span>
              <select class="be-settings-select" id="be-status-select" name="">
                <?php foreach (($categories ?? []) as $cat): ?>
                  <div class="be-toggle-row">
                    <input type="checkbox" id="cat-<?= $cat->id ?>" name="category_ids[]" value="<?= $cat->id ?>"
                      <?= in_array($cat->id, $post->category_ids ?? []) ? 'checked' : '' ?>>
                    <label class="be-toggle-label" for="cat-<?= $cat->id ?>" style="cursor:pointer">
                      <?= htmlspecialchars($cat->name) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                  <div class="be-settings-hint">Chưa có danh mục nào.</div>
                <?php endif; ?>
                <option value="draft" <?= $oldStatus === 'draft' ? 'selected' : '' ?>>Nháp</option>
                <option value="published" <?= $oldStatus === 'published' ? 'selected' : '' ?>>Xuất bản</option>
                <option value="archived" <?= $oldStatus === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
              </select>
            </div>

            <div class="field">
              <span class="field__label">Slug</span>
              <input type="text" class="be-settings-input" id="be-slug-input" value="<?= htmlspecialchars($oldSlug) ?>"
                placeholder="duong-dan-bai-viet">
              <div class="be-settings-hint">Tự sinh từ tiêu đề. Chỉnh tay nếu cần.</div>
            </div>

            <div class="field">
              <span class="field__label">Danh mục</span>
              <?php foreach (($categories ?? []) as $cat): ?>
                <div class="be-toggle-row">
                  <input type="checkbox" id="cat-<?= $cat->id ?>" name="category_ids[]" value="<?= $cat->id ?>"
                    <?= in_array($cat->id, $post->category_ids ?? []) ? 'checked' : '' ?>>
                  <label class="be-toggle-label" for="cat-<?= $cat->id ?>" style="cursor:pointer">
                    <?= htmlspecialchars($cat->name) ?>
                  </label>
                </div>
              <?php endforeach; ?>
              <?php if (empty($categories)): ?>
                <div class="be-settings-hint">Chưa có danh mục nào.</div>
              <?php endif; ?>
            </div>

            <div class="field">
              <span class="field__label">Mô tả</span>
              <textarea class="be-settings-textarea" rows="3" name="seo_desc"
                placeholder="Để trống: tự lấy từ đoạn văn đầu tiên"
                maxlength="500"><?= htmlspecialchars($post->seo_desc ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ════ END: RIGHT PANEL ════ -->

  </div>
  <!-- ════ END: BODY ════ -->
</div>

<!-- /block-editor-shell -->

<!-- ════════════════════════════════════════════════════════════
     FORM SUBMIT (ẩn, không hiển thị)
════════════════════════════════════════════════════════════ -->
<form id="be-post-form" action="<?= $formAction ?>" method="POST" style="display:none">
  <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>
  <input type="hidden" name="content_json" id="be-content-json-input">
  <input type="hidden" name="title" id="be-title-hidden">
  <input type="hidden" name="slug" id="be-slug-hidden">
  <input type="hidden" name="status" id="be-status-hidden">
  <input type="hidden" name="seo_title" id="be-seo-title-hidden">
  <input type="hidden" name="seo_desc" id="be-seo-desc-hidden">
  <input type="hidden" name="author_id" id="be-show-author-hidden">
  <input type="hidden" name="published_at" id="be-show-date-hidden">
  <input type="hidden" name="read_time" id="be-show-readtime-hidden">
  <input type="hidden" name="view_count" id="be-views-hidden">
</form>

<!-- ════════════════════════════════════════════════════════════
     SCRIPTS
     Load order:
       1. block_registry.js   — types, renderPreview, renderSettings
       2. block_editor.js     — core state + canvas + panels
       3. block_serializer.js — serialize → form submit
════════════════════════════════════════════════════════════ -->

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('be-toggle-left')?.addEventListener('click', () => {
      document.getElementById('be-left')?.classList.toggle('collapsed');
    });
    document.getElementById('be-toggle-right')?.addEventListener('click', () => {
      document.getElementById('be-right')?.classList.toggle('collapsed');
    });

    /* ── Khởi tạo Core ──────────────────────────────────────── */
    const core = PageCore.create();

    core.hydrate({
      blocks: window.__initialBlocks__ ?? [],
      meta: window.__initialMeta__ ?? {},
    });

    /* ── Khởi tạo DnD manager (dnd.js load sẵn trong layout) ── */
    const dndManager = new DragDropManager();

    /* Lắng nghe dragend từ dnd.js → reorder core state */
    dndManager.monitor.addEventListener('dragend', (e) => {
      if (e.canceled || !isSortable(e.operation.source)) return;
      /* Đọc thứ tự DOM hiện tại sau khi drag */
      const orderedIds = [...document.querySelectorAll('.be-block-card')]
        .map(el => el.dataset.id)
        .filter(Boolean);
      core.reorderBlocks(orderedIds);
    });

    /* ── Khởi tạo UI ─────────────────────────────────────────── */
    EditorUI.init(core, dndManager, {
      isEdit: window.__isEdit__,
    });

    /* ── Serializer ──────────────────────────────────────────── */
    BlockSerializer.attachToForm(
      document.getElementById('be-post-form'),
      core,
      {
        contentJson: 'be-content-json-input',
        title: 'be-title-hidden',
        slug: 'be-slug-hidden',
        status: 'be-status-hidden',
        seoTitle: 'be-seo-title-hidden',
        seoDesc: 'be-seo-desc-hidden',
        authorId: 'be-show-author-hidden',
        publishedAt: 'be-show-date-hidden',
        readTime: 'be-show-readtime-hidden',
        views: 'be-views-hidden',
      }
    );

    /* ── Submit buttons ──────────────────────────────────────── */
    function submit(overrideStatus) {
      if (overrideStatus) {
        document.getElementById('be-status-select').value = overrideStatus;
      }
      document.getElementById('be-post-form').requestSubmit();
    }
    document.getElementById('be-save-btn')?.addEventListener('click', () => submit(null));
    document.getElementById('be-publish-btn')?.addEventListener('click', () => submit('published'));

    <?php if ($isEdit): ?>
      document.getElementById('be-preview-btn')?.addEventListener('click', () => {
        window.open('<?= url("posts/{$post->slug}") ?>', '_blank');
      });
    <?php endif; ?>

    /* ── Cleanup ─────────────────────────────────────────────── */
    window.addEventListener('beforeunload', () => {
      document.body.classList.remove('block-editor-active');
    });
  });
</script>