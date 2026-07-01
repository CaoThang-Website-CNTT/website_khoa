export const CMS_STATIC_RENDERERS_ENABLED = true;

export const CMS_VIEWPORTS = {
  desktop: { width: 1440, height: 900 },
  mobile: { width: 390, height: 844 },
};

export const CMS_TEXT_FIELDS = {
  landing_about: [
    ['items.*.number', 'Number'],
    ['items.*.card.value', 'Card value'],
    ['items.*.card.label', 'Card label'],
    ['items.*.eyebrow', 'Eyebrow'],
    ['items.*.title', 'Title'],
    ['items.*.description', 'Description', 'textarea'],
  ],
  why_choose_us: [
    ['badge', 'Badge'],
    ['title', 'Title'],
    ['subtitle', 'Subtitle', 'textarea'],
    ['feature.badge', 'Feature badge'],
    ['feature.title', 'Feature title'],
    ['feature.description', 'Feature description', 'textarea'],
    ['feature.cta_label', 'Feature button label'],
    ['stats.*.number', 'Stat number'],
    ['stats.*.title', 'Stat title'],
    ['stats.*.description', 'Stat description', 'textarea'],
    ['perks.*.title', 'Perk title'],
    ['perks.*.description', 'Perk description', 'textarea'],
    ['highlights.*.title', 'Highlight title'],
    ['highlights.*.description', 'Highlight description', 'textarea'],
  ],
  stats: [
    ['title', 'Title'],
    ['subtitle', 'Subtitle', 'textarea'],
    ['stats.*.number', 'Stat number'],
    ['stats.*.label', 'Stat label'],
    ['stats.*.description', 'Stat description', 'textarea'],
    ['benefits.*.title', 'Benefit title'],
    ['benefits.*.items.*', 'Benefit item'],
    ['cta.title', 'CTA title'],
    ['cta.description', 'CTA description', 'textarea'],
    ['cta.buttons.*.label', 'Button label'],
  ],
  partnerships: [
    ['title', 'Title'],
    ['subtitle', 'Subtitle', 'textarea'],
    ['partners.*.name', 'Partner name'],
    ['partners.*.url', 'Partner URL'],
    ['partners.*.image.alt', 'Partner logo alt text'],
    ['partners.*.description', 'Partner description', 'textarea'],
    ['partners.*.description_source_url', 'Description source URL'],
  ],
  about_hero: [
    ['badge', 'Badge'],
    ['title', 'Title'],
    ['subtitle', 'Subtitle', 'textarea'],
  ],
  history: [
    ['sections.*.image.caption', 'Image caption'],
    ['sections.*.year', 'Year'],
    ['sections.*.title', 'Title'],
    ['sections.*.timeline.*.year', 'Timeline year'],
    ['sections.*.timeline.*.description', 'Timeline description', 'textarea'],
  ],
  bento_grid: [
    ['items.*.content', 'Content'],
    ['items.*.subContent', 'Sub content'],
    ['items.*.footer', 'Footer', 'textarea'],
  ],
};

export const CMS_IMAGE_FIELDS = {
  landing_about: [
    ['items.*.image.src', 'Image'],
  ],
  why_choose_us: [
    ['feature.image', 'Feature image'],
    ['highlights.*.image', 'Highlight image'],
  ],
  about_hero: [
    ['image', 'Hero image'],
  ],
  history: [
    ['sections.*.image.src', 'History image'],
  ],
  bento_grid: [
    ['items.0.image.src', 'Featured item image'],
    ['items.5.image.src', 'Community item image'],
  ],
  partnerships: [
    ['partners.*.image.src', 'Partner logo'],
  ],
};
