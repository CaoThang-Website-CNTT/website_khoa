  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('onboard-form');
    if (!form) return;

    const progressRoot = document.getElementById('onboard-step-wizard');
    const panels = Array.from(document.querySelectorAll('[data-step-wizard-panel]'));
    const btnBack = document.getElementById('onboard-back');
    const btnNext = document.getElementById('onboard-next');
    const btnSubmit = document.getElementById('onboard-submit');
    const steps = window.__onboardSteps__ || [];

    function visibleInputs(root) {
      return Array.from(root.querySelectorAll('input, select, textarea')).filter(el => {
        if (el.disabled || el.type === 'hidden' || el.hasAttribute('readonly')) return false;
        return true;
      });
    }

    function validateStep(stepEl) {
      const list = visibleInputs(stepEl);
      return list.every((input) => {
        if (typeof input.checkValidity === 'function' && !input.checkValidity()) {
          input.reportValidity();
          return false;
        }
        return true;
      });
    }

    function fillReview() {
      form.querySelectorAll('[data-review]').forEach(span => {
        const name = span.getAttribute('data-review');
        const input = form.querySelector(`[name="${name}"]`);
        if (!input) return;

        const radioGroup = input.closest('.radio-group');
        if (radioGroup) {
          const checkedBtn = radioGroup.querySelector('button[data-state="checked"]');
          if (checkedBtn) {
            const label = checkedBtn.closest('label') || checkedBtn.parentElement;
            span.textContent = label.textContent.trim();
          } else {
            span.textContent = '-';
          }
          return;
        }

        if (input.tagName === 'SELECT') {
          const selectedOption = input.options[input.selectedIndex];
          span.textContent = selectedOption ? selectedOption.textContent.trim() : '-';
          return;
        }
        span.textContent = input.value.trim() || '-';
      });
    }

    // Khởi tạo StepWizard
    const wizard = new StepWizard({
      root: progressRoot,
      panels,
      initialIndex: 0,
      beforeChange: (nextIndex, currentIndex) => {
        if (nextIndex > currentIndex) {
          return validateStep(panels[currentIndex]);
        }

        return true;
      }
    });

    wizard.onChange((idx, total) => {
      btnBack.style.display = idx === 0 ? "none" : "block";
      btnNext.style.display = idx === total - 1 ? "none" : "block";
      btnSubmit.style.display = idx !== total - 1 ? "none" : "block";

      if (idx === total - 1) {
        fillReview();
      }
    });

    btnNext.addEventListener('click', function () {
      wizard.next();
    });

    btnBack.addEventListener('click', function () {
      wizard.back();
    });

    wizard.renderProgress(progressRoot, steps).init();
  });
