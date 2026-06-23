<?php

/**
 * View: In Giấy Giới Thiệu (Admin)
 * Route: /admin/internship_batches/{id}/referral_letters/{letter_id}/print
 */

$batch = $batch ?? [];
$letter = $letter ?? [];

$orgName = "BỘ CÔNG THƯƠNG";
$schoolName = "TRƯỜNG CĐ KỸ THUẬT CAO THẮNG";
$docTitle = "V/v: Liên hệ thực tập tốt nghiệp cho sinh viên";

$students = $letter['students'] ?? [];
$studentCount = count($students);

// Lấy danh sách unique tên ngành/nghề
$trainingPrograms = array_unique(array_filter(array_column($students, 'training_program')));
$trainingProgramStr = implode(', ', $trainingPrograms) ?: 'Công nghệ thông tin';

// Date format
$now = new DateTime();
$docDateStr = "TP.Hồ Chí Minh, ngày " . $now->format('d') . " tháng " . $now->format('m') . " năm " . $now->format('Y');

$startDateStr = $letter['internship_start_date'] ? date('d/m/Y', strtotime($letter['internship_start_date'])) : date('d/m/Y', strtotime($batch['start_at']));
$endDateStr = $letter['internship_end_date'] ? date('d/m/Y', strtotime($letter['internship_end_date'])) : date('d/m/Y', strtotime($batch['end_at']));

