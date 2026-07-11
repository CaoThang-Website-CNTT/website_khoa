export function asArray(value) {
  return Array.isArray(value) ? value : [];
}

export function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

export function escapeAttr(value) {
  return escapeHtml(value);
}

export function cssEscape(value) {
  if (window.CSS?.escape) return CSS.escape(String(value || ''));
  return String(value || '').replace(/["\\]/g, '\\$&');
}

export function joinUrl(base, path) {
  const cleanBase = String(base || '').replace(/\/+$/, '');
  const cleanPath = String(path || '').replace(/^\/+/, '');
  return cleanBase ? `${cleanBase}${cleanPath ? `/${cleanPath}` : ''}` : cleanPath;
}

export function assetUrl(urls, value) {
  const src = String(value || '').trim();
  if (!src) return '';
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src;

  const normalized = src.replace(/^\.\//, '').replace(/^\/+/, '');
  if (normalized.startsWith('public/media/')) {
    return joinUrl(urls.media, normalized.slice('public/media/'.length));
  }
  if (normalized.startsWith('media/')) {
    return joinUrl(urls.media, normalized.slice('media/'.length));
  }
  if (normalized.startsWith('public/')) {
    return joinUrl(urls.base, normalized);
  }

  return joinUrl(urls.public, normalized);
}

export function getPath(source, path) {
  return path.split('.').reduce((value, segment) => value?.[segment], source);
}

export function setPath(target, path, value) {
  const segments = path.split('.');
  let cursor = target;
  while (segments.length > 1) {
    const segment = segments.shift();
    if (!cursor[segment] || typeof cursor[segment] !== 'object') cursor[segment] = {};
    cursor = cursor[segment];
  }
  cursor[segments[0]] = value;
}

export function plainEditableText(element) {
  return element.dataset.multiline === 'true'
    ? element.innerText.replace(/\n{3,}/g, '\n\n').trim()
    : element.innerText.replace(/\s+/g, ' ').trim();
}

export function isEditableScalar(value) {
  return typeof value === 'string' || typeof value === 'number';
}
