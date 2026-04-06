<?php
/**
 * create.php — Trang tạo / chỉnh sửa bài viết
 * 
 * Dùng chung cho cả create và edit mode.
 * Edit mode: $post được inject từ controller, chứa content_json đã decode.
 * Create mode: $post = null.
 */
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];

// Edit mode: controller inject $post (object), $post->content_json là array đã decode
$isEdit = isset($post);
$pageTitle = $isEdit ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới';
$formAction = $isEdit ? url("admin/posts/{$post->id}") : url('admin/posts');
$formMethod = 'POST'; // method spoofing qua hidden _method nếu cần PUT

// Dữ liệu cũ cho input thường (title, slug...)
$oldTitle = $old_input['title'] ?? ($post->title ?? '');
$oldSlug = $old_input['slug'] ?? ($post->slug ?? '');

// Blocks để hydrate editor trong edit mode — JSON string để JS parse
$initialBlocksJson = $isEdit
  ? json_encode($post->content_json ?? [], JSON_UNESCAPED_UNICODE)
  : '[]';
?>

<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
  window.__initialBlocks__ = <?= $initialBlocksJson ?>;
</script>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>('<?= $flash['title'] ?>', '<?= $flash['desc'] ?>');
    });
  </script>
<?php endif; ?>

<!-- ── Title bar ─────────────────────────────────────────────────────────── -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">
        <?= $pageTitle ?>
      </h2>
      <p>Điền thông tin và soạn nội dung bài viết bên dưới</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= request()->previous(fallback: 'admin/posts') ?>" data-variant="outline" data-size="lg" class="btn">
        <i class="fa-solid fa-chevron-left"></i> Quay lại
      </a>
      <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
        <i class="fa-solid fa-floppy-disk"></i>
        <?= $isEdit ? 'Lưu thay đổi' : 'Thêm bài viết' ?>
      </button>
    </div>
  </div>
</div>

