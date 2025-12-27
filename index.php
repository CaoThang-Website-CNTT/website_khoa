<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" sizes="32x32" href="./public/favicon-32x32.png">
  <link rel="preload" as="style" href="./public/css/fonts.css">
  <link rel="stylesheet" href="./public/css/fonts.css">
  <link rel="preload" as="style" href="./public/css/base.css">
  <link rel="stylesheet" href="./public/css/base.css">
  <link rel="preload" as="style" href="./public/css/main.css">
  <link rel="stylesheet" href="./public/css/main.css">
  <link rel="stylesheet" href="./public/css/carousel.css">
  <title>Khoa Công nghệ Thông tin - Trường CĐKT Cao Thắng</title>
  <script src="./public/js/utils.js"></script>
</head>

<body>
  <!-- HEADER: START -->
  <header class="z-50">
    <!-- BANNER: START -->
    <div class="banner py-2">
      <div class="container flex gap-4 px-4 font-light">
        <div class="flex items-center gap-1">
          <svg class="banner__icon" aria-label="Email" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.667 12C14.6669 12.7363 14.0693 13.333 13.333 13.333H2.66699C1.93067 13.333 1.3331 12.7363 1.33301 12V5.25977L7.06445 8.90723C7.07009 8.91081 7.07625 8.91364 7.08203 8.91699C7.36162 9.07938 7.67961 9.16498 8.00293 9.16504C8.3261 9.16499 8.64433 9.07922 8.92383 8.91699C8.92943 8.91373 8.93591 8.90974 8.94141 8.90625L14.667 5.25879V12ZM13.333 2.66602C14.0694 2.66602 14.667 3.26362 14.667 4V4.16699C14.5747 4.16676 14.481 4.19189 14.3975 4.24512L8.41699 8.05371C8.29094 8.12598 8.14828 8.16499 8.00293 8.16504C7.85701 8.16498 7.71332 8.12654 7.58691 8.05371L1.60156 4.24512C1.51809 4.19201 1.42515 4.16684 1.33301 4.16699V4C1.33301 3.26362 1.93061 2.66602 2.66699 2.66602H13.333Z" fill="currentColor" />
          </svg>

          cntt@caothang.edu.vn
        </div>
        <div class="flex items-center gap-1">
          <svg class="banner__icon" aria-label="Phone" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.22133 11.0455C9.35902 11.1087 9.51413 11.1232 9.66113 11.0865C9.80812 11.0497 9.93822 10.964 10.03 10.8435L10.2667 10.5335C10.3909 10.3679 10.5519 10.2335 10.737 10.1409C10.9222 10.0484 11.1263 10.0002 11.3333 10.0002H13.3333C13.687 10.0002 14.0261 10.1406 14.2761 10.3907C14.5262 10.6407 14.6667 10.9799 14.6667 11.3335V13.3335C14.6667 13.6871 14.5262 14.0263 14.2761 14.2763C14.0261 14.5264 13.687 14.6668 13.3333 14.6668C10.1507 14.6668 7.09849 13.4025 4.84805 11.1521C2.59761 8.90167 1.33333 5.84943 1.33333 2.66683C1.33333 2.31321 1.47381 1.97407 1.72386 1.72402C1.9739 1.47397 2.31304 1.3335 2.66667 1.3335H4.66667C5.02029 1.3335 5.35943 1.47397 5.60947 1.72402C5.85952 1.97407 6 2.31321 6 2.66683V4.66683C6 4.87382 5.95181 5.07797 5.85923 5.26311C5.76666 5.44825 5.63226 5.6093 5.46667 5.7335L5.15467 5.9675C5.03228 6.06095 4.94601 6.19389 4.91053 6.34373C4.87504 6.49357 4.89252 6.65108 4.96 6.7895C5.87112 8.64007 7.36961 10.1367 9.22133 11.0455Z" fill="currentColor" />
          </svg>
          +84 (08) 3821 2360
        </div>
      </div>
    </div>
    <!-- BANNER: END -->
    <!-- MAIN-HEADER: START -->
    <div class="main-header">
      <div class="container">
        <div class="flex justify-between items-center p-4">
          <div class="flex gap-4">
            <div class="web-logo object-contain">
              <img src="./public/img/faculty_logo.jpg" alt="Logo Khoa CNTT cua Truong CDKT Cao Thang">
            </div>
            <div class="flex flex-col justify-center">
              <div class="text-xl uppercase">KHOA CÔNG NGHỆ THÔNG TIN</div>
              <div class="uni-name uppercase">
                TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG
              </div>
            </div>
          </div>
          <div class="search-bar flex items-center px-4 gap-2 rounded-2xl text-sm">
            <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M14 14L11.1067 11.1067" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
              <path
                d="M7.33333 12.6667C10.2789 12.6667 12.6667 10.2789 12.6667 7.33333C12.6667 4.38781 10.2789 2 7.33333 2C4.38781 2 2 4.38781 2 7.33333C2 10.2789 4.38781 12.6667 7.33333 12.6667Z"
                stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>

            <input class="search-bar__input" placeholder="Tìm kiếm..." autocomplete="off" autocorrect="off">
          </div>
        </div>
      </div>
      <nav class="navbar">
        <div class="container flex py-2 px-4 gap-4">
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative navbar__item--active">
            <div class="uppercase">Trang Chủ</div>
          </div>
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative">
            <div class="uppercase">Giới Thiệu</div>
            <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>

            <div class="dropdown-menu absolute rounded-md">
              <a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/gioi-thieu/chung">
                <div>Giới thiệu chung</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/gioi-thieu/lich-su">
                <div>Lịch sử</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative">
            <div class="uppercase">Chương Trình Đào Tạo</div>
            <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>

            <div class="dropdown-menu absolute rounded-md">
              <a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/dao-tao/cao-dang">
                <div>Cao đẳng</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2"
                href="/dao-tao/cao-dang-nghe">
                <div>Trung cấp</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative">
            <div class="uppercase">Nghiên Cứu</div>
            <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>

            <div class="dropdown-menu absolute rounded-md">
              <a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/nghien-cuu/de-tai">
                <div>Đề tài</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/nghien-cuu/cong-bo">
                <div>Công bố</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative">
            <div class="uppercase">Tin Tức</div>
            <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>

            <div class="dropdown-menu absolute rounded-md">
              <a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/tin-tuc/su-kien">
                <div>Sự kiện</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/tin-tuc/thong-bao">
                <div>Thông báo</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative">
            <div class="uppercase">Sinh Viên</div>
            <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>

            <div class="dropdown-menu absolute rounded-md">
              <a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/sinh-vien/hoc-bong">
                <div>Học bổng</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2"
                href="/sinh-vien/hoat-dong">
                <div>Hoạt động</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
          <div class="navbar__item flex items-center py-2 gap-2 z-50 relative">
            <div class="uppercase">Liên Hệ</div>
            <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>

            <div class="dropdown-menu absolute rounded-md">
              <a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/lien-he/dia-chi">
                <div>Địa chỉ</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/lien-he/phan-hoi">
                <div>Gửi phản hồi</div>
                <div>
                  <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
        </div>
      </nav>
    </div>
    <!-- MAIN-HEADER: END -->
  </header>
  <!-- HEADER: END -->

  <!-- HERO-SECTION: START -->
  <section class="relative" id="hero-section">
    <div class="container">
      <div class="carousel py-8 relative" id="learningCarousel">
        <div class="carousel__inner flex" id="carouselInner">
          <div class="carousel__item flex justify-between items-center gap-8">
            <div class="carousel__content flex flex-col gap-6">
              <h2 class="carousel__title text-6xl font-normal">
                Môi trường học tập
                <span class="text-6xl">Chuyên nghiệp &amp; Sáng tạo</span>
              </h2>
              <p class="carousel__description">
                Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác,
                với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận
                tâm.
              </p>
              <div>
                <a href="#" class="carousel__btn px-8 py-2 rounded-lg text-sm font-medium inline-block secondary-btn bouncy-btn">Tìm hiểu thêm</a>
              </div>
            </div>

            <div class="image-wrapper carousel__image-wrapper rounded-2xl">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Lecture hall with students" class="image carousel__image">
            </div>
          </div>
          <div class="carousel__item flex justify-between items-center gap-8">
            <div class="carousel__content flex flex-col gap-6">
              <h2 class="carousel__title text-6xl font-normal">
                Công nghệ tiên tiến
                <span class="text-6xl">Hỗ trợ học tập 24/7</span>
              </h2>
              <p class="carousel__description">
                Hệ thống học trực tuyến hiện đại, tài liệu số hóa đầy đủ, và
                phòng lab công nghệ cao giúp bạn học mọi lúc, mọi nơi.
              </p>
              <div>
                <a href="#" class="carousel__btn px-8 py-2 rounded-lg text-sm font-medium inline-block">Khám phá ngay</a>
              </div>
            </div>

            <div class="image-wrapper carousel__image-wrapper rounded-2xl">
              <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?..." alt="Modern computer lab" class="image carousel__image">
            </div>
          </div>
          <div class="carousel__item flex justify-between items-center gap-8">
            <div class="carousel__content flex flex-col gap-6">
              <h2 class="carousel__title text-6xl font-normal">
                Môi trường học tập
                <span class="text-6xl">Chuyên nghiệp &amp; Sáng tạo</span>
              </h2>
              <p class="carousel__description">
                Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác,
                với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận
                tâm.
              </p>
              <div>
                <a href="#" class="carousel__btn px-8 py-2 rounded-lg text-sm font-medium inline-block">Tìm hiểu thêm</a>
              </div>
            </div>

            <div class="image-wrapper carousel__image-wrapper rounded-2xl">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Lecture hall with students" class="image carousel__image">
            </div>
          </div>
          <div class="carousel__item flex justify-between items-center gap-8">
            <div class="carousel__content flex flex-col gap-6">
              <h2 class="carousel__title text-6xl font-normal">
                Công nghệ tiên tiến
                <span class="text-6xl">Hỗ trợ học tập 24/7</span>
              </h2>
              <p class="carousel__description">
                Hệ thống học trực tuyến hiện đại, tài liệu số hóa đầy đủ, và
                phòng lab công nghệ cao giúp bạn học mọi lúc, mọi nơi.
              </p>
              <div>
                <a href="#" class="carousel__btn px-8 py-2 rounded-lg text-sm font-medium inline-block">Khám phá ngay</a>
              </div>
            </div>

            <div class="image-wrapper carousel__image-wrapper rounded-2xl">
              <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?..." alt="Modern computer lab" class="image carousel__image">
            </div>
          </div>
        </div>

        <!-- Controls -->
        <button class="carousel__control absolute rounded-full flex justify-center items-center carousel__control--prev"
          id="prevBtn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round"></path>
          </svg>
        </button>
        <button class="carousel__control absolute rounded-full flex justify-center items-center carousel__control--next"
          id="nextBtn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round"></path>
          </svg>
        </button>

        <!-- Indicators -->
        <div class="carousel__indicators z-10 flex justify-center gap-2" id="indicators">
        </div>

        <!-- Thêm Script -->
        <script src="./public/js/carousel.js"></script>
      </div>
    </div>
    <!-- Wave -->
    <div class="wave-container">
      <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="none">
        <path
          d="M0 60L48 48.3333C96 38.6667 192 19.3333 288 9.66667C384 0 480 0 576 4.83333C672 9.66667 768 19.3333 864 24.1667C960 29 1056 29 1152 24.1667C1248 19.3333 1344 9.66667 1392 4.83333L1440 0V60H1392C1344 60 1248 60 1152 60C1056 60 960 60 864 60C768 60 672 60 576 60C480 60 384 60 288 60C192 60 96 60 48 60H0Z"
          fill="currentColor"></path>
      </svg>
    </div>
  </section>
  <!-- HERO-SECTION: END -->

  <!-- ABOUT-SECTION: START -->
  <section class="relative container py-16" id="about-section">
    <h2 class="sr-only">About Us</h2>
    <div class="container-wrapper">
      <div class="flex flex-col justify-center items-center gap-4 mb-12"></div>
      <div class="about-container flex flex-col">
        <div class="about-item flex gap-12 flex-row">
          <div class="relative">
            <div class="about-item__image-container overflow-hidden rounded-2xl">
              <div class="image-wrapper">
                <img class="image w-full h-full object-fit" src="./public/img/about.jpg"
                  alt="Lecture hall with students">
              </div>
            </div>
            <div class="about-item__card absolute z-10 rounded-2xl p-6 flex flex-col gap-1">
              <div class="about-item__card-main-content text-5xl">Top 1</div>
              <div class="about-item__card-sub-content text-sm">
                Khoa CNTT tại Miền Nam (So với các Cao Đẳng khác)
              </div>
            </div>
          </div>
          <div class="about-item__content-container flex flex-col justify-center gap-4">
            <p class="number-of-text text-7xl">01</p>
            <p class="about-item__sub-title text-xs uppercase font-medium">
              LOREM ISPUM GÌ ĐÓ Ở ĐÂY
            </p>
            <p class="about-item__title text-4xl">
              Đảm bảo chất lượng đào tạo
            </p>
            <p class="about-item__content">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris
              sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum
              dolor sit amet, consectetur adipiscing elit. Mauris sed leo et
              neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet,
              consectetur adipiscing elit. Mauris sed leo et neque vehicula
              lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur
              adipiscing elit.
            </p>
          </div>
        </div>
        <div class="about-item flex gap-12 flex-row-reverse">
          <div class="relative">
            <div class="about-item__image-container overflow-hidden rounded-2xl">
              <div class="image-wrapper">
                <img class="image w-full h-full object-fit" src="./public/img/about.jpg" alt="Lecture hall with students">
              </div>
            </div>
            <div class="about-item__card absolute z-10 rounded-2xl p-6 flex flex-col gap-1">
              <div class="about-item__card-main-content text-5xl">98%</div>
              <div class="about-item__card-sub-content text-sm">
                Tỷ lệ có việc làm
              </div>
            </div>
          </div>
          <div class="about-item__content-container flex flex-col justify-center gap-4">
            <p class="number-of-text text-7xl">02</p>
            <p class="about-item__sub-title text-xs uppercase font-medium">
              LOREM ISPUM GÌ ĐÓ Ở ĐÂY
            </p>
            <p class="about-item__title text-4xl">Cơ hội Nghề nghiệp</p>
            <p class="about-item__content">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris
              sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum
              dolor sit amet, consectetur adipiscing elit. Mauris sed leo et
              neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet,
              consectetur adipiscing elit. Mauris sed leo et neque vehicula
              lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur
              adipiscing elit.
            </p>
          </div>
        </div>
        <div class="about-item flex gap-12 flex-row">
          <div class="relative">
            <div class="about-item__image-container overflow-hidden rounded-2xl">
              <div class="image-wrapper">
                <img class="image w-full h-full object-fit" src="./public/img/about.jpg" alt="Lecture hall with students">
              </div>
            </div>
            <div class="about-item__card absolute z-10 rounded-2xl p-6 flex flex-col gap-1">
              <div class="about-item__card-main-content text-5xl">50+</div>
              <div class="about-item__card-sub-content text-sm">
                Doanh nghiệp
              </div>
            </div>
          </div>
          <div class="about-item__content-container flex flex-col justify-center gap-4">
            <p class="number-of-text text-7xl">03</p>
            <p class="about-item__sub-title text-xs uppercase font-medium">
              LOREM ISPUM GÌ ĐÓ Ở ĐÂY
            </p>
            <p class="about-item__title text-4xl">Nghiên cứu Đột phá</p>
            <p class="about-item__content">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris
              sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum
              dolor sit amet, consectetur adipiscing elit. Mauris sed leo et
              neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet,
              consectetur adipiscing elit. Mauris sed leo et neque vehicula
              lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur
              adipiscing elit.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ABOUT-SECTION: END -->

  <!-- WHY-CHOOSE-US-SECTION: START -->
  <section class="wcu relative container py-16" id="why-choose-us-section">
    <div class="wcu__container container-wrapper">
      <div class="wcu__header flex flex-col justify-center items-center gap-4 mb-12">
        <div class="wcu__badge section__badge px-4 py-2 rounded-2xl text-sm mb-4">
          Tại sao chọn chúng tôi
        </div>

        <h2 class="wcu__title section__title text-4xl font-semibold">
          Trải nghiệm Khoa CNTT Cao Thắng
        </h2>

        <p class="wcu__subtitle section__sub-title font-normal text-base">
          Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực
          tiễn
        </p>
      </div>

      <div class="wcu__content flex flex-col items-center justify-center">

        <div class="wcu__features-grid grid grid-cols-3 grid-rows-2 gap-6 mb-6 self-stretch">

          <div class="wcu__feature-card wcu__feature-card--large wcu-feature-container overflow-hidden relative col-span-2 row-span-2 rounded-3xl image-wrapper">
            <img class="wcu__feature-card-image image" src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Trường Cao Thắng">
            <div class="wcu__feature-card-content absolute inset-0 flex flex-col justify-end items-start p-8">
              <span class="wcu__feature-card-badge rounded-full text-center font-normal text-sm px-3 py-1 mb-3">
                Nổi bật
              </span>
              <h3 class="wcu__feature-card-title text-3xl font-semibold mb-4">
                Môi trường học tập hiện đại, sáng tạo
              </h3>
              <p class="wcu__feature-card-description text-base font-normal mb-4">
                Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú,
                không gian làm việc nhóm linh hoạt và hệ thống học tập trực
                tuyến tiên tiến.
              </p>
              <a href="#" class="wcu__feature-card-link text-base font-normal">
                Khám phá ngay
                <svg class="wcu__feature-card-link-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5.83334 5.83325H14.1667V14.1666" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M5.83334 14.1666L14.1667 5.83325" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </a>
            </div>
          </div>

          <div class="wcu__stat-card wcu__stat-card--primary col-start-3 row-start-1 rounded-2xl p-8 flex flex-col gap-2 justify-center">
            <h2 class="wcu__stat-card-number text-7xl font-bold">20</h2>
            <p class="wcu__stat-card-title text-xl font-semibold">
              Năm kinh nghiệm
            </p>
            <p class="wcu__stat-card-description text-base font-normal">
              Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm
              2003
            </p>
          </div>

          <div class="wcu__stat-card wcu__stat-card--gradient col-start-3 row-start-2 bg-pink-gradient rounded-2xl p-8 flex flex-col gap-2 justify-center">
            <h2 class="wcu__stat-card-number text-7xl font-bold">95%</h2>
            <p class="wcu__stat-card-title text-xl font-semibold">Tỷ lệ việc làm</p>
            <p class="wcu__stat-card-description text-base font-normal">
              Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp
            </p>
          </div>
        </div>

        <div class="wcu__perks-list flex justify-center items-stretch self-stretch gap-6 mb-6">

          <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-2xl p-6">
            <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
              <svg class="wcu__perk-item-icon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 18L22 12L16 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M8 6L2 12L8 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </div>
            <h4 class="wcu__perk-item-title text-base font-semibold mb-2">
              Công nghệ tiên tiến
            </h4>
            <p class="wcu__perk-item-description text-sm font-normal">
              Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT
            </p>
          </div>

          <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-2xl p-6">
            <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
              <svg class="wcu__perk-item-icon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H6C4.93913 15 3.92172 15.4214 3.17157 16.1716C2.42143 16.9217 2 17.9391 2 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M16 3.12793C16.8578 3.3503 17.6174 3.85119 18.1597 4.55199C18.702 5.25279 18.9962 6.11382 18.9962 6.99993C18.9962 7.88604 18.702 8.74707 18.1597 9.44787C17.6174 10.1487 16.8578 10.6496 16 10.8719" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M22 20.9999V18.9999C21.9993 18.1136 21.7044 17.2527 21.1614 16.5522C20.6184 15.8517 19.8581 15.3515 19 15.1299" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </div>
            <h4 class="wcu__perk-item-title text-base font-semibold text-foreground mb-2">
              Cộng đồng Mạnh mẽ
            </h4>
            <p class="wcu__perk-item-description text-sm font-normal text-muted-foreground">
              Kết nối với 10,000+ sinh viên và cựu sinh viên trên toàn quốc
            </p>
          </div>

          <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-2xl p-6">
            <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
              <svg class="wcu__perk-item-icon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.477 12.8899L16.992 21.4159C17.009 21.5163 16.9949 21.6195 16.9516 21.7116C16.9084 21.8038 16.838 21.8806 16.7499 21.9317C16.6619 21.9828 16.5603 22.0058 16.4588 21.9976C16.3573 21.9894 16.2607 21.9504 16.182 21.8859L12.602 19.1989C12.4292 19.0698 12.2192 19 12.0035 19C11.7878 19 11.5778 19.0698 11.405 19.1989L7.819 21.8849C7.74032 21.9493 7.64386 21.9882 7.54249 21.9964C7.44112 22.0046 7.33967 21.9817 7.25166 21.9308C7.16365 21.8798 7.09327 21.8032 7.04991 21.7112C7.00656 21.6192 6.99228 21.5162 7.009 21.4159L8.523 12.8899" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12 14C15.3137 14 18 11.3137 18 8C18 4.68629 15.3137 2 12 2C8.68629 2 6 4.68629 6 8C6 11.3137 8.68629 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </div>
            <h4 class="wcu__perk-item-title text-base font-semibold text-foreground mb-2">
              Chất lượng Quốc tế
            </h4>
            <p class="wcu__perk-item-description text-sm font-normal text-muted-foreground">
              Chương trình đạt chuẩn ABET và kiểm định quốc tế
            </p>
          </div>

          <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-2xl p-6">
            <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
              <svg class="wcu__perk-item-icon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.5 16.5001C3 17.7601 2.5 21.5001 2.5 21.5001C2.5 21.5001 6.24 21.0001 7.5 19.5001C8.21 18.6601 8.2 17.3701 7.41 16.5901C7.02131 16.2191 6.50929 16.0047 5.97223 15.9881C5.43516 15.9715 4.91088 16.1538 4.5 16.5001Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12 15L9 12C9.53214 10.6194 10.2022 9.29607 11 8.05C12.1652 6.18699 13.7876 4.65305 15.713 3.5941C17.6384 2.53514 19.8027 1.98637 22 2C22 4.72 21.22 9.5 16 13C14.7369 13.7987 13.3968 14.4687 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M9 12H4C4 12 4.55 8.97002 6 8.00002C7.62 6.92002 11 8.00002 11 8.00002" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12 15V20C12 20 15.03 19.45 16 18C17.08 16.38 16 13 16 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </div>
            <h4 class="wcu__perk-item-title text-base font-semibold text-foreground mb-2">
              Khởi nghiệp
            </h4>
            <p class="wcu__perk-item-description text-sm font-normal text-muted-foreground">
              Hỗ trợ ý tưởng startup và kết nối nhà đầu tư
            </p>
          </div>
        </div>

        <div class="wcu__highlights-list flex justify-center items-stretch self-stretch gap-6">

          <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-2xl image-wrapper">
            <img class="wcu__highlight-item-image image object-fit" src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Trường Cao Thắng">
            <div class="wcu__highlight-item-content wcu__highlight-item-content--blue absolute inset-0 bg-blue-gradient flex flex-col justify-end items-start p-6">
              <h3 class="wcu__highlight-item-title text-2xl font-semibold mb-2">
                Nghiên cứu &amp; Phát triển
              </h3>
              <p class="wcu__highlight-item-description text-sm font-normal">
                Tham gia các dự án nghiên cứu thực tế cùng giảng viên
              </p>
            </div>
          </div>

          <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-2xl image-wrapper">
            <img class="wcu__highlight-item-image image object-fit" src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Trường Cao Thắng">
            <div class="wcu__highlight-item-content wcu__highlight-item-content--green absolute inset-0 bg-green-gradient flex flex-col justify-end items-start p-6">
              <h3 class="wcu__highlight-item-title text-2xl font-semibold mb-2">
                Hợp tác Quốc tế
              </h3>
              <p class="wcu__highlight-item-description text-sm font-normal">
                Cơ hội trao đổi sinh viên và học bổng du học
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- WHY-CHOOSE-US-SECTION: END -->

  <!-- STATS-SECTION: START -->
  <section class="relative container py-16" id="stats-section">
    <div class="container-wrapper">
      <div class="flex flex-col justify-center items-center gap-4 mb-12">

        <h2 class="section__title text-4xl font-semibold">
          Khoa CNTT Cao Thắng
        </h2>

        <p class="section__sub-title font-normal text-base">
          Định hình tương lai công nghệ thông tin Việt Nam
        </p>
      </div>
      <div class="flex flex-col items-stretch justify-center gap-6">
        <div class="stats__grid flex gap-6">
          <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-2xl p-8">
            <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
              <svg class="stats__stat-card-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.477 12.8899L16.992 21.4159C17.009 21.5163 16.9949 21.6195 16.9516 21.7116C16.9084 21.8038 16.838 21.8806 16.7499 21.9317C16.6619 21.9828 16.5603 22.0058 16.4588 21.9976C16.3573 21.9894 16.2607 21.9504 16.182 21.8859L12.602 19.1989C12.4292 19.0698 12.2192 19 12.0035 19C11.7878 19 11.5778 19.0698 11.405 19.1989L7.819 21.8849C7.74032 21.9493 7.64386 21.9882 7.54249 21.9964C7.44112 22.0046 7.33967 21.9817 7.25166 21.9308C7.16365 21.8798 7.09327 21.8032 7.04991 21.7112C7.00656 21.6192 6.99228 21.5162 7.009 21.4159L8.523 12.8899" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M12 14C15.3137 14 18 11.3137 18 8C18 4.68629 15.3137 2 12 2C8.68629 2 6 4.68629 6 8C6 11.3137 8.68629 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="flex flex-col gap-1 items-center">
              <h3 class="stats__stat-card-number text-5xl font-bold">50+</h3>
              <h4 class="stats__stat-card-label font-semibold">Giải thưởng</h4>
              <p class="stats__stat-card-description text-sm">
                Trong các cuộc thi lập trình
              </p>
            </div>
          </div>
          <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-2xl p-8">
            <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
              <svg class="stats__stat-card-icon" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24.99 12.7424C25.1989 12.6503 25.3761 12.4989 25.4998 12.307C25.6234 12.1152 25.6881 11.8912 25.6857 11.663C25.6834 11.4347 25.6141 11.2121 25.4865 11.0229C25.3589 10.8336 25.1786 10.6859 24.9678 10.5981L14.9683 6.04342C14.6644 5.90476 14.3341 5.83301 14 5.83301C13.6659 5.83301 13.3357 5.90476 13.0317 6.04342L3.03334 10.5934C2.82564 10.6844 2.64894 10.8339 2.52487 11.0237C2.40079 11.2135 2.33472 11.4353 2.33472 11.6621C2.33472 11.8888 2.40079 12.1107 2.52487 12.3005C2.64894 12.4903 2.82564 12.6398 3.03334 12.7308L13.0317 17.2901C13.3357 17.4288 13.6659 17.5005 14 17.5005C14.3341 17.5005 14.6644 17.4288 14.9683 17.2901L24.99 12.7424Z" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M25.6667 11.6667V18.6667" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M7 14.5833V18.6666C7 19.5948 7.7375 20.4851 9.05025 21.1415C10.363 21.7978 12.1435 22.1666 14 22.1666C15.8565 22.1666 17.637 21.7978 18.9497 21.1415C20.2625 20.4851 21 19.5948 21 18.6666V14.5833" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="flex flex-col gap-1 items-center">
              <h3 class="stats__stat-card-number text-5xl font-bold">10K+</h3>
              <h4 class="stats__stat-card-label font-semibold">Sinh viên</h4>
              <p class="stats__stat-card-description text-sm">Tốt nghiệp thành công</p>
            </div>
          </div>
          <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-2xl p-8">
            <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
              <svg class="stats__stat-card-icon" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.6666 23.3333V4.66659C18.6666 4.04775 18.4208 3.45425 17.9832 3.01667C17.5456 2.57908 16.9522 2.33325 16.3333 2.33325H11.6666C11.0478 2.33325 10.4543 2.57908 10.0167 3.01667C9.57915 3.45425 9.33331 4.04775 9.33331 4.66659V23.3333" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M23.3333 7H4.66665C3.37798 7 2.33331 8.04467 2.33331 9.33333V21C2.33331 22.2887 3.37798 23.3333 4.66665 23.3333H23.3333C24.622 23.3333 25.6666 22.2887 25.6666 21V9.33333C25.6666 8.04467 24.622 7 23.3333 7Z" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="flex flex-col gap-1 items-center">
              <h3 class="stats__stat-card-number text-5xl font-bold">95%</h3>
              <h4 class="stats__stat-card-label font-semibold">Việc làm</h4>
              <p class="stats__stat-card-description text-sm">Sau 6 tháng tốt nghiệp</p>
            </div>
          </div>
          <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-2xl p-8">
            <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
              <svg class="stats__stat-card-icon" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.13 17.5H19.8333C19.2145 17.5 18.621 17.7458 18.1834 18.1834C17.7458 18.621 17.5 19.2145 17.5 19.8333V25.13" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8.16663 3.89673V5.8334C8.16663 6.76165 8.53538 7.65189 9.19175 8.30827C9.84813 8.96465 10.7384 9.3334 11.6666 9.3334C12.2855 9.3334 12.879 9.57923 13.3165 10.0168C13.7541 10.4544 14 11.0479 14 11.6667C14 12.9501 15.05 14.0001 16.3333 14.0001C16.9521 14.0001 17.5456 13.7542 17.9832 13.3166C18.4208 12.8791 18.6666 12.2856 18.6666 11.6667C18.6666 10.3834 19.7166 9.3334 21 9.3334H24.6983" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M12.8334 25.6083V20.9999C12.8334 20.3811 12.5876 19.7876 12.15 19.35C11.7124 18.9124 11.1189 18.6666 10.5001 18.6666C9.88122 18.6666 9.28773 18.4208 8.85014 17.9832C8.41256 17.5456 8.16672 16.9521 8.16672 16.3333V15.1666C8.16672 14.5477 7.92089 13.9543 7.48331 13.5167C7.04572 13.0791 6.45223 12.8333 5.83339 12.8333H2.39172" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M14 25.6666C20.4434 25.6666 25.6667 20.4432 25.6667 13.9999C25.6667 7.5566 20.4434 2.33325 14 2.33325C7.55672 2.33325 2.33337 7.5566 2.33337 13.9999C2.33337 20.4432 7.55672 25.6666 14 25.6666Z" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="flex flex-col gap-1 items-center">
              <h3 class="stats__stat-card-number text-5xl font-bold">20+</h3>
              <h4 class="stats__stat-card-label font-semibold">Quốc gia</h4>
              <p class="stats__stat-card-description text-sm">Hợp tác quốc tế</p>
            </div>
          </div>
        </div>
        <div class="stats__benefits-grid flex gap-6 items-stretch">
          <div class="stats__benefit-card flex-1 flex flex-col gap-6 p-8 rounded-2xl">
            <div class="stats__benefit-card-header flex gap-4 items-center">
              <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full">
                <svg class="stats__benefit-card-icon" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M14 8.16675V24.5001" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M3.49999 21C3.19058 21 2.89383 20.8771 2.67504 20.6583C2.45624 20.4395 2.33333 20.1428 2.33333 19.8333V4.66667C2.33333 4.35725 2.45624 4.0605 2.67504 3.84171C2.89383 3.62292 3.19058 3.5 3.49999 3.5H9.33333C10.571 3.5 11.758 3.99167 12.6332 4.86683C13.5083 5.742 14 6.92899 14 8.16667C14 6.92899 14.4917 5.742 15.3668 4.86683C16.242 3.99167 17.429 3.5 18.6667 3.5H24.5C24.8094 3.5 25.1062 3.62292 25.325 3.84171C25.5437 4.0605 25.6667 4.35725 25.6667 4.66667V19.8333C25.6667 20.1428 25.5437 20.4395 25.325 20.6583C25.1062 20.8771 24.8094 21 24.5 21H17.5C16.5717 21 15.6815 21.3687 15.0251 22.0251C14.3687 22.6815 14 23.5717 14 24.5C14 23.5717 13.6312 22.6815 12.9749 22.0251C12.3185 21.3687 11.4283 21 10.5 21H3.49999Z" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <h3 class="stats__benefit-card-title text-2xl font-semibold">
                Chương trình Đào tạo Tiên tiến
              </h3>
            </div>
            <ul class="stats__benefit-card-list flex flex-col gap-4">
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Cập nhật theo công nghệ mới nhất
                </p>
              </li>
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Tích hợp chứng chỉ quốc tế
                </p>
              </li>
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Thực hành dự án thực tế
                </p>
              </li>
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Đào tạo kỹ năng mềm
                </p>
              </li>
            </ul>
          </div>

          <div class="stats__benefit-card flex-1 flex flex-col gap-6 p-8 rounded-2xl">
            <div class="stats__benefit-card-header flex gap-4 items-center">
              <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full">
                <svg class="stats__benefit-card-icon" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M18.6667 8.16675H25.6667V15.1667" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M25.6666 8.16675L15.75 18.0834L9.91665 12.2501L2.33331 19.8334" stroke="currentColor" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <h3 class="stats__benefit-card-title text-2xl font-semibold">
                Phát triển Nghề nghiệp
              </h3>
            </div>
            <ul class="stats__benefit-card-list flex flex-col gap-4">
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Kết nối với 100+ doanh nghiệp
                </p>
              </li>
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Thực tập tại công ty hàng đầu
                </p>
              </li>
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Tư vấn định hướng nghề nghiệp
                </p>
              </li>
              <li class="stats__benefit-card-item flex items-center gap-2">
                <span class="stats__benefit-card-item-icon rounded-full"></span>
                <p class="stats__benefit-card-item-text">
                  Cơ hội việc làm cao
                </p>
              </li>
            </ul>
          </div>
        </div>
        <div class="stats__cta flex flex-col items-center p-12 rounded-2xl">
          <h3 class="stats__cta-title text-center text-3xl font-semibold mb-2">
            Sẵn sàng bắt đầu hành trình của bạn?
          </h3>
          <p class="stats__cta-description text-center text-xl font-light mb-6">
            Gia nhập cộng đồng hơn 10,000 sinh viên và cựu sinh viên đang
            làm việc tại các công ty công nghệ hàng đầu
          </p>
          <div class="stats__cta-buttons flex gap-4">
            <a href="#" class="stats__cta-button stats__cta-button--primary flex items-center px-8 py-4 secondary-btn bouncy-btn">
              Đăng ký tư vấn
            </a>
            <a href="#" class="stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 outline-btn bouncy-btn">
              Xem chương trình đào tạo
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- STATS-SECTION: END -->

  <!-- NEWSFEED-SECTION: START -->
  <section class="newsfeed relative container py-16" id="newsfeed-section">
    <div class="newsfeed__container container-wrapper">

      <div class="newsfeed__header flex flex-col justify-center items-center gap-4 mb-12">
        <h2 class="newsfeed__title section__title text-4xl font-semibold text-center">
          Tin tức &amp; Sự kiện
        </h2>
        <p class="newsfeed__subtitle section__sub-title font-normal text-base text-center">
          Cập nhật những tin tức mới nhất về hoạt động của khoa, thành tích
          sinh viên và các sự kiện sắp tới
        </p>
      </div>

      <div class="newsfeed__content-wrapper container-wrapper flex flex-col gap-16">

        <div id="featured-news" class="newsfeed__featured-group flex flex-col gap-6">

          <div class="news-card news-card--featured relative overflow-hidden rounded-2xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-8">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-sm px-3">
                    Nổi bật
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-base">
                    <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title text-4xl font-medium mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025
                </h3>
                <p class="news-card__description font-light text-xl mb-6">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                  đạt được danh hiệu cao quý
                </p>
                <a href="#" class="news-card__link flex items-center gap-2 text-base px-4 py-2 secondary-btn bouncy-btn">
                  Đọc thêm
                  <svg class="news-card__link-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.16666 10H15.8333" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10 4.1665L15.8333 9.99984L10 15.8332" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <div class="newsfeed__secondary-grid flex gap-6 justify-center items-stretch self-stretch">

            <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-2xl">
              <div class="news-card__image-wrapper image-wrapper">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
                <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                  <div class="news-card__meta mb-2 flex items-center gap-2">
                    <span class="news-card__tag badge text-xs">
                      Thành tích
                    </span>
                    <span class="news-card__date flex items-center gap-1 text-sm">
                      <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      15/01/2025
                    </span>
                  </div>
                  <h3 class="news-card__title text-xl font-semibold mb-2">
                    Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học
                    2025
                  </h3>
                  <p class="news-card__description font-light text-sm">
                    Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết
                    để đạt được danh hiệu cao quý
                  </p>
                </div>
              </div>
            </a>

            <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-2xl">
              <div class="news-card__image-wrapper image-wrapper">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
                <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                  <div class="news-card__meta mb-2 flex items-center gap-2">
                    <span class="news-card__tag badge text-xs">
                      Thành tích
                    </span>
                    <span class="news-card__date flex items-center gap-1 text-sm">
                      <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      15/01/2025
                    </span>
                  </div>
                  <h3 class="news-card__title text-xl font-semibold mb-2">
                    Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học
                    2025
                  </h3>
                  <p class="news-card__description font-light text-sm">
                    Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết
                    để đạt được danh hiệu cao quý
                  </p>
                </div>
              </div>
            </a>

            <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-2xl">
              <div class="news-card__image-wrapper image-wrapper">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
                <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                  <div class="news-card__meta mb-2 flex items-center gap-2">
                    <span class="news-card__tag badge text-xs">
                      Thành tích
                    </span>
                    <span class="news-card__date flex items-center gap-1 text-sm">
                      <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      15/01/2025
                    </span>
                  </div>
                  <h3 class="news-card__title text-xl font-semibold mb-2">
                    Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học
                    2025
                  </h3>
                  <p class="news-card__description font-light text-sm">
                    Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết
                    để đạt được danh hiệu cao quý
                  </p>
                </div>
              </div>
            </a>
          </div>
        </div>

        <div id="newsfeed-other" class="flex flex-col gap-4">
          <div class="newsfeed__other-header flex">
            <h2 class="newsfeed__other-title text-4xl font-medium flex-1">
              Tin tức khác
            </h2>
            <a href="#" class="newsfeed__view-all-link flex items-center gap-1 text-base font-medium link-hover--underline">
              Xem thêm
              <svg class="newsfeed__view-all-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.83334 5.83325H14.1667V14.1666" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M5.83334 14.1666L14.1667 5.83325" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
          </div>

          <div class="newsfeed__other-grid flex gap-6 justify-center items-stretch self-stretch">

            <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-2xl">
              <div class="news-card__image-wrapper image-wrapper">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
                <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                  <div class="news-card__meta mb-2 flex items-center gap-2">
                    <span class="news-card__tag badge text-xs">
                      Thành tích
                    </span>
                    <span class="news-card__date flex items-center gap-1 text-sm">
                      <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      15/01/2025
                    </span>
                  </div>
                  <h3 class="news-card__title text-xl font-semibold mb-2">
                    Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025
                  </h3>
                  <p class="news-card__description font-light text-sm">
                    Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                    đạt được danh hiệu cao quý
                  </p>
                </div>
              </div>
            </a>

            <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-2xl">
              <div class="news-card__image-wrapper image-wrapper">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
                <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                  <div class="news-card__meta mb-2 flex items-center gap-2">
                    <span class="news-card__tag badge text-xs">
                      Thành tích
                    </span>
                    <span class="news-card__date flex items-center gap-1 text-sm">
                      <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      15/01/2025
                    </span>
                  </div>
                  <h3 class="news-card__title news__item--title text-xl font-semibold mb-2">
                    Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2024
                  </h3>
                  <p class="news-card__description font-light news__item--sub_title text-sm font-normal">
                    Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                    đạt được danh hiệu
                  </p>
                </div>
              </div>
            </a>

            <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-2xl">
              <div class="news-card__image-wrapper image-wrapper">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="This is an image of a student won a tournament" class="news-card__image absolute w-full h-full object-cover image">
                <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                  <div class="news-card__meta mb-2 flex items-center gap-2">
                    <span class="news-card__tag badge text-xs">
                      Thành tích
                    </span>
                    <span class="news-card__date flex items-center gap-1 text-sm">
                      <svg class="news-card__date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5.33333 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.6667 1.3335V4.00016" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12.6667 2.6665H3.33333C2.59695 2.6665 2 3.26346 2 3.99984V13.3332C2 14.0696 2.59695 14.6665 3.33333 14.6665H12.6667C13.403 14.6665 14 14.0696 14 13.3332V3.99984C14 3.26346 13.403 2.6665 12.6667 2.6665Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M2 6.6665H14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      15/01/2025
                    </span>
                  </div>
                  <h3 class="news-card__title text-xl font-semibold mb-2">
                    Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025
                  </h3>
                  <p class="news-card__description font-light text-sm">
                    Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                    đạt được danh hiệu cao quý
                  </p>
                </div>
              </div>
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- NEWSFEED-SECTION: END -->

  <!-- FOOTER: START -->
  <footer class="footer">
    <div class="footer__main-content container flex gap-16 py-12 px-4">

      <div class="footer__info">
        <div class="footer__brand-group flex gap-3 mb-4 items-center">
          <div class="footer__logo overflow-hidden rounded-full">
            <img class="footer__logo-image w-full h-full object-fit" src="./public/img/faculty_logo.jpg" alt="Logo Khoa CNTT cua Truong CDKT Cao Thang">
          </div>
          <div class="footer__brand-text flex flex-col justify-center">
            <div class="footer__faculty-name text-xl uppercase">
              KHOA CÔNG NGHỆ THÔNG TIN
            </div>
            <div class="footer__uni-name font-normal uppercase">
              TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG
            </div>
          </div>
        </div>
        <p class="footer__description text-sm">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Adipisci
          earum quisquam id quibusdam veniam. Libero omnis voluptate ipsam.
        </p>
      </div>

      <div class="footer__nav-group flex-1">
        <h3 class="footer__nav-title font-normal mb-4">Liên kết nhanh</h3>
        <ul class="footer__nav-list">
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Giới thiệu</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Chương trình đào tạo</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Tuyển sinh</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Nghiên cứu khoa học</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Sinh viên</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Cựu sinh viên</a>
          </li>
        </ul>
      </div>

      <div class="footer__nav-group flex-1">
        <h3 class="footer__nav-title font-normal mb-4">
          Chương trình đào tạo
        </h3>
        <ul class="footer__nav-list">
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Công nghệ phần mềm</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Lập trình di động</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Công nghệ phần mềm</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Lập trình website</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Công nghệ phần mềm</a>
          </li>
          <li class="footer__nav-item text-sm font-normal mb-2">
            <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Trí tuệ nhân tạo</a>
          </li>
        </ul>
      </div>

      <div class="footer__nav-group flex-1">
        <h3 class="footer__nav-title font-normal mb-4">Liên hệ</h3>
        <ul class="footer__contact-list">
          <li class="footer__contact-item mb-3 flex gap-3">
            <span class="footer__contact-icon">
              <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M13.3333 6.66683C13.3333 9.9955 9.64063 13.4622 8.40063 14.5328C8.28511 14.6197 8.14449 14.6667 7.99996 14.6667C7.85543 14.6667 7.71481 14.6197 7.59929 14.5328C6.35929 13.4622 2.66663 9.9955 2.66663 6.66683C2.66663 5.25234 3.22853 3.89579 4.22872 2.89559C5.22892 1.8954 6.58547 1.3335 7.99996 1.3335C9.41445 1.3335 10.771 1.8954 11.7712 2.89559C12.7714 3.89579 13.3333 5.25234 13.3333 6.66683Z"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                <path
                  d="M8 8.6665C9.10457 8.6665 10 7.77107 10 6.6665C10 5.56193 9.10457 4.6665 8 4.6665C6.89543 4.6665 6 5.56193 6 6.6665C6 7.77107 6.89543 8.6665 8 8.6665Z"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </span>
            <p class="footer__contact-text text-sm font-normal">
              Lầu 7 - Dãy F, 65 Huỳnh Thúc Kháng, Phường Sài Gòn, TP.HCM, Việt
              Nam
            </p>
          </li>
          <li class="footer__contact-item mb-3 flex gap-3">
            <span class="footer__contact-icon">
              <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_462_195)">
                  <path
                    d="M9.22137 11.0455C9.35906 11.1087 9.51417 11.1232 9.66117 11.0865C9.80816 11.0497 9.93826 10.964 10.03 10.8435L10.2667 10.5335C10.3909 10.3679 10.5519 10.2335 10.7371 10.1409C10.9222 10.0484 11.1264 10.0002 11.3334 10.0002H13.3334C13.687 10.0002 14.0261 10.1406 14.2762 10.3907C14.5262 10.6407 14.6667 10.9799 14.6667 11.3335V13.3335C14.6667 13.6871 14.5262 14.0263 14.2762 14.2763C14.0261 14.5264 13.687 14.6668 13.3334 14.6668C10.1508 14.6668 7.09853 13.4025 4.84809 11.1521C2.59766 8.90167 1.33337 5.84943 1.33337 2.66683C1.33337 2.31321 1.47385 1.97407 1.7239 1.72402C1.97395 1.47397 2.31309 1.3335 2.66671 1.3335H4.66671C5.02033 1.3335 5.35947 1.47397 5.60952 1.72402C5.85956 1.97407 6.00004 2.31321 6.00004 2.66683V4.66683C6.00004 4.87382 5.95185 5.07797 5.85928 5.26311C5.76671 5.44825 5.6323 5.6093 5.46671 5.7335L5.15471 5.9675C5.03232 6.06095 4.94605 6.19389 4.91057 6.34373C4.87508 6.49357 4.89256 6.65108 4.96004 6.7895C5.87116 8.64007 7.36966 10.1367 9.22137 11.0455Z"
                    stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
                <defs>
                  <clipPath id="clip0_462_195">
                    <rect width="16" height="16" fill="currentColor"></rect>
                  </clipPath>
                </defs>
              </svg>
            </span>
            <p class="footer__contact-text text-sm font-normal">
              +84 (08) 3821 2360
            </p>
          </li>
          <li class="footer__contact-item mb-3 flex gap-3">
            <span class="footer__contact-icon">
              <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M14.6667 4.6665L8.67271 8.4845C8.4693 8.60265 8.23827 8.66487 8.00304 8.66487C7.76782 8.66487 7.53678 8.60265 7.33337 8.4845L1.33337 4.6665"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                <path
                  d="M13.3334 2.6665H2.66671C1.93033 2.6665 1.33337 3.26346 1.33337 3.99984V11.9998C1.33337 12.7362 1.93033 13.3332 2.66671 13.3332H13.3334C14.0698 13.3332 14.6667 12.7362 14.6667 11.9998V3.99984C14.6667 3.26346 14.0698 2.6665 13.3334 2.6665Z"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </span>
            <p class="footer__contact-text text-sm font-normal">
              cntt@caothang.edu.vn
            </p>
          </li>
        </ul>

        <h3 class="footer__nav-title font-normal mb-2">Theo dõi chúng tôi</h3>
        <ul class="footer__social-list flex gap-2 items-center">
          <li class="footer__social-item">
            <a href="#" class="footer__social-link p-3 link-hover--standout">
              <svg aria-label="Facebook Link" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 1.3335H9.99996C9.1159 1.3335 8.26806 1.68469 7.64294 2.30981C7.01782 2.93493 6.66663 3.78277 6.66663 4.66683V6.66683H4.66663V9.3335H6.66663V14.6668H9.33329V9.3335H11.3333L12 6.66683H9.33329V4.66683C9.33329 4.49002 9.40353 4.32045 9.52856 4.19542C9.65358 4.0704 9.82315 4.00016 9.99996 4.00016H12V1.3335Z"
                  stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
          </li>
          <li class="footer__social-item">
            <a href="#" class="footer__social-link p-3 link-hover--standout">
              <svg aria-label="Youtube Link" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M1.66667 11.3333C1.20095 9.13551 1.20095 6.86449 1.66667 4.66667C1.72786 4.44347 1.8461 4.24005 2.00974 4.0764C2.17339 3.91276 2.37681 3.79453 2.6 3.73333C6.17564 3.14097 9.82437 3.14097 13.4 3.73333C13.6232 3.79453 13.8266 3.91276 13.9903 4.0764C14.1539 4.24005 14.2721 4.44347 14.3333 4.66667C14.7991 6.86449 14.7991 9.13551 14.3333 11.3333C14.2721 11.5565 14.1539 11.7599 13.9903 11.9236C13.8266 12.0872 13.6232 12.2055 13.4 12.2667C9.82438 12.8591 6.17563 12.8591 2.6 12.2667C2.37681 12.2055 2.17339 12.0872 2.00974 11.9236C1.8461 11.7599 1.72786 11.5565 1.66667 11.3333Z"
                  stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M6.66663 10L9.99996 8L6.66663 6V10Z" stroke="currentColor" stroke-width="1.33333"
                  stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
          </li>
          <li class="footer__social-item">
            <a href="#" class="footer__social-link p-3 link-hover--standout">
              <svg aria-label="Instagram Link" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M10.6666 5.3335C11.7275 5.3335 12.7449 5.75492 13.4951 6.50507C14.2452 7.25521 14.6666 8.27263 14.6666 9.3335V14.0002H12V9.3335C12 8.97987 11.8595 8.64074 11.6094 8.39069C11.3594 8.14064 11.0202 8.00016 10.6666 8.00016C10.313 8.00016 9.97387 8.14064 9.72382 8.39069C9.47377 8.64074 9.33329 8.97987 9.33329 9.3335V14.0002H6.66663V9.3335C6.66663 8.27263 7.08805 7.25521 7.8382 6.50507C8.58834 5.75492 9.60576 5.3335 10.6666 5.3335Z"
                  stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M4.00004 6H1.33337V14H4.00004V6Z" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round"
                  stroke-linejoin="round"></path>
                <path
                  d="M2.66671 4.00016C3.40309 4.00016 4.00004 3.40321 4.00004 2.66683C4.00004 1.93045 3.40309 1.3335 2.66671 1.3335C1.93033 1.3335 1.33337 1.93045 1.33337 2.66683C1.33337 3.40321 1.93033 4.00016 2.66671 4.00016Z"
                  stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer__copyright">
      <div class="footer__copyright-container container py-12 px-4">
        <p class="footer__copyright-text text-sm">
          © 2025 Khoa Công nghệ Thông tin - Trường Cao Đẳng Kỹ Thuật Cao
          Thắng. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
  <!-- FOOTER: END -->
</body>

</html>