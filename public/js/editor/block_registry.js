class BlockRegistry {
  #blocks = new Map();

  register(schema, blockClass) {
    this.#blocks.set(schema.name, {
      schema: schema,
      blockClass: blockClass
    });
  }

  get(name) {
    return this.#blocks.get(name);
  }

  getAll() {
    return this.#blocks.entries();
  }
}

export const registry = new BlockRegistry();