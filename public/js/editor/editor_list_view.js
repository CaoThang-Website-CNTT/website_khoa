export class EditorListView {
  #bus;
  #canvas;
  #containerEl;

  constructor(bus, canvas) {
    this.#bus = bus;
    this.#canvas = canvas;
    this.#containerEl = document.querySelector('#be-list-view-panel');

    if (this.#containerEl) {
      this.#initEvents();
      this.render();
    }
  }

  #initEvents() {
    this.#bus.subscribe('block:added', () => this.render());
    this.#bus.subscribe('block:removed', () => this.render());
    this.#bus.subscribe('block:reordered', () => this.render());

    this.#bus.subscribe('block:selected', ({ blockId }) => {
      this.highlightItem(blockId);
    });

    this.#containerEl.addEventListener('click', (e) => {
      const item = e.target.closest('.be-list-item');
      if (!item) return;

      const blockId = item.dataset.id;

      // Dispatch sự kiện chọn block (Canvas sẽ hứng và focus)
      this.#bus.dispatch('block:selected', { blockId });

      // Cuộn màn hình Canvas tới block đó
      const block = this.#canvas.getBlock(blockId);
      if (block && block.dom) {
        block.dom.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  render() {
    if (!this.#containerEl) return;

    const blocks = this.#canvas.getBlocks();
    this.#containerEl.innerHTML = '';

    const ul = document.createElement('ul');
    ul.className = 'be-list-view-tree';

    blocks.forEach(block => {
      console.log(block);
      const li = document.createElement('li');
      li.className = 'be-list-view__item';
      li.dataset.id = block.id;

      const icon = block.schema.icon;

      const previewText = block.schema.title;

      li.innerHTML = `
        <span class="be-list-view__icon">${icon}</span>
        <span class="be-list-view__title">${previewText}</span>
      `;

      ul.appendChild(li);
    });

    this.#containerEl.appendChild(ul);
  }

  highlightItem(blockId) {
    // Xóa active cũ
    const currentActive = this.#containerEl.querySelector('.be-list-item.is-active');
    if (currentActive) currentActive.classList.remove('is-active');

    // Thêm active mới
    const newItem = this.#containerEl.querySelector(`.be-list-item[data-id="${blockId}"]`);
    if (newItem) newItem.classList.add('is-active');
  }
}