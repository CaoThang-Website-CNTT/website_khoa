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
<div id="be-shell">

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
        value="<?= htmlspecialchars($oldTitle) ?>" autocomplete="off" data-be-meta-key="title">
      <span class="badge" data-variant="<?= $oldStatus === 'published' ? 'primary' : 'secondary' ?>"
        id="be-status-badge">
        <?= $statusLabels[$oldStatus] ?? 'Nháp' ?>
      </span>
    </div>

    <div id="be-topbar-right">
      <!-- Toggle right panel -->
      <button type="button" class="btn" data-size="md" id="be-toggle-right" title="Ẩn/hiện panel settings">
        <i class="fa-solid fa-table-columns"></i>
      </button>
      <button type="button" class="btn" data-variant="primary" data-size="md" id="be-publish-btn">
        Lưu
      </button>
    </div>
  </div>
  <!-- ── /TOPBAR ────────────────────────────────────────────── -->

  <!-- ════ START: BODY ════ -->
  <div id="be-body">
    <!-- ════ START: LEFT PANEL ════ -->
    <!-- Tabs -->
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
          <!-- Tab: Blocks -->
          <div id="be-blocks-menu-panel" class="tabs__panel" data-tabs-panel="be-blocks-panel" role="tabpanel">
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
    </div>

    <!-- ════ CANVAS ════ -->
    <div id="be-canvas-wrap">
      <div id="be-canvas">

        <!-- Tiêu đề bài viết hiển thị trên canvas như trang public -->
        <div class="be-canvas-title-area">
          <h1 contenteditable="true" id="be-canvas-title" class="be-canvas-title" data-be-meta-key="title">
            <?= $oldTitle ? htmlspecialchars($oldTitle) : 'Tiêu đề bài viết' ?>
          </h1>
          <div class="be-canvas-meta">
            <div class="be-canvas-meta__info" data-be-meta-preview="show_author" data-be-preview-action="toggle">
              <i class="fa-regular fa-user"></i>
              <span class="be-canvas-meta__text">Ban biên tập</span>
            </div>
            <div class="be-canvas-meta__info" data-be-meta-preview="show_date" data-be-preview-action="toggle">
              <i class="fa-regular fa-calendar"></i>
              <span class="be-canvas-meta__text"><?= date('d/m/Y') ?></span>
            </div>
            <div class="be-canvas-meta__info" data-be-meta-preview="show_read_time" data-be-preview-action="toggle">
              <i class="fa-regular fa-clock"></i>
              <span class="be-canvas-meta__text">5 phút đọc</span>
            </div>
            <div class="be-canvas-meta__info" data-be-meta-preview="show_view_count" data-be-preview-action="toggle">
              <i class="fa-regular fa-eye"></i>
              <span class="be-canvas-meta__text">
                <span class="be-canvas-meta__value" data-be-meta-preview="init_view_count" data-be-preview-action="text"
                  data-preview-default="0">0</span>
                lượt xem
              </span>
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
    <div class="be-panel__wrapper">
      <div class="be-panel__gap"></div>
      <div id="be-right" class="be-panel" data-tabs data-tabs-id="be-right-panel"
        data-tabs-panel-active="be-post-settings-panel" data-tabs-sync="false">
        <div class="tabs__list be-panel__tabs-list" role="tablist">
          <button type="button" class="be-panel__tabs-trigger be-right-tab"
            data-tabs-trigger="be-post-settings-panel">Bài
            viết</button>
          <button type="button" class="be-panel__tabs-trigger be-right-tab active"
            data-tabs-trigger="be-block-settings-panel">Block</button>
        </div>

        <div class="be-panel__content">
          <!-- Tab: Block settings -->
          <div class="tabs__panel" data-tabs-panel="be-block-settings-panel" role="tabpanel"
            id="be-block-settings-panel">
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
                <span class="field__label">Tiêu đề</span>
                <div id="be-post-title" data-be-meta-preview="title">
                  <?= $oldTitle ? htmlspecialchars($oldTitle) : 'Tiêu đề bài viết' ?>
                </div>
              </div>

              <div class="field">
                <span class="field__label">Tác giả</span>
                <button type="button" class="select" data-select-id="be-author-select" data-select-searchable
                  data-select-placeholder="Chọn tác giả" name="author_id" data-be-meta-key="author_id" role="listbox">
                  <div class="select__content">
                    <?php foreach (($authors ?? []) as $author): ?>
                      <div class="select__item" data-select-value=" <?= $author ?>">
                        <?= htmlspecialchars($author->name) ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </button>
              </div>

              <div class="field">
                <span class="field__label">Trạng thái</span>
                <button type="button" class="select" data-select-id="be-status-select" data-select-placeholder="Chọn"
                  name="status" data-meta-key="status" data-meta-preview="status" data-meta-action="status-badge" role="listbox" data-select-default-value="draft">
                  <div class="select__content">
                    <div class="select__item" data-select-value="draft" <?= $oldStatus === 'draft' ? 'selected' : '' ?>>
                      Nháp</div>
                    <div class="select__item" data-select-value="published" <?= $oldStatus === 'published' ? 'selected' : '' ?>>Xuất bản</div>
                    <div class="select__item" data-select-value="archived" <?= $oldStatus === 'archived' ? 'selected' : '' ?>>Lưu trữ</div>
                  </div>
                </button>
              </div>

              <div class="field">
                <span class="field__label">Slug</span>
                <input type="text" class="field__input" id="be-slug-input" name="slug"
                  value="<?= htmlspecialchars($oldSlug) ?>" placeholder="duong-dan-bai-viet" data-be-meta-key="slug">
              </div>

              <div class="field">
                <span class="field__label">Danh mục</span>
                <button type="button" class="select" 
                  data-select-id="be-categories-select" 
                  data-select-multiple 
                  data-select-searchable 
                  data-select-placeholder="Chọn danh mục..."
                  name="category_ids"
                  data-be-meta-key="category_ids"
                  role="listbox"
                  <?php if (empty($categories)): ?>data-select-disabled<?php endif; ?>
                  <?php 
                    // Pre-select saved categories (comma-separated values)
                    $selectedCats = $post->category_ids ?? ($old_input['category_ids'] ?? []);
                    if (!empty($selectedCats)): 
                  ?>
                  data-select-default-value="<?= implode(',', array_map('intval', $selectedCats)) ?>"
                  <?php endif; ?>
                  >
                    <div class="select__content">
                      <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                          <div class="select__item" 
                              data-select-value="<?= $cat->id ?>"
                              <?php if ($cat->disabled ?? false): ?>data-select-disabled<?php endif; ?>>
                            <?= htmlspecialchars($cat->name) ?>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="select__item" data-select-value="" data-select-disabled>
                          Chưa có danh mục nào
                        </div>
                      <?php endif; ?>
                    </div>
                  </button>
                </div>

              <div class="field">
                <span class="field__label">Mô tả</span>
                <textarea id="be-excerpt-input" class="field__input" rows="3" name="seo_desc"
                  placeholder="Để trống: tự lấy từ đoạn văn đầu tiên" maxlength="500"
                  data-be-meta-key="seo_desc"><?= htmlspecialchars($post->seo_desc ?? '') ?></textarea>
              </div>

              <div class="field" data-orientation="horizontal">
                <div class="field__content">
                  <span class="field__label">Hiển thị Tác giả</span>
                </div>
                <button class="switch" type="button" role="switch" name="show_author" data-be-meta-key="show_author">
                  <span class="switch__thumb"></span>
                </button>
              </div>

              <div class="field" data-orientation="horizontal">
                <div class="field__content">
                  <span class="field__label">Hiển thị Ngày xuất bản</span>
                </div>
                <button class="switch" type="button" role="switch" data-switch-default-state="checked" name="show_date"
                  data-be-meta-key="show_date">
                  <span class="switch__thumb"></span>
                </button>
              </div>

              <div class="field" data-orientation="horizontal">
                <div class="field__content">
                  <span class="field__label">Hiển thị Thời gian đọc</span>
                </div>
                <button class="switch" type="button" role="switch" name="show_read_time"
                  data-be-meta-key="show_read_time">
                  <span class="switch__thumb"></span>
                </button>
              </div>

              <div class="field" data-orientation="horizontal">
                <div class="field__content">
                  <span class="field__label">Hiển thị Lượt xem</span>
                </div>
                <button class="switch" type="button" role="switch" name="show_view_count"
                  data-be-meta-key="show_view_count">
                  <span class="switch__thumb"></span>
                </button>
              </div>

              <div class="field" data-be-meta-preview="show_view_count" data-be-preview-action="toggle">
                <span class="field__label">Lượt xem khởi tạo</span>
                <input type="number" class="field__input" data-be-meta-key="init_view_count" placeholder="VD: 500"
                  min="0">
              </div>
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

