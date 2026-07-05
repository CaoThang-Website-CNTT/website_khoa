<?php

namespace App\Cms;

final class CmsEditorMarkupInstrumenter
{
  public function instrument(string $html, array $data, array $editableFields, string $sectionId): string
  {
    if ($html === '' || !class_exists(\DOMDocument::class)) return $html;

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
      '<?xml encoding="UTF-8"><div id="cms-editor-fragment">' . $html . '</div>',
      LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) return $html;

    $root = $dom->getElementById('cms-editor-fragment');
    if (!$root) return $html;

    foreach ($editableFields as $pattern) {
      if (!is_string($pattern) || $pattern === 'variant') continue;
      foreach ($this->expand($data, $pattern) as [$path, $value]) {
        if (!is_scalar($value) || trim((string) $value) === '') continue;
        if ($this->isImagePath($path)) $this->markImage($root, $path, (string) $value, $sectionId);
        elseif ($this->isIconPath($path)) $this->markIcon($root, $path, (string) $value, $sectionId);
        else $this->markText($root, $path, (string) $value, $sectionId);
      }
    }
    $this->markRepeaterItems($root, $sectionId);

    $result = '';
    foreach ($root->childNodes as $child) $result .= $dom->saveHTML($child);
    return $result;
  }

  private function markText(\DOMElement $root, string $path, string $value, string $sectionId): void
  {
    $expected = $this->normalizeText($value);
    $xpath = new \DOMXPath($root->ownerDocument);
    foreach ($xpath->query('.//text()[normalize-space(.) != ""]', $root) ?: [] as $textNode) {
      $element = $textNode->parentNode;
      if (!$element instanceof \DOMElement || $element->hasAttribute('data-cms-path')) continue;
      if (in_array(strtolower($element->tagName), ['script', 'style'], true)) continue;
      if ($this->normalizeText($textNode->nodeValue ?? '') !== $expected) continue;
      $this->setCommon($element, $path, $sectionId);
      $element->setAttribute('contenteditable', 'true');
      $element->setAttribute('data-inline-edit', 'true');
      $element->setAttribute('data-multiline', $this->isMultilinePath($path) ? 'true' : 'false');
      $element->setAttribute('class', trim($element->getAttribute('class') . ' cms-editable-text'));
      return;
    }
  }

  private function markImage(\DOMElement $root, string $path, string $value, string $sectionId): void
  {
    foreach ($root->getElementsByTagName('img') as $image) {
      if ($image->hasAttribute('data-cms-path')) continue;
      $src = rawurldecode($image->getAttribute('src'));
      $needle = rawurldecode(str_replace('\\', '/', $value));
      if ($needle !== '' && !str_ends_with(str_replace('\\', '/', $src), ltrim($needle, '/')) && basename($src) !== basename($needle)) continue;
      $this->setCommon($image, $path, $sectionId);
      $image->setAttribute('data-cms-image-edit', 'true');
      $image->setAttribute('class', trim($image->getAttribute('class') . ' cms-editable-image'));
      return;
    }
  }

  private function markIcon(\DOMElement $root, string $path, string $value, string $sectionId): void
  {
    $tokens = preg_split('/\s+/', trim($value)) ?: [];
    foreach ($root->getElementsByTagName('*') as $element) {
      if (!$element instanceof \DOMElement || $element->hasAttribute('data-cms-path')) continue;
      $classes = preg_split('/\s+/', trim($element->getAttribute('class'))) ?: [];
      if (!$tokens || array_diff($tokens, $classes)) continue;
      $this->setCommon($element, $path, $sectionId);
      $element->setAttribute('data-cms-icon-edit', 'true');
      $element->setAttribute('class', trim($element->getAttribute('class') . ' cms-editable-icon'));
      return;
    }
  }

  private function setCommon(\DOMElement $element, string $path, string $sectionId): void
  {
    $element->setAttribute('data-section-id', $sectionId);
    $element->setAttribute('data-cms-path', $path);
  }

  private function markRepeaterItems(\DOMElement $root, string $sectionId): void
  {
    $xpath = new \DOMXPath($root->ownerDocument);
    foreach ($xpath->query('.//*[@data-cms-path]', $root) ?: [] as $field) {
      if (!$field instanceof \DOMElement) continue;
      $path = $field->getAttribute('data-cms-path');
      if (!preg_match_all('/(?:^|\.)(\d+)(?=\.|$)/', $path, $matches, PREG_OFFSET_CAPTURE)) continue;
      $last = end($matches[1]);
      if (!is_array($last)) continue;
      [$index, $offset] = $last;
      $prefix = rtrim(substr($path, 0, max(0, $offset - 1)), '.');
      if ($prefix === '') continue;

      $candidate = $field->parentNode;
      while ($candidate instanceof \DOMElement && $candidate !== $root) {
        if (in_array(strtolower($candidate->tagName), ['li', 'article', 'tr'], true)) break;
        $candidate = $candidate->parentNode;
      }
      if (!$candidate instanceof \DOMElement || $candidate === $root) $candidate = $field->parentNode;
      if (!$candidate instanceof \DOMElement) continue;
      $candidate->setAttribute('data-section-id', $sectionId);
      $candidate->setAttribute('data-cms-repeater-path', $prefix);
      $candidate->setAttribute('data-cms-repeater-index', (string) $index);
    }
  }

  private function expand(array $data, string $pattern): array
  {
    $results = [];
    $walk = function (mixed $value, array $segments, array $trail) use (&$walk, &$results): void {
      if (!$segments) { $results[] = [implode('.', $trail), $value]; return; }
      $segment = array_shift($segments);
      if ($segment === '*') {
        if (!is_array($value)) return;
        foreach ($value as $index => $item) $walk($item, $segments, [...$trail, (string) $index]);
        return;
      }
      if (is_array($value) && array_key_exists($segment, $value)) $walk($value[$segment], $segments, [...$trail, $segment]);
    };
    $walk($data, explode('.', $pattern), []);
    return $results;
  }

  private function normalizeText(string $value): string
  {
    return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
  }

  private function isImagePath(string $path): bool { return (bool) preg_match('/(^|\.)(image|src)(\.|$)/', $path); }
  private function isIconPath(string $path): bool { return (bool) preg_match('/(^|\.)icon$/', $path); }
  private function isMultilinePath(string $path): bool
  {
    return (bool) preg_match('/description|subtitle|summary|content|footer|career|objective|outcome|introduction|vision|mission/i', $path);
  }
}
