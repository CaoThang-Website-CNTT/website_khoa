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

  private function shell(array $data, CmsRenderContext $context, string $body): string
  {
    $breadcrumb = $context->pageSlug() === 'education' ? 'Đào tạo' : $this->e($data['title'] ?? 'Đào tạo');
    $homeUrl = $this->e(url('/'));
    $sectionAttributes = $context->sectionAttributes();
    $titleAttributes = $context->textAttributes('title');

    return <<<HTML
      <section class="site-breadcrumbs py-4"{$sectionAttributes}>
        <div class="container">
          <div class="container-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb__list">
                <li class="breadcrumb__item">
                  <a class="breadcrumb__link" href="{$homeUrl}"><i class="fa-regular fa-house"></i> Trang chủ</a>
                </li>
                <li class="breadcrumb__separator" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
                <li class="breadcrumb__item">
                  <span class="breadcrumb__page" aria-current="page"{$titleAttributes}>{$breadcrumb}</span>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </section>
      <section class="my-8">
        <div class="container">
          <div class="container-wrapper">
            {$body}
          </div>
        </div>
      </section>
      HTML;
  }

  private function hub(array $data, CmsRenderContext $context): string
  {
    return $this->shell($data, $context, '');
  }

  private function admissions(array $data, CmsRenderContext $context): string
  {
    $cta = $this->safeExternalUrl($data['cta_url'] ?? '');
    $ctaUrl = $this->e($cta);
    $ctaLabel = $this->e($data['cta_label'] ?? '');
    $title = $this->e($data['title'] ?? '');
    $description = $this->e($data['description'] ?? '');
    $titleAttributes = $context->textAttributes('title');
    $descriptionAttributes = $context->textAttributes('description', true);
    $ctaUrlAttributes = $context->linkAttributes('cta_url');
    $body = <<<HTML
      <div class="stats__cta flex flex-col items-center p-3 md:p-12 rounded-3xl">
        <h2 class="stats__cta-title text-center text-xl md:text-3xl font-semibold mb-2"{$titleAttributes}>{$title}</h2>
        <p class="stats__cta-description text-center text-sm md:text-xl font-light mb-6"{$descriptionAttributes}>{$description}</p>
        <div class="stats__cta-buttons flex flex-col w-full md:w-fit md:flex-row gap-2 md:gap-4">
          <a class="stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 btn bouncy-btn rounded-full" data-variant="secondary" href="{$ctaUrl}"{$ctaUrlAttributes} target="_blank" rel="noopener noreferrer">
            {$ctaLabel} <i class="fa-solid fa-arrow-up-right-from-square"></i>
          </a>
        </div>
      </div>
      HTML;

    return $this->detailSection('tuyen-sinh', $data, $body, $context, false, false);
  }

  private function programs(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $specializations = '';
      foreach ($this->items($program, 'specializations') as $item)
        $specializations .= '<li>' . $this->e($item) . '</li>';
      $objectives = '';
      foreach ($this->items($program, 'objectives') as $item)
        $objectives .= '<li>' . $this->e($item) . '</li>';

      $duration = $this->e($program['duration'] ?? '');
      $credits = $this->e($program['credits'] ?? '');
      $sourceYear = $this->e($program['source_year'] ?? '');
      $career = $this->e($program['career'] ?? '');
      $outcomesUrl = $this->e(url('dao-tao') . '#chuan-dau-ra');
      $curriculumUrl = $this->e(url('dao-tao') . '#danh-sach-mon-hoc');
      $specializationsAside = $specializations === '' ? '' : <<<HTML
        <aside>
          <h3>Các hướng chuyên môn</h3>
          <ul>{$specializations}</ul>
        </aside>
        HTML;

      $details = <<<HTML
        <div class="education-facts grid grid-cols-2 md:grid-cols-3 gap-4 p-5 rounded-3xl">
          <span><strong>{$duration}</strong> Thời lượng</span>
          <span><strong>{$credits}</strong> Tín chỉ</span>
          <span><strong>{$sourceYear}</strong> Áp dụng</span>
        </div>
        <div class="education-copy-grid grid gap-8 mt-8">
          <div>
            <h3>Định hướng nghề nghiệp</h3>
            <ul><li>{$career}</li></ul>
            <h3>Mục tiêu chương trình</h3>
            <ol>{$objectives}</ol>
          </div>
          {$specializationsAside}
        </div>
        <div class="education-inline-actions flex flex-col md:flex-row gap-3 mt-6">
          <a class="btn rounded-full" data-variant="outline" data-size="lg" href="{$outcomesUrl}">Xem chuẩn đầu ra</a>
          <a class="btn rounded-full" data-variant="outline" data-size="lg" href="{$curriculumUrl}">Xem danh sách môn học</a>
        </div>
        HTML;

      $content .= $this->accordion($program, $index, $details);
    }
    return $this->detailSection('chuong-trinh-dao-tao', $data, $this->accordionGroup($data, $content), $context);
  }

  private function outcomes(array $data, CmsRenderContext $context): string
  {
    $content = '';
    foreach ($this->items($data, 'programs') as $index => $program) {
      $objectives = $outcomes = '';
      foreach ($this->items($program, 'objectives') as $item)
        $objectives .= '<li>' . $this->e($item) . '</li>';
      foreach ($this->items($program, 'outcomes') as $item)
        $outcomes .= '<li>' . $this->e($item) . '</li>';
      $sourceMeta = $this->sourceMeta($program);
      $details = <<<HTML
        {$sourceMeta}
        <div class="education-content grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
          <section>
            <h3>Mục tiêu chương trình</h3>
            <ol>{$objectives}</ol>
          </section>
          <section>
            <h3>Chuẩn đầu ra</h3>
            <ol>{$outcomes}</ol>
          </section>
        </div>
        HTML;

      $content .= $this->accordion($program, $index, $details);
    }
    return $this->detailSection('chuan-dau-ra', $data, $this->accordionGroup($data, $content), $context);
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
        if ($active)
          $firstSemesterKey = $semesterKey;
        $escapedSemesterKey = $this->e($semesterKey);
        $tabState = $active ? 'active' : 'idle';
        $badgeVariant = $active ? 'primary' : 'outline';
        $semesterName = $this->e($semester['name'] ?? ('Học kỳ ' . ($semesterIndex + 1)));
        $tabs .= <<<HTML
          <button type="button" data-tabs-trigger="{$escapedSemesterKey}" data-tabs-trigger-state="{$tabState}">
            <span class="badge" data-variant="{$badgeVariant}" data-size="lg">{$semesterName}</span>
          </button>
          HTML;

        $rows = '';
        foreach ($this->items($semester, 'courses') as $courseIndex => $course) {
          $number = $courseIndex + 1;
          $code = $this->e($course['code'] ?? '');
          $name = $this->e($course['name'] ?? '');
          $credits = $this->e($course['credits'] ?? '');
          $theory = $this->e($course['theory'] ?? '');
          $practice = $this->e($course['practice'] ?? '');
          $rows .= <<<HTML
            <tr>
              <td>{$number}</td>
              <td>{$code}</td>
              <th scope="row">{$name}</th>
              <td>{$credits}</td>
              <td>{$theory}</td>
              <td>{$practice}</td>
            </tr>
            HTML;
        }
        if ($rows === '')
          $rows = '<tr><td colspan="6" class="text-center">Chưa có học phần cho học kỳ này.</td></tr>';
        $panels .= <<<HTML
          <div class="tabs__panel" data-tabs-panel="{$escapedSemesterKey}" data-tabs-panel-state="{$tabState}">
            <div class="education-table-wrap w-full overflow-x-auto border rounded-3xl">
              <table class="data-table education-course-table">
                <thead>
                  <tr><th>STT</th><th>Mã HP</th><th>Học phần</th><th>Tín chỉ</th><th>LT</th><th>BT/TH</th></tr>
                </thead>
                <tbody>{$rows}</tbody>
              </table>
            </div>
          </div>
          HTML;
      }
      $tabsId = 'education-semesters-' . $this->key($program['key'] ?? '', 'program-' . $index);
      $tabsId = $this->e($tabsId);
      $firstSemesterKey = $this->e($firstSemesterKey);
      $sourceMeta = $this->sourceMeta($program);
      $details = <<<HTML
        {$sourceMeta}
        <div data-tabs data-tabs-id="{$tabsId}" data-tabs-panel-active="{$firstSemesterKey}">
          <div class="education-semester-tabs flex gap-2 overflow-x-auto py-5">{$tabs}</div>
          {$panels}
        </div>
        HTML;

      $content .= $this->accordion($program, $index, $details);
    }
    return $this->detailSection('danh-sach-mon-hoc', $data, $this->accordionGroup($data, $content), $context);
  }

  private function detailSection(string $id, array $data, string $body, CmsRenderContext $context, bool $showDescription = true, bool $showHeader = true): string
  {
    $sectionId = $this->e($id);
    $titleId = $this->e($id . '-title');
    $title = $this->e($data['title'] ?? '');
    $sectionAttributes = $context->sectionAttributes();
    $titleAttributes = $context->textAttributes('title');
    $descriptionAttributes = $context->textAttributes('description', true);
    $description = $showDescription
      ? '<p class="section-title__subtitle"' . $descriptionAttributes . '>' . $this->e($data['description'] ?? '') . '</p>'
      : '';
    $header = $showHeader ? <<<HTML
            <header class="section-title mb-8" aria-labelledby="{$titleId}">
              <h2 id="{$titleId}" class="section-title__heading"{$titleAttributes}>{$title}</h2>
              {$description}
            </header>
      HTML : '';

    return <<<HTML
      <section class="py-8 container" id="{$sectionId}"{$sectionAttributes}>
          <div class="container-wrapper">
            {$header}
            {$body}
          </div>
      </section>
      HTML;
  }

  private function accordionGroup(array $data, string $content): string
  {
    $programs = $this->items($data, 'programs');
    $defaultValue = isset($programs[0])
      ? $this->key($programs[0]['key'] ?? '', 'program-0')
      : '';

    $defaultValue = $this->e($defaultValue);

    return <<<HTML
      <div class="accordion education-accordion flex flex-col gap-4" data-accordion-type="single" data-accordion-collapsible data-accordion-default-value="{$defaultValue}">
        {$content}
      </div>
      HTML;
  }

  private function accordion(array $program, int $index, string $details): string
  {
    $key = $this->key($program['key'] ?? '', 'program-' . $index);
    $id = 'education-program-' . $key . '-' . (++$this->accordionId);
    $key = $this->e($key);
    $id = $this->e($id);
    $shortName = $this->e($program['short_name'] ?? '');
    $name = $this->e($program['name'] ?? '');

    return <<<HTML
      <article class="accordion_item border rounded-3xl overflow-hidden" data-accordion-value="{$key}">
        <h2>
          <button class="accordion__trigger flex w-full justify-between items-center gap-4 py-6 px-5 md:px-8" type="button">
            <span class="flex flex-col md:flex-row gap-1 md:gap-4"><span class="education-program-short-name">{$shortName}</span>{$name}</span>
          </button>
        </h2>
        <div class="accordion__content p-0" id="{$id}">
          <div class="px-5 md:px-8 pb-8">{$details}</div>
        </div>
      </article>
      HTML;
  }

  private function sourceMeta(array $program): string
  {
    $updatedAt = $this->e($program['updated_at'] ?? '');

    return <<<HTML
      <div class="education-source flex flex-col md:flex-row justify-between gap-4 text-sm pb-4">
        <span>Cập nhật: {$updatedAt}</span>
      </div>
      HTML;
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
