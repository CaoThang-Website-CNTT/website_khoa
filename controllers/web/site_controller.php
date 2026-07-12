<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Cms\CmsStaticPageRenderer;
use App\Middlewares\Traits\HasDashboardRouting;
use App\Services\{PostService, CarouselService, MenuService, WebSettingsService, CmsPageService, CategoryService};
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
  private CategoryService $_categoryService;

  /**
   * Header Menu
   */
  private ?Menu $_headerMenu = null;
  private ?Menu $_footerMenu = null;

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
    CategoryService $categoryService,
  ) {
    $this->_menuService = $menuService;
    $this->_carouselService = $carouselService;
    $this->_postService = $postService;
    $this->_settingService = $settingService;
    $this->_cmsPageService = $cmsPageService;
    $this->_categoryService = $categoryService;

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
      'footerMenu' => $this->_footerMenu?->items ?? [],
      'carouselSlides' => $carouselSlides,
      'featuredNews' => $featuredNews,
      'latestNewsItems' => $latestNewsItems,
      'cmsHtml' => $cmsHtml,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function news_index(Request $request)
  {
    $page = max(1, (int) $request->query('page', 1));
    $search = trim((string) $request->query('search', ''));
    $category = trim((string) $request->query('category', $request->query('filter', '')));
    $sortMode = $request->query('sort') === 'oldest' ? 'oldest' : 'newest';
    $filters = [
      'status' => 'published',
      'search' => $search,
      'category' => $category,
      'sort' => 'published_at',
      'order' => $sortMode === 'oldest' ? 'asc' : 'desc',
    ];

    $featuredPage = $this->_postService->getPosts(1, 2, true, [
      ...$filters,
      'is_featured' => '1',
      'order' => 'desc',
    ]);
    $featuredNews = $featuredPage->getItems();
    $allNews = $this->_postService->getPosts($page, 6, true, [
      ...$filters,
      'is_featured' => '0',
    ]);

    $allCategories = $this->_categoryService->getAllCategories();
    $categoryMap = [];
    foreach ($allCategories as $item) {
      $categoryMap[(string) $item->slug] = $item;
    }
    $filterSlugs = [
      'hoat-dong', 'cong-tac-giang-day', 'nghien-cuu-khoa-hoc', 'hoc-thuat',
      'thi-dua-doan-the', 'phong-trao-ngoai-khoa', 'clb-tin-hoc', 'thong-bao',
      'tuyen-dung',
    ];
    $newsCategories = array_values(array_filter(array_map(
      fn(string $slug) => $categoryMap[$slug] ?? null,
      $filterSlugs,
    )));

    $activeCategoryNames = [];
    foreach (array_filter(array_map('trim', explode(',', $category))) as $slug) {
      if (isset($categoryMap[$slug])) $activeCategoryNames[] = $categoryMap[$slug]->name;
    }

    $pageUrl = url('tin-tuc');
    $siteTitle = $this->_settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';
    $pageTitle = $search !== ''
      ? 'Kết quả tìm kiếm tin tức'
      : ($activeCategoryNames ? implode(' · ', $activeCategoryNames) : 'Tin tức & Sự kiện');
    $pageDescription = $search !== ''
      ? 'Kết quả tìm kiếm cho “' . $search . '” trên trang tin Khoa Công nghệ Thông tin.'
      : 'Tin tức và sự kiện mới nhất từ Khoa Công nghệ Thông tin. Cập nhật thông tin về sinh viên, nghiên cứu và các hoạt động của Khoa.';

    return $this->render('site/news/index', [
      'headerMenu' => $this->_headerMenu->items,
      'footerMenu' => $this->_footerMenu?->items ?? [],
      'featuredNews' => $featuredNews,
      'allNews' => $allNews,
      'newsCategories' => $newsCategories,
      'newsQuery' => compact('search', 'category', 'sortMode'),
      'activeCategoryNames' => $activeCategoryNames,
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
      'footerMenu' => $this->_footerMenu?->items ?? [],
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
      'footerMenu' => $this->_footerMenu?->items ?? [],
      'cmsHtml' => $cmsHtml,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function faculty(): void
  {
    $title = 'Đội ngũ giảng viên';
    $description = 'Đội ngũ giảng viên Khoa Công nghệ Thông tin giàu kinh nghiệm, vững chuyên môn và tiên phong đổi mới.';
    $siteTitle = $this->_settings['site_title'] ?? 'Khoa Công nghệ Thông tin';

    $this->render('site/faculty', [
      'headerMenu' => $this->_headerMenu->items,
      'footerMenu' => $this->_footerMenu?->items ?? [],
      'cmsHtml' => $this->_renderCmsPage('faculty'),
      'settings' => $this->_settings,
      'pageTitle' => $title,
      'pageDescription' => $description,
      'pageCanonical' => 'giang-vien',
      'pageSeo' => [
        'og:title' => seo_title($title, $siteTitle),
        'og:description' => $description,
        'og:type' => 'website',
        'og:url' => url('giang-vien'),
        'og:site_name' => $siteTitle,
      ],
    ], 'site_layout');
  }

  public function partners(): void
  {
    $partnerships = $this->_cmsPageService->getSectionData('landing', 'partnerships');
    $partners = array_values(array_filter(
      is_array($partnerships['partners'] ?? null) ? $partnerships['partners'] : [],
      static fn(mixed $partner): bool => is_array($partner) && trim((string) ($partner['name'] ?? '')) !== '',
    ));
    $siteTitle = $this->_settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';
    $description = 'Các doanh nghiệp đồng hành cùng Khoa Công nghệ thông tin trong đào tạo, thực tập và tuyển dụng.';

    $this->render('site/partners', [
      'headerMenu' => $this->_headerMenu->items,
      'footerMenu' => $this->_footerMenu?->items ?? [],
      'partners' => $partners,
      'settings' => $this->_settings,
      'pageTitle' => 'Doanh nghiệp đối tác',
      'pageDescription' => $description,
      'pageCanonical' => 'viec-lam/doanh-nghiep',
      'pageSeo' => ['og:title' => seo_title('Doanh nghiệp đối tác', $siteTitle), 'og:description' => $description,
        'og:type' => 'website', 'og:url' => url('viec-lam/doanh-nghiep'), 'og:site_name' => $siteTitle],
    ], 'site_layout');
  }

  public function education(): void
  {
    $this->_renderEducationPage('education', 'Đào tạo', 'Khám phá chương trình đào tạo, chuẩn đầu ra và kế hoạch học tập của Khoa Công nghệ thông tin.', 'dao-tao');
  }

  public function admissions(): void
  {
    $this->redirect('dao-tao#tuyen-sinh', 301);
  }

  public function academicPrograms(): void
  {
    $this->redirect('dao-tao#chuong-trinh-dao-tao', 301);
  }

  public function programOutcomes(): void
  {
    $this->redirect('dao-tao#chuan-dau-ra', 301);
  }

  public function curriculum(): void
  {
    $this->redirect('dao-tao#danh-sach-mon-hoc', 301);
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
    $this->_footerMenu = $this->_menuService->getMenuByKeyWithItems('footer_menu');
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
      'footerMenu' => $this->_footerMenu?->items ?? [],
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
