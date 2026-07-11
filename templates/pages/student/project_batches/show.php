<?php

/**
 * View: Đồ án tốt nghiệp sinh viên
 * Route: /student/project_batches
 */
$student = $student ?? null;
$currentBatch = $currentBatch ?? null;
$isEligible = $isEligible ?? false;
$ineligibilityReason = $ineligibilityReason ?? '';
$group = $group ?? null;
$groupMembers = $groupMembers ?? [];
$phase = $phase ?? 'upcoming';
$isAllocationPublished = $isAllocationPublished ?? false;
$assignedTopic = $assignedTopic ?? null;
$canInteract = ($phase === 'registration' && !$isAllocationPublished);

$isLeader = false;
$isConfirmed = false;
$leaderPhone = '';
$leaderName = '';
if ($group) {
  foreach ($groupMembers as $member) {
    if ($member['student_id'] == $student->id) {
      $isLeader = (bool)$member['is_leader'];
      $isConfirmed = (bool)$member['is_confirmed'];
    }
    if ($member['is_leader']) {
      $leaderPhone = $member['phone'] ?? 'Chưa cập nhật';
      $leaderName = $member['full_name'];
    }
  }
}
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Đồ án tốt nghiệp</h2>
<p class="title-wrapper__description">Theo dõi tiến độ, quản lý nhóm và đăng ký nguyện vọng đề tài.</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('student/project_batches') ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  Danh sách đợt
</a>
<?php $layout->end() ?>

