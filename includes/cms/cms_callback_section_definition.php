<?php

namespace App\Cms;

final class CmsCallbackSectionDefinition implements CmsSectionDefinitionInterface
{
  /**
   * @param callable(array, CmsRenderContext): string $renderer
   * @param array<string, string> $variants
   * @param array<string, string> $fieldLabels
   */
  public function __construct(
    private string $_type,
    private string $_label,
    private array $_defaults,
    private array $_editableFields,
    private array $_variants,
    private $renderer,
    private array $_fieldLabels = [],
  ) {
  }

  public function type(): string
  {
    return $this->_type;
  }

  public function label(): string
  {
    return $this->_label;
  }

  public function defaults(): array
  {
    return $this->_defaults;
  }

  public function editableFields(): array
  {
    return $this->_editableFields;
  }

  public function fieldLabels(): array
  {
    return $this->_fieldLabels;
  }

  public function variants(): array
  {
    return $this->_variants;
  }

  public function render(array $data, CmsRenderContext $context): string
  {
    return (string) call_user_func($this->renderer, $data, $context);
  }
}
