class BlockRegistry {
  #blocks = new Map();
  #groupedBlocks = {};

  register(schema, blockClass) {
    this.#blocks.set(schema.name, {
      schema: schema,
      blockClass: blockClass
    });

    const groupKey = schema.group || 'general';

    if (!this.#groupedBlocks[groupKey]) {
      this.#groupedBlocks[groupKey] = {
        label: schema.groupLabel || 'Chung',
        items: []
      };
    }

    this.#groupedBlocks[groupKey].items.push(schema);
  }

  get(name) {
    return this.#blocks.get(name);
  }

  getAll() {
    return Array.from(this.#blocks.entries());
  }

  getGrouped() {
    return this.#groupedBlocks;
  }
}

export const registry = new BlockRegistry();