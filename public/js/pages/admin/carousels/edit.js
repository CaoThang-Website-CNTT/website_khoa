  document.addEventListener("DOMContentLoaded", () => {
    const modalHandler = ModalHandler.instance;
    const editForm = document.querySelector('#carousel-edit-form');
    const confirmModal = document.querySelector('#confirm-modal');
    const confirmBtn = confirmModal?.querySelector('#confirm-modal-btn');
    if (confirmBtn) confirmBtn.addEventListener('click', () => editForm.submit());

    const deleteForm = document.querySelector('#carousel-delete-form');
    const deleteModal = document.querySelector('#delete-modal');
    const deleteConfirmBtn = deleteModal?.querySelector('#delete-confirm-btn');
    if (deleteConfirmBtn) deleteConfirmBtn.addEventListener('click', () => deleteForm.submit());

    const slidesContainer = document.querySelector('#slides-container');

    if (slidesContainer) {
      new DnD(slidesContainer, {
        animation: 150,
        group: "slides",
        handle: '.drag-handle',
        ghostClass: 'dnd-ghost',
        chosenClass: 'dnd-chosen',
        onEnd: e => {
          const ordered = Array.from(slidesContainer.querySelectorAll('.slide-item')).map(el => el.dataset.id); // array of slide IDs
          const reorderInput = document.querySelector('#carousel-reorder-input');
          if (reorderInput) {
            reorderInput.value = JSON.stringify(ordered);
          }
        }
      });
    }

    // Slide Modal Logic
    const slideModal = document.querySelector('#slide-modal');
    const slideForm = slideModal?.querySelector('#slide-form');
    const slideModalTitle = slideModal?.querySelector('#slide-modal-title');
    const slideCustomHtmlField = slideForm?.querySelector('#slide-custom-html-field');
    const slideUseCustomHtml = slideForm?.querySelector('#slide-use-custom-html');
    const slideDeleteBtn = slideModal?.querySelector('#slide-delete-btn');
    const slideDeleteForm = document.querySelector('#slide-delete-form');

    const slideAddBtn = document.querySelector('#add-item-btn');
    const slideDeleteConfirmModal = document.querySelector('#slide-delete-confirm-modal');
    const slideSaveBtn = slideModal?.querySelector('#slide-save-btn');
    const confirmDeleteBtn = slideDeleteConfirmModal?.querySelector('#slide-delete-confirm-btn');

    slideUseCustomHtml.addEventListener('change', function() {
      slideCustomHtmlField.style.display = this.checked ? 'flex' : 'none';
    });

    function resetSlideForm() {
      slideForm.reset();
      slideCustomHtmlField.style.display = 'none';
      slideDeleteBtn.classList.add('hidden');

      slideForm.querySelector('#slide-media-id').value = '';
      document.querySelector('#slide-media-img').hidden = true;
      document.querySelector('#slide-media-img').src = '';
      document.querySelector('#slide-media-empty').hidden = false;
    }

    // Thêm slide mới
    if (slideAddBtn) {
      slideAddBtn.addEventListener('click', () => {
        resetSlideForm();
        slideModalTitle.textContent = 'Thêm slide mới';
        slideForm.action = window.__carouselEdit__?.slideCreateUrl || '';
        slideForm.querySelector('#slide-is-active').checked = true;
      });
    }

    // Sửa/Xem slide
    document.querySelectorAll('.edit-slide-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        resetSlideForm();
        const slide = JSON.parse(btn.dataset.slide);

        slideModalTitle.textContent = 'Chỉnh sửa slide';
        slideForm.action = `${window.__carouselEdit__?.slideBaseUrl || ''}${slide.id}`;

        slideForm.querySelector('#slide-is-active').checked = Boolean(slide.is_active);
        slideForm.querySelector('#slide-title').value = slide.title || '';
        slideForm.querySelector('#slide-title-highlight').value = slide.title_highlight || '';
        slideForm.querySelector('#slide-description').value = slide.description || '';
        slideForm.querySelector('#slide-media-id').value = slide.media_id || '';
        slideForm.querySelector('#slide-cta-label').value = slide.cta_label || '';
        slideForm.querySelector('#slide-cta-variant').value = slide.cta_variant || 'primary';
        slideForm.querySelector('#slide-cta-url').value = slide.cta_url || '';

        const slideImg   = document.querySelector('#slide-media-img');
        const slideEmpty = document.querySelector('#slide-media-empty');
        if (slide.media_url) {
          slideImg.src    = slide.media_url;
          slideImg.alt    = slide.media_alt || '';
          slideImg.hidden = false;
          slideEmpty.hidden = true;
        } else {
          slideImg.hidden   = true;
          slideImg.src      = '';
          slideEmpty.hidden = false;
        }
        
        const useCustom = Boolean(slide.use_custom_html);
        slideUseCustomHtml.checked = useCustom;
        slideCustomHtmlField.style.display = useCustom ? 'flex' : 'none';
        slideForm.querySelector('#slide-custom-html').value = slide.custom_html || '';

        if (slideDeleteBtn) {
          slideDeleteBtn.classList.remove('hidden');
          slideDeleteBtn.onclick = () => {
            if (confirmDeleteBtn) {
              confirmDeleteBtn.onclick = () => {
                slideDeleteForm.action = `${window.__carouselEdit__?.slideBaseUrl || ''}${slide.id}/delete`;
                slideDeleteForm.submit();
              };
            }
            modalHandler.open('#slide-delete-confirm-modal');
          };
        }
      });
    });

    document.querySelector('#media-selector-modal')?.addEventListener('msm:submit', (e) => {
      const { media, close } = e.detail;

      // Cập nhật hidden input
      slideForm.querySelector('#slide-media-id').value = media.id;

      // Cập nhật thumbnail preview
      const slideImg   = document.querySelector('#slide-media-img');
      const slideEmpty = document.querySelector('#slide-media-empty');
      let mediaPath = String(media.file_path || '').replace(/\\/g, '/').replace(/^\/+/, '');
      if (mediaPath.startsWith('public/media/')) mediaPath = mediaPath.slice('public/media/'.length);
      if (mediaPath.startsWith('media/')) mediaPath = mediaPath.slice('media/'.length);
      const mediaUrl   = `${(window.__carouselEdit__?.mediaBaseUrl || '').replace(/\/$/, '')}/${mediaPath}`;

      slideImg.src      = mediaUrl;
      slideImg.alt      = media.alt_text || '';
      slideImg.hidden   = false;
      slideEmpty.hidden = true;

      close();
    });

    if (slideSaveBtn) {
      slideSaveBtn.addEventListener('click', () => {
        if (slideForm.reportValidity()) {
          slideForm.submit();
        }
      });
    }
  });
