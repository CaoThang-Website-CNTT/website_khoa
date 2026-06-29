<?php

namespace App\Cms;

final class EducationSectionRenderer
{
  private int $accordionId = 0;
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
    return $this->shell($data, $context, '');
  }

  private function admissions(array $data, CmsRenderContext $context): string
  {
    $cta = $this->safeExternalUrl($data['cta_url'] ?? '');
    $body = '<a class="btn" data-variant="primary" href="' . $this->e($cta) . '" target="_blank" rel="noopener noreferrer">' . $this->e($data['cta_label'] ?? '') . ' <i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
    return $this->detailSection('tuyen-sinh', $data, $body, false);
  }

  private function programs(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $specializations = '';
      foreach ($this->items($program, 'specializations') as $item) $specializations .= '<li>' . $this->e($item) . '</li>';
      $objectives = '';
      foreach ($this->items($program, 'objectives') as $item) $objectives .= '<li>' . $this->e($item) . '</li>';
      $details = '<div class="education-facts"><span><strong>' . $this->e($program['duration'] ?? '') . '</strong> Thời lượng</span><span><strong>' . $this->e($program['credits'] ?? '') . '</strong> Tín chỉ</span><span><strong>' . $this->e($program['source_year'] ?? '') . '</strong> Áp dụng</span></div><div class="education-copy-grid"><div><h3>Định hướng nghề nghiệp</h3><p>' . $this->e($program['career'] ?? '') . '</p><h3>Mục tiêu chương trình</h3><ol>' . $objectives . '</ol></div>';
      if ($specializations !== '') $details .= '<aside><h3>Các hướng chuyên môn</h3><ul>' . $specializations . '</ul></aside>';
      $key = $this->key($program['key'] ?? '', 'program-' . $index);
      $details .= '</div><div class="education-inline-actions"><a href="' . $this->e(url('dao-tao') . '#chuan-dau-ra') . '">Xem chuẩn đầu ra</a><a href="' . $this->e(url('dao-tao') . '#danh-sach-mon-hoc') . '">Xem danh sách môn học</a></div>';
      $content .= $this->accordion($program, $index, $details);
    }
    return $this->detailSection('chuong-trinh-dao-tao', $data, $this->accordionGroup($data, $content));
  }

  private function outcomes(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $objectives = $outcomes = '';
      foreach ($this->items($program, 'objectives') as $item) $objectives .= '<li>' . $this->e($item) . '</li>';
      foreach ($this->items($program, 'outcomes') as $item) $outcomes .= '<li>' . $this->e($item) . '</li>';
      $details = $this->sourceMeta($program) . '<div class="education-outcome-grid"><section><h3>Mục tiêu chương trình</h3><ol>' . $objectives . '</ol></section><section><h3>Chuẩn đầu ra</h3><ol>' . $outcomes . '</ol></section></div>';
      $content .= $this->accordion($program, $index, $details);
    }
    return $this->detailSection('chuan-dau-ra', $data, $this->accordionGroup($data, $content));
  }

  private function curriculum(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $tabs = $panels = '';
      $firstSemesterKey = '';
      foreach ($this->items($program, 'semesters') as $semesterIndex => $semester) {
        $semesterKey = $this->key($semester['key'] ?? '', (string) ($semesterIndex + 1));
        $active = $semesterIndex === 0;
        if ($active) $firstSemesterKey = $semesterKey;
        $tabs .= '<button type="button" data-tabs-trigger="' . $this->e($semesterKey) . '" data-tabs-trigger-state="' . ($active ? 'active' : 'idle') . '">' . $this->e($semester['name'] ?? ('Học kỳ ' . ($semesterIndex + 1))) . '</button>';
        $rows = '';
        foreach ($this->items($semester, 'courses') as $courseIndex => $course) {
          $rows .= '<tr><td>' . ($courseIndex + 1) . '</td><td>' . $this->e($course['code'] ?? '') . '</td><th scope="row">' . $this->e($course['name'] ?? '') . '</th><td>' . $this->e($course['credits'] ?? '') . '</td><td>' . $this->e($course['theory'] ?? '') . '</td><td>' . $this->e($course['practice'] ?? '') . '</td></tr>';
        }
        if ($rows === '') $rows = '<tr><td colspan="6" class="education-empty">Chưa có học phần cho học kỳ này.</td></tr>';
        $panels .= '<div class="tabs__panel" data-tabs-panel="' . $this->e($semesterKey) . '" data-tabs-panel-state="' . ($active ? 'active' : 'idle') . '"><div class="education-table-wrap"><table><thead><tr><th>STT</th><th>Mã HP</th><th>Học phần</th><th>Tín chỉ</th><th>LT</th><th>BT/TH</th></tr></thead><tbody>' . $rows . '</tbody></table></div></div>';
      }
      $tabsId = 'education-semesters-' . $this->key($program['key'] ?? '', 'program-' . $index);
      $details = $this->sourceMeta($program) . '<div class="education-semesters" data-tabs data-tabs-id="' . $this->e($tabsId) . '" data-tabs-panel-active="' . $this->e($firstSemesterKey) . '"><div class="education-semester-tabs">' . $tabs . '</div>' . $panels . '</div>';
      $content .= $this->accordion($program, $index, $details);
    }
    return $this->detailSection('danh-sach-mon-hoc', $data, $this->accordionGroup($data, $content));
  }

  private function detailSection(string $id, array $data, string $body, bool $showDescription = true): string
  {
    return '<section class="education-page education-detail" id="' . $this->e($id) . '"><div class="container"><div class="container-wrapper">'
      . '<header class="education-section-heading"><h2>' . $this->e($data['title'] ?? '') . '</h2>' . ($showDescription ? '<p>' . $this->e($data['description'] ?? '') . '</p>' : '') . '</header>'
      . $body . '</div></div></section>';
  }

  private function accordionGroup(array $data, string $content): string
  {
    $programs = $this->items($data, 'programs');
    $defaultValue = isset($programs[0])
      ? $this->key($programs[0]['key'] ?? '', 'program-0')
      : '';

    return '<div class="accordion education-accordion" data-accordion-type="single" data-accordion-collapsible data-accordion-default-value="' . $this->e($defaultValue) . '">' . $content . '</div>';
  }

  private function accordion(array $program, int $index, string $details): string
  {
    $key = $this->key($program['key'] ?? '', 'program-' . $index);
    $id = 'education-program-' . $key . '-' . (++$this->accordionId);
    return '<article class="accordion_item education-accordion__item" data-accordion-value="' . $this->e($key) . '"><h2><button class="accordion__trigger" type="button"><span><small>' . $this->e($program['short_name'] ?? '') . '</small>' . $this->e($program['name'] ?? '') . '</span><i class="fa-solid fa-plus" aria-hidden="true"></i></button></h2><div class="accordion__content education-accordion__panel" id="' . $this->e($id) . '"><div class="education-accordion__panel-inner">' . $details . '</div></div></article>';
  }

  private function sourceMeta(array $program): string
  {
    return '<div class="education-source"><span>Cập nhật: ' . $this->e($program['updated_at'] ?? '') . '</span></div>';
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
