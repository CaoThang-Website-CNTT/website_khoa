<!-- Breadcrumbs -->
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
        include_once BASE_PATH . '/templates/components/breadcrumb.php';
        renderBreadcrumb([
          ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => '/', 'title' => 'Trang chủ'],
          ['url' => '/tin-tuc', 'title' => 'Tin tức & Sự kiện'],
        ]);
      ?>
    </div>
  </div>
</section>

<!-- News Detail -->
<section>
  <div class="container">
    <div class="container-wrapper">
      <?php
        $payload['blocks'] = json_decode($news->content_json, true);
        $payload['meta'] = json_decode($news->settings_json, true);

        print_r($payload['meta']);

        echo \App\Editor\BlockRenderer::fromArray($payload);
      ?>
    </div>
  </div>
</section>