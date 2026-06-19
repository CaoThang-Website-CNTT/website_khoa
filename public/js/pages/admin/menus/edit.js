  document.addEventListener("DOMContentLoaded", () => {
    // ── BIẾN DÙNG CHUNG CỦA MODAL ──────────────────────────────────────────
    const itemModal = document.querySelector('#item-modal');
    const itemModalTitle = itemModal?.querySelector('#item-modal-title');
    const itemModalDescription = itemModal?.querySelector('#item-modal-description');
    const itemForm = itemModal?.querySelector('#item-form');
    const itemDeleteBtn = itemModal?.querySelector('#item-delete-btn');

    // Hàm reset form
    function resetItemForm() {
      if (itemForm) itemForm.reset();
      if (itemDeleteBtn) itemDeleteBtn.classList.add('hidden');
    }

    document.querySelectorAll('.edit-item-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        resetItemForm();
        const item = JSON.parse(btn.dataset.item);

        if (itemModalTitle) {
          itemModalTitle.textContent = window.__menuEdit__?.editTitle || '';
        }
        if (itemModalDescription) {
          itemModalDescription.textContent = window.__menuEdit__?.editDescription || '';
        }

        if (itemForm) {
          itemForm.querySelector('#item-label').value = item.label || '';
          itemForm.querySelector('#item-url').value = item.url || '';
          itemForm.querySelector('#item-sort-order').value = item.sort_order || 0;

          // Xử lý mục cha tĩnh cho trường hợp read-only
          const parentSelect = itemForm.querySelector('#item-parent-id');
          if (parentSelect) {
            parentSelect.innerHTML = '';

            // Tìm nhãn mục cha trực tiếp từ cấu trúc DOM ngoài danh sách
            let parentLabel = '-- Không có (mục gốc) --';
            if (item.parent_id) {
              const parentEl = document.querySelector(`.menu-item[data-id="${item.parent_id}"]`);
              parentLabel = parentEl?.querySelector('.item-label')?.textContent.trim() || `Mục cha (ID: ${item.parent_id})`;
            }

            const opt = document.createElement('option');
            opt.value = item.parent_id || '';
            opt.textContent = parentLabel;
            parentSelect.appendChild(opt);
            parentSelect.value = item.parent_id || '';
          }
        }
      });
    });

    if (window.__menuEdit__?.isEditable) {
      // ── CHỈ CHẠY LOGIC EDIT / CUD KHI EDITABLE ──────────────────────────────

      function handleDragEnd() {
        const rootContainer = document.querySelector('#menu-items-root');
        const serializedData = serializeMenuTree(rootContainer, null);

        const reorderInput = document.querySelector('#menu-reorder-input');
        if (reorderInput) {
          reorderInput.value = JSON.stringify(serializedData);
        }

        updateMenuDOMStates(serializedData);
      }

      document.querySelectorAll('.menu-item-children, #menu-items-root').forEach(container => {
        new DnD(container, {
          animation: 150,
          group: "menu-items",
          handle: '.drag-handle',
          draggable: '.menu-item',
          ghostClass: 'dnd-ghost',
          chosenClass: 'dnd-chosen',
          onEnd: handleDragEnd
        });
      });

      // Trích xuất cây DOM đệ quy thành mảng cấu trúc phẳng
      function serializeMenuTree(container, parentId = null) {
        const serialized = [];
        const childItems = container.querySelectorAll(':scope > .menu-item');

        childItems.forEach((el, index) => {
          const id = parseInt(el.dataset.id);
          serialized.push({
            id: id,
            parent_id: parentId,
            sort_order: index
          });

          const subContainer = el.querySelector('.menu-item-children');
          if (subContainer) {
            serialized.push(...serializeMenuTree(subContainer, id));
          }
        });

        return serialized;
      }

      // Đồng bộ hoá trạng thái data attributes trong DOM sau khi kéo thả thành công
      function updateMenuDOMStates(items) {
        items.forEach(item => {
          const itemEl = document.querySelector(`.menu-item[data-id="${item.id}"]`);
          if (itemEl) {
            const editBtn = itemEl.querySelector('.edit-item-btn');
            if (editBtn) {
              const currentData = JSON.parse(editBtn.dataset.item || '{}');
              currentData.parent_id = item.parent_id;
              currentData.sort_order = item.sort_order;
              editBtn.dataset.item = JSON.stringify(currentData);
            }
          }
        });

        updateMenuItemsListFromDOM();
      }

      // Dialog & Modal Crud Handling
      let menuItemsList = [];

      // Tự động thu thập toàn bộ menu item hiện hữu từ DOM
      function updateMenuItemsListFromDOM() {
        menuItemsList = [];
        document.querySelectorAll('.menu-item').forEach(el => {
          const id = parseInt(el.dataset.id);
          const label = el.querySelector('.item-label')?.textContent.trim() || `ID: ${id}`;
          menuItemsList.push({ id, label });
        });
      }

      const editForm = document.querySelector('#menu-edit-form');
      const confirmModal = document.querySelector('#confirm-modal');
      const confirmBtn = confirmModal?.querySelector('#confirm-modal-btn');
      confirmBtn?.addEventListener('click', () => editForm.submit());

      const itemAddBtn = document.querySelector('#add-item-btn');
      const itemSaveBtn = itemModal?.querySelector('#item-save-btn');
      const itemDeleteForm = document.querySelector('#item-delete-form');
      const itemDeleteConfirmModal = document.querySelector('#item-delete-confirm-modal');
      const confirmDeleteBtn = itemDeleteConfirmModal?.querySelector('#item-delete-confirm-btn');

      // Dựng dropdown parent dynamic
      function populateParentSelect(excludeId = null) {
        const parentSelect = itemForm.querySelector('#item-parent-id');
        parentSelect.innerHTML = '<option value="">-- Không có (mục gốc) --</option>';
        menuItemsList.forEach(item => {
          if (excludeId && parseInt(item.id) === parseInt(excludeId)) return;
          const opt = document.createElement('option');
          opt.value = item.id;
          opt.textContent = item.label;
          parentSelect.appendChild(opt);
        });
      }

      // Thêm item mới
      if (itemAddBtn) {
        itemAddBtn.addEventListener('click', () => {
          resetItemForm();
          itemModalTitle.textContent = 'Thêm mục mới';
          itemForm.action = window.__menuEdit__?.itemCreateUrl || '';
          populateParentSelect();
        });
      }

      // Sửa/Xem item cho chế độ Editable (Được phép sửa action và gán nút xóa)
      document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const item = JSON.parse(btn.dataset.item);
          itemForm.action = `${window.__menuEdit__?.itemBaseUrl || ''}${item.id}`;
          populateParentSelect(item.id);

          // Cập nhật giá trị cha
          itemForm.querySelector('#item-parent-id').value = item.parent_id || '';

          if (itemDeleteBtn) {
            itemDeleteBtn.classList.remove('hidden');
            itemDeleteBtn.onclick = () => {
              if (confirmDeleteBtn) {
                confirmDeleteBtn.onclick = () => {
                  itemDeleteForm.action = `${window.__menuEdit__?.itemBaseUrl || ''}${item.id}/delete`;
                  itemDeleteForm.submit();
                };
              }
              modalHandler.open('#item-delete-confirm-modal');
            };
          }
        });
      });

      if (itemSaveBtn) {
        itemSaveBtn.addEventListener('click', () => {
          if (itemForm.reportValidity()) {
            itemForm.submit();
          }
        });
      }

      updateMenuItemsListFromDOM();
    } else {
      // Chế độ chỉ xem: Ẩn các nút kéo thả (drag handles)
      document.querySelectorAll('.drag-handle').forEach(el => el.style.display = 'none');
    }
  });
