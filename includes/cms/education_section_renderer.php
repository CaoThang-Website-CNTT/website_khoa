<?php

namespace App\Cms;

final class EducationSectionRenderer
{
  public function render(string $type, array $data, CmsRenderContext $context): string
  {
    return match ($type) {
      'sections/education_hub' => $this->hub($data, $context),
      'sections/admissions' => $this->admissions($data, $context),
      'sections/programs' => $this->programs($data, $context),
      'sections/outcomes' => $this->outcomes($data, $context),
      'sections/curriculum' => $this->curriculum($data, $context),
      default => '',
    };
  }

  private function header(array $data): string
  {
    return '<header class="education-hero">'
      . '<span class="education-hero__eyebrow">' . $this->e($data['eyebrow'] ?? '') . '</span>'
      . '<h1>' . $this->e($data['title'] ?? '') . '</h1>'
      . '<p>' . $this->e($data['description'] ?? '') . '</p>'
      . '</header>';
  }

  private function shell(array $data, CmsRenderContext $context, string $body): string
  {
    $breadcrumb = $context->pageSlug() === 'education' ? 'Đào tạo' : $this->e($data['title'] ?? 'Đào tạo');
    return '<section class="site-breadcrumbs py-4"><div class="container"><div class="container-wrapper">'
      . '<nav aria-label="breadcrumb"><ol class="breadcrumb__list"><li class="breadcrumb__item"><a class="breadcrumb__link" href="' . $this->e(url('/')) . '"><i class="fa-regular fa-house"></i> Trang chủ</a></li><li class="breadcrumb__separator" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li><li class="breadcrumb__item"><span class="breadcrumb__page" aria-current="page">' . $breadcrumb . '</span></li></ol></nav>'
      . '</div></div></section><section class="education-page"><div class="container"><div class="container-wrapper">'
      . $this->header($data) . $body . '</div></div></section>';
  }

  private function hub(array $data, CmsRenderContext $context): string
  {
    $cards = '';
    foreach ($this->items($data, 'links') as $link) {
      $cards .= '<a class="education-link-card" href="' . $this->e($this->href($link['url'] ?? '')) . '"><span class="education-link-card__icon"><i class="' . $this->e($link['icon'] ?? 'fa-solid fa-link') . '"></i></span><h2>' . $this->e($link['title'] ?? '') . '</h2><p>' . $this->e($link['description'] ?? '') . '</p><span class="education-link-card__cta">' . $this->e($link['label'] ?? 'Xem thêm') . ' <i class="fa-solid fa-arrow-right"></i></span></a>';
    }
    $programs = '';
    foreach ($this->items($data, 'programs') as $program) {
      $programs .= '<article class="education-program-summary"><span>' . $this->e($program['short_name'] ?? '') . '</span><h3>' . $this->e($program['name'] ?? '') . '</h3><p>' . $this->e($program['summary'] ?? '') . '</p><strong>' . $this->e($program['credits'] ?? '') . ' tín chỉ</strong></article>';
    }
    $body = '<div class="education-link-grid">' . $cards . '</div><section class="education-programs-intro"><div class="education-section-heading"><h2>' . $this->e($data['programs_title'] ?? '') . '</h2><p>' . $this->e($data['programs_description'] ?? '') . '</p></div><div class="education-program-summary-grid">' . $programs . '</div></section>';
    return $this->shell($data, $context, $body);
  }

