document.addEventListener('DOMContentLoaded', () => {
  const accordion = document.querySelector('[data-program-accordion]');
  if (!accordion) return;

  const items = [...accordion.querySelectorAll('[data-program]')];
  const params = new URLSearchParams(window.location.search);

  const setProgram = (key, { updateHistory = true } = {}) => {
    const target = items.find((item) => item.dataset.program === key) || items[0];
    if (!target) return;

    items.forEach((item) => {
      const open = item === target;
      const trigger = item.querySelector('[data-program-trigger]');
      const panel = item.querySelector('.education-accordion__panel');
      trigger?.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (panel) panel.hidden = !open;
      item.dataset.state = open ? 'open' : 'closed';
    });

    if (updateHistory) {
      const next = new URL(window.location.href);
      next.searchParams.set('program', target.dataset.program);
      history.pushState({ program: target.dataset.program }, '', next);
    }
  };

  items.forEach((item) => item.querySelector('[data-program-trigger]')?.addEventListener('click', () => {
    setProgram(item.dataset.program);
  }));

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

  setProgram(params.get('program') || items[0]?.dataset.program, { updateHistory: false });
  window.addEventListener('popstate', () => {
    const current = new URLSearchParams(window.location.search);
    setProgram(current.get('program') || items[0]?.dataset.program, { updateHistory: false });
  });
});
