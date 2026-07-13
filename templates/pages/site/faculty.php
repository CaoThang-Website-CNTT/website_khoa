<?php
$layout->start('head');
?>
<link rel="stylesheet"
  href="<?= url('public/css/pages/faculty.css') ?>?v=<?= filemtime(BASE_PATH . '/public/css/pages/faculty.css') ?>">
<?php
$layout->end();
$layout->start('content');
echo $cmsHtml ?? '';
?>
<script src="<?= url('public/js/pages/site/faculty.js') ?>" type="module"></script>
<?php $layout->end(); ?>