$documentNumber = $letter['document_number'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>In Giấy Giới Thiệu - <?= htmlspecialchars($letter['company_name'] ?? '') ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="<?= url('public/favicon-32x32.png') ?>">
  <link rel="stylesheet" href="<?= url('public/css/referral_letter_print.css') ?>">
</head>

<body>
  <!-- Controls -->
  <div class="admin-controls no-print">
    <h3>Thiết lập in</h3>
    <form id="printForm">
      <label>Số công văn</label>
      <input type="text" id="inp_document_number" value="<?= htmlspecialchars($documentNumber) ?>" placeholder="VD: 123/CĐKTCT-CTCT HSSV">

      <label>Ngày bắt đầu thực tập</label>
      <input type="date" id="inp_start_date" value="<?= $letter['internship_start_date'] ?: date('Y-m-d', strtotime($batch['start_at'])) ?>">

      <label>Ngày kết thúc thực tập</label>
      <input type="date" id="inp_end_date" value="<?= $letter['internship_end_date'] ?: date('Y-m-d', strtotime($batch['end_at'])) ?>">

      <button type="submit" id="btnPrint">Lưu & In Giấy</button>
      <button type="button" class="btn-close" onclick="window.close()">Đóng</button>
    </form>
    <div id="printMessage" class="print-message"></div>
  </div>

  <!-- Page 1 -->
  <div class="print-page page-1">
    <div class="header">
      <div class="header-left">
        <div class="org-name"><?= $orgName ?></div>
        <div class="org-name"><strong><?= $schoolName ?></strong></div>
        <div class="number-wrapper">Số: <span class="dyn-num"><?= htmlspecialchars($documentNumber) ?></span><span>/CĐKTCT-CTCT HSSV</span></div>
        <div class="title"><?= $docTitle ?></div>
      </div>
      <div class="header-right">
        <div class="republic">CỘNG HOÀ XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
        <div class="motto">Độc lập - Tự do - Hạnh phúc</div>
        <div class="date"><?= $docDateStr ?></div>
      </div>
    </div>

    <div class="recipient-section">
      <span class="recipient-label">Kính gửi:</span>
      <strong class="recipient-name"><?= htmlspecialchars($letter['company_name'] ?? '') ?></strong>
    </div>

    <p class="paragraph">
      Để thực hiện tốt nhiệm vụ giáo dục đào tạo, giúp sinh viên học tập trong nhà trường phối hợp thực hành, sản xuất nâng cao tay nghề từ thực tiễn tại nhà máy, công ty, cơ sở sản xuất.
    </p>

    <p class="paragraph">
      Trường Cao Đẳng Kỹ Thuật Cao Thắng kính đề nghị Quý đơn vị:
    </p>

    <p class="paragraph">* Tạo điều kiện cho: <strong><?= $studentCount ?></strong> sinh viên (danh sách đính kèm).</p>
    <p class="paragraph">* Đến thực tập sản xuất tại đơn vị theo ngành, nghề đào tạo: <strong><?= htmlspecialchars($trainingProgramStr) ?></strong></p>
    <p class="paragraph">* Với giáo viên hướng dẫn là Thầy/Cô: <strong><?= htmlspecialchars($letter['teacher_name'] ?? '..................................') ?></strong></p>
    <p class="paragraph">* Thời gian thực tập từ ngày: <strong class="dyn-start-date"><?= htmlspecialchars($startDateStr) ?></strong> đến ngày <strong class="dyn-end-date"><?= htmlspecialchars($endDateStr) ?></strong></p>
    <p class="paragraph">* Nội dung thực tập: theo đề cương thực tập (đính kèm).</p>

    <p class="paragraph">
      Nhà trường cùng với giáo viên hướng dẫn có trách nhiệm giáo dục, nhắc nhở sinh viên trường chấp hành nghiêm nội quy, quy định thực tập, sản xuất tại Quý đơn vị.
    </p>

    <p class="paragraph">
      Rất mong được Quý đơn vị xem xét giải quyết.
    </p>

    <p class="paragraph">
      Trân trọng kính chào./.
    </p>

    <div class="signature">
      <div class="signature-title">TL.HIỆU TRƯỞNG</div>
    </div>
  </div>

  <!-- Page 2 -->
  <div class="print-page page-2">
    <div class="page2-header">
      <div class="page2-title">DANH SÁCH SINH VIÊN THỰC TẬP TỐT NGHIỆP</div>
      <div class="page2-subtitle">Kèm theo công văn Số: <span class="dyn-num"><?= htmlspecialchars($documentNumber) ?><span>/CĐKTCT-CTCT HSSV</span></span> ngày <?= $now->format('d') ?> tháng <?= $now->format('m') ?> năm <?= $now->format('Y') ?></div>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th class="col-stt">STT</th>
          <th>HỌ VÀ TÊN</th>
          <th class="col-major">NGÀNH, NGHỀ</th>
          <th class="col-dob">NGÀY SINH</th>
          <th class="col-address">ĐỊA CHỈ</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $index => $st): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($st['full_name']) ?></td>
            <td><?= htmlspecialchars($st['training_program'] ?? '') ?></td>
            <td><?= $st['dob'] ? date('d/m/Y', strtotime($st['dob'])) : '' ?></td>
            <td><?= htmlspecialchars($st['address'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="signature">
      <div class="signature-title">TL.HIỆU TRƯỞNG</div>
    </div>
  </div>

  <script>
    const API_URL = "<?= url("admin/internship_batches/{$batch['id']}/referral_letters/{$letter['id']}/print") ?>";

    // Format date string to dd/mm/yyyy
    function formatDateStr(dateStr) {
      if (!dateStr) return '';
      const parts = dateStr.split('-');
      if (parts.length !== 3) return dateStr;
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    // Dynamic UI update
    document.getElementById('inp_document_number').addEventListener('input', function(e) {
      const val = e.target.value;
      document.querySelectorAll('.dyn-num').forEach(el => el.textContent = val || '___/CĐKTCT-CTCT HSSV');
    });

    document.getElementById('inp_start_date').addEventListener('change', function(e) {
      document.querySelectorAll('.dyn-start-date').forEach(el => el.textContent = formatDateStr(e.target.value));
    });

    document.getElementById('inp_end_date').addEventListener('change', function(e) {
      document.querySelectorAll('.dyn-end-date').forEach(el => el.textContent = formatDateStr(e.target.value));
    });

    // Form submission
    document.getElementById('printForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const btn = document.getElementById('btnPrint');
      const msg = document.getElementById('printMessage');
      btn.disabled = true;
      btn.textContent = 'Đang lưu...';

      const data = new FormData();
      data.append('_token', '<?= csrf_token() ?>');
      data.append('document_number', document.getElementById('inp_document_number').value);
      data.append('internship_start_date', document.getElementById('inp_start_date').value);
      data.append('internship_end_date', document.getElementById('inp_end_date').value);

      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          body: data
        });
        const result = await response.json();

        if (result.success) {
          msg.textContent = 'Đã lưu trạng thái in!';
          msg.style.display = 'block';

          setTimeout(() => {
            window.print();
          }, 500);
        } else {
          alert(result.message || 'Có lỗi xảy ra');
        }
      } catch (err) {
        alert('Lỗi kết nối');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Lưu & In Giấy';
      }
    });
  </script>
</body>

</html>