<form id="be-post-form" method="POST" action="<?= url("admin/posts"); ?>" class="hidden">
  <?= csrf_field() ?>
  <input type="hidden" id="be-editor-data" name="editor_data" />
</form>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#be-post-form');
    const canvas = window.BeEditor.getCanvas();

    const headingId = canvas.addBlock('blocks/heading', {
        content: 'Welcome to the Future of Editing',
        level: 2
    });

    const introId = canvas.addBlock('blocks/paragraph', {
        content: 'This post was created entirely via code, demonstrating the power of the underlying API.',
        align: 'left'
    }, headingId);

    const imageId = canvas.addBlock('blocks/image', {
        url: 'https://placehold.co/600x400',
        alt: 'Placeholder image',
        caption: 'A sample image inserted via API',
        align: 'center',
        width: '100%'
    }, introId);

    const listId = canvas.addBlock('blocks/list', {
        values: [
            { text: 'Fast performance', children: [] },
            { text: 'Schema-driven data', children: [] },
            { text: 'Extensible architecture', children: [
                { text: 'Easy to add new blocks', children: [] }
            ]}
        ],
        ordered: false
    }, imageId);

    const quoteId = canvas.addBlock('blocks/quote', {
        content: 'Code is poetry.',
        citation: 'Matt Mullenweg'
    }, listId);

    const tableId = canvas.addBlock('blocks/table', {
        rows: [
            ['Feature', 'Status'],
            ['API', 'Ready'],
            ['UI', 'Polished']
        ],
        hasHeader: true
    }, quoteId);

    document.querySelector('#be-publish-btn')?.addEventListener('click', () => {
      const payload = window.BeEditor.getPayload();
      console.log(payload);

      document.querySelector('#be-editor-data').value = JSON.stringify(payload);

      // form.submit();
    });
  });
</script>