document.addEventListener('DOMContentLoaded', () => {
  const accordion = document.querySelector('[data-program-accordion]');
  if (!accordion) return;

  const items = [...accordion.querySelectorAll('[data-program]')];
  const params = new URLSearchParams(window.location.search);
  const initialProgram = items.find(item => item.dataset.program === params.get('program')) || items[0];
  if (initialProgram) {
    accordion.dataset.accordionDefaultValue = initialProgram.dataset.program;
    AccordionHandler.instance.setValue(accordion, initialProgram.dataset.program, { emit: false });
  }

  let syncingHistory = false;
  accordion.addEventListener('accordion:change', event => {
    const value = event.detail?.value;
    if (!value || syncingHistory) return;

    const next = new URL(window.location.href);
    next.searchParams.set('program', value);
    history.pushState({ program: value }, '', next);
  });

  const syncProgramFromUrl = () => {
    const current = new URLSearchParams(window.location.search);
    const target = items.find(item => item.dataset.program === current.get('program')) || items[0];
    if (!target || target.dataset.state === 'open') return;

    syncingHistory = true;
    AccordionHandler.instance.setValue(accordion, target.dataset.program, { emit: false });
    syncingHistory = false;
  };

  document.querySelectorAll('[data-semester-tabs]').forEach((tabs) => {
    const triggers = [...tabs.querySelectorAll('[data-semester-trigger]')];
    const panels = [...tabs.querySelectorAll('[data-semester-panel]')];
    const activate = (key, updateHistory = true) => {
      if (!triggers.some((trigger) => trigger.dataset.semesterTrigger === key)) key = triggers[0]?.dataset.semesterTrigger;
      triggers.forEach((trigger) => {
        const active = trigger.dataset.semesterTrigger === key;
        trigger.setAttribute('aria-selected', active ? 'true' : 'false');
        trigger.tabIndex = active ? 0 : -1;
      });
      panels.forEach((panel) => { panel.hidden = panel.dataset.semesterPanel !== key; });
      if (updateHistory && key) {
        const next = new URL(window.location.href);
        next.searchParams.set('semester', key);
        history.pushState({ semester: key }, '', next);
      }
    };
    triggers.forEach((trigger, index) => {
      trigger.addEventListener('click', () => activate(trigger.dataset.semesterTrigger));
      trigger.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        let nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? triggers.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + triggers.length) % triggers.length;
        triggers[nextIndex]?.focus();
        activate(triggers[nextIndex]?.dataset.semesterTrigger);
      });
    });
    activate(params.get('semester') || triggers[0]?.dataset.semesterTrigger, false);
    window.addEventListener('popstate', () => {
      const current = new URLSearchParams(window.location.search);
      activate(current.get('semester') || triggers[0]?.dataset.semesterTrigger, false);
    });
  });

  window.addEventListener('popstate', syncProgramFromUrl);
});
