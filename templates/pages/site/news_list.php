<section class="about__breadcrumb">
  <div class="container-wrapper py-4 flex items-center gap-2">
    <a href="/" class="about__breadcrumb-link flex items-center gap-1">
      <div class="about__breadcrumb-icon-wrapper flex items-center justify-center">
        <i class="fa-regular fa-house"></i>
      </div>
      <span class="about__breadcrumb-text text-sm">Trang chủ</span>
    </a>
    <i class="fa-solid fa-chevron-right text-xs"></i>
    <div class="about__breadcrumb-link about__breadcrumb-link--active flex items-center font-medium">
      <span class="about__breadcrumb-text text-sm">Tin tức</span>
    </div>
  </div>
</section>
<!--News List Title-->
<section class="news-list__title-section container">
  <div class="container-wrapper">
    <div class="news-list__title-header">
      <h1 class="news-list__title">Tin tức &amp; Sự kiện</h1>
      <p class="news-list__subtitle">
        Cập nhật thông tin mới nhất từ Khoa CNTT
      </p>
    </div>
  </div>
</section>
<!---->
<!--News Searchbar-->
<section class="news-list__search-section container">
  <div class="container-wrapper">
    <form class="news-list__search-form" role="search">
      <div class="news-list__search-input-wrapper">
        <input class="news-list__search-input" placeholder="Tìm kiếm tin tức..." type="search" name="search"
          aria-label="Tìm kiếm tin tức" />
        <i class="fa-solid fa-magnifying-glass news-list__search-icon"></i>
      </div>
      <button type="button" class="news-list__filter-btn">
        <i class="fa-solid fa-filter"></i>
        <span>Lọc nâng cao</span>
      </button>
    </form>
    <div class="news-list__filter-chips">
      <button class="news-list__chip news-list__chip--active" data-category="all">
        <span>Tất cả</span>
      </button>
      <button class="news-list__chip" data-category="department">
        <span>Tin khoa</span>
      </button>
      <button class="news-list__chip" data-category="research">
        <span>Nghiên cứu</span>
      </button>
      <button class="news-list__chip" data-category="event">
        <span>Sự kiện</span>
      </button>
      <button class="news-list__chip" data-category="student">
        <span>Sinh viên</span>
      </button>
      <button class="news-list__chip" data-category="recruitment">
        <span>Tuyển dụng</span>
      </button>
      <button class="news-list__chip" data-category="award">
        <span>Giải thưởng</span>
      </button>
    </div>
  </div>
</section>
<!---->
<!--Feature News-->
<section class="news-list__featured-section container">
  <div class="container-wrapper">
    <div class="news-list__featured-header">
      <i class="fa-solid fa-arrow-trend-up news-list__featured-icon"></i>
      <h3 class="news-list__featured-title">Nổi bật</h3>
    </div>
    <div class="news-list__featured-grid">

      <article class="news-card news-card--featured">
        <div class="news-card__image-wrapper">
          <img class="news-card__image" src="./public/img/newslist01.jpg"
            alt="Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025" />
          <span class="news-card__badge" data-variant="primary">Sinh viên</span>
          <div class="news-card__views">
            <i class="fa-solid fa-arrow-trend-up"></i>
            <span>1243 lượt xem</span>
          </div>
        </div>
        <div class="news-card__content">
          <div class="news-card__meta">
            <span class="news-card__date">
              <i class="fa-regular fa-calendar"></i>
              <time datetime="2025-11-15">15/11/2025</time>
            </span>
            <span class="news-card__read-time">
              <i class="fa-regular fa-clock"></i>
              <span>5 phút đọc</span>
            </span>
          </div>
          <h3 class="news-card__title">
            Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025
          </h3>
          <p class="news-card__description">
            Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150 đội từ
            các trường đại học hàng đầu để giành giải Nhất. Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150
            đội từ
            các trường đại học hàng đầu để giành giải Nhất...
          </p>
          <a href="#" class="news-card__read-more">
            <span>Đọc tiếp</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </article>

      <article class="news-card news-card--featured">
        <div class="news-card__image-wrapper">
          <img class="news-card__image" src="./public/img/newslist02.png"
            alt="Khoa CNTT ký kết hợp tác với Google Cloud Platform" />
          <span class="news-card__badge" data-variant="primary">Tin khoa</span>
          <div class="news-card__views">
            <i class="fa-solid fa-arrow-trend-up"></i>
            <span>2156 lượt xem</span>
          </div>
        </div>
        <div class="news-card__content">
          <div class="news-card__meta">
            <span class="news-card__date">
              <i class="fa-regular fa-calendar"></i>
              <time datetime="2025-11-12">12/11/2025</time>
            </span>
            <span class="news-card__read-time">
              <i class="fa-regular fa-clock"></i>
              <span>4 phút đọc</span>
            </span>
          </div>
          <h3 class="news-card__title">
            Khoa CNTT ký kết hợp tác với Google Cloud Platform
          </h3>
          <p class="news-card__description">
            Thỏa thuận hợp tác chiến lược mở ra cơ hội đào tạo và chứng chỉ quốc
            tế cho sinh viên và giảng viên...
          </p>
          <a href="#" class="news-card__read-more">
            <span>Đọc tiếp</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </article>

    </div>
  </div>
