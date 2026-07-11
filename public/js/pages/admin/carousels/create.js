  document.addEventListener("DOMContentLoaded", () => {
    const modalHandler = ModalHandler.instance;
    const mainForm = document.querySelector('#carousel-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');

    // Auto-slug normalization
    nameInput.addEventListener('input', () => {
      if (slugInput.dataset.manual) return;
      slugInput.value = nameInput.value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    });

    slugInput.addEventListener('input', () => {
      slugInput.dataset.manual = 'true';
    });

    // Submit main form
    confirmBtn.addEventListener('click', () => {
      mainForm.submit();
    });

    // ── SLIDES LOCAL STATE MANAGEMENT ──────────────────────────────────────
    let localSlides = [];
    const slidesContainer = document.querySelector('#slides-container');
    const slidesEmptyHint = document.querySelector('#slides-empty-hint');
    const slidesInputsContainer = document.querySelector('#slides-inputs-container');

    const slideModal = document.querySelector('#slide-modal');
    const slideForm = slideModal.querySelector('#slide-form');
    const slideModalTitle = slideModal.querySelector('#slide-modal-title');
    const slideUseCustomHtml = slideModal.querySelector('#slide-use-custom-html');
    const slideCustomHtmlField = slideModal.querySelector('#slide-custom-html-field');
    const slideDeleteBtn = slideModal.querySelector('#slide-delete-btn');
    const slideSaveBtn = slideModal.querySelector('#slide-save-btn');

    const slideDeleteConfirmModal = document.querySelector('#slide-delete-confirm-modal');
    const confirmDeleteBtn = slideDeleteConfirmModal.querySelector('#slide-delete-confirm-btn');

    // Toggle Custom HTML field in modal
    slideUseCustomHtml.addEventListener('change', function () {
      slideCustomHtmlField.style.display = this.checked ? 'flex' : 'none';
    });

    // Reset slide modal form
    function resetSlideForm() {
      slideForm.reset();
      slideCustomHtmlField.style.display = 'none';
      slideDeleteBtn.classList.add('hidden');
      delete slideModal.dataset.editingTempId;

      slideForm.querySelector('#slide-media-id').value = '';
      document.querySelector('#slide-media-img').hidden = true;
      document.querySelector('#slide-media-img').src = '';
      document.querySelector('#slide-media-empty').hidden = false;
    }

    // Open modal to add slide
    document.querySelector('#add-item-btn').addEventListener('click', () => {
      resetSlideForm();
      slideModalTitle.textContent = 'Thêm slide mới';
      modalHandler.open('#slide-modal');
    });

    // Render list visual DOM and hidden form inputs
    function renderSlides() {
      // 1. Clear old content (keep empty hint template ready if needed)
      const visualItems = slidesContainer.querySelectorAll('.slide-item');
      visualItems.forEach(el => el.remove());

      if (localSlides.length === 0) {
        slidesEmptyHint.style.display = 'flex';
      } else {
        slidesEmptyHint.style.display = 'none';
      }

      // 2. Clear hidden inputs
      slidesInputsContainer.innerHTML = '';

      // 3. Rebuild visual DOM and inputs
      localSlides.forEach((slide, index) => {
        // A. Visual Card
        const card = document.createElement('div');
        card.className = 'slide-item flex items-center p-2 border rounded-md gap-2 shadow-sm hover-lift';
        card.dataset.tempId = slide.tempId;
        card.setAttribute('data-dnd-draggable', '');

        const imgHtml = slide.media_url
          ? `<img class="slide-item__img" src="${AppUtils.escapeHtml(slide.media_url)}" alt="">`
          : `<div class="slide-item__img"><div class="w-full h-full flex justify-center items-center gap-1"><i class="fa-solid fa-image"></i>N/A</div></div>`;

        card.innerHTML = `
          <div class="drag-handle shrink-0 flex flex-col cursor-grab">
            <i class="fa-solid fa-grip-vertical"></i>
          </div>
          <div class="flex-1 flex gap-2 items-center">
            ${imgHtml}
            <span class="flex-1 w-full font-medium text-sm">
              ${AppUtils.escapeHtml(slide.title || '')} 
              <span class="font-bold">${AppUtils.escapeHtml(slide.title_highlight || '')}</span>
            </span>
            <div>
              <span class="badge" data-variant="${slide.is_active ? 'primary' : 'secondary'}">
                ${slide.is_active ? 'Hiển thị' : 'Đã ẩn'}
              </span>
              <button type="button" class="btn ml-2 edit-slide-btn" data-variant="outline" data-size="md">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
        `;

        // Attach edit click handler
        card.querySelector('.edit-slide-btn').addEventListener('click', () => {
          resetSlideForm();
          slideModalTitle.textContent = 'Chỉnh sửa slide';
          slideModal.dataset.editingTempId = slide.tempId;

          // Populate fields
          slideForm.querySelector('#slide-is-active').checked = Boolean(slide.is_active);
          slideForm.querySelector('#slide-title').value = slide.title || '';
          slideForm.querySelector('#slide-title-highlight').value = slide.title_highlight || '';
          slideForm.querySelector('#slide-description').value = slide.description || '';
          slideForm.querySelector('#slide-media-id').value = slide.media_id || '';
          slideForm.querySelector('#slide-cta-label').value = slide.cta_label || '';
          slideForm.querySelector('#slide-cta-variant').value = slide.cta_variant || 'primary';
          slideForm.querySelector('#slide-cta-url').value = slide.cta_url || '';

          const slideImg = document.querySelector('#slide-media-img');
          const slideEmpty = document.querySelector('#slide-media-empty');
          if (slide.media_url) {
            slideImg.src = slide.media_url;
            slideImg.alt = slide.media_alt || '';
            slideImg.hidden = false;
            slideEmpty.hidden = true;
          } else {
            slideImg.hidden = true;
            slideImg.src = '';
            slideEmpty.hidden = false;
          }

          const useCustom = Boolean(slide.use_custom_html);
          slideUseCustomHtml.checked = useCustom;
          slideCustomHtmlField.style.display = useCustom ? 'flex' : 'none';
          slideForm.querySelector('#slide-custom-html').value = slide.custom_html || '';

          slideDeleteBtn.classList.remove('hidden');
          modalHandler.open('#slide-modal');
        });

        slidesContainer.appendChild(card);

        const slideFields = [
          'title', 'title_highlight', 'description', 'media_id',
          'cta_label', 'cta_variant', 'cta_url', 'custom_html'
        ];

        slideFields.forEach(field => {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = `slides[${index}][${field}]`;
          inp.value = slide[field] || '';
          slidesInputsContainer.appendChild(inp);
        });

        // Booleans
        const activeInp = document.createElement('input');
        activeInp.type = 'hidden';
        activeInp.name = `slides[${index}][is_active]`;
        activeInp.value = slide.is_active ? '1' : '0';
        slidesInputsContainer.appendChild(activeInp);

        const customHtmlInp = document.createElement('input');
        customHtmlInp.type = 'hidden';
        customHtmlInp.name = `slides[${index}][use_custom_html]`;
        customHtmlInp.value = slide.use_custom_html ? '1' : '0';
        slidesInputsContainer.appendChild(customHtmlInp);

        // sort_order
        const sortInp = document.createElement('input');
        sortInp.type = 'hidden';
        sortInp.name = `slides[${index}][sort_order]`;
        sortInp.value = String(index + 1);
        slidesInputsContainer.appendChild(sortInp);
      });
    }

    // Modal Save Button Handler
    slideSaveBtn.addEventListener('click', () => {
      if (!slideForm.reportValidity()) return;

      const slideData = {
        is_active: slideForm.querySelector('#slide-is-active').checked ? 1 : 0,
        title: slideForm.querySelector('#slide-title').value.trim(),
        title_highlight: slideForm.querySelector('#slide-title-highlight').value.trim(),
        description: slideForm.querySelector('#slide-description').value.trim(),
        media_id: slideForm.querySelector('#slide-media-id').value,
        media_url: document.querySelector('#slide-media-img').src,
        media_alt: document.querySelector('#slide-media-img').alt,
        cta_label: slideForm.querySelector('#slide-cta-label').value.trim(),
        cta_variant: slideForm.querySelector('#slide-cta-variant').value,
        cta_url: slideForm.querySelector('#slide-cta-url').value.trim(),
        use_custom_html: slideUseCustomHtml.checked ? 1 : 0,
        custom_html: slideForm.querySelector('#slide-custom-html').value.trim()
      };

      const editingTempId = slideModal.dataset.editingTempId;
      if (editingTempId) {
        // Edit existing
        const idx = localSlides.findIndex(s => s.tempId === editingTempId);
        if (idx !== -1) {
          localSlides[idx] = { ...localSlides[idx], ...slideData };
        }
      } else {
        // Add new
        slideData.tempId = 'slide_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localSlides.push(slideData);
      }

      renderSlides();
      modalHandler.close('#slide-modal');
    });

    // Modal Delete Button Handler
    slideDeleteBtn.addEventListener('click', () => {
      const editingTempId = slideModal.dataset.editingTempId;
      if (!editingTempId) return;

      confirmDeleteBtn.onclick = () => {
        localSlides = localSlides.filter(s => s.tempId !== editingTempId);
        renderSlides();
        modalHandler.close('#slide-delete-confirm-modal');
        modalHandler.close('#slide-modal');
      };
      modalHandler.open('#slide-delete-confirm-modal');
    });

    // Media Selector Modal Handler
    document.querySelector('#media-selector-modal')?.addEventListener('msm:submit', (e) => {
      const { media, close } = e.detail;

      // Cập nhật hidden input
      slideForm.querySelector('#slide-media-id').value = media.id;

      // Cập nhật thumbnail preview
      const slideImg = document.querySelector('#slide-media-img');
      const slideEmpty = document.querySelector('#slide-media-empty');
      let mediaPath = String(media.file_path || '').replace(/\\/g, '/').replace(/^\/+/, '');
      if (mediaPath.startsWith('public/media/')) mediaPath = mediaPath.slice('public/media/'.length);
      if (mediaPath.startsWith('media/')) mediaPath = mediaPath.slice('media/'.length);
      const mediaUrl = `${(window.__carouselCreate__?.mediaBaseUrl || '').replace(/\/$/, '')}/${mediaPath}`;

      slideImg.src = mediaUrl;
      slideImg.alt = media.alt_text || '';
      slideImg.hidden = false;
      slideEmpty.hidden = true;

      close();
    });

    if (slidesContainer) {
      new DnD(slidesContainer, {
        animation: 150,
        group: "slides",
        handle: '.drag-handle',
        ghostClass: 'dnd-ghost',
        chosenClass: 'dnd-chosen',
        onEnd: () => {
          const reorderedTempIds = Array.from(slidesContainer.querySelectorAll('.slide-item')).map(el => el.dataset.tempId);

          const sortedSlides = [];
          reorderedTempIds.forEach(tempId => {
            const found = localSlides.find(s => s.tempId === tempId);
            if (found) sortedSlides.push(found);
          });

          localSlides = sortedSlides;
          renderSlides();
        }
      });
    }
  });