  private function admissions(array $data, CmsRenderContext $context): string
  {
    $steps = '';
    foreach ($this->items($data, 'steps') as $index => $step) {
      $steps .= '<li><span>' . ($index + 1) . '</span><div><h3>' . $this->e($step['title'] ?? '') . '</h3><p>' . $this->e($step['description'] ?? '') . '</p></div></li>';
    }
    $programs = '';
    foreach ($this->items($data, 'programs') as $program) {
      $programs .= '<a href="' . $this->e(url('dao-tao/chuong-trinh-dao-tao?program=' . rawurlencode((string) ($program['key'] ?? '')))) . '"><strong>' . $this->e($program['name'] ?? '') . '</strong><span>' . $this->e($program['summary'] ?? '') . '</span></a>';
    }
    $cta = $this->safeExternalUrl($data['cta_url'] ?? '');
    $body = '<aside class="education-notice"><div><span class="education-notice__icon"><i class="fa-solid fa-circle-info"></i></span><div><h2>' . $this->e($data['notice_title'] ?? '') . '</h2><p>' . $this->e($data['notice'] ?? '') . '</p></div></div><a class="btn" data-variant="primary" href="' . $this->e($cta) . '" target="_blank" rel="noopener noreferrer">' . $this->e($data['cta_label'] ?? '') . ' <i class="fa-solid fa-arrow-up-right-from-square"></i></a></aside><div class="education-admissions-grid"><section><h2>' . $this->e($data['steps_title'] ?? '') . '</h2><ol class="education-steps">' . $steps . '</ol></section><section><h2>Chương trình đang đào tạo</h2><div class="education-program-links">' . $programs . '</div></section></div>';
    return $this->shell($data, $context, $body);
  }

