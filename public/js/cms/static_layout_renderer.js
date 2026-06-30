import { CMS_STATIC_RENDERERS_ENABLED } from './cms_config.js';
import { asArray, assetUrl, escapeAttr, escapeHtml } from './cms_utils.js';

export class StaticLayoutRenderer {
  constructor(cmsDocument, getActiveState) {
    this.cmsDocument = cmsDocument;
    this.getActiveState = getActiveState;
  }

  renderSection(section) {
    if (!CMS_STATIC_RENDERERS_ENABLED) {
      return this.renderLockedPlaceholder(section.id, 'Preview renderer tĩnh bị disabled.');
    }

    const data = section.data || {};

    switch (section.type || section.id) {
      case 'sections/landing_hero':
      case 'hero':
        return this.renderLockedPlaceholder('Landing Carousel', 'Carousel slides đươc quản lý từ CMS editor.');
      case 'sections/newsfeed':
      case 'newsfeed':
        return this.renderLockedPlaceholder('Newsfeed', 'Nội dung News được lấy từ Database.');
      case 'sections/breadcrumbs':
      case 'breadcrumbs':
        return this.renderBreadcrumbsPreview();
      case 'sections/landing_about':
      case 'landing_about':
        return this.renderLandingAbout(data, section.id);
      case 'sections/why_choose_us':
      case 'why_choose_us':
        return this.renderWhyChooseUs(data, section.id);
      case 'sections/stats':
      case 'stats':
        return this.renderStats(data, section.id);
      case 'sections/about_hero':
      case 'about_hero':
        return this.renderAboutHero(data, section.id);
      case 'sections/history':
      case 'history':
        return this.renderHistory(data, section.id);
      case 'sections/bento_grid':
      case 'bento_grid':
        return this.renderBentoGrid(data, section.id);
      case 'sections/education_hub':
      case 'sections/admissions':
      case 'sections/programs':
      case 'sections/outcomes':
      case 'sections/curriculum':
        return this.renderEducation(data, section.id, section.type);
      default:
        return this.renderLockedPlaceholder(section.id, 'Section này không thể edit ở v1.');
    }
  }

  editable(sectionId, path, value, multiline = false) {
    if (!this.cmsDocument.isTextEditable(sectionId, path)) return escapeHtml(value ?? '');
    const active = this.getActiveState();
    const activeClass = active.sectionId === sectionId && active.path === path ? ' is-active' : '';
    return `<span class="cms-editable-text${activeClass}" contenteditable="true" spellcheck="false" data-placeholder="Click to edit" data-inline-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(path)}" data-multiline="${multiline ? 'true' : 'false'}">${escapeHtml(value ?? '')}</span>`;
  }

  editableHtml(sectionId, path, value, multiline = false) {
    if (!this.cmsDocument.isTextEditable(sectionId, path)) return value || '';
    const active = this.getActiveState();
    const activeClass = active.sectionId === sectionId && active.path === path ? ' is-active' : '';
    return `<span class="cms-editable-text${activeClass}" contenteditable="true" spellcheck="false" data-placeholder="Click to edit" data-inline-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(path)}" data-multiline="${multiline ? 'true' : 'false'}">${value || ''}</span>`;
  }

  imageAttrs(sectionId, path) {
    if (!this.cmsDocument.isImageEditable(sectionId, path)) return '';
    const active = this.getActiveState();
    const activeClass = active.sectionId === sectionId && active.path === path ? ' is-active' : '';
    return `class="cms-editable-image${activeClass}" data-cms-image-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(path)}"`;
  }

  imageDataAttrs(sectionId, path) {
    if (!this.cmsDocument.isImageEditable(sectionId, path)) return '';
    return `data-cms-image-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(path)}"`;
  }

  imageActiveClass(sectionId, path) {
    const active = this.getActiveState();
    return active.sectionId === sectionId && active.path === path ? ' is-active' : '';
  }

