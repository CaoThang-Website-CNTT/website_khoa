  document.addEventListener('DOMContentLoaded', () => {
    const zone = document.querySelector('[data-mu-zone]');
    const titleInput = document.querySelector('#title');
    const form = document.querySelector('#media-form');
    const confirmBtn = document.querySelector('#confirm-btn');

    // Auto-fill tiêu đề từ tên file nếu input đang rỗng
    zone.addEventListener('mu:file-selected', (e) => {
      const { name } = e.detail;
      if (!titleInput.value.trim()) {
        titleInput.value = name.replace(/\.[^.]+$/, '');
      }
    });

    // Reset tiêu đề khi file bị xóa (chỉ nếu nó vẫn là tên file tự fill)
    zone.addEventListener('mu:file-removed', () => {
      // Không reset - người dùng có thể đã chỉnh tay
    });

    zone.addEventListener('mu:error', (e) => {
      const { reason, maxBytes } = e.detail;
      if (reason === 'type') alert('Định dạng file không được hỗ trợ.');
      if (reason === 'size') alert(`File vượt quá dung lượng cho phép (${Math.round(maxBytes / 1024 / 1024)} MB).`);
    });

    confirmBtn.addEventListener('click', () => form.submit());
  });