</section>
<!--News List-->
<section class="news-list__list-section container">
  <div class="container-wrapper">
    <div class="news-list__list-header">
      <h3 class="news-list__list-title">Tất cả tin tức</h3>
      <div class="news-list__sort-wrapper">
        <span class="news-list__sort-label">Sắp xếp:</span>
        <div class="news-list__sort-dropdown" id="sort-dropdown">
          <button class="news-list__sort-trigger" type="button" aria-haspopup="listbox" aria-expanded="false">
            <span class="news-list__sort-selected" data-value="newest">Mới nhất</span>
            <i class="fa-solid fa-chevron-down news-list__sort-chevron"></i>
          </button>
          <ul class="news-list__sort-list" role="listbox" aria-label="Sắp xếp theo">
            <li class="news-list__sort-item" role="option" data-value="newest" aria-selected="true">
              Mới nhất
            </li>
            <li class="news-list__sort-item" role="option" data-value="oldest" aria-selected="false">
              Cũ nhất
            </li>
            <li class="news-list__sort-item" role="option" data-value="featured" aria-selected="false">
              Nổi bật nhất
            </li>
          </ul>
        </div>
      </div>
    </div>
    <ul class="news-list__items-list">

      <li class="news-list__item news-card news-card--horizontal">
        <div class="news-card__image-wrapper">
          <img class="news-card__image" src="./public/img/newslist01.jpg"
            alt="Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025" />
        </div>
        <div class="news-card__content">
          <div class="news-card__meta">
            <span class="news-card__badge" data-variant="secondary">Tin khoa</span>
            <span class="news-card__date">
              <i class="fa-regular fa-calendar"></i>
              <time datetime="2025-11-15">15/11/2025</time>
            </span>
            <span class="news-card__read-time">
              <i class="fa-regular fa-clock"></i>
              <span>5 phút đọc</span>
            </span>
            <span class="news-card__views-inline">
              <i class="fa-solid fa-eye"></i>
              <span>1,250 lượt xem</span>
            </span>
          </div>
          <h3 class="news-card__title">
            Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025
          </h3>
          <p class="news-card__description">
            Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150 đội từ
            các trường đại học hàng đầu để giành giải Nhất. Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150
            đội từ
            các trường đại học hàng đầu để giành giải Nhất...
          </p>
          <a href="#" class="news-card__read-more">
            <span>Xem chi tiết</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </li>

      <li class="news-list__item news-card news-card--horizontal">
        <div class="news-card__image-wrapper">
          <img class="news-card__image" src="./public/img/newslist01.jpg"
            alt="Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025" />
        </div>
        <div class="news-card__content">
          <div class="news-card__meta">
            <span class="news-card__badge" data-variant="secondary">Tin khoa</span>
            <span class="news-card__date">
              <i class="fa-regular fa-calendar"></i>
              <time datetime="2025-11-15">15/11/2025</time>
            </span>
            <span class="news-card__read-time">
              <i class="fa-regular fa-clock"></i>
              <span>5 phút đọc</span>
            </span>
            <span class="news-card__views-inline">
              <i class="fa-solid fa-eye"></i>
              <span>1,250 lượt xem</span>
            </span>
          </div>
          <h3 class="news-card__title">
            Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025
          </h3>
          <p class="news-card__description">
            Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150 đội từ
            các trường đại học hàng đầu để giành giải Nhất...
          </p>
          <a href="#" class="news-card__read-more">
            <span>Xem chi tiết</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </li>

      <li class="news-list__item news-card news-card--horizontal">
        <div class="news-card__image-wrapper">
          <img class="news-card__image" src="./public/img/newslist01.jpg"
            alt="Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025" />
        </div>
        <div class="news-card__content">
          <div class="news-card__meta">
            <span class="news-card__badge" data-variant="secondary">Tin khoa</span>
            <span class="news-card__date">
              <i class="fa-regular fa-calendar"></i>
              <time datetime="2025-11-15">15/11/2025</time>
            </span>
            <span class="news-card__read-time">
              <i class="fa-regular fa-clock"></i>
              <span>5 phút đọc</span>
            </span>
            <span class="news-card__views-inline">
              <i class="fa-solid fa-eye"></i>
              <span>1,250 lượt xem</span>
            </span>
          </div>
          <h3 class="news-card__title">
            Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025
          </h3>
          <p class="news-card__description">
            Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150 đội từ
            các trường đại học hàng đầu để giành giải Nhất...
          </p>
          <a href="#" class="news-card__read-more">
            <span>Xem chi tiết</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </li>

      <li class="news-list__item news-card news-card--horizontal">
        <div class="news-card__image-wrapper">
          <img class="news-card__image" src="./public/img/newslist01.jpg"
            alt="Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025" />
        </div>
        <div class="news-card__content">
          <div class="news-card__meta">
            <span class="news-card__badge" data-variant="secondary">Tin khoa</span>
            <span class="news-card__date">
              <i class="fa-regular fa-calendar"></i>
              <time datetime="2025-11-15">15/11/2025</time>
            </span>
            <span class="news-card__read-time">
              <i class="fa-regular fa-clock"></i>
              <span>5 phút đọc</span>
            </span>
            <span class="news-card__views-inline">
              <i class="fa-solid fa-eye"></i>
              <span>1,250 lượt xem</span>
            </span>
          </div>
          <h3 class="news-card__title">
            Sinh viên CNTT giành giải Nhất cuộc thi Lập trình toàn quốc 2025
          </h3>
          <p class="news-card__description">
            Đội tuyển sinh viên của Khoa CNTT đã xuất sắc vượt qua 150 đội từ
            các trường đại học hàng đầu để giành giải Nhất...
          </p>
          <a href="#" class="news-card__read-more">
            <span>Xem chi tiết</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </li>

    </ul>
  </div>
