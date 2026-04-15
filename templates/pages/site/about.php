<!-- BREADCRUMB: START -->
<nav class="about__breadcrumb" aria-label="Breadcrumb">
  <div class="container-wrapper py-4 flex items-center gap-2">
    <ol class="about__breadcrumb-list flex items-center gap-2" itemscope itemtype="https://schema.org/BreadcrumbList">
      <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center gap-1">
        <a href="/" itemprop="item" class="about__breadcrumb-link flex items-center gap-1">
          <div class="about__breadcrumb-icon-wrapper flex items-center justify-center">
            <i class="fa-regular fa-house"></i>
          </div>
          <span itemprop="name" class="about__breadcrumb-text text-sm">Trang chủ</span>
        </a>
        <meta itemprop="position" content="1">
      </li>
      <li class="about__breadcrumb-separator flex items-center">
        <i class="fa-solid fa-chevron-right text-xs"></i>
      </li>
      <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"
        class="about__breadcrumb-link about__breadcrumb-link--active flex items-center font-medium" aria-current="page">
        <span itemprop="name" class="about__breadcrumb-text text-sm">Giới Thiệu</span>
        <meta itemprop="position" content="2">
      </li>
    </ol>
  </div>
</nav>
<!-- BREADCRUMB: END -->

<!-- HERO: START -->
<section class="about__hero relative flex flex-col justify-start items-center overflow-hidden w-full" role="banner"
  aria-label="Giới thiệu về Khoa CNTT Cao Thắng">
  <img class="about__hero-bg object-cover absolute inset-0 z-0 w-full h-full" src="./public/img/about.jpg"
    alt="Khoa Công nghệ thông tin - Trường Cao đẳng Kỹ thuật Cao Thắng" loading="eager" fetchpriority="high">
  <div
    class="about__hero-content relative z-10 flex flex-col items-center justify-center text-center gap-6 w-full px-8">
    <div class="px-4 py-2">
      <span class="badge text-md" data-variant="primary">Về Chúng Tôi</span>
    </div>
    <h1 class="about__hero-title text-6xl text-center font-normal">Câu chuyện của Cao Thắng</h1>
    <p class="about__hero-desc text-2xl text-center">
      Từ những ngày đầu tiên đến hôm nay, Cao Thắng không ngừng phát triển để mang đến giáo dục công nghệ chất lượng cao
      cho sinh viên Việt Nam
    </p>
  </div>
  <div class="about__hero-overlay"></div>
</section>
<!-- HERO: END -->

<!-- HISTORY: START -->
<section class="about__history container my-16" aria-label="Lịch sử phát triển Khoa CNTT">
  <div class="about__history-container container-wrapper flex items-center justify-center gap-12">
    <figure class="about__history-image-container relative overflow-hidden rounded-3xl flex-1">
      <img class="about__history-image object-cover image" src="./public/img/about.jpg" alt="Khoa CNTT năm 2003"
        loading="lazy">
      <figcaption class="about__history-image-caption absolute inset-0 z-10 flex flex-col justify-end gap-2 p-8">
        <time class="about__history-image-year text-6xl" datetime="2003">2003</time>
        <h2 class="about__history-image-title text-2xl">Khoa CNTT được thành lập</h2>
      </figcaption>
    </figure>

    <div class="about__history-content w-full flex-1">
      <div class="about__history-badge px-4 py-2 flex items-center gap-2 badge mb-4" data-variant="secondary">
        <i class="fa-solid fa-graduation-cap"></i>
        <span class="about__history-badge-text font-medium text-md">Khoa Công nghệ Thông tin</span>
      </div>
      <h2 class="about__history-title text-5xl mb-6">27 năm đổi mới & phát triển</h2>

      <div class="about__history-timeline-wrapper flex flex-col gap-4 text-md mb-8">
        <ul class="about__history-timeline flex flex-col gap-4" aria-label="Dòng thời gian">
          <li class="about__history-timeline-item flex gap-4">
            <time class="about__history-timeline-year font-bold" datetime="1998">1998:</time>
            <span class="about__history-timeline-desc">Khoa Khoa Điện Tử - Tin Học, tiền thân của Khoa Công
              Nghệ Thông Tin được thành lập.</span>
          </li>
          <li class="about__history-timeline-item flex gap-4">
            <time class="about__history-timeline-year font-bold" datetime="2017">2017:</time>
            <span class="about__history-timeline-desc">Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ
              thông tin.</span>
          </li>
          <li class="about__history-timeline-item flex gap-4">
            <time class="about__history-timeline-year font-bold" datetime="2020">2020:</time>
            <span class="about__history-timeline-desc">Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ
              thông tin.</span>
          </li>
          <li class="about__history-timeline-item flex gap-4">
            <time class="about__history-timeline-year font-bold" datetime="2025">2025:</time>
            <span class="about__history-timeline-desc">Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ
              thông tin.</span>
          </li>
        </ul>
      </div>

      <div class="about__history-stats flex flex-row lg:flex-col gap-4 w-full lg:w-auto" role="list"
        aria-label="Thống kê nổi bật">
        <figure class="about__history-stat-card rounded-3xl p-4 flex flex-col flex-1" role="listitem">
          <figcaption class="about__history-stat-number text-3xl">5,247</figcaption>
          <p class="about__history-stat-label text-sm">Sinh viên tốt nghiệp</p>
        </figure>
        <figure class="about__history-stat-card rounded-3xl p-4 flex flex-col flex-1" role="listitem">
          <figcaption class="about__history-stat-number text-3xl">65</figcaption>
          <p class="about__history-stat-label text-sm">Giảng viên</p>
        </figure>
        <figure class="about__history-stat-card rounded-3xl p-4 flex flex-col flex-1" role="listitem">
          <figcaption class="about__history-stat-number text-3xl">28</figcaption>
          <p class="about__history-stat-label text-sm">Đối tác quốc tế</p>
        </figure>
      </div>
    </div>
  </div>