  private function programs(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $specializations = '';
      foreach ($this->items($program, 'specializations') as $item) $specializations .= '<li>' . $this->e($item) . '</li>';
      $objectives = '';
      foreach ($this->items($program, 'objectives') as $item) $objectives .= '<li>' . $this->e($item) . '</li>';
      $details = '<div class="education-facts"><span><strong>' . $this->e($program['duration'] ?? '') . '</strong> Thời lượng</span><span><strong>' . $this->e($program['credits'] ?? '') . '</strong> Tín chỉ</span><span><strong>' . $this->e($program['practice_ratio'] ?? '') . '</strong> Thực hành</span><span><strong>' . $this->e($program['source_year'] ?? '') . '</strong> Áp dụng</span></div><div class="education-copy-grid"><div><h3>Định hướng nghề nghiệp</h3><p>' . $this->e($program['career'] ?? '') . '</p><h3>Mục tiêu chương trình</h3><ol>' . $objectives . '</ol></div>';
      if ($specializations !== '') $details .= '<aside><h3>Các hướng chuyên môn</h3><ul>' . $specializations . '</ul></aside>';
      $key = $this->key($program['key'] ?? '', 'program-' . $index);
      $details .= '</div><div class="education-inline-actions"><a href="' . $this->e(url('dao-tao/chuan-dau-ra?program=' . $key)) . '">Xem chuẩn đầu ra</a><a href="' . $this->e(url('dao-tao/danh-sach-mon-hoc?program=' . $key)) . '">Xem danh sách môn học</a></div>';
      $content .= $this->accordion($program, $index, $details);
    }
    return $this->shell($data, $context, $this->accordionGroup($data, $content));
  }

  private function outcomes(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $objectives = $outcomes = '';
      foreach ($this->items($program, 'objectives') as $item) $objectives .= '<li>' . $this->e($item) . '</li>';
      foreach ($this->items($program, 'outcomes') as $item) $outcomes .= '<li>' . $this->e($item) . '</li>';
      $details = $this->sourceMeta($program) . '<div class="education-outcome-grid"><section><span class="education-label">Sau 2–3 năm làm việc</span><h3>Mục tiêu chương trình</h3><ol>' . $objectives . '</ol></section><section><span class="education-label">Tại thời điểm tốt nghiệp</span><h3>Chuẩn đầu ra</h3><ol>' . $outcomes . '</ol></section></div>';
      $content .= $this->accordion($program, $index, $details);
    }
    return $this->shell($data, $context, $this->accordionGroup($data, $content));
  }

  private function curriculum(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $tabs = $panels = '';
      foreach ($this->items($program, 'semesters') as $semesterIndex => $semester) {
        $semesterKey = $this->key($semester['key'] ?? '', (string) ($semesterIndex + 1));
        $active = $semesterIndex === 0;
        $tabs .= '<button type="button" role="tab" aria-selected="' . ($active ? 'true' : 'false') . '" tabindex="' . ($active ? '0' : '-1') . '" data-semester-trigger="' . $this->e($semesterKey) . '">' . $this->e($semester['name'] ?? ('Học kỳ ' . ($semesterIndex + 1))) . '</button>';
        $rows = '';
        foreach ($this->items($semester, 'courses') as $courseIndex => $course) {
          $rows .= '<tr><td>' . ($courseIndex + 1) . '</td><td>' . $this->e($course['code'] ?? '') . '</td><th scope="row">' . $this->e($course['name'] ?? '') . '</th><td>' . $this->e($course['credits'] ?? '') . '</td><td>' . $this->e($course['theory'] ?? '') . '</td><td>' . $this->e($course['practice'] ?? '') . '</td></tr>';
        }
        if ($rows === '') $rows = '<tr><td colspan="6" class="education-empty">Chưa có học phần cho học kỳ này.</td></tr>';
        $panels .= '<div role="tabpanel" data-semester-panel="' . $this->e($semesterKey) . '"' . ($active ? '' : ' hidden') . '><div class="education-table-wrap"><table><thead><tr><th>STT</th><th>Mã HP</th><th>Học phần</th><th>Tín chỉ</th><th>LT</th><th>BT/TH</th></tr></thead><tbody>' . $rows . '</tbody></table></div></div>';
      }
      $details = $this->sourceMeta($program) . '<div class="education-semesters" data-semester-tabs data-program-key="' . $this->e($program['key'] ?? '') . '"><div class="education-semester-tabs" role="tablist" aria-label="Chọn học kỳ">' . $tabs . '</div>' . $panels . '</div>';
      $content .= $this->accordion($program, $index, $details);
    }
    return $this->shell($data, $context, $this->accordionGroup($data, $content));
  }

  private function accordionGroup(array $data, string $content): string
  {
    $programs = $this->items($data, 'programs');
    $defaultValue = isset($programs[0])
      ? $this->key($programs[0]['key'] ?? '', 'program-0')
      : '';

    return '<div class="accordion education-accordion" data-program-accordion data-accordion-type="single" data-accordion-default-value="' . $this->e($defaultValue) . '">' . $content . '</div>';
  }

  private function accordion(array $program, int $index, string $details): string
  {
    $key = $this->key($program['key'] ?? '', 'program-' . $index);
    $id = 'education-program-' . $key;
    return '<article class="accordion_item education-accordion__item" data-accordion-value="' . $this->e($key) . '" data-program="' . $this->e($key) . '"><h2><button class="accordion__trigger" type="button"><span><small>' . $this->e($program['short_name'] ?? '') . '</small>' . $this->e($program['name'] ?? '') . '</span><i class="fa-solid fa-plus" aria-hidden="true"></i></button></h2><div class="accordion__content education-accordion__panel" id="' . $this->e($id) . '"><div class="education-accordion__panel-inner">' . $details . '</div></div></article>';
  }

  private function sourceMeta(array $program): string
  {
    return '<div class="education-source"><span><i class="fa-regular fa-file-lines"></i> Nguồn: chương trình năm ' . $this->e($program['source_year'] ?? '') . '</span><span>Cập nhật: ' . $this->e($program['updated_at'] ?? '') . '</span></div>';
  }

  private function items(array $data, string $key): array
  {
    return is_array($data[$key] ?? null) ? $data[$key] : [];
  }

  private function safeExternalUrl(mixed $value): string
  {
    $value = trim((string) $value);
    return filter_var($value, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $value) ? $value : '#';
  }

  private function href(mixed $value): string
  {
    $value = trim((string) $value);
    return preg_match('/^https?:\/\//i', $value) ? $this->safeExternalUrl($value) : url(ltrim($value, '/'));
  }

  private function key(mixed $value, string $fallback): string
  {
    $key = strtolower(trim((string) $value));
    $key = preg_replace('/[^a-z0-9_-]+/', '-', $key) ?: '';
    return trim($key, '-') ?: $fallback;
  }

  private function e(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}