<!-- ── Form ───────────────────────────────────────────────────────────────── -->
<form id="post-form" class="detail-layout" action="<?= $formAction ?>" method="<?= $formMethod ?>">

  <?php if ($isEdit): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

  <!-- Hidden input nhận JSON từ BlockSerializer -->
  <input type="hidden" name="content_json" id="content-json-input">

  <!-- ── Main column ──────────────────────────────────────────────────────── -->
  <div class="detail-layout__main">

    <!-- Card: Thông tin cơ bản -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin bài viết</legend>
          <p class="field__description">Những trường có dấu * là bắt buộc.</p>
        </div>
        <hr class="separator">
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required>
              <label class="field__label" for="title">Tiêu đề bài viết</label>
              <input id="title" class="field__input" type="text" name="title" placeholder="Nhập tiêu đề bài viết..."
                value="<?= htmlspecialchars($oldTitle) ?>">
            </div>

            <div class="field" data-field-readonly>
              <label class="field__label" for="slug">Slug (đường dẫn SEO)</label>
              <input id="slug" class="field__input" type="text" name="slug" placeholder="tieu-de-bai-viet"
                value="<?= htmlspecialchars($oldSlug) ?>" readonly>
              <p class="field__description">Tự động sinh từ tiêu đề. Chỉnh tay nếu cần.</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>

    <!-- Card: Block Editor -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <div class="flex justify-between items-center">
            <div>
              <legend class="field__legend">Nội dung bài viết</legend>
              <p class="field__description">Thêm và sắp xếp các block nội dung.</p>
            </div>
            <!-- BlockToolbar sẽ được mount vào đây bởi JS -->
            <div id="block-toolbar-mount"></div>
          </div>
        </div>
        <hr class="separator">
        <div class="card__content">
          <!-- Container chứa danh sách block -->
          <div id="block-list-container">
            <div class="block-list__empty empty" style="display: flex;">
              <div class="empty__header">
                <div class="empty__title">Chưa có block nào</div>
                <div class="empty__description">
                  Nhấn "Thêm block" để bắt đầu soạn thảo.
                </div>
              </div>
            </div>
          </div>
        </div>
      </fieldset>
    </div>

  </div>

  <!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
  <div class="detail-layout__sidebar">

    <!-- Card: Trạng thái xuất bản -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Trạng thái</legend>
        </div>
        <hr class="separator">
        <div class="card__content">
          <div class="field-group">
            <div class="field">
              <label class="field__label" for="status">Trạng thái bài viết</label>
              <select id="status" class="field__input" name="status">
                <option value="draft" <?= ($post->status ?? 'draft') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                <option value="published" <?= ($post->status ?? '') === 'published' ? 'selected' : '' ?>>Xuất bản
                </option>
                <option value="archived" <?= ($post->status ?? '') === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
              </select>
            </div>
          </div>
        </div>
      </fieldset>
    </div>

    <!-- Card: Danh mục -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Danh mục</legend>
        </div>
        <hr class="separator">
        <div class="card__content">
          <div class="field-group">
            <?php foreach (($categories ?? []) as $cat): ?>
              <div class="field" data-orientation="horizontal">
                <label class="field__label" for="cat-<?= $cat->id ?>">
                  <?= htmlspecialchars($cat->name) ?>
                </label>
                <input type="checkbox" class="field__input" id="cat-<?= $cat->id ?>" name="category_ids[]"
                  value="<?= $cat->id ?>" <?= in_array($cat->id, $post->category_ids ?? []) ? 'checked' : '' ?>>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </fieldset>
    </div>

    <!-- Card: SEO -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">SEO</legend>
          <p class="field__description">Để trống để tự động lấy từ nội dung.</p>
        </div>
        <hr class="separator">
        <div class="card__content">
          <div class="field-group">
            <div class="field">
              <label class="field__label" for="seo_title">Tiêu đề SEO</label>
              <input id="seo_title" class="field__input" type="text" name="seo_title" maxlength="255"
                value="<?= htmlspecialchars($post->seo_title ?? '') ?>">
            </div>
            <div class="field">
              <label class="field__label" for="seo_desc">Mô tả SEO</label>
              <textarea id="seo_desc" class="field__input" name="seo_desc" maxlength="500" rows="3"
                placeholder="Để trống: tự lấy từ paragraph đầu tiên"><?= htmlspecialchars($post->seo_desc ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </fieldset>
    </div>

  </div>
</form>

<!-- ── Confirm Modal ──────────────────────────────────────────────────────── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận lưu bài viết</h2>
    <p class="modal__description">Kiểm tra lại nội dung trước khi lưu.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">
      Hủy
    </button>
    <button id="confirm-submit-btn" data-variant="primary" data-size="lg" class="btn" type="button">
      Xác nhận
    </button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- ── Scripts ────────────────────────────────────────────────────────────── -->
<!--
  Load order (dnd.js đã được layout admin include sẵn):
  1. block_registry.js  — phải đầu tiên (các module khác dùng BlockRegistry)
  2. block_item.js      — dùng BlockRegistry
  3. block_list.js      — dùng BlockItem + Sortable (từ dnd.js)
  4. block_toolbar.js   — dùng BlockList + BlockRegistry
  5. block_serializer.js — dùng BlockList + BlockRegistry
-->
<script src="<?= url('public/js/editor/block_registry.js') ?>"></script>
<script src="<?= url('public/js/editor/block_item.js') ?>"></script>
<script src="<?= url('public/js/editor/block_list.js') ?>"></script>
<script src="<?= url('public/js/editor/block_toolbar.js') ?>"></script>
<script src="<?= url('public/js/editor/block_serializer.js') ?>"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {

    // ── Khởi tạo DnD manager (dùng chung pattern như carousel) ───────────────
    const manager = new DragDropManager();

    // ── Khởi tạo BlockList ────────────────────────────────────────────────────
    const containerEl = document.querySelector('#block-list-container');
    const blockList = createBlockList(containerEl, manager);

    // ── Khởi tạo BlockToolbar và mount vào header ─────────────────────────────
    const toolbar = createBlockToolbar(blockList);
    const toolbarMount = document.querySelector('#block-toolbar-mount');
    toolbarMount.appendChild(toolbar.el);

    // ── Hydrate blocks trong edit mode ────────────────────────────────────────
    const initialBlocks = window.__initialBlocks__ ?? [];
    if (initialBlocks.length > 0) {
      // Sắp xếp theo order trước khi hydrate để đúng thứ tự
      initialBlocks
        .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
        .forEach(block => {
          if (BlockRegistry.has(block.type)) {
            // Truyền _id để giữ nguyên id gốc (quan trọng cho việc track orphan media sau này)
            blockList.add(block.type, { ...block.data, _id: block.id });
          }
        });
    }

    // ── Slug auto-generate từ title ───────────────────────────────────────────
    const titleInput = document.querySelector('#title');
    const slugInput = document.querySelector('#slug');
    let slugManuallyEdited = <?= $isEdit ? 'true' : 'false' ?>; // edit mode: không ghi đè slug

    titleInput.addEventListener('input', function () {
      if (slugManuallyEdited) return;
      slugInput.value = this.value
        .toLowerCase().trim()
        .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a')
        .replace(/[èéẹẻẽêềếệểễ]/g, 'e')
        .replace(/[ìíịỉĩ]/g, 'i')
        .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o')
        .replace(/[ùúụủũưừứựửữ]/g, 'u')
        .replace(/[ỳýỵỷỹ]/g, 'y')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    });

    // Nếu user tự sửa slug thì không ghi đè nữa
    slugInput.addEventListener('input', () => { slugManuallyEdited = true; });

    // ── Gắn Serializer vào form ───────────────────────────────────────────────
    const formEl = document.querySelector('#post-form');
    const hiddenInput = document.querySelector('#content-json-input');

    BlockSerializer.attachToForm(formEl, blockList, hiddenInput, {
      onValidate(blocks) {
        // Kiểm tra tất cả heading block phải có text
        const emptyHeading = blocks.find(b => b.type === 'heading' && !b.data.text?.trim());
        if (emptyHeading) {
          alert('Có block Tiêu đề chưa được điền nội dung.');
          return false;
        }
        return true;
      },
    });

    // ── Submit qua confirm modal ──────────────────────────────────────────────
    document.querySelector('#confirm-submit-btn').addEventListener('click', () => {
      formEl.requestSubmit(); // trigger submit event (để serializer chạy) rồi submit
    });
  });
</script>