</section>
<!-- HISTORY: END -->

<!-- TRADITION: START -->
<section class="about__tradition container flex items-start my-16" aria-label="Truyền thống 100+ năm của trường">
  <div class="about__tradition-container container-wrapper flex items-start justify-center gap-12 ">
    <div class="about__tradition-content flex-1">
      <div class="about__tradition-badge px-4 py-2 flex items-center gap-2 badge mb-4" data-variant="accent">
        <i class="fa-solid fa-school"></i>
        <span class="about__tradition-badge-text font-medium text-md">Trường Cao Đẳng Kỹ Thuật Cao Thắng</span>
      </div>
      <header class="about__tradition-header">
        <h2 class="about__tradition-title text-5xl mb-6">100+ năm truyền thống</h2>
      </header>

      <ul class="about__tradition-timeline flex flex-col gap-4" aria-label="Dòng thời gian truyền thống">
        <li class="about__tradition-timeline-item flex gap-4">
          <time class="about__tradition-timeline-year font-bold" datetime="1906">1906:</time>
          <span class="about__tradition-timeline-desc">Chính thức thành lập Trường Cơ khí Á Châu (L'école
            des Mécaniciens Asiatiques), tiền thân của trường.</span>
        </li>
        <li class="about__tradition-timeline-item flex gap-4">
          <time class="about__tradition-timeline-year font-bold" datetime="1915">1915:</time>
          <span class="about__tradition-timeline-desc">Chủ tịch Tôn Đức Thắng nhập học.</span>
        </li>
        <li class="about__tradition-timeline-item flex gap-4">
          <time class="about__tradition-timeline-year font-bold" datetime="2004">2004:</time>
          <span class="about__tradition-timeline-desc">Chính thức đổi tên thành Trường Cao đẳng Kỹ thuật Cao
            Thắng.</span>
        </li>
        <li class="about__tradition-timeline-item flex gap-4">
          <time class="about__tradition-timeline-year font-bold" datetime="2016">2016:</time>
          <span class="about__tradition-timeline-desc">Đạt chuẩn kiểm định quốc tế ABET.</span>
        </li>
      </ul>
    </div>

    <figure class="about__tradition-image-container relative overflow-hidden rounded-3xl flex-1 my-5">
      <img class="about__tradition-image object-cover image" src="./public/img/about.jpg"
        alt="Trường Cao Thắng năm 1906" loading="lazy">
      <figcaption class="about__tradition-image-caption absolute inset-0 z-10 flex flex-col justify-end gap-2 p-8">
        <time class="about__tradition-image-year text-6xl" datetime="1906">1906</time>
        <h3 class="about__tradition-image-title text-2xl">Trường được thành lập</h3>
      </figcaption>
    </figure>
  </div>
</section>
<!-- TRADITION: END -->

