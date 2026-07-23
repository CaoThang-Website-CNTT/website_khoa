<?php

use App\Core\Schema\TableBuilder;
use App\Migration\BaseMigration;

return new class extends BaseMigration
{
  public function forward(TableBuilder $schema): void
  {
    $this->orderHistory(true);
  }

  public function back(TableBuilder $schema): void
  {
    $this->orderHistory(false);
  }

  private function orderHistory(bool $schoolFirst): void
  {
    $connection = Database::getInstance()->getConnection();
    $statement = $connection->prepare('SELECT content_json FROM cms_pages WHERE slug = ? LIMIT 1');
    $statement->execute(['about']);
    $contentJson = $statement->fetchColumn();

    if (!is_string($contentJson) || $contentJson === '') {
      return;
    }

    $content = json_decode($contentJson, true, 512, JSON_THROW_ON_ERROR);
    foreach ($content['sections'] ?? [] as &$section) {
      if (($section['type'] ?? '') !== 'sections/history') {
        continue;
      }

      $history = $section['data']['sections'] ?? [];
      usort($history, static function (array $left, array $right) use ($schoolFirst): int {
        $leftYear = (int) ($left['year'] ?? PHP_INT_MAX);
        $rightYear = (int) ($right['year'] ?? PHP_INT_MAX);
        return $schoolFirst ? $leftYear <=> $rightYear : $rightYear <=> $leftYear;
      });
      $section['data']['sections'] = $history;
      break;
    }
    unset($section);

    $this->updateData(
      'cms_pages',
      ['content_json' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)],
      ['slug' => 'about']
    );
  }
};
