class StepWizard {
  constructor(options = {}) {
    this.root = options.root ?? null;
    this.panels = Array.from(options.panels ?? []);
    this.initialIndex = Number(options.initialIndex ?? 0);
    this.beforeChange = options.beforeChange ?? null;
    this.currentIndex = 0;
    this.maxUnlockedIndex = 0;
    this.steps = [];
    this.triggers = [];
    this.progressBar = null;
    this.changeCallbacks = [];
  }

  init() {
    this.currentIndex = this.normalizeIndex(this.initialIndex);
    this.maxUnlockedIndex = Math.max(this.maxUnlockedIndex, this.currentIndex);
    this.sync();
    return this;
  }

  renderProgress(root = this.root, steps = this.steps) {
    if (!root) return this;

    this.root = root;
    this.steps = Array.from(steps);
    this.root.classList.add("step-wizard");
    this.root.innerHTML = "";

    const list = document.createElement("div");
    list.className = "step-wizard__list";
    list.setAttribute("role", "tablist");
    list.setAttribute("aria-label", this.root.dataset.stepWizardLabel || "Step progress");

    this.steps.forEach((step, index) => {
      const item = document.createElement("div");
      item.className = "step-wizard__step";

      const trigger = document.createElement("button");
      trigger.type = "button";
      trigger.className = "step-wizard__trigger";
      trigger.dataset.stepWizardTrigger = String(index);
      trigger.textContent = step.number ?? String(index + 1);
      trigger.addEventListener("click", () => this.goTo(index));

      const label = document.createElement("span");
      label.className = "step-wizard__label";
      label.textContent = step.label ?? "";

      item.append(trigger, label);
      list.append(item);
    });

    const track = document.createElement("div");
    track.className = "step-wizard__track separator";

    const bar = document.createElement("div");
    bar.className = "step-wizard__bar";
    track.append(bar);
    list.append(track);
    this.root.append(list);

    this.triggers = Array.from(this.root.querySelectorAll("[data-step-wizard-trigger]"));
    this.progressBar = bar;
    this.sync();
    return this;
  }

  next() {
    return this.goTo(this.currentIndex + 1);
  }

  back() {
    return this.goTo(this.currentIndex - 1);
  }

  goTo(index) {
    const nextIndex = this.normalizeIndex(index);
    const currentIndex = this.currentIndex;

    if (nextIndex === currentIndex) return true;
    if (nextIndex > this.maxUnlockedIndex + 1) return false;
    if (typeof this.beforeChange === "function" && this.beforeChange(nextIndex, currentIndex, this) === false) {
      return false;
    }

    this.currentIndex = nextIndex;
    this.maxUnlockedIndex = Math.max(this.maxUnlockedIndex, nextIndex);
    this.sync();
    return true;
  }

  getCurrentIndex() {
    return this.currentIndex;
  }

  onChange(callback) {
    if (typeof callback === "function") {
      this.changeCallbacks.push(callback);
    }

    return this;
  }

  sync() {
    const total = this.getTotal();

    this.panels.forEach((panel, index) => {
      const isActive = index === this.currentIndex;
      panel.dataset.stepWizardPanelState = isActive ? "active" : "idle";
      panel.hidden = !isActive;
    });

    this.triggers.forEach((trigger, index) => {
      const state = index < this.currentIndex ? "passed" : index === this.currentIndex ? "active" : "idle";
      const isLocked = index > this.maxUnlockedIndex + 1;
      trigger.dataset.stepWizardState = state;
      trigger.disabled = isLocked;
      trigger.setAttribute("aria-selected", state === "active" ? "true" : "false");
      trigger.setAttribute("tabindex", state === "active" ? "0" : "-1");
    });

    if (this.progressBar && total > 0) {
      this.progressBar.style.width = `${this.getProgressPercent()}%`;
    }

    this.changeCallbacks.forEach(callback => callback(this.currentIndex, total, this));
  }

  getProgressPercent() {
    const total = this.getTotal();
    if (total <= 0) return 0;
    return ((this.currentIndex + 0.5) / total) * 100;
  }

  getTotal() {
    return this.panels.length || this.steps.length;
  }

  normalizeIndex(index) {
    const total = this.getTotal();
    if (total <= 0) return 0;
    const parsed = Number.parseInt(index, 10);
    if (Number.isNaN(parsed)) return 0;
    return Math.min(Math.max(parsed, 0), total - 1);
  }
}

window.StepWizard = StepWizard;
