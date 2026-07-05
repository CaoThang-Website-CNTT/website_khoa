<?php

namespace App\Cms;

interface CmsSectionDefinitionInterface
{
  public function type(): string;

  public function label(): string;

  public function defaults(): array;

  public function editableFields(): array;

  /**
   * @return array<string, string>
   */
  public function fieldLabels(): array;

  /**
   * @return array<string, string>
   */
  public function variants(): array;

  public function render(array $data, CmsRenderContext $context): string;
}
