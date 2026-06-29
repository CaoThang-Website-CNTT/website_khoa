<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Cms\CmsStaticPageRenderer;
use App\Middlewares\Traits\HasDashboardRouting;
use App\Services\{PostService, CarouselService, MenuService, WebSettingsService, CmsPageService};
use App\Editor\BlockRenderer;
use App\Models\Menu;

class SiteController extends Controller
{
  use HasDashboardRouting;

  private MenuService $_menuService;
  private PostService $_postService;
  private CarouselService $_carouselService;
  private WebSettingsService $_settingService;
  private CmsPageService $_cmsPageService;

  /**
   * Header Menu
   */
  private ?Menu $_headerMenu = null;

  /**
   * Settings được load một lần tại constructor và tái sử dụng cho mọi method.
   * Key là setting key, value là cast_value đã được service xử lý.
   *
   * Chỉ load các group cần thiết cho public site: general, contact, seo, social.
   * Đây là substitute cho cache - khi có cache layer thật thì
   * chỉ cần thay getByGroup() bằng getAutoloaded() ở service layer.
   *
   * @var array<string, mixed>
   */
  private array $_settings = [];

  /**
   * Groups cần load cho public site.
   * Thêm/bớt group tại đây khi site mở rộng.
   */
  private const PRELOAD_GROUPS = ['general', 'contact', 'seo', 'social'];

  public function __construct(
    MenuService $menuService,
    CarouselService $carouselService,
    PostService $postService,
    WebSettingsService $settingService,
    CmsPageService $cmsPageService,
  ) {
    $this->_menuService = $menuService;
    $this->_carouselService = $carouselService;
    $this->_postService = $postService;
    $this->_settingService = $settingService;
    $this->_cmsPageService = $cmsPageService;

    $this->_loadHeaderMenu();
    $this->_loadSettings();
  }