<div class="grid grid-cols-1 gap-6">
  <?php if (!$currentBatch): ?>
    <div class="card shadow py-12 text-center">
      <div class="card__content flex flex-col items-center">
        <div class="text-5xl mb-6">
          <i class="fa-solid fa-folder-open"></i>
        </div>
        <h2 class="text-xl font-semibold mb-2">Không có đợt đồ án nào</h2>
        <p class="mx-auto">
          Hiện tại chưa có đợt đồ án tốt nghiệp nào đang mở.
          Vui lòng quay lại sau.
        </p>
      </div>
    </div>
  <?php elseif (!$isEligible): ?>
    <div class="alert" data-variant="error">
      <i class="fa-solid fa-ban"></i>
      <div class="alert-content">
        <h3 class="alert-title">Không đủ điều kiện tham gia</h3>
        <p class="alert-description">
          <?= htmlspecialchars($ineligibilityReason) ?><br><br>
          Nếu bạn cho rằng đây là sự nhầm lẫn, vui lòng liên hệ giáo vụ khoa.
        </p>
      </div>
    </div>
  <?php else: ?>
    <!-- Thông tin đợt đồ án -->
    <div class="card shadow">
      <div class="card__content">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-bold"><?= htmlspecialchars($currentBatch['title']) ?></h2>
            <?php if (!empty($currentBatch['description'])): ?>
              <p class="mt-1"><?= nl2br(htmlspecialchars($currentBatch['description'])) ?></p>
            <?php endif; ?>
          </div>
          <?php
          $phaseLabel = match ($phase) {
            'registration' => 'Đang mở đăng ký',
            'reviewing' => 'Đang xét duyệt',
            'allocated' => 'Đã phân công',
            'closed' => 'Đã kết thúc',
            default => 'Đang diễn ra'
          };
          $phaseVariant = match ($phase) {
            'registration' => 'primary',
            'reviewing' => 'warning',
            'allocated' => 'success',
            'closed' => 'secondary',
            default => 'outline'
          };
          ?>
          <span class="badge" data-variant="<?= $phaseVariant ?>"><?= $phaseLabel ?></span>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-calendar-check text-xl"></i>
            </div>
            <div>
              <p class="text-sm font-medium">Đăng ký nguyện vọng</p>
              <p class="text-sm mt-1">
                <?= $currentBatch['registration_start'] ? date('d/m/Y', strtotime($currentBatch['registration_start'])) : 'Chưa có' ?>
                -
                <?= $currentBatch['registration_end'] ? date('d/m/Y', strtotime($currentBatch['registration_end'])) : 'Chưa có' ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if ($phase === 'reviewing' && !$isAllocationPublished): ?>
      <div class="alert" data-variant="info">
        <i class="fa-solid fa-hourglass-half"></i>
        <div class="alert-content">
          <h3 class="alert-title">Đang trong giai đoạn xét duyệt</h3>
          <p class="alert-description">
            Thời gian đăng ký đã kết thúc. Khoa đang trong quá trình xét duyệt
            và phân bổ đề tài cho các nhóm. Vui lòng chờ thông báo chính thức từ khoa.
          </p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Nhóm & Nguyện vọng -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div>
        <div class="card shadow">
          <div class="card__header">
            <h3 class="text-lg font-semibold">Nhóm của tôi</h3>
          </div>
          <hr class="separator">
          <div class="card__content">

            <?php if (!$group): ?>
              <!-- Form tạo nhóm -->
              <?php if ($canInteract): ?>
                <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/create") ?>" method="POST" class="mt-4">
                  <?= csrf_field() ?>
                  <p class="text-sm mb-3">Nhập MSSV của bạn cùng nhóm để gửi lời mời (Nhóm 2 người).</p>
                  <div class="field">
                    <input type="text" name="partner_mssv" class="field__input" placeholder="MSSV thành viên" data-field-required>
                    <button type="submit" class="btn" data-size="md" data-variant="primary">Mời & Tạo nhóm</button>
                  </div>
                </form>
              <?php else: ?>
                <div class="py-4 text-center text-gray-500">
                  <i class="fa-solid fa-clock text-3xl mb-3"></i>
                  <p>Đã hết thời gian tạo nhóm.</p>
                </div>
              <?php endif; ?>

            <?php elseif (!$isLeader && !$isConfirmed): ?>
              <!-- Lời mời vào nhóm -->
              <div class="alert" data-variant="info">
                <i class="fa-solid fa-envelope-open-text"></i>
                <div class="alert-content">
                  <h3 class="alert-title">Lời mời vào nhóm</h3>
                  <p class="alert-description">
                    Bạn được mời tham gia nhóm đồ án bởi <strong><?= htmlspecialchars($leaderName) ?></strong>.<br>
                    Liên hệ: <strong><?= htmlspecialchars($leaderPhone) ?></strong><br><br>
                    <em>Lưu ý: Bạn phải đồng ý thì nhóm mới được duyệt.</em>
                  </p>
                  <div class="mt-3 flex gap-2">
                    <?php if ($canInteract): ?>
                      <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/confirm") ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn" data-variant="primary" data-size="sm">Đồng ý</button>
                      </form>
                      <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/reject") ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="sm" data-confirm-msg="Bạn có chắc chắn muốn từ chối tham gia nhóm của <?= htmlspecialchars($leaderName) ?>?" data-modal-trigger="#action-confirm-modal">Từ chối</button>
                      </form>
                    <?php else: ?>
                      <div class="text-sm font-medium" style="color: var(--destructive);">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Đã hết hạn đăng ký, bạn không thể xác nhận tham gia / từ chối vào nhóm nữa. Nhóm này sẽ bị xem là không hợp lệ.
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

            <?php else: ?>
              <!-- Danh sách thành viên nhóm -->
              <div class="space-y-3">
                <?php foreach ($groupMembers as $member): ?>
                  <div class="flex justify-between items-center p-3 border rounded-md">
                    <div>
                      <div class="font-medium">
                        <?= htmlspecialchars($member['student_code']) ?> - <?= htmlspecialchars($member['full_name']) ?>
                        <?php if ($member['is_leader']): ?>
                          <span class="badge" data-variant="primary">Nhóm trưởng</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-sm text-gray-500 mt-1">
                        SĐT: <?= htmlspecialchars($member['phone'] ?? 'N/A') ?> | Lớp: <?= htmlspecialchars($member['classroom_name'] ?? 'N/A') ?>
                      </div>
                    </div>
                    <div>
                      <?php if ($member['is_confirmed']): ?>
                        <span class="badge" data-variant="success">Đã xác nhận</span>
                      <?php else: ?>
                        <span class="badge" data-variant="warning">Chờ xác nhận</span>
                        <?php if ($canInteract && $isLeader): ?>
                          <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/cancel") ?>" method="POST" class="inline-block ml-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="student_id" value="<?= $member['student_id'] ?>">
                            <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="sm" title="Hủy lời mời / Xóa" data-confirm-msg="Bạn có chắc chắn muốn xóa thành viên <?= htmlspecialchars($member['full_name']) ?> khỏi nhóm?" data-modal-trigger="#action-confirm-modal">
                              <i class="fa-solid fa-xmark"></i>
                            </button>
                          </form>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <?php
              $maxStudents = $currentBatch['max_students'] ?? 2;
              if ($canInteract && $isLeader && count($groupMembers) < $maxStudents):
              ?>
                <div class="alert mt-4" data-variant="warning">
                  <i class="fa-solid fa-triangle-exclamation"></i>
                  <div class="alert-content">
                    <h3 class="alert-title">Thiếu thành viên</h3>
                    <p class="alert-description">Nhóm của bạn hiện tại chưa đủ số lượng thành viên (<?= count($groupMembers) ?>/<?= $maxStudents ?>). Vui lòng mời thêm thành viên để nhóm hợp lệ.</p>
                  </div>
                </div>
                <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/create") ?>" method="POST" class="mt-4 border-t pt-4">
                  <?= csrf_field() ?>
                  <p class="text-sm mb-3">Nhập MSSV của sinh viên khác để gửi lời mời.</p>
                  <div class="field">
                    <input type="text" name="partner_mssv" class="field__input" placeholder="MSSV thành viên" data-field-required>
                    <button type="submit" class="btn" data-size="md" data-variant="primary">Gửi lời mời</button>
                  </div>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div>
        <?php if ($isAllocationPublished): ?>
          <?php if ($assignedTopic): ?>
            <div class="card shadow border" style="border-color: var(--toast-success-border);">
              <div class="card__header">
                <h3 class="card__title font-semibold">Đề tài đã được phân công</h3>
                <p class="header__description">
                  Công bố lúc <?= date('H:i d/m/Y', strtotime($currentBatch['allocation_published_at'])) ?>
                </p>
              </div>
              <hr class="separator">
              <div class="card__content">
                <div class="space-y-3">
                  <div>
                    <p class="text-sm font-medium">Tên đề tài</p>
                    <div class="font-semibold">
                      <?= htmlspecialchars($assignedTopic['title']) ?>
                    </div>
                  </div>
                  <div class="space-y-2">
                    <p class="text-sm font-medium">Tài liệu mô tả đề tài</p>
                    <?php if (!empty(trim($assignedTopic['pdf_file_path'] ?? ''))) { ?>
                      <a href="<?= url('storage/' . ltrim($assignedTopic['pdf_file_path'], '/')) ?>" target="_blank" rel="noopener noreferrer" class="btn" data-variant="secondary" data-size="md">
                        <i class="fa-solid fa-file-pdf mr-1"></i> Xem tài liệu
                      </a>
                    <?php } else { ?>
                      <p class="font-semibold">Chưa có.</p>
                    <?php } ?>
                  </div>
                  <div>
                    <p class="text-sm font-medium">Giảng viên hướng dẫn</p>
                    <p class="font-semibold"><?= htmlspecialchars($assignedTopic['teacher_name']) ?></p>
                  </div>
                  <div>
                    <p class="text-sm font-medium">Số điện thoại</p>
                    <p class="font-semibold"><?= htmlspecialchars($assignedTopic['teacher_phone'] ?? 'Chưa cập nhật') ?></p>
                  </div>
                  <div>
                    <p class="text-sm font-medium">Địa chỉ Email</p>
                    <p class="font-semibold"><?= htmlspecialchars($assignedTopic['teacher_email'] ?? 'Chưa cập nhật') ?></p>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <?php if (!$group): ?>
              <div class="alert" data-variant="error">
                <i class="fa-solid fa-circle-xmark"></i>
                <div class="alert-content">
                  <h3 class="alert-title">Không có nhóm</h3>
                  <p class="alert-description">
                    Bạn chưa tham gia nhóm nào trong đợt này nên không được phân bổ đề tài.
                  </p>
                </div>
              </div>
            <?php else: ?>
              <div class="alert" data-variant="error">
                <i class="fa-solid fa-circle-xmark"></i>
                <div class="alert-content">
                  <h3 class="alert-title">Chưa được phân bổ đề tài</h3>
                  <p class="alert-description">
                    Nhóm của bạn chưa được phân công đề tài nào trong đợt này.
                    Vui lòng liên hệ Văn phòng Khoa để được hỗ trợ.
                  </p>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>
          <div class="card shadow">
            <div class="card__header">
              <h3 class="text-lg font-semibold">Nguyện vọng đề tài</h3>
            </div>
            <hr class="separator">
            <div class="card__content">

              <?php if ($isLocked): ?>
                <div class="alert mb-4 flex justify-between items-center" data-variant="success">
                  <div>
                    <i class="fa-solid fa-lock mr-2"></i> Nguyện vọng đã được chốt. Thời điểm chốt sẽ có ảnh hưởng đến ưu tiên xét duyệt.
                  </div>
                  <?php if ($canInteract && $isLeader): ?>
                    <form action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/unlock") ?>" method="POST" class="inline-block shrink-0">
                      <?= csrf_field() ?>
                      <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="md" data-confirm-msg="CẢNH BÁO: Mở khóa sẽ làm mất lợi thế thời gian của nhóm khi hệ thống xét duyệt nguyện vọng. Bạn có chắc chắn muốn mở khóa để sửa?" data-modal-trigger="#action-confirm-modal">
                        <i class="fa-solid fa-unlock"></i> Mở khóa
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <?php if (!$group): ?>
                <div class="py-8 text-center">
                  <i class="fa-solid fa-list-check text-3xl mb-3"></i>
                  <p>Bạn cần tham gia nhóm trước khi đăng ký NV.</p>
                </div>
              <?php elseif (empty($aspirations)): ?>
                <div class="py-8 text-center">
                  <i class="fa-solid fa-file-circle-question text-3xl mb-3"></i>
                  <p>Nhóm chưa đăng ký nguyện vọng nào.</p>
                  <?php if ($canInteract && $isLeader): ?>
                    <a href="<?= url("student/project_batches/{$currentBatch['id']}/topics") ?>" class="btn" data-size="sm" data-variant="primary">Đăng ký ngay</a>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <form id="form-reorder-aspirations" action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/reorder") ?>" method="POST">
                  <?= csrf_field() ?>
                  <div id="aspirations-list" class="space-y-3">
                    <?php foreach ($aspirations as $index => $aspiration): ?>
                      <div class="flex justify-between items-center p-3 border rounded-md drag-item" style="background-color: var(--card);" data-id="<?= $aspiration['topic_id'] ?>">
                        <div class="flex items-center flex-1">
                          <?php if ($canInteract && $isLeader && !$isLocked && count($aspirations) > 1): ?>
                            <i class="fa-solid fa-grip-vertical cursor-grab px-3 mr-3 handle" style="color: var(--muted-foreground);"></i>
                          <?php endif; ?>
                          <div>
                            <div class="font-medium aspiration-title line-clamp-1" title=<?= htmlspecialchars($aspiration['topic_title']) ?>>
                              <span class="mr-2 font-bold aspiration-index">#<?= $index + 1 ?></span>
                              <?= htmlspecialchars($aspiration['topic_title']) ?>
                            </div>
                            <div class="text-sm mt-1" style="color: var(--muted-foreground);">
                              GV: <?= htmlspecialchars($aspiration['teacher_name']) ?>
                            </div>
                          </div>
                          <input type="hidden" name="topic_ids[]" value="<?= $aspiration['topic_id'] ?>">
                        </div>
                        <?php if ($canInteract && $isLeader && !$isLocked): ?>
                          <div class="ml-2">
                            <button type="button" class="btn btn-remove-aspiration" data-variant="destructive" data-size="sm" title="Xóa nguyện vọng" data-topic-id="<?= $aspiration['topic_id'] ?>" data-topic-title="<?= htmlspecialchars($aspiration['topic_title']) ?>">
                              <i class="fa-solid fa-trash-can"></i>
                            </button>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <?php if ($canInteract && $isLeader && count($aspirations) > 1 && !$isLocked): ?>
                    <div class="mt-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                      <div class="text-xs" style="color: var(--muted-foreground);">
                        * Nhấn giữ <i class="fa-solid fa-grip-vertical mx-1"></i> và kéo thả để thay đổi thứ tự ưu tiên.
                      </div>
                      <button type="submit" class="btn hidden shrink-0" id="btn-save-order" data-variant="primary" data-size="sm">Lưu thứ tự mới</button>
                    </div>
                  <?php endif; ?>
                </form>

                <?php if ($canInteract && $isLeader && !$isLocked): ?>
                  <form id="form-remove-aspiration" action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/remove") ?>" method="POST" class="hidden">
                    <?= csrf_field() ?>
                    <input type="hidden" name="topic_id" id="remove_topic_id" value="">
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            <hr class="separator">
            <div class="card__footer">
              <?php if ($group): ?>
                <?php if ($canInteract && $isLeader && !empty($aspirations) && !$isLocked): ?>
                  <form action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/lock") ?>" class="w-full" method="POST">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-confirm-action w-full" data-variant="primary" data-size="md" data-confirm-msg="Sau khi chốt, bạn KHÔNG THỂ thay đổi thứ tự hoặc xóa nguyện vọng. Chốt nguyện vọng càng sớm càng có lợi khi xét duyệt nguyện vọng. Bạn có chắc chắn muốn chốt?" data-modal-trigger="#action-confirm-modal">
                      <i class="fa-solid fa-lock mr-2"></i> Chốt nguyện vọng
                    </button>
                  </form>
                <?php endif; ?>

                <a href="<?= url("student/project_batches/{$currentBatch['id']}/topics") ?>" class="btn" data-variant="secondary" data-size="md">Xem danh sách tất cả đề tài</a>
              <?php else: ?>
                <a href="<?= url("student/project_batches/{$currentBatch['id']}/topics") ?>" class="btn" data-variant="secondary" data-size="md">Xem danh sách tất cả đề tài</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Modal xác nhận thao tác -->
