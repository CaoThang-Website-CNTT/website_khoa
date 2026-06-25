export class EditorHistory {
  #bus;
  #limit;
  #past = [];
  #future = [];
  #current = [];
  #currentKey = '[]';

  constructor(bus, { limit = 100 } = {}) {
    this.#bus = bus;
    this.#limit = limit;
  }

  reset(snapshot = []) {
    this.#past = [];
    this.#future = [];
    this.#current = this.#clone(snapshot);
    this.#currentKey = this.#key(this.#current);
    this.#emit();
  }

  record(snapshot, label = 'Edit') {
    const next = this.#clone(snapshot);
    const nextKey = this.#key(next);
    if (nextKey === this.#currentKey) {
      this.#emit();
      return false;
    }

    this.#past.push({ snapshot: this.#clone(this.#current), label });
    if (this.#past.length > this.#limit) this.#past.shift();

    this.#current = next;
    this.#currentKey = nextKey;
    this.#future = [];
    this.#emit();
    return true;
  }

  undo() {
    if (!this.canUndo()) return null;
    const previous = this.#past.pop();
    this.#future.push({ snapshot: this.#clone(this.#current), label: previous.label });
    this.#current = this.#clone(previous.snapshot);
    this.#currentKey = this.#key(this.#current);
    this.#emit();
    return this.#clone(this.#current);
  }

  redo() {
    if (!this.canRedo()) return null;
    const next = this.#future.pop();
    this.#past.push({ snapshot: this.#clone(this.#current), label: next.label });
    this.#current = this.#clone(next.snapshot);
    this.#currentKey = this.#key(this.#current);
    this.#emit();
    return this.#clone(this.#current);
  }

  canUndo() {
    return this.#past.length > 0;
  }

  canRedo() {
    return this.#future.length > 0;
  }

  #emit() {
    this.#bus.dispatch('history:changed', {
      canUndo: this.canUndo(),
      canRedo: this.canRedo(),
      undoLabel: this.#past[this.#past.length - 1]?.label ?? null,
      redoLabel: this.#future[this.#future.length - 1]?.label ?? null,
    });
  }

  #clone(value) {
    try { return JSON.parse(JSON.stringify(value ?? [])); } catch { return []; }
  }

  #key(value) {
    return JSON.stringify(value ?? []);
  }
}
