import { CMS_IMAGE_FIELDS, CMS_TEXT_FIELDS } from './cms_config.js';
import { getPath, isEditableScalar } from './cms_utils.js';

export class CmsDocument {
  constructor({ page = {}, schema = {}, document = {}, urls = {} } = {}) {
    this.page = page;
    this.schema = schema;
    this.document = document?.sections ? document : { version: 1, sections: [] };
    this.urls = urls;
  }

  get sections() {
    return this.document.sections || [];
  }

  section(sectionId) {
    return this.sections.find((section) => section.id === sectionId) || null;
  }

  sectionSchema(sectionId) {
    return (this.schema.sections || []).find((section) => section.id === sectionId) || null;
  }

  textFieldInstances(sectionId) {
    const section = this.section(sectionId);
    const specs = CMS_TEXT_FIELDS[sectionId] || [];
    return specs.flatMap(([pattern, label, control]) => this.#expandTextPattern(section?.data || {}, pattern).map((path) => ({
      sectionId,
      path,
      label: this.#labelForPath(label, path),
      control: control || (path.match(/description|subtitle/i) ? 'textarea' : 'input'),
    })));
  }

  imageFieldInstances(sectionId) {
    const section = this.section(sectionId);
    const specs = CMS_IMAGE_FIELDS[sectionId] || [];
    return specs.flatMap(([pattern, label]) => this.#expandPathPattern(section?.data || {}, pattern).map((path) => ({
      sectionId,
      path,
      label: this.#labelForPath(label, path),
      value: getPath(section?.data || {}, path),
    })));
  }

  isTextEditable(sectionId, path) {
    return this.textFieldInstances(sectionId).some((field) => field.path === path);
  }

  isImageEditable(sectionId, path) {
    return this.imageFieldInstances(sectionId).some((field) => field.path === path);
  }

  #expandTextPattern(data, pattern) {
    return this.#expandPathPattern(data, pattern)
      .filter((path) => isEditableScalar(getPath(data, path)));
  }

  #expandPathPattern(data, pattern) {
    const paths = [];
    const segments = pattern.split('.');

    const walk = (value, index, trail) => {
      if (index >= segments.length) {
        paths.push(trail.join('.'));
        return;
      }

      const segment = segments[index];
      if (segment === '*') {
        if (!Array.isArray(value)) return;
        value.forEach((item, itemIndex) => walk(item, index + 1, [...trail, String(itemIndex)]));
        return;
      }

      if (value && typeof value === 'object' && Object.prototype.hasOwnProperty.call(value, segment)) {
        walk(value[segment], index + 1, [...trail, segment]);
      }
    };

    walk(data, 0, []);
    return paths;
  }

  #labelForPath(baseLabel, path) {
    const indexes = path.split('.').filter((part) => /^\d+$/.test(part)).map((part) => Number(part) + 1);
    return indexes.length ? `${baseLabel} ${indexes.join('.')}` : baseLabel;
  }
}
