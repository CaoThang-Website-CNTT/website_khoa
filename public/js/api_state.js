(function () {
  if (window.ApiResultState) return;

  window.ApiResultState = class {
    constructor(options = {}) {
      this.page = Number(options.page || 1);
      this.limit = Number(options.limit || 10);
      this.lastPage = Number(options.lastPage || 1);
      this.loading = false;
      this.params = { ...(options.params || {}) };
    }

    getParam(key, fallback = null) {
      return Object.prototype.hasOwnProperty.call(this.params, key) ? this.params[key] : fallback;
    }

    setParam(key, value) {
      if (value === null || value === undefined || value === "") {
        delete this.params[key];
        return;
      }

      this.params[key] = value;
    }

    setParams(params = {}) {
      Object.entries(params).forEach(([key, value]) => this.setParam(key, value));
    }

    nextPage() {
      return this.page + 1;
    }

    resetPage() {
      this.page = 1;
    }

    canLoadMore() {
      return this.page < this.lastPage;
    }

    setMeta(meta = {}) {
      this.page = Number(meta.current_page || this.page);
      this.limit = Number(meta.per_page || this.limit);
      this.lastPage = Number(meta.last_page || this.lastPage);
    }

    toQueryParams(overrides = {}) {
      const params = new URLSearchParams();
      const merged = {
        page: this.page,
        limit: this.limit,
        ...this.params,
        ...overrides,
      };

      Object.entries(merged).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== "") {
          params.set(key, String(value));
        }
      });

      return params;
    }

    queryString(overrides = {}) {
      return this.toQueryParams(overrides).toString();
    }

    static fromLocation(search = window.location.search) {
      const params = new URLSearchParams(search);
      const state = new window.ApiResultState({
        page: Number(params.get("page") || 1),
        limit: Number(params.get("limit") || 10),
      });

      params.forEach((value, key) => {
        if (!["page", "limit"].includes(key)) {
          state.setParam(key, value);
        }
      });

      return state;
    }
  };
})();
