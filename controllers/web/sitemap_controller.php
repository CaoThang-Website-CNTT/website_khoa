<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PostService;
use SimpleXMLElement;

class SitemapController extends Controller
{
  private PostService $_postService;

  public function __construct(PostService $postService)
  {
    $this->_postService = $postService;
  }

  public function index()
  {
    $cacheDir = BASE_PATH . '/storage/cache';
    $cacheFile = $cacheDir . '/sitemap.xml';
    $cacheTtl = isset($_ENV['SITEMAP_CACHE_TTL']) ? (int)$_ENV['SITEMAP_CACHE_TTL'] : 86400; // 24 hours

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
      header('Content-Type: application/xml; charset=UTF-8');
      readfile($cacheFile);
      exit;
    }

    $now = date('c');
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

    $staticUrls = [
      ['loc' => url('/'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0'],
      ['loc' => url('tin-tuc'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.8'],
      ['loc' => url('gioi-thieu'), 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.6'],
      ['loc' => url('lien-he'), 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.6']
    ];

    foreach ($staticUrls as $url) {
      $urlElement = $xml->addChild('url');
      $urlElement->addChild('loc', htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8'));
      $urlElement->addChild('lastmod', $url['lastmod']);
      $urlElement->addChild('changefreq', $url['changefreq']);
      $urlElement->addChild('priority', $url['priority']);
    }

    $posts = $this->_postService->getPostsForSitemap();

    foreach ($posts as $post) {
      $lastmod = date('c', strtotime($post['updated_at'] ?? ($post['published_at'] ?? $post['created_at'])));
      $urlElement = $xml->addChild('url');
      $urlElement->addChild('loc', htmlspecialchars(url('tin-tuc/' . $post['slug']), ENT_XML1, 'UTF-8'));
      $urlElement->addChild('lastmod', $lastmod);
      $urlElement->addChild('changefreq', 'weekly');
      $urlElement->addChild('priority', '0.7');
    }

    $xmlString = $xml->asXML();

    if (!is_dir($cacheDir)) {
      mkdir($cacheDir, 0755, true);
    }
    file_put_contents($cacheFile, $xmlString);

    header('Content-Type: application/xml; charset=UTF-8');
    echo $xmlString;
    exit;
  }
}
