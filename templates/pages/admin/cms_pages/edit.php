<?php
$pageSettings = $page->settings();
$pagePayload = [
  'page' => [
    'title' => $page->title,
    'slug' => $page->slug,
    'status' => $page->status,
    'settings' => $pageSettings,
  ],
  'schema' => $schema,
  'document' => $document,
  'builderDocument' => $builderDocument ?? ['version' => 2, 'blocks' => [], 'globalStyles' => [], 'theme' => ['active' => 'default', 'options' => []]],
  'builderPublishedDocument' => $builderPublishedDocument ?? ['version' => 2, 'blocks' => [], 'globalStyles' => [], 'theme' => ['active' => 'default', 'options' => []]],
  'builderSnapshots' => $builderSnapshots ?? [],
  'builderActions' => [
    'draft' => url('admin/cms-pages/' . $schema['slug'] . '/builder-draft'),
    'publish' => url('admin/cms-pages/' . $schema['slug'] . '/builder-publish'),
  ],
  'urls' => [
    'base' => url(''),
    'public' => url('public'),
    'media' => url('public/media'),
  ],
];
?>

<?php include BASE_PATH . '/templates/partials/cms_editor_ui.php'; ?>

<?php $layout->start('scripts') ?>
<script>
  window.CmsPageEditor = <?= json_encode(
    $pagePayload,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_APOS
    | JSON_HEX_AMP
    | JSON_HEX_QUOT
  ) ?>;
</script>
<?php $layout->end() ?>