<div class="modal" id="action-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận thao tác</h3>
    <p class="modal__description" id="action-confirm-msg">
      Bạn có chắc chắn muốn thực hiện thao tác này?
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="md" class="btn" type="button">Hủy</button>
    <button id="action-confirm-btn" data-variant="primary" data-size="md" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<?php $layout->start("scripts") ?>
<script>
  let currentForm = null;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-confirm-action');
    if (btn) {
      currentForm = btn.closest('form');
      const msg = btn.getAttribute('data-confirm-msg') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';

      const confirmMsg = document.getElementById('action-confirm-msg');
      if (confirmMsg) confirmMsg.textContent = msg;
    }

    // Nút xóa nguyện vọng lẻ (tránh lồng form)
    const removeBtn = e.target.closest('.btn-remove-aspiration');
    if (removeBtn) {
      const topicId = removeBtn.getAttribute('data-topic-id');
      const topicTitle = removeBtn.getAttribute('data-topic-title');
      document.getElementById('remove_topic_id').value = topicId;
      currentForm = document.getElementById('form-remove-aspiration');

      const confirmMsg = document.getElementById('action-confirm-msg');
      if (confirmMsg) confirmMsg.textContent = `Bạn có chắc chắn muốn xóa nguyện vọng "${topicTitle}"?`;

      // Mở modal xác nhận
      const modal = document.getElementById('action-confirm-modal');
      if (typeof ModalHandler !== 'undefined') {
        ModalHandler.instance.open('#action-confirm-modal');
      } else {
        // Fallback if modal script not loaded yet
        modal.setAttribute('data-state', 'open');
      }
    }
  });

  const confirmBtn = document.getElementById('action-confirm-btn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      if (currentForm) {
        currentForm.submit();
      }
    });
  }

  // Khởi tạo kéo thả (DnD) cho danh sách nguyện vọng
  document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('aspirations-list');
    const saveBtn = document.getElementById('btn-save-order');

    if (list && saveBtn && window.DnD) {
      DnD.create(list, {
        handle: '.handle',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: function() {
          // Hiện nút Lưu
          saveBtn.classList.remove('hidden');
          // Cập nhật lại số thứ tự hiển thị #1, #2
          const items = list.querySelectorAll('.drag-item');
          items.forEach((item, index) => {
            const idxSpan = item.querySelector('.aspiration-index');
            if (idxSpan) {
              idxSpan.textContent = '#' + (index + 1);
            }
          });
        }
      });
    }
  });
</script>
<?php $layout->end() ?>
