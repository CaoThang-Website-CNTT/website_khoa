import { TableManager } from './table_manager.js';

// Khởi tạo tự động khi trang load xong
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => TableManager.init());
} else {
  TableManager.init();
}

export { TableManager };
