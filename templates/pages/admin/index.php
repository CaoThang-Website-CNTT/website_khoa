<h2>Single select - static HTML options</h2>
<div class="demo-row">
  <div class="demo-col">
    <div class="select" data-select-id="sel-single" data-select-multiple data-select-searchable
      data-select-placeholder="Chọn framework...">
      <!-- Content container is optional; handler will auto-create if missing -->
      <div class="select__content">
        <div class="select__item" data-select-value="react">React</div>
        <div class="select__item" data-select-value="vue">Vue</div>
        <div class="select__item" data-select-value="svelte">Svelte</div>
        <div class="select__item" data-select-value="angular" data-select-disabled>Angular (disabled)</div>
        <div class="select__item" data-select-value="php" data-select-disabled>PHP</div>
      </div>
    </div>
    <span class="log-label">onChange value:</span>
    <pre id="out-single">-</pre>
  </div>
</div>

<h2>Single select - flattened options (no groups/search)</h2>
<div class="demo-row">
  <div class="demo-col" style="min-width:280px">
    <div class="select" data-select-id="sel-flat" data-select-placeholder="Chọn ngôn ngữ...">
      <div class="select__content">
        <div class="select__group">
          <div class="select__label">Frontend</div>
          <div class="select__item" data-select-value="ts">TypeScript</div>
          <div class="select__item" data-select-value="js">JavaScript</div>
        </div>
        <div class="select__group">
          <div class="select__label">Backend</div>
          <div class="select__item" data-select-value="php">PHP</div>
          <div class="select__item" data-select-value="python">Python</div>
        </div>
      </div>
    </div>
    <span class="log-label">onChange value:</span>
    <pre id="out-flat">-</pre>
  </div>
</div>

<h2>JS API - dynamic registration</h2>
<div class="demo-row">
  <div class="demo-col">
    <div class="select" data-select-id="sel-dynamic" data-select-placeholder="Chọn tỉnh/thành...">
      <div class="select__content" id="dynamic-options"></div>
    </div>
    <span class="log-label">onChange value:</span>
    <pre id="out-dynamic">-</pre>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const handler = SelectHandler.instance;

    // 1. Static single select
    const selSingle = document.querySelector('[data-select-id="sel-single"]');
    selSingle.addEventListener('select:change', e => {
      document.getElementById('out-single').textContent = e.detail.value
        ? `Selected: ${e.detail.label} (${e.detail.value})`
        : 'Cleared';
    });

    // 2. Flattened single select
    const selFlat = document.querySelector('[data-select-id="sel-flat"]');
    selFlat.addEventListener('select:change', e => {
      document.getElementById('out-flat').textContent = e.detail.value
        ? `Selected: ${e.detail.label} (${e.detail.value})`
        : 'Cleared';
    });

    // 3. Dynamic options → inject into DOM first, then register
    const dynamicContainer = document.getElementById('dynamic-options');
    const options = [
      {
        group: 'Miền Bắc', items: [
          { value: 'hn', label: 'Hà Nội' },
          { value: 'hp', label: 'Hải Phòng' },
          { value: 'qn', label: 'Quảng Ninh' }
        ]
      },
      {
        group: 'Miền Nam', items: [
          { value: 'hcm', label: 'TP. Hồ Chí Minh' },
          { value: 'bd', label: 'Bình Dương' },
          { value: 'dn', label: 'Đồng Nai' }
        ]
      }
    ];

    // Flatten & inject as standard .select__item elements
    options.forEach(group => {
      group.items.forEach(item => {
        const opt = document.createElement('div');
        opt.className = 'select__item';
        opt.dataset.selectValue = item.value;
        opt.textContent = item.label;
        dynamicContainer.appendChild(opt);
      });
    });

    const selDynamic = document.querySelector('[data-select-id="sel-dynamic"]');
    selDynamic.addEventListener('select:change', e => {
      document.getElementById('out-dynamic').textContent = e.detail.value
        ? `Selected: ${e.detail.label} (${e.detail.value})`
        : 'Cleared';
    });

    // Register with the simplified handler
    handler.register(selDynamic);
  });
</script>