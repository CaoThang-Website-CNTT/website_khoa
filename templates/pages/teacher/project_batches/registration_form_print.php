<?php
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$defaultHtml = static function (?string $html, ?string $fallback = null) use ($escape): string {
  if (trim((string)$html) !== '') return (string)$html;
  $lines = preg_split('/\R+/', trim((string)$fallback)) ?: [];
  return implode('', array_map(fn($line) => '<p>' . $escape($line) . '</p>', array_filter($lines, 'strlen')));
};

$editorSets = [];
foreach ($groups as $group) {
  $topicId = (int)$group['assigned_topic_id'];
  $requirements = $defaultHtml($group['registration_requirements'], $group['topic_description'] ?? '');
  $opinion = $defaultHtml($group['supervisor_opinion']);
  $fingerprint = sha1(json_encode([$requirements, $opinion, $group['execution_start'], $group['execution_end']]));
  $editorSets[$topicId][$fingerprint][] = [
    'group' => $group,
    'requirements' => $requirements,
    'opinion' => $opinion,
  ];
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Xem trước phiếu đăng ký đồ án tốt nghiệp</title>
  <link rel="stylesheet" href="<?= url('public/css/fonts.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/base.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/common.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/fontawesome/fontawesome.min.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/fontawesome/solid.min.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/project_registration_form.css') ?>">
</head>
<body class="project-form-editor">
  <header class="project-form-editor__topbar">
    <button type="button" class="btn" data-variant="outline" data-size="md" onclick="window.close()"><i class="fa-solid fa-chevron-left"></i> Đóng</button>
    <strong>Xem trước phiếu đăng ký đồ án tốt nghiệp</strong>
    <button type="button" class="btn" data-variant="primary" data-size="md" id="save-print"><i class="fa-solid fa-print"></i> Lưu &amp; In</button>
  </header>

  <div class="project-form-editor__body">
    <aside class="project-form-editor__panel" aria-label="Thông tin có thể chỉnh sửa">
      <p class="text-sm mb-4">Nội dung được lưu riêng cho từng nhóm. Các nhóm có cùng đề tài và cùng dữ liệu được chỉnh chung.</p>
      <?php foreach ($editorSets as $topicSets): ?>
        <?php foreach ($topicSets as $set): $first = $set[0]; $ids = array_map(fn($item) => (int)$item['group']['id'], $set); ?>
          <section class="project-form-editor__set" data-editor-set data-group-ids="<?= $escape(json_encode($ids)) ?>">
            <h2><?= $escape($first['group']['assigned_topic_title']) ?></h2>
            <p class="text-xs mb-3"><?= count($ids) > 1 ? count($ids) . ' nhóm dùng chung nội dung này' : 'Nhóm #' . $ids[0] ?></p>
            <div class="field mb-4">
              <label class="field__label">Nội dung yêu cầu đề tài</label>
              <div class="project-rich-toolbar" role="toolbar" aria-label="Định dạng nội dung">
                <button type="button" data-command="bold" aria-label="In đậm"><i class="fa-solid fa-bold"></i></button>
                <button type="button" data-command="italic" aria-label="In nghiêng"><i class="fa-solid fa-italic"></i></button>
                <button type="button" data-command="insertUnorderedList" aria-label="Danh sách"><i class="fa-solid fa-list-ul"></i></button>
                <button type="button" data-command="insertOrderedList" aria-label="Danh sách số"><i class="fa-solid fa-list-ol"></i></button>
              </div>
              <div class="project-rich-editor" contenteditable="true" data-field="registration_requirements" role="textbox" aria-multiline="true"><?= $first['requirements'] ?></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
              <div class="field"><label class="field__label">Ngày bắt đầu</label><input class="field__input" type="date" data-field="execution_start" value="<?= $escape($first['group']['execution_start']) ?>"></div>
              <div class="field"><label class="field__label">Ngày kết thúc</label><input class="field__input" type="date" data-field="execution_end" value="<?= $escape($first['group']['execution_end']) ?>"></div>
            </div>
            <div class="field">
              <label class="field__label">Ý kiến của giảng viên hướng dẫn</label>
              <div class="project-rich-toolbar" role="toolbar" aria-label="Định dạng ý kiến">
                <button type="button" data-command="bold" aria-label="In đậm"><i class="fa-solid fa-bold"></i></button>
                <button type="button" data-command="italic" aria-label="In nghiêng"><i class="fa-solid fa-italic"></i></button>
                <button type="button" data-command="insertUnorderedList" aria-label="Danh sách"><i class="fa-solid fa-list-ul"></i></button>
              </div>
              <div class="project-rich-editor" contenteditable="true" data-field="supervisor_opinion" role="textbox" aria-multiline="true"><?= $first['opinion'] ?></div>
            </div>
          </section>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <p id="save-message" class="text-sm" role="status" aria-live="polite"></p>
    </aside>

    <main class="project-form-editor__canvas">
      <div class="project-form-editor__source" hidden>
        <?php foreach ($groups as $group):
          $requirements = $defaultHtml($group['registration_requirements'], $group['topic_description'] ?? '');
          $opinion = $defaultHtml($group['supervisor_opinion']);
          $classYears = array_values(array_unique(array_filter(array_map(function ($member) {
            return preg_match('/(\d{2})/', (string)($member['classroom_name'] ?? ''), $match) ? $match[1] : null;
          }, $group['members'] ?? []))));
          $academicYear = $classYears ? implode(', ', $classYears) : (($batch['min_class_of'] ?? '') . ' - ' . ($batch['max_class_of'] ?? ''));
        ?>
          <article class="project-registration-page" data-print-group="<?= (int)$group['id'] ?>">
            <div class="project-registration-header">
              <div><div>Trường CĐ Kỹ Thuật Cao Thắng</div><strong>Khoa Công Nghệ Thông Tin</strong></div>
              <div><strong>Cộng hòa xã hội chủ nghĩa Việt Nam</strong><div class="project-registration-motto">Độc lập - Tự do - Hạnh phúc</div></div>
            </div>
            <h1>ĐĂNG KÝ ĐỀ TÀI TỐT NGHIỆP</h1>
            <p class="project-registration-center">Niên khóa: <?= $escape($academicYear) ?></p>
            <p><strong>GIẢNG VIÊN HƯỚNG DẪN:</strong> <?= $escape($group['teacher_name']) ?></p>
            <p><strong>SINH VIÊN THỰC HIỆN:</strong></p>
            <ol class="project-registration-students">
              <?php foreach ($group['members'] as $member): ?>
                <li><strong><?= $escape($member['full_name']) ?></strong><span>MSSV: <?= $escape($member['student_code']) ?></span><span>Lớp: <?= $escape($member['classroom_name']) ?></span></li>
              <?php endforeach; ?>
            </ol>
            <p><strong>TÊN ĐỀ TÀI:</strong> <?= $escape($group['assigned_topic_title']) ?></p>
            <section><h2>NỘI DUNG YÊU CẦU CỦA ĐỀ TÀI:</h2><div data-preview-field="registration_requirements"><?= $requirements ?></div></section>
            <p>Thời gian thực hiện đề tài: từ ngày <strong data-preview-field="execution_start"><?= $escape($group['execution_start'] ? date('d/m/Y', strtotime($group['execution_start'])) : '.../.../......') ?></strong> đến ngày <strong data-preview-field="execution_end"><?= $escape($group['execution_end'] ? date('d/m/Y', strtotime($group['execution_end'])) : '.../.../......') ?></strong></p>
            <section><h2>Ý KIẾN CỦA GIẢNG VIÊN HƯỚNG DẪN:</h2><div data-preview-field="supervisor_opinion"><?= $opinion ?></div></section>
            <div class="project-registration-signatures"><div><strong>Giám Hiệu</strong></div><div><strong>Khoa Công Nghệ Thông Tin</strong></div><div><strong>GV Hướng dẫn</strong><small>(Ký và ghi rõ họ tên)</small></div></div>
          </article>
        <?php endforeach; ?>
      </div>
      <iframe class="project-form-editor__preview" title="Bản xem trước phiếu đăng ký"></iframe>
    </main>
  </div>
  <script>window.PROJECT_FORM_SAVE_URL = <?= json_encode(url("teacher/project_batches/{$batch['id']}/registration-forms/save")) ?>; window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;</script>
  <script src="<?= url('public/js/pages/project_registration_form.js') ?>"></script>
</body>
</html>
