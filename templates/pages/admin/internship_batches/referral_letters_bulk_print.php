<?php

use App\Core\AppTime;

$batch = $batch ?? [];
$letters = array_values($letters ?? []);
$letter = $letters[0] ?? [];
$isMerged = $isMerged ?? false;
$confirmUrl = $confirmUrl ?? url("admin/internship_batches/{$batch['id']}/referral_letters/bulk-print/confirm");
$ids = $ids ?? [];
$now = AppTime::now();
$documentNumber = $letter['document_number'] ?? '';
$authUser = $authUser ?? [];
$defaultApproverName = $letter['approver_name'] ?? ($authUser['full_name'] ?? ($authUser['name'] ?? ($authUser['email'] ?? '')));
$approverStorageKey = 'referral-letter-approver:' . (string)($authUser['account_id'] ?? 'anonymous');
$defaultStart = date('Y-m-d', strtotime($batch['start_at']));
$defaultEnd = date('Y-m-d', strtotime($batch['end_at']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>In hàng loạt giấy giới thiệu</title>
  <link rel="preload" as="style" href="<?= url('public/css/fonts.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/fonts.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/base.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/common.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/main.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/fontawesome/fontawesome.min.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/fontawesome/solid.min.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/block_editor.css') ?>">
  <link rel="stylesheet" href="<?= url('public/css/referral_letter_editor.css') ?>?v=20260702-1">
  <style>@media print {.letter-set + .letter-set .page-1{page-break-before:always}}</style>
</head>
<body>
  <header id="be-topbar">
    <div id="be-topbar-left"><button type="button" class="btn" data-variant="outline" data-size="md" onclick="window.close()"><i class="fa-solid fa-chevron-left"></i> Quay lại</button></div>
    <div id="be-topbar-center"><?= $isMerged ? 'Xem trước công văn in gộp' : 'Xem trước ' . count($letters) . ' giấy giới thiệu' ?></div>
    <div id="be-topbar-right"><button type="button" class="btn" id="be-toggle-right" data-variant="outline" data-size="md" aria-controls="be-right" aria-expanded="true"><i class="fa-solid fa-table-columns"></i> <span>Thông tin</span></button><button type="submit" form="printForm" id="btnPrint" class="btn" data-variant="primary" data-size="md">Lưu &amp; In</button></div>
  </header>
  <div id="be-body">
  <div class="be-panel__wrapper"><div class="be-panel__gap"></div><aside id="be-right" class="be-panel">
    <div class="tabs__list be-panel__tabs-list"><button type="button" class="be-panel__tabs-trigger active">Thông tin công văn</button></div>
    <div class="be-panel__content">
      <form id="printForm">
        <div class="field-group">
          <div class="field"><label class="field__label" for="inp_document_number">Số công văn dùng chung</label><input class="field__input" type="text" id="inp_document_number" value="<?= htmlspecialchars($documentNumber) ?>"></div>
          <div class="field" data-field-required><label class="field__label" for="inp_approver_name">Giảng viên phê duyệt</label><input class="field__input" type="text" id="inp_approver_name" value="<?= htmlspecialchars($defaultApproverName) ?>" required maxlength="255"></div>
          <div class="field" data-field-required><label class="field__label" for="inp_start_date">Ngày bắt đầu thực tập</label><input class="field__input" type="date" id="inp_start_date" value="<?= $defaultStart ?>" required></div>
          <div class="field" data-field-required><label class="field__label" for="inp_end_date">Ngày kết thúc thực tập</label><input class="field__input" type="date" id="inp_end_date" value="<?= $defaultEnd ?>" required></div>
        </div>
      </form>
      <div id="printMessage" class="print-message"></div>
    </div>
  </aside></div>
  <main id="be-canvas-wrap"><div id="be-canvas"><div class="print-source">

  <?php foreach ($letters as $letter):
    $students = $letter['students'] ?? [];
    $programs = array_unique(array_filter(array_column($students, 'training_program')));
    $program = implode(', ', $programs) ?: 'Công nghệ thông tin';
  ?>
  <div class="letter-set">
    <div class="print-page page-1">
      <div class="header">
        <div class="header-left">
          <div class="org-name">BỘ CÔNG THƯƠNG</div>
          <div class="org-name"><strong>TRƯỜNG CĐ KỸ THUẬT CAO THẮNG</strong></div>
          <div class="number-wrapper">Số: <span class="dyn-num"><?= htmlspecialchars($documentNumber ?: '___') ?></span><span>/CĐKTCT-CTCT HSSV</span></div>
          <div class="title">V/v: Liên hệ thực tập tốt nghiệp cho sinh viên</div>
        </div>
        <div class="header-right">
          <div class="republic">CỘNG HOÀ XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
          <div class="motto">Độc lập - Tự do - Hạnh phúc</div>
          <div class="date">TP.Hồ Chí Minh, ngày <?= $now->format('d') ?> tháng <?= $now->format('m') ?> năm <?= $now->format('Y') ?></div>
        </div>
      </div>
      <div class="recipient-section"><span class="recipient-label">Kính gửi:</span> <strong class="recipient-name"><?= htmlspecialchars($letter['company_name'] ?? '') ?></strong></div>
      <p class="paragraph">Để thực hiện tốt nhiệm vụ giáo dục đào tạo, giúp sinh viên học tập trong nhà trường phối hợp thực hành, sản xuất nâng cao tay nghề từ thực tiễn tại nhà máy, công ty, cơ sở sản xuất.</p>
      <p class="paragraph">Trường Cao Đẳng Kỹ Thuật Cao Thắng kính đề nghị Quý đơn vị:</p>
      <p class="paragraph">* Tạo điều kiện cho: <strong><?= count($students) ?></strong> sinh viên (danh sách đính kèm).</p>
      <p class="paragraph">* Đến thực tập sản xuất tại đơn vị theo ngành, nghề đào tạo: <strong><?= htmlspecialchars($program) ?></strong></p>
      <p class="paragraph">* Với giáo viên hướng dẫn là Thầy/Cô: <strong class="dyn-approver"><?= htmlspecialchars($defaultApproverName ?: '..................................') ?></strong></p>
      <p class="paragraph">* Thời gian thực tập từ ngày: <strong class="dyn-start-date"><?= date('d/m/Y', strtotime($batch['start_at'])) ?></strong> đến ngày <strong class="dyn-end-date"><?= date('d/m/Y', strtotime($batch['end_at'])) ?></strong></p>
      <p class="paragraph">* Nội dung thực tập: theo đề cương thực tập (đính kèm).</p>
      <p class="paragraph">Nhà trường cùng với giáo viên hướng dẫn có trách nhiệm giáo dục, nhắc nhở sinh viên trường chấp hành nghiêm nội quy, quy định thực tập, sản xuất tại Quý Đơn vị.</p>
      <p class="paragraph">Rất mong được Quý Đơn vị xem xét giải quyết.</p>
      <p class="paragraph">Trân trọng kính chào./.</p>
      <div class="signature"><div class="signature-title">TL.HIỆU TRƯỞNG</div></div>
    </div>
    <div class="print-page page-2">
      <div class="page2-header">
        <div class="page2-title">DANH SÁCH SINH VIÊN THỰC TẬP TỐT NGHIỆP</div>
      </div>
      <table class="table"><thead><tr><th class="col-stt">STT</th><th>HỌ VÀ TÊN</th><th class="col-major">NGÀNH, NGHỀ</th><th class="col-dob">NGÀY SINH</th><th class="col-address">ĐỊA CHỈ</th></tr></thead><tbody>
        <?php foreach ($students as $index => $student): ?><tr><td><?= $index + 1 ?></td><td><?= htmlspecialchars($student['full_name']) ?></td><td><?= htmlspecialchars($student['training_program'] ?? '') ?></td><td><?= $student['dob'] ? date('d/m/Y', strtotime($student['dob'])) : '' ?></td><td><?= htmlspecialchars($student['address'] ?? '') ?></td></tr><?php endforeach; ?>
      </tbody></table>
      <div class="signature"><div class="signature-title">TL.HIỆU TRƯỞNG</div></div>
    </div>
  </div>
  <?php endforeach; ?>
  </div></div></main></div>
  <script>
    const ids = <?= json_encode(array_values($ids)) ?>;
    const apiUrl = <?= json_encode($confirmUrl) ?>;
    const panel = document.querySelector('#be-right');
    const collapsedTrigger = document.querySelector('#be-toggle-right');
    const setControlsCollapsed = collapsed => {
      panel.dataset.bePanelState = collapsed ? 'collapsed' : 'expanded';
      collapsedTrigger.setAttribute('aria-expanded', String(!collapsed));
    };
    collapsedTrigger.addEventListener('click', () => setControlsCollapsed(panel.dataset.bePanelState !== 'collapsed'));
    const source = document.querySelector('.print-source');
    const previewFrame = document.createElement('iframe');
    previewFrame.className = 'print-preview-frame';
    previewFrame.title = 'Xem trước giấy giới thiệu';
    previewFrame.addEventListener('load', () => source.remove());
    document.querySelector('#be-canvas').append(previewFrame);
    previewFrame.srcdoc = `<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="<?= url('public/css/referral_letter_print.css') ?>?v=20260702-3"></head><body><main class="print-pages">${source.innerHTML}</main></body></html>`;
    const previewElements = selector => previewFrame.contentDocument?.querySelectorAll(selector) || [];
    const approverInput = document.getElementById('inp_approver_name');
    const approverStorageKey = <?= json_encode($approverStorageKey) ?>;
    const storedApprover = localStorage.getItem(approverStorageKey);
    if (storedApprover?.trim()) approverInput.value = storedApprover.trim();
    const syncApprover = () => { const value = approverInput.value.trim(); previewElements('.dyn-approver').forEach(el => el.textContent = value || '..................................'); if (value) localStorage.setItem(approverStorageKey, value); };
    previewFrame.addEventListener('load', syncApprover);
    approverInput.addEventListener('input', syncApprover);
    const formatDate = value => { const p = value.split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : value; };
    document.getElementById('inp_document_number').addEventListener('input', e => previewElements('.dyn-num').forEach(el => el.textContent = e.target.value || '___'));
    document.getElementById('inp_start_date').addEventListener('change', e => previewElements('.dyn-start-date').forEach(el => el.textContent = formatDate(e.target.value)));
    document.getElementById('inp_end_date').addEventListener('change', e => previewElements('.dyn-end-date').forEach(el => el.textContent = formatDate(e.target.value)));
    document.getElementById('printForm').addEventListener('submit', async e => {
      e.preventDefault(); if (!approverInput.value.trim()) { approverInput.reportValidity(); approverInput.focus(); return; } const btn = document.getElementById('btnPrint'); btn.disabled = true;
      const data = new FormData(); data.append('_token', <?= json_encode(csrf_token()) ?>);
      ids.forEach(id => data.append('ids[]', id));
      data.append('document_number', document.getElementById('inp_document_number').value);
      data.append('approver_name', approverInput.value.trim());
      data.append('internship_start_date', document.getElementById('inp_start_date').value);
      data.append('internship_end_date', document.getElementById('inp_end_date').value);
      try { const response = await fetch(apiUrl, {method:'POST', body:data}); const result = await response.json().catch(() => ({})); if (!response.ok || !result.success) throw new Error(result.message || result.data?.message || 'Không thể in các giấy đã chọn. Chỉ giấy ở trạng thái Đang xử lý mới được in.'); previewFrame.contentWindow.print(); }
      catch (error) { alert(error.message); } finally { btn.disabled = false; }
    });
  </script>
</body></html>
