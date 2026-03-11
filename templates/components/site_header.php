<header class="z-50">
  <!-- BANNER: START -->
  <div class="banner py-2">
    <div class="container flex gap-4 px-4 font-light">
      <div class="flex items-center gap-1">
        <svg class="banner__icon" aria-label="Email" width="16" height="16" viewBox="0 0 16 16" fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <path
            d="M14.667 12C14.6669 12.7363 14.0693 13.333 13.333 13.333H2.66699C1.93067 13.333 1.3331 12.7363 1.33301 12V5.25977L7.06445 8.90723C7.07009 8.91081 7.07625 8.91364 7.08203 8.91699C7.36162 9.07938 7.67961 9.16498 8.00293 9.16504C8.3261 9.16499 8.64433 9.07922 8.92383 8.91699C8.92943 8.91373 8.93591 8.90974 8.94141 8.90625L14.667 5.25879V12ZM13.333 2.66602C14.0694 2.66602 14.667 3.26362 14.667 4V4.16699C14.5747 4.16676 14.481 4.19189 14.3975 4.24512L8.41699 8.05371C8.29094 8.12598 8.14828 8.16499 8.00293 8.16504C7.85701 8.16498 7.71332 8.12654 7.58691 8.05371L1.60156 4.24512C1.51809 4.19201 1.42515 4.16684 1.33301 4.16699V4C1.33301 3.26362 1.93061 2.66602 2.66699 2.66602H13.333Z"
            fill="currentColor" />
        </svg>

        cntt@caothang.edu.vn
      </div>
      <div class="flex items-center gap-1">
        <svg class="banner__icon" aria-label="Phone" width="16" height="16" viewBox="0 0 16 16" fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <path
            d="M9.22133 11.0455C9.35902 11.1087 9.51413 11.1232 9.66113 11.0865C9.80812 11.0497 9.93822 10.964 10.03 10.8435L10.2667 10.5335C10.3909 10.3679 10.5519 10.2335 10.737 10.1409C10.9222 10.0484 11.1263 10.0002 11.3333 10.0002H13.3333C13.687 10.0002 14.0261 10.1406 14.2761 10.3907C14.5262 10.6407 14.6667 10.9799 14.6667 11.3335V13.3335C14.6667 13.6871 14.5262 14.0263 14.2761 14.2763C14.0261 14.5264 13.687 14.6668 13.3333 14.6668C10.1507 14.6668 7.09849 13.4025 4.84805 11.1521C2.59761 8.90167 1.33333 5.84943 1.33333 2.66683C1.33333 2.31321 1.47381 1.97407 1.72386 1.72402C1.9739 1.47397 2.31304 1.3335 2.66667 1.3335H4.66667C5.02029 1.3335 5.35943 1.47397 5.60947 1.72402C5.85952 1.97407 6 2.31321 6 2.66683V4.66683C6 4.87382 5.95181 5.07797 5.85923 5.26311C5.76666 5.44825 5.63226 5.6093 5.46667 5.7335L5.15467 5.9675C5.03228 6.06095 4.94601 6.19389 4.91053 6.34373C4.87504 6.49357 4.89252 6.65108 4.96 6.7895C5.87112 8.64007 7.36961 10.1367 9.22133 11.0455Z"
            fill="currentColor" />
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
            <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo Khoa CNTT cua Truong CDKT Cao Thang">
          </div>
          <div class="flex flex-col justify-center">
            <div class="text-xl uppercase">KHOA CÔNG NGHỆ THÔNG TIN</div>
            <div class="uni-name uppercase">
              TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG
            </div>
          </div>
        </div>
        <div class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm">
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
          <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16"
            viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16"
            viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16"
            viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16"
            viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16"
            viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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
            </a><a class="dropdown-menu__item flex justify-between items-center px-4 py-2" href="/sinh-vien/hoat-dong">
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
          <svg class="dropdown-icon" aria-haspopup="true" aria-label="Dropdown Icon" width="16" height="16"
            viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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