  iconAttrs(sectionId, path) {
    if (!this.cmsDocument.isTextEditable(sectionId, path)) return '';
    const active = this.getActiveState();
    const activeClass = active.sectionId === sectionId && active.path === path ? ' is-active' : '';
    return `cms-editable-icon${activeClass}" data-cms-icon-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(path)}`;
  }

  bentoItemStyle(item) {
    const background = String(item?.background || '').trim();
    if (!/^#[0-9a-f]{6}$/i.test(background)) return '';
    return ` style="--bento-item-background:${escapeAttr(background)}"`;
  }

  renderImagePlaceholder(sectionId, path, interactive = true) {
    const dataAttrs = interactive ? this.imageDataAttrs(sectionId, path) : '';
    const activeClass = interactive ? this.imageActiveClass(sectionId, path) : '';

    return `
      <div class="${interactive ? 'cms-editable-image ' : ''}cms-editable-image--empty${activeClass}" ${dataAttrs}>
        <i class="fa-regular fa-image"></i>
      </div>
    `;
  }

  renderLockedPlaceholder(title, description) {
    return `
      <section class="cms-dynamic-section relative container py-16">
        <div class="container-wrapper">
          <div class="cms-dynamic-section__box">
            <span class="badge" data-variant="primary"><i class="fa-solid fa-lock"></i> Bị Khóa</span>
            <h2 class="section__title">${escapeHtml(title)}</h2>
            <p class="section__sub-title">${escapeHtml(description)}</p>
          </div>
        </div>
      </section>
    `;
  }

  renderBreadcrumbsPreview() {
    return `
      <section class="site-breadcrumbs py-4">
        <div class="container"><div class="container-wrapper">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb__list">
              <li class="breadcrumb__item">
                <a href="#" class="breadcrumb__link" aria-current="false">
                  <i class="fa-regular fa-house"></i>
                  Trang chủ
                </a>
              </li>
              <li class="breadcrumb__separator" role="presentation" aria-hidden="true">
                <i class="fa-solid fa-chevron-right"></i>
              </li>
              <li class="breadcrumb__item">
                <span class="breadcrumb__page" role="link" aria-disabled="true" aria-current="page">
                  Giới Thiệu
                </span>
              </li>
            </ol>
          </nav>
        </div></div>
      </section>
    `;
  }
  renderLandingAbout(data, sectionId) {
    return `
      <section class="relative container py-16" id="landing-about-section">
        <h2 class="sr-only">About Us</h2>
        <div class="container-wrapper">
          <div class="landing-about-container flex flex-col gap-12 md:gap-0">
            ${asArray(data.items).map((item, index) => `
              <div class="flex gap-4 md:gap-12 flex-col md:${index % 2 === 0 ? 'flex-row-reverse' : 'flex-row'}">
                <div class="flex-1 relative">
                  <div class="overflow-hidden rounded-3xl"><div class="image-wrapper">
                    <img class="image w-full h-full cms-editable-image${this.getActiveState().sectionId === sectionId && this.getActiveState().path === `items.${index}.image.src` ? ' is-active' : ''}" src="${escapeAttr(assetUrl(this.cmsDocument.urls, item?.image?.src))}" alt="" data-cms-image-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(`items.${index}.image.src`)}">
                  </div></div>
                  <div class="landing-about-item__card absolute z-10 rounded-3xl p-3 md:p-6 flex flex-col gap-1">
                    <div class="landing-about-item__card-main-content text-lg md:text-5xl">${this.editable(sectionId, `items.${index}.card.value`, item?.card?.value)}</div>
                    <div class="landing-about-item__card-sub-content md:text-sm">${this.editable(sectionId, `items.${index}.card.label`, item?.card?.label)}</div>
                  </div>
                </div>
                <div class="flex-1 flex flex-col justify-center gap-4">
                  <p class="number-of-text text-7xl hidden md:block">${this.editable(sectionId, `items.${index}.number`, item?.number)}</p>
                  <p class="landing-about-item__sub-title text-xs uppercase font-medium">${this.editable(sectionId, `items.${index}.eyebrow`, item?.eyebrow)}</p>
                  <p class="about-item__title text-4xl">${this.editable(sectionId, `items.${index}.title`, item?.title)}</p>
                  <p class="landing-about-item__content">${this.editable(sectionId, `items.${index}.description`, item?.description, true)}</p>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      </section>
    `;
  }

  renderWhyChooseUs(data, sectionId) {
    return `
      <section class="wcu relative container py-16" id="why-choose-us-section">
        <div class="wcu__container container-wrapper">
          <div class="wcu__header flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
            <div class="wcu__badge section__badge px-4 py-2 rounded-3xl text-sm mb-2 md:mb-4">${this.editable(sectionId, 'badge', data.badge)}</div>
            <h2 class="wcu__title section__title">${this.editable(sectionId, 'title', data.title)}</h2>
            <p class="wcu__subtitle section__sub-title">${this.editable(sectionId, 'subtitle', data.subtitle, true)}</p>
          </div>
          <div class="wcu__content flex flex-col items-center justify-center">
            <div class="wcu__features-grid grid grid-cols-2 md:grid-cols-3 grid-rows-2 gap-3 md:gap-6 mb-6 self-stretch">
              <div class="wcu__feature-card wcu__feature-card--large wcu-feature-container overflow-hidden relative row-start-1 col-span-2 row-span-1 md:row-span-2 rounded-3xl image-wrapper">
                <img class="wcu__feature-card-image image cms-editable-image${this.getActiveState().sectionId === sectionId && this.getActiveState().path === 'feature.image' ? ' is-active' : ''}" src="${escapeAttr(assetUrl(this.cmsDocument.urls, data.feature?.image))}" alt="" data-cms-image-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="feature.image">
                <div class="wcu__feature-card-content absolute inset-0 flex flex-col justify-end items-start gap-2 md:gap-4 p-3 md:p-6">
                  <span class="wcu__feature-card-badge badge" data-variant="primary">${this.editable(sectionId, 'feature.badge', data.feature?.badge)}</span>
                  <h3 class="wcu__feature-card-title text-md md:text-3xl font-semibold">${this.editable(sectionId, 'feature.title', data.feature?.title)}</h3>
                  <p class="wcu__feature-card-description text-xs md:text-md font-normal">${this.editable(sectionId, 'feature.description', data.feature?.description, true)}</p>
                  <span class="wcu__feature-card-link md:text-md font-normal">${this.editable(sectionId, 'feature.cta_label', data.feature?.cta_label)} <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </div>
              </div>
              ${asArray(data.stats).map((stat, index) => `
                <div class="wcu__stat-card ${index === 0 ? 'wcu__stat-card--primary col-start-1 md:col-start-3 row-start-2 md:row-start-1' : 'wcu__stat-card--gradient col-start-2 md:col-start-3 row-start-2 md:row-start-2'} rounded-3xl p-3 md:p-6 flex flex-col gap-2 justify-center">
                  <h2 class="wcu__stat-card-number flex-1 md:flex-none flex justify-center items-center md:block text-6xl md:text-7xl font-bold">${this.editable(sectionId, `stats.${index}.number`, stat.number)}</h2>
                  <div class="wcu__stat-card-content flex flex-col gap-2">
                    <p class="wcu__stat-card-title md:text-xl font-semibold">${this.editable(sectionId, `stats.${index}.title`, stat.title)}</p>
                    <p class="wcu__stat-card-description text-xs md:text-md font-normal">${this.editable(sectionId, `stats.${index}.description`, stat.description, true)}</p>
                  </div>
                </div>
              `).join('')}
            </div>
            <div class="wcu__perks-list grid grid-cols-2 grid-rows-2 md:flex justify-center items-stretch self-stretch gap-3 md:gap-6 mb-6">
              ${asArray(data.perks).map((perk, index) => `
                <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-3 md:p-6">
                  <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3"><i class="${escapeAttr(perk.icon || 'fa-solid fa-circle')} wcu__perk-item-icon ${this.iconAttrs(sectionId, `perks.${index}.icon`)}"></i></div>
                  <h4 class="wcu__perk-item-title md:text-md font-semibold mb-2">${this.editable(sectionId, `perks.${index}.title`, perk.title)}</h4>
                  <p class="wcu__perk-item-description text-xs md:text-sm font-normal">${this.editable(sectionId, `perks.${index}.description`, perk.description, true)}</p>
                </div>
              `).join('')}
            </div>
            <div class="wcu__highlights-list self-stretch grid grid-rows-2 md:grid-rows-1 md:grid-cols-2 gap-3 md:gap-6">
              ${asArray(data.highlights).map((highlight, index) => `
                <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-3xl image-wrapper text-white">
                  <img class="wcu__highlight-item-image image cms-editable-image${this.getActiveState().sectionId === sectionId && this.getActiveState().path === `highlights.${index}.image` ? ' is-active' : ''}" src="${escapeAttr(assetUrl(this.cmsDocument.urls, highlight.image))}" alt="" data-cms-image-edit="true" data-section-id="${escapeAttr(sectionId)}" data-cms-path="${escapeAttr(`highlights.${index}.image`)}">
                  <div class="wcu__highlight-item-content ${index === 0 ? 'wcu__highlight-item-content--blue' : 'wcu__highlight-item-content--green'} absolute inset-0 flex flex-col justify-end items-start p-3 md:p-6">
                    <h3 class="wcu__highlight-item-title text-md md:text-2xl font-semibold mb-2">${this.editable(sectionId, `highlights.${index}.title`, highlight.title)}</h3>
                    <p class="wcu__highlight-item-description text-xs md:text-sm font-normal">${this.editable(sectionId, `highlights.${index}.description`, highlight.description, true)}</p>
                  </div>
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      </section>
    `;
  }

  renderStats(data, sectionId) {
    return `
      <section class="relative container py-16" id="stats-section">
        <div class="container-wrapper">
          <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
            <h2 class="section__title">${this.editable(sectionId, 'title', data.title)}</h2>
            <p class="section__sub-title">${this.editable(sectionId, 'subtitle', data.subtitle, true)}</p>
          </div>
          <div class="flex flex-col items-stretch justify-center gap-3 md:gap-6">
            <div class="stats__grid grid grid-cols-2 grid-rows-2 md:grid-cols-4 md:grid-rows-1 gap-3 md:gap-6">
              ${asArray(data.stats).map((stat, index) => `
                <div class="stats__stat-card flex flex-1 flex-col items-center gap-3 md:gap-6 rounded-3xl p-3 md:p-6">
                  <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full"><i class="${escapeAttr(stat.icon || 'fa-solid fa-award')} stats__stat-card-icon ${this.iconAttrs(sectionId, `stats.${index}.icon`)}"></i></div>
                  <div class="flex flex-col gap-1 items-center">
                    <h3 class="stats__stat-card-number text-3xl md:text-5xl font-bold">${this.editable(sectionId, `stats.${index}.number`, stat.number)}</h3>
                    <h4 class="stats__stat-card-label font-semibold">${this.editable(sectionId, `stats.${index}.label`, stat.label)}</h4>
                    <p class="stats__stat-card-description text-xs md:text-sm text-center">${this.editable(sectionId, `stats.${index}.description`, stat.description, true)}</p>
                  </div>
                </div>
              `).join('')}
            </div>
            <div class="stats__benefits-grid grid grid-cols-1 md:grid-cols-2 grid-rows-2 md:grid-rows-1 gap-3 md:gap-6 items-stretch">
              ${asArray(data.benefits).map((benefit, index) => `
                <div class="stats__benefit-card flex-1 flex flex-col gap-3 md:gap-6 p-3 md:p-6 rounded-3xl">
                  <div class="stats__benefit-card-header flex gap-2 md:gap-4 items-center">
                    <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full"><i class="${escapeAttr(benefit.icon || 'fa-solid fa-building-columns')} stats__benefit-card-icon ${this.iconAttrs(sectionId, `benefits.${index}.icon`)}"></i></div>
                    <h3 class="stats__benefit-card-title text-lg md:text-2xl font-semibold">${this.editable(sectionId, `benefits.${index}.title`, benefit.title)}</h3>
                  </div>
                  <ul class="stats__benefit-card-list flex flex-col gap-2 md:gap-4">
                    ${asArray(benefit.items).map((item, itemIndex) => `
                      <li class="stats__benefit-card-item flex items-center gap-2">
                        <span class="stats__benefit-card-item-icon rounded-full"></span>
                        <p class="stats__benefit-card-item-text">${this.editable(sectionId, `benefits.${index}.items.${itemIndex}`, item)}</p>
                      </li>
                    `).join('')}
                  </ul>
                </div>
              `).join('')}
            </div>
            <div class="stats__cta flex flex-col items-center p-3 md:p-12 rounded-3xl">
              <h3 class="stats__cta-title text-center text-xl md:text-3xl font-semibold mb-2">${this.editable(sectionId, 'cta.title', data.cta?.title)}</h3>
              <p class="stats__cta-description text-center text-sm md:text-xl font-light mb-6">${this.editable(sectionId, 'cta.description', data.cta?.description, true)}</p>
              <div class="stats__cta-buttons flex flex-col w-full md:w-fit md:flex-row gap-2 md:gap-4">
                ${asArray(data.cta?.buttons).map((button, index) => `<span data-variant="${escapeAttr(button.variant || 'outline')}" class="stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 btn bouncy-btn rounded-full ${index === 1 ? 'bg-transparent' : ''}">${this.editable(sectionId, `cta.buttons.${index}.label`, button.label)}</span>`).join('')}
              </div>
            </div>
          </div>
        </div>
      </section>
    `;
  }

  renderAboutHero(data, sectionId) {
    return `
      <section class="relative cms-editable-image${this.imageActiveClass(sectionId, 'image')}" ${this.imageDataAttrs(sectionId, 'image')}>
        <div class="about-thumbnail__wrapper"><img class="w-full h-full object-cover" src="${escapeAttr(assetUrl(this.cmsDocument.urls, data.image))}" alt=""></div>
        <div class="about-thumbnail-content__wrapper absolute inset-0 flex justify-center items-center">
          <div class="container"><div class="container-wrapper">
            <div class="about-thumbnail-content flex flex-col justify-center items-center gap-6 text-center">
              <span class="badge" data-variant="primary">${this.editable(sectionId, 'badge', data.badge)}</span>
              <div class="about-thumbnail-content__title">${this.editable(sectionId, 'title', data.title)}</div>
              <div class="about-thumbnail-content__sub-title">${this.editable(sectionId, 'subtitle', data.subtitle, true)}</div>
            </div>
          </div></div>
        </div>
      </section>
    `;
  }

  renderHistory(data, sectionId) {
    return `
      <section class="py-12">
        <div class="container"><div class="container-wrapper flex flex-col gap-16">
          ${asArray(data.sections).map((item, index) => `
            <div class="flex flex-col md:${index % 2 !== 0 ? 'flex-row-reverse' : 'flex-row'} flex-1 items-center gap-12">
              <div class="history-image-card flex-1 relative overflow-hidden rounded-3xl cms-editable-image${this.imageActiveClass(sectionId, `sections.${index}.image.src`)}" ${this.imageDataAttrs(sectionId, `sections.${index}.image.src`)}>
                <div class="history-image-wrapper image-wrapper"><img class="image w-full h-full" src="${escapeAttr(assetUrl(this.cmsDocument.urls, item.image?.src))}" alt=""></div>
                <div class="history-image-wrapper__content absolute inset-0 flex flex-col justify-end gap-1">
                  <div class="text-6xl">${this.editable(sectionId, `sections.${index}.year`, item.year)}</div>
                  <div class="text-xl">${this.editable(sectionId, `sections.${index}.image.caption`, item.image?.caption)}</div>
                </div>
              </div>
              <div class="flex-1 history-content flex flex-col justify-center gap-8">
                <span class="badge" data-variant="primary">${item.badge || ''}</span>
                <p class="text-4xl">${this.editable(sectionId, `sections.${index}.title`, item.title)}</p>
                <div class="history-content-timeline flex flex-col gap-4">
                  ${asArray(item.timeline).map((timeline, timelineIndex) => `
                    <div class="history-content-timeline__item">
                      <span class="history-content-timeline__item-year">${this.editable(sectionId, `sections.${index}.timeline.${timelineIndex}.year`, timeline.year)}:</span>
                      <span>${this.editable(sectionId, `sections.${index}.timeline.${timelineIndex}.description`, timeline.description, true)}</span>
                    </div>
                  `).join('')}
                </div>
              </div>
            </div>
          `).join('')}
        </div></div>
      </section>
    `;
  }

  renderBentoGrid(data, sectionId) {
    return `
      <section class="py-12">
        <div class="container"><div class="container-wrapper">
          <div class="bento-grid">
            ${asArray(data.items).map((item, index) => {
      const hasImage = item.image?.src;
      const imagePath = `items.${index}.image.src`;
      const isImageEditable = this.cmsDocument.isImageEditable(sectionId, imagePath);
      const editableClass = isImageEditable ? ` cms-editable-image${this.imageActiveClass(sectionId, imagePath)}` : '';
      const editableAttrs = isImageEditable ? this.imageDataAttrs(sectionId, imagePath) : '';
      return `
                <div class="card bento-grid-item${editableClass} ${hasImage ? 'bento-grid-item--has-image' : 'bento-grid-item--empty-image'}" ${editableAttrs}${this.bentoItemStyle(item)}>
                  ${hasImage ? `<img class="bento-grid-item__image" src="${escapeAttr(assetUrl(this.cmsDocument.urls, item.image.src))}" alt="">` : ''}
                  <div class="card__header"><span class="badge" data-variant="glass">${item.badge || '<i class="fa-solid fa-lock"></i>'}</span></div>
                  <div class="card__content">
                    <div class="text-4xl md:text-6xl">${this.editable(sectionId, `items.${index}.content`, item.content)}</div>
                    <div class="text-xl">${this.editable(sectionId, `items.${index}.subContent`, item.subContent)}</div>
                  </div>
                  <div class="card__footer flex flex-row flex-wrap">${this.editableHtml(sectionId, `items.${index}.footer`, item.footer, true)}</div>
                </div>
              `;
    }).join('')}
          </div>
        </div></div>
      </section>
    `;
  }

  renderEducation(data, sectionId, type) {
    const isIntro = type === 'sections/education_hub';
    const anchorIds = { admissions: 'tuyen-sinh', programs: 'chuong-trinh-dao-tao', outcomes: 'chuan-dau-ra', curriculum: 'danh-sach-mon-hoc' };
    const anchorId = anchorIds[sectionId] || '';

    const header = `
        <header class="section-title mb-8"${anchorId ? ` aria-labelledby="${anchorId}-title"` : ''}>
          <h2${anchorId ? ` id="${anchorId}-title"` : ''} class="section-title__heading">
            ${this.editable(sectionId, 'title', data.title)}
          </h2>
          ${type === 'sections/admissions'
        ? ''
        : `<p class="section-title__subtitle">${this.editable(sectionId, 'description', data.description, true)}</p>`}
        </header>
      `;

    let body = '';
    if (type === 'sections/admissions') {
      body = `
        <span class="btn" data-variant="primary">
          ${this.editable(sectionId, 'cta_label', data.cta_label)}
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </span>
      `;
    } else if (!isIntro) {
      body = `
        <div class="accordion education-accordion flex flex-col gap-4">
          ${asArray(data.programs)
        .map((program, index) => this.renderEducationProgram(sectionId, program, index, type))
        .join('')}
        </div>
      `;
    }

    if (isIntro) {
      return `
        <section class="my-8">
          <div class="container">
            <div class="container-wrapper"></div>
          </div>
        </section>
      `;
    }

    return `
      <section class="py-8 container"${anchorId ? ` id="${anchorId}"` : ''}>
        <div class="container-wrapper">
          ${header}
          ${body}
        </div>
      </section>
    `;
  }

  renderEducationProgram(sectionId, program, index, type) {
    const prefix = `programs.${index}`;
    let details = '';

    if (type === 'sections/programs') {
      details = this.renderEducationProgramDetails(sectionId, program, prefix);
    } else if (type === 'sections/outcomes') {
      details = this.renderEducationOutcomes(sectionId, program, prefix);
    } else {
      details = this.renderEducationCurriculum(sectionId, program, prefix);
    }

    return `
      <article class="accordion_item border rounded-3xl overflow-hidden" data-state="open">
        <h2>
          <button class="accordion__trigger flex w-full justify-between items-center gap-4 py-6 px-5 md:px-8" type="button">
            <span class="flex flex-col md:flex-row gap-1 md:gap-4">
              <span>${this.editable(sectionId, `${prefix}.short_name`, program.short_name)}</span>
              ${this.editable(sectionId, `${prefix}.name`, program.name)}
            </span>
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </button>
        </h2>
        <div class="accordion__content p-0">
          <div class="px-5 md:px-8 pb-8">${details}</div>
        </div>
      </article>
    `;
  }

  renderEducationProgramDetails(sectionId, program, prefix) {
    const specializations = asArray(program.specializations);

    return `
      <div class="education-facts grid grid-cols-2 md:grid-cols-3 gap-4 p-5 rounded-3xl">
        <span><strong>${this.editable(sectionId, `${prefix}.duration`, program.duration)}</strong> Thời lượng</span>
        <span><strong>${this.editable(sectionId, `${prefix}.credits`, program.credits)}</strong> Tín chỉ</span>
        <span><strong>${this.editable(sectionId, `${prefix}.source_year`, program.source_year)}</strong> Áp dụng</span>
      </div>
      <div class="education-copy-grid grid gap-8 mt-8">
        <div>
          <h3>Định hướng nghề nghiệp</h3>
          <p>${this.editable(sectionId, `${prefix}.career`, program.career, true)}</p>
          <h3>Mục tiêu chương trình</h3>
          <ol>${this.renderEducationList(sectionId, `${prefix}.objectives`, program.objectives, true)}</ol>
        </div>
        ${specializations.length ? `
          <aside>
            <h3>Các hướng chuyên môn</h3>
            <ul>${this.renderEducationList(sectionId, `${prefix}.specializations`, specializations)}</ul>
          </aside>
        ` : ''}
      </div>
      <div class="education-inline-actions flex flex-col md:flex-row gap-6 mt-6">
        <a href="#chuan-dau-ra">Xem chuẩn đầu ra</a>
        <a href="#danh-sach-mon-hoc">Xem danh sách môn học</a>
      </div>
    `;
  }

  renderEducationOutcomes(sectionId, program, prefix) {
    return `
      ${this.renderEducationSource(sectionId, program, prefix)}
      <div class="education-content grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
        <section>
          <h3>Mục tiêu chương trình</h3>
          <ol>${this.renderEducationList(sectionId, `${prefix}.objectives`, program.objectives, true)}</ol>
        </section>
        <section>
          <h3>Chuẩn đầu ra</h3>
          <ol>${this.renderEducationList(sectionId, `${prefix}.outcomes`, program.outcomes, true)}</ol>
        </section>
      </div>
    `;
  }

  renderEducationCurriculum(sectionId, program, prefix) {
    const semesters = asArray(program.semesters);

    if (!semesters.length) return '<p>Chưa có học kỳ.</p>';

    const semesterKey = (semester, semesterIndex) => escapeAttr(semester.key || String(semesterIndex + 1));
    const firstSemesterKey = semesterKey(semesters[0], 0);
    const tabsId = escapeAttr(`education-semesters-${sectionId}-${prefix.replace(/\./g, '-')}`);
    const tabs = semesters.map((semester, semesterIndex) => `
      <button
        type="button"
        data-tabs-trigger="${semesterKey(semester, semesterIndex)}"
        data-tabs-trigger-state="${semesterIndex === 0 ? 'active' : 'idle'}"
      >
        ${this.editable(sectionId, `${prefix}.semesters.${semesterIndex}.name`, semester.name)}
      </button>
    `).join('');
    const panels = semesters.map((semester, semesterIndex) => `
      <div
        class="tabs__panel"
        data-tabs-panel="${semesterKey(semester, semesterIndex)}"
        data-tabs-panel-state="${semesterIndex === 0 ? 'active' : 'idle'}"
      >
        <div class="education-table-wrap w-full overflow-x-auto border rounded-3xl">
          ${this.renderEducationCourseTable(sectionId, semester, prefix, semesterIndex)}
        </div>
      </div>
    `).join('');

    return `
      ${this.renderEducationSource(sectionId, program, prefix)}
      <div data-tabs data-tabs-id="${tabsId}" data-tabs-panel-active="${firstSemesterKey}">
        <div class="education-semester-tabs flex gap-2 overflow-x-auto py-5">${tabs}</div>
        ${panels}
      </div>
    `;
  }

  renderEducationCourseTable(sectionId, semester, prefix, semesterIndex) {
    const courses = asArray(semester.courses);
    const rows = courses.length
      ? courses.map((course, courseIndex) => {
        const coursePath = `${prefix}.semesters.${semesterIndex}.courses.${courseIndex}`;
        return `
          <tr>
            <td>${courseIndex + 1}</td>
            <td>${this.editable(sectionId, `${coursePath}.code`, course.code)}</td>
            <th scope="row">${this.editable(sectionId, `${coursePath}.name`, course.name)}</th>
            <td>${this.editable(sectionId, `${coursePath}.credits`, course.credits)}</td>
            <td>${this.editable(sectionId, `${coursePath}.theory`, course.theory)}</td>
            <td>${this.editable(sectionId, `${coursePath}.practice`, course.practice)}</td>
          </tr>
        `;
      }).join('')
      : '<tr><td colspan="6" class="text-center">Chưa có học phần cho học kỳ này.</td></tr>';

    return `
      <table>
        <thead><tr><th>STT</th><th>Mã HP</th><th>Học phần</th><th>Tín chỉ</th><th>LT</th><th>BT/TH</th></tr></thead>
        <tbody>${rows}</tbody>
      </table>
    `;
  }

  renderEducationSource(sectionId, program, prefix) {
    return `
      <div class="education-source flex flex-col md:flex-row justify-between gap-4 text-sm pb-4">
        <span>Cập nhật: ${this.editable(sectionId, `${prefix}.updated_at`, program.updated_at)}</span>
      </div>
    `;
  }

  renderEducationList(sectionId, path, items, multiline = false) {
    return asArray(items)
      .map((item, index) => `<li>${this.editable(sectionId, `${path}.${index}`, item, multiline)}</li>`)
      .join('');
  }
}
