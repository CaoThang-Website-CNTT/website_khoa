/**
 * Layout Collapsible Utility
 * Handles sidebar collapse for .detail-layout
 */
export default class LayoutCollapsible {
  constructor(options = {}) {
    this.containerSelector = options.containerSelector || '.detail-layout--collapsible';
    this.toggleSelector = options.toggleSelector || '.js-sidebar-toggle';
    this.storageKey = options.storageKey || 'sidebar-collapsed';
    this.defaultCollapsed = options.defaultCollapsed !== undefined ? options.defaultCollapsed : true;
    
    this.container = document.querySelector(this.containerSelector);
    this.toggleBtns = document.querySelectorAll(this.toggleSelector);
    
    if (this.container) {
      this.init();
    }
  }

  init() {
    // Restore state from localStorage
    // Use defaultCollapsed if no state is stored
    const storedState = localStorage.getItem(this.storageKey);
    const isCollapsed = storedState === null ? this.defaultCollapsed : storedState === 'true';

    if (isCollapsed) {
      this.container.classList.add('is-collapsed');
    } else {
      this.container.classList.remove('is-collapsed');
    }

    this.toggleBtns.forEach(btn => {
      btn.addEventListener('click', () => this.toggle());
    });
    
    this._updateToggle(isCollapsed);
  }

  toggle() {
    const isCollapsed = this.container.classList.toggle('is-collapsed');
    localStorage.setItem(this.storageKey, isCollapsed);
    this._updateToggle(isCollapsed);
    
    // Trigger window resize to recalculate table widths
    setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
    }, 400); // Wait for transition
  }

  _updateToggle(isCollapsed) {
    this.toggleBtns.forEach(btn => {
      // Toggle active state or icons if needed
      btn.dataset.state = isCollapsed ? 'collapsed' : 'expanded';
      
      // If the button has a title or aria-label, update it
      const title = isCollapsed ? 'Mở rộng thông tin' : 'Thu gọn thông tin';
      btn.title = title;
      btn.setAttribute('aria-label', title);
    });
  }
}