<!-- FEATURES: START -->
<section class="about__features container my-16" aria-label="Thành tựu và đặc điểm nổi bật">
  <div class="about__features-container container-wrapper">
    <div class="about__features-grid">
      <!-- Card 1: Primary Achievement (Blue - Large) -->
      <article class="about__feature-card about__feature-card--primary" aria-label="Thành tựu đặc biệt">
        <img class="about__feature-card__bg image" src="./public/img/about02.png" alt="Chứng nhận quốc tế cho Khoa CNTT"
          loading="lazy">
        <div class="about__feature-card__content">
          <header class="about__feature-card__header">
            <span class="about__feature-card__badge">
              <i class="fa-solid fa-globe" aria-hidden="true"></i>
              <span>Chứng nhận Quốc tế</span>
            </span>
            <h2 class="about__feature-card__title">Thành tựu đặc biệt/tiêu điểm</h2>
            <p class="about__feature-card__desc">Chi tiết Thành tựu đặc biệt/tiêu điểm.</p>
          </header>
          <div class="about__feature-card__stats" role="list" aria-label="Thống kê thành tựu">
            <figure class="about__feature-card__stat px-6 py-4" role="listitem">
              <figcaption class="about__feature-card__stat-num text-3xl">30+</figcaption>
              <p class="about__feature-card__stat-label text-sm">Quốc gia công nhận</p>
            </figure>
            <figure class="about__feature-card__stat px-6 py-4" role="listitem">
              <figcaption class="about__feature-card__stat-num text-3xl">Top 5</figcaption>
              <p class="about__feature-card__stat-label text-sm">Khoa CNTT VN</p>
            </figure>
          </div>
        </div>
      </article>

      <!-- Card 2: Instructors (Green - Small) -->
      <article class="about__feature-card about__feature-card--green" aria-label="Giảng viên">
        <div class="about__feature-card__icon" aria-hidden="true">
          <i class="fa-solid fa-user-group"></i>
        </div>
        <div class="about__feature-card__text">
          <h3 class="about__feature-card__num">25+</h3>
          <h4 class="about__feature-card__title">Giảng Viên</h4>
          <p class="about__feature-card__desc">Có hơn 15 năm kinh nghiệm trong việc giảng dạy</p>
        </div>
      </article>

      <!-- Card 3: Awards (Purple-Pink - Small) -->
      <article class="about__feature-card about__feature-card--purple-pink" aria-label="Giải thưởng">
        <div class="about__feature-card__icon" aria-hidden="true">
          <i class="fa-solid fa-award"></i>
        </div>
        <div class="about__feature-card__text">
          <h3 class="about__feature-card__num">50+</h3>
          <h4 class="about__feature-card__title">Giải Thưởng</h4>
          <p class="about__feature-card__desc">Từ chính phủ và các tổ chức kiểm định quốc tế</p>
        </div>
      </article>

      <!-- Card 4: Learning Environment (Image - Tall Left) -->
      <article class="about__feature-card about__feature-card--image" aria-label="Môi trường năng động">
        <img class="about__feature-card__bg image" src="./public/img/about03.png" alt="Cộng đồng học tập năng động"
          loading="lazy">
        <div class="about__feature-card__overlay"></div>
        <div class="about__feature-card__content about__feature-card__content--bottom">
          <span class="about__feature-card__title about__feature-card__title--large">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span>Cộng đồng học tập</span>
          </span>
          <h3 class="about__feature-card__desc about__feature-card__desc--large">Môi trường năng động & sáng tạo</h3>
        </div>
      </article>

      <!-- Card 5: Scholarships (Orange - Small) -->
      <article class="about__feature-card about__feature-card--orange" aria-label="Học bổng hàng năm">
        <div class="about__feature-card__icon" aria-hidden="true">
          <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="about__feature-card__text">
          <h3 class="about__feature-card__num">100+</h3>
          <h4 class="about__feature-card__title">Học bổng hàng năm</h4>
          <p class="about__feature-card__desc">Từ học bổng toàn phần đến các suất trao đổi quốc tế</p>
        </div>
      </article>

      <!-- Card 6: Labs (White - Small) -->
      <article class="about__feature-card about__feature-card--white" aria-label="Phòng Lab hiện đại">
        <div class="about__feature-card__icon about__feature-card__icon--blue" aria-hidden="true">
          <i class="fa-solid fa-flask"></i>
        </div>
        <div class="about__feature-card__text">
          <h3 class="about__feature-card__num about__feature-card__num--blue">10+</h3>
          <h4 class="about__feature-card__title">Phòng Lab hiện đại</h4>
          <p class="about__feature-card__desc about__feature-card__desc--dark">Trang bị công nghệ tiên tiến phục vụ học
            tập và nghiên cứu</p>
        </div>
      </article>

      <!-- Card 7: Partners (Dark - Small) -->
      <article class="about__feature-card about__feature-card--dark" aria-label="Doanh nghiệp đối tác">
        <div class="about__feature-card__icon" aria-hidden="true">
          <i class="fa-solid fa-handshake"></i>
        </div>
        <div class="about__feature-card__text">
          <h3 class="about__feature-card__num">100+</h3>
          <h4 class="about__feature-card__title">Doanh nghiệp đối tác</h4>
          <p class="about__feature-card__desc">FPT, Viettel, Samsung, Google và nhiều công ty hàng đầu</p>
        </div>
      </article>

      <!-- Card 8: Alumni (Cyan - Small) -->
      <article class="about__feature-card about__feature-card--cyan" aria-label="Cựu sinh viên">
        <div class="about__feature-card__icon" aria-hidden="true">
          <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div class="about__feature-card__text">
          <h3 class="about__feature-card__num">1,000+</h3>
          <h4 class="about__feature-card__title">Cựu sinh viên</h4>
          <p class="about__feature-card__desc">Làm việc tại các công ty công nghệ hàng đầu toàn cầu</p>
        </div>
      </article>
    </div>
  </div>
</section>
<!-- FEATURES: END -->