  public function index()
  {

    // Lấy slide carousel (Kèm fallback an toàn nếu chưa có dữ liệu)
    $carousel = $this->_carouselService->getBySlugWithSlides("landing-page", with_media: true);
    $carouselSlides = $carousel !== null ? $carousel->slides : [];

    $featuredNews = $this->_postService->getFeaturedPosts(4, true, ['status' => 'published']);

    // Lấy 3 bài viết mới nhất không phải bài nổi bật
    $allNewsPageable = $this->_postService->getPosts(1, 3, true, [
      'status' => 'published',
      'is_featured' => "0"
    ]);
    $latestNewsItems = $allNewsPageable->getItems();

    $cmsHtml = $this->_renderCmsPage('landing', [
      'carouselSlides' => $carouselSlides,
      'featuredNews' => $featuredNews,
      'latestNewsItems' => $latestNewsItems,
    ]);

    return $this->render('site/landing', [
      'headerMenu' => $this->_headerMenu->items,
      'carouselSlides' => $carouselSlides,
      'featuredNews' => $featuredNews,
      'latestNewsItems' => $latestNewsItems,
      'cmsHtml' => $cmsHtml,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function news_index()
  {

    $featuredNews = $this->_postService->getFeaturedPosts(2, true, ['status' => 'published']);

    $allNews = $this->_postService->getPosts(1, 6, true, [
      'status' => 'published',
      'is_featured' => "0"
    ]);

    $pageUrl = url('tin-tuc');
    $siteTitle = $this->_settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';
    $pageTitle = 'Tin tức & Sự kiện';
    $pageDescription = 'Tin tức và sự kiện mới nhất từ Khoa Công nghệ Thông tin. Cập nhật thông tin về sinh viên, nghiên cứu, tuyển dụng và các sự kiện đặc biệt.';

    return $this->render('site/news/index', [
      'headerMenu' => $this->_headerMenu->items,
      'featuredNews' => $featuredNews,
      'allNews' => $allNews,
      'settings' => $this->_settings,
      'pageTitle' => $pageTitle,
      'pageDescription' => $pageDescription,
      'pageCanonical' => 'tin-tuc',
      'pageSeo' => [
        'og:title' => seo_title($pageTitle, $siteTitle),
        'og:description' => $pageDescription,
        'og:type' => 'website',
        'og:url' => $pageUrl,
        'og:site_name' => $siteTitle,
      ]
    ], "site_layout");
  }

  public function news_show($slug)
  {
    $news = $this->_postService->getPostBySlug($slug, with_author: true);
    $result = BlockRenderer::compile($news->content_json);

    $description = $news->seo_description;
    if (empty($description)) {
      $plainText = strip_tags($result->html);
      $plainText = preg_replace('/\s+/', ' ', $plainText);
      $description = mb_substr(trim($plainText), 0, 155) . (mb_strlen($plainText) > 155 ? '...' : '');
    }

    $pageUrl = url('tin-tuc/' . $news->slug);
    $imageUrl = $news->toArray()['image_url'] ?? url('public/img/default-post-thumb.jpg');
    $siteTitle = $this->_settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';

    return $this->render('site/news/detail', [
      'headerMenu' => $this->_headerMenu->items,
      'news' => $news,
      'newsSettings' => json_decode($news->settings_json ?? '{}', true)['settings'] ?? [],
      'detail' => $result,
      'settings' => $this->_settings,
      'pageTitle' => $news->title,
      'pageDescription' => $description,
      'pageCanonical' => 'tin-tuc/' . $news->slug,
      'pageSeo' => [
        'og:title' => seo_title($news->title, $siteTitle),
        'og:description' => $description,
        'og:type' => 'article',
        'og:url' => $pageUrl,
        'og:image' => $imageUrl,
        'og:site_name' => $siteTitle,
        'twitter:card' => 'summary_large_image',
        'twitter:title' => seo_title($news->title, $siteTitle),
        'twitter:description' => $description,
        'twitter:image' => $imageUrl,
      ]
    ], "site_layout");
  }

  public function about()
  {
    $cmsHtml = $this->_renderCmsPage('about');

    return $this->render('site/about', [
      'headerMenu' => $this->_headerMenu->items,
      'cmsHtml' => $cmsHtml,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function education(): void
  {
    $this->_renderEducationPage('education', 'Đào tạo', 'Khám phá chương trình đào tạo, chuẩn đầu ra và kế hoạch học tập của Khoa Công nghệ thông tin.', 'dao-tao');
  }

  public function admissions(): void
  {
    $this->_renderEducationPage('admissions', 'Thông tin tuyển sinh', 'Thông tin định hướng chương trình và liên kết đến cổng tuyển sinh chính thức của Trường Cao đẳng Kỹ thuật Cao Thắng.', 'dao-tao/tuyen-sinh');
  }

  public function academicPrograms(): void
  {
    $this->_renderEducationPage('academic-programs', 'Chương trình đào tạo', 'Tổng quan ba chương trình đào tạo cao đẳng của Khoa Công nghệ thông tin.', 'dao-tao/chuong-trinh-dao-tao');
  }

  public function programOutcomes(): void
  {
    $this->_renderEducationPage('program-outcomes', 'Chuẩn đầu ra', 'Mục tiêu chương trình và chuẩn đầu ra của các ngành thuộc Khoa Công nghệ thông tin.', 'dao-tao/chuan-dau-ra');
  }

  public function curriculum(): void
  {
    $this->_renderEducationPage('curriculum', 'Danh sách môn học', 'Kế hoạch học tập, tín chỉ và thời lượng lý thuyết, thực hành theo từng học kỳ.', 'dao-tao/danh-sach-mon-hoc');
  }

  public function portal(Request $request): void
  {
    $user = $request->session()->authUser();

    $this->redirect($this->dashboardFor($user['role'] ?? null));
  }

  // ============================================================================
  // Private helpers
  // ============================================================================

  /**
   * Load Header Menu
   */
  private function _loadHeaderMenu(): void
  {
    $this->_headerMenu = $this->_menuService->getMenuByKeyWithItems('header_menu');
  }

  /**
   * Load tất cả settings thuộc PRELOAD_GROUPS vào $_settings.
   * Kết quả là flat map: key → cast_value.
   */
  private function _loadSettings(): void
  {
    foreach (self::PRELOAD_GROUPS as $group) {
      $rows = $this->_settingService->getByGroup($group);
      foreach ($rows as $setting) {
        $this->_settings[$setting->key] = $setting->cast_value;
      }
    }
  }

  private function _renderCmsPage(string $slug, array $context = []): string
  {
    $page = $this->_cmsPageService->getPublishedPageBySlug($slug)
      ?? $this->_cmsPageService->getPageBySlug($slug);

    return (new CmsStaticPageRenderer($context, pageSlug: $slug))->render($page->content());
  }

  private function _renderEducationPage(string $slug, string $title, string $description, string $canonical): void
  {
    $siteTitle = $this->_settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';
    $this->render('site/education', [
      'headerMenu' => $this->_headerMenu->items,
      'cmsHtml' => $this->_renderCmsPage($slug),
      'settings' => $this->_settings,
      'pageTitle' => $title,
      'pageDescription' => $description,
      'pageCanonical' => $canonical,
      'pageSeo' => [
        'og:title' => seo_title($title, $siteTitle),
        'og:description' => $description,
        'og:type' => 'website',
        'og:url' => url($canonical),
        'og:site_name' => $siteTitle,
      ],
    ], 'site_layout');
  }
}