</section>
<nav class="news-list__pagination" aria-label="Page navigation">
  <button class="news-list__page-btn news-list__page-btn--nav" aria-label="Previous page" disabled>
    <span>Trước</span>
  </button>
  <button class="news-list__page-btn news-list__page-btn--active" aria-current="page">
    <span>1</span>
  </button>
  <button class="news-list__page-btn">
    <span>2</span>
  </button>
  <button class="news-list__page-btn">
    <span>3</span>
  </button>
  <button class="news-list__page-btn">
    <span>4</span>
  </button>
  <button class="news-list__page-btn">
    <span>5</span>
  </button>
  <button class="news-list__page-btn news-list__page-btn--nav" aria-label="Next page">
    <span>Sau</span>
  </button>
</nav>

<script>
//TODO: move to seperate file
document.addEventListener('DOMContentLoaded', function() {
  const dropdown = document.getElementById('sort-dropdown');
  if (!dropdown) return;

  const trigger = dropdown.querySelector('.news-list__sort-trigger');
  const list = dropdown.querySelector('.news-list__sort-list');
  const items = dropdown.querySelectorAll('.news-list__sort-item');
  const selected = dropdown.querySelector('.news-list__sort-selected');

  // Toggle dropdown
  trigger.addEventListener('click', function(e) {
    e.stopPropagation();
    const isOpen = list.classList.contains('news-list__sort-list--open');

    // Close all other dropdowns
    document.querySelectorAll('.news-list__sort-list--open').forEach(el => {
      el.classList.remove('news-list__sort-list--open');
      el.closest('.news-list__sort-dropdown')
        .querySelector('.news-list__sort-trigger')
        .setAttribute('aria-expanded', 'false');
    });

    // Toggle current dropdown
    if (!isOpen) {
      list.classList.add('news-list__sort-list--open');
      trigger.setAttribute('aria-expanded', 'true');
    } else {
      list.classList.remove('news-list__sort-list--open');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });

  // Handle item selection
  items.forEach(item => {
    item.addEventListener('click', function(e) {
      e.stopPropagation();

      // Update selected state
      items.forEach(i => {
        i.setAttribute('aria-selected', 'false');
      });
      this.setAttribute('aria-selected', 'true');

      // Update trigger text
      selected.textContent = this.textContent.trim();
      selected.setAttribute('data-value', this.getAttribute('data-value'));

      // Close dropdown
      list.classList.remove('news-list__sort-list--open');
      trigger.setAttribute('aria-expanded', 'false');

      // TODO: add sorting logic here
      // fetchNewsBySort(this.getAttribute('data-value'));
      console.log('Sort by:', this.getAttribute('data-value'));
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function() {
    list.classList.remove('news-list__sort-list--open');
    trigger.setAttribute('aria-expanded', 'false');
  });
});
</script>