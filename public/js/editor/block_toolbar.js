/**
 * block_toolbar.js
 * ================
 * Thanh công cụ thêm block mới.
 * 
 * Render menu dropdown từ BlockRegistry.getAll().
 * Khi user chọn một type → gọi blockList.add(type).
 * 
 * Toolbar tự động cập nhật nếu Registry thay đổi
 * (hiện tại render một lần khi init, đủ cho use case này).
 * 
 * Public API:
 *   toolbar.el   — DOM node của nút trigger (để append vào layout)
 *   toolbar.destroy()
 */

/**
 * createBlockToolbar(blockList)
 * 
 * @param {ReturnType<createBlockList>} blockList  Instance của BlockList
 */
function createBlockToolbar(blockList) {
  // ── Build DOM ──────────────────────────────────────────────────────────────
  const wrapper = document.createElement('div');
  wrapper.className = 'block-toolbar';

  const trigger = document.createElement('button');
  trigger.type      = 'button';
  trigger.className = 'btn block-toolbar__trigger';
  trigger.dataset.variant = 'outline';
  trigger.dataset.size    = 'lg';
  trigger.innerHTML = `<i class="fa-solid fa-plus"></i> Thêm block`;

  const dropdown = document.createElement('div');
  dropdown.className    = 'block-toolbar__dropdown';
  dropdown.style.display = 'none';
  dropdown.setAttribute('role', 'menu');

  // Render menu items từ Registry
  const registry = BlockRegistry.getAll();
  for (const [type, descriptor] of Object.entries(registry)) {
    const item = document.createElement('button');
    item.type      = 'button';
    item.className = 'block-toolbar__item';
    item.setAttribute('role', 'menuitem');
    item.innerHTML = `
      <span class="block-toolbar__item-icon">${descriptor.icon}</span>
      <span class="block-toolbar__item-label">${descriptor.label}</span>
    `;
    item.addEventListener('click', () => {
      blockList.add(type);
      _closeDropdown();
    });
    dropdown.appendChild(item);
  }

  wrapper.appendChild(trigger);
  wrapper.appendChild(dropdown);

  // ── Dropdown toggle ────────────────────────────────────────────────────────
  let _isOpen = false;

  function _openDropdown() {
    dropdown.style.display = 'block';
    _isOpen = true;
    // Đóng khi click ra ngoài
    setTimeout(() => document.addEventListener('click', _onClickOutside), 0);
  }

  function _closeDropdown() {
    dropdown.style.display = 'none';
    _isOpen = false;
    document.removeEventListener('click', _onClickOutside);
  }

  function _onClickOutside(e) {
    if (!wrapper.contains(e.target)) _closeDropdown();
  }

  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    _isOpen ? _closeDropdown() : _openDropdown();
  });

  // Đóng khi nhấn Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && _isOpen) _closeDropdown();
  });

  return {
    el: wrapper,
    destroy() {
      document.removeEventListener('click', _onClickOutside);
      wrapper.remove();
    },
  };
}
