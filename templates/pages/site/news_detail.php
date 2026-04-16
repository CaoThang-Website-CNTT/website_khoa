<?php
$breadcrumb = [
  'items' => [
    ['label' => 'Trang chủ', 'url' => '/', 'active' => false],
    ['label' => 'Tin tức', 'url' => '/tin-tuc', 'active' => false],
    ['label' => 'Tin khoa', 'active' => true],
  ],
  'schema' => true,
];
?>

<!-- News Detail Page -->
<div class="news-detail-page container" itemscope itemtype="https://schema.org/NewsArticle">
  <meta itemprop="datePublished" content="2024-01-15">
  <meta itemprop="author" content="Ban biên tập">

  <div class="news-detail__layout container-wrapper">

    <!-- Main Content Area -->
    <article class="news-detail__main">

      <!-- Back Button -->
      <a href="/news" class="news-detail__back-btn" aria-label="Quay lại">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <span>Quay lại</span>
      </a>

      <!-- Article Header -->
      <header class="news-detail__header">
        <span class="news-detail__badge badge" data-variant="dark">Tin khoa</span>
        <h1 class="news-detail__title" itemprop="headline">
          Khoa CNTT đạt chứng nhận ABET cho chương trình Công nghệ Phần mềm
        </h1>
      </header>

      <!-- Meta Info -->
      <div class="news-detail__meta">
        <div class="news-detail__meta-row">
          <span class="news-detail__meta-item">
            <i class="fa-regular fa-user" aria-hidden="true"></i>
            <span>Ban biên tập</span>
          </span>
          <span class="news-detail__meta-item">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <time datetime="2024-01-15">15 tháng 1, 2024</time>
          </span>
          <span class="news-detail__meta-item">
            <i class="fa-regular fa-clock" aria-hidden="true"></i>
            <span>5 phút đọc</span>
          </span>
          <span class="news-detail__meta-item">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
            <span>1,250 lượt xem</span>
          </span>
        </div>
        <div class="news-detail__share-section">
          <span class="news-detail__share-label">Chia sẻ:</span>
          <div class="news-detail__share-btns">
            <button class="news-detail__share-btn" aria-label="Facebook" data-share="facebook">
              <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </button>
            <button class="news-detail__share-btn" aria-label="Zalo" data-share="zalo">
              <i class="fa-solid fa-comment-dots" aria-hidden="true"></i>
            </button>
            <button class="news-detail__share-btn" aria-label="Email" data-share="email">
              <i class="fa-regular fa-envelope" aria-hidden="true"></i>
            </button>
            <button class="news-detail__share-btn news-detail__share-btn--copy" aria-label="Sao chép" data-share="copy">
              <i class="fa-solid fa-link" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Article Content -->
      <div class="article-content news-detail__content" itemprop="articleBody">

        <figure class="article-content__figure">
          <img class="article-content__image" src="./public/img/newsdetail01.png" alt="Lễ trao chứng nhận ABET"
            loading="lazy" itemprop="image">
          <figcaption class="article-content__caption">
            Lễ trao chứng nhận ABET cho chương trình Công nghệ Phần mềm của Khoa CNTT
          </figcaption>
        </figure>

        <p class="article-content__paragraph">
          Ngày 15 tháng 1 năm 2024, Khoa Công nghệ Thông tin chính thức nhận được chứng nhận ABET
          (Accreditation Board for Engineering and Technology) cho chương trình Công nghệ Phần mềm.
          Đây là dấu mốc quan trọng khẳng định chất lượng đào tạo của khoa đạt tiêu chuẩn quốc tế,
          mở ra cơ hội hợp tác với các trường đại học hàng đầu thế giới.
        </p>

        <h2 class="article-content__heading" id="ve-chung-nhan-abet">Về chứng nhận ABET</h2>
        <p class="article-content__paragraph">
          ABET là tổ chức chứng nhận chất lượng giáo dục kỹ thuật và công nghệ được công nhận rộng rãi
          trên toàn thế giới. Chứng nhận ABET đảm bảo rằng chương trình đào tạo đáp ứng các tiêu chuẩn
          chất lượng cao về chương trình học, đội ngũ giảng viên, cơ sở vật chất và kết quả học tập
          của sinh viên.
        </p>

        <h2 class="article-content__heading" id="y-nghia-cua-chung-nhan">Ý nghĩa của chứng nhận</h2>
        <p class="article-content__paragraph">
          Việc đạt được chứng nhận ABET mang lại nhiều lợi ích quan trọng:
        </p>

        <ul class="article-content__list">
          <li class="article-content__list-item">
            <strong>Công nhận quốc tế:</strong> Bằng cấp được công nhận tại hơn 30 quốc gia trên thế giới
          </li>
          <li class="article-content__list-item">
            <strong>Cơ hội việc làm:</strong> Sinh viên tốt nghiệp có lợi thế cạnh tranh cao khi ứng tuyển
            vào các công ty đa quốc gia
          </li>
          <li class="article-content__list-item">
            <strong>Học tập nâng cao:</strong> Dễ dàng xin học bổng và theo học chương trình sau đại học
            tại các trường top thế giới
          </li>
          <li class="article-content__list-item">
            <strong>Hợp tác quốc tế:</strong> Tạo điều kiện cho các hoạt động trao đổi sinh viên và hợp tác
            nghiên cứu
          </li>
        </ul>

        <blockquote class="article-content__blockquote">
          <p class="article-content__blockquote-text">
            "Chứng nhận ABET là minh chứng cho nỗ lực không ngừng của toàn thể cán bộ, giảng viên và
            sinh viên trong việc nâng cao chất lượng đào tạo. Đây là bước đệm vững chắc để Khoa CNTT
            tiếp tục phát triển và khẳng định vị thế trong khu vực."
          </p>
          <cite class="article-content__blockquote-author">
            - PGS.TS. Nguyễn Văn A, Trưởng Khoa CNTT
          </cite>
        </blockquote>

        <h2 class="article-content__heading" id="qua-trinh-danh-gia">Quá trình đánh giá</h2>
        <p class="article-content__paragraph">
          Quá trình đánh giá ABET diễn ra trong 18 tháng với sự tham gia của đội ngũ chuyên gia quốc tế.
          Khoa đã chuẩn bị hồ sơ tự đánh giá chi tiết, tổ chức thăm quan cơ sở vật chất, phỏng vấn sinh
          viên và cựu sinh viên, cũng như trình bày về chương trình đào tạo và kết quả nghiên cứu.
        </p>

        <h2 class="article-content__heading" id="cam-ket-chat-luong">Cam kết với chất lượng</h2>
        <p class="article-content__paragraph">
          Để duy trì chứng nhận ABET, Khoa CNTT cam kết tiếp tục đầu tư vào cơ sở vật chất, nâng cao
          năng lực đội ngũ giảng viên, cập nhật chương trình đào tạo theo xu hướng công nghệ mới nhất,
          và tăng cường hoạt động nghiên cứu khoa học cùng với hợp tác quốc tế.
        </p>

        <h2 class="article-content__heading" id="thong-tin-lien-he">Thông tin liên hệ</h2>
        <p class="article-content__paragraph">
          Để biết thêm thông tin về chương trình Công nghệ Phần mềm và chứng nhận ABET, vui lòng liên hệ:
        </p>
        <p class="article-content__paragraph">
          Email: cntt@caothang.edu.vn
        </p>
        <p class="article-content__paragraph">
          Điện thoại: +84 28 38212360
        </p>
        <p class="article-content__paragraph">
          Website: cntt.caothang.edu.vn
        </p>
      </div>

      <!-- Tags -->
      <footer class="news-detail__tags-section">
        <div class="news-detail__tags">
          <i class="fa-solid fa-tag"></i>
          <span class="news-detail__tag">ABET</span>
          <span class="news-detail__tag">Chứng nhận quốc tế</span>
          <span class="news-detail__tag">Chất lượng đào tạo</span>
          <span class="news-detail__tag">Công nghệ Phần mềm</span>
          <span class="news-detail__tag">Hợp tác quốc tế</span>
        </div>
      </footer>

      <!-- Author Card -->
      <aside class="news-detail__author" aria-label="Thông tin tác giả">
        <div class="news-detail__author-icon">
          <i class="fa-regular fa-user"></i>
        </div>
        <div class="news-detail__author-info">
          <h3 class="news-detail__author-name">Ban biên tập</h3>
          <p class="news-detail__author-bio">
            Ban biên tập Khoa Công nghệ Thông tin phụ trách việc biên tập và xuất bản các tin tức,
            bài viết về hoạt động của khoa.
          </p>
        </div>
      </aside>

    </article>

    <!-- Sidebar: TOC + Related Articles -->
    <aside class="news-detail__sidebar" aria-label="Thanh bên">

      <!-- Table of Contents -->
      <nav class="news-detail__toc card" aria-label="Mục lục bài viết">
        <h3 class="news-detail__toc-title">Nội dung bài viết</h3>
        <ul class="news-detail__toc-list" id="toc-list">
          <li class="news-detail__toc-item">
            <a href="#ve-chung-nhan-abet" class="news-detail__toc-link">Về chứng nhận ABET</a>
          </li>
          <li class="news-detail__toc-item">
            <a href="#y-nghia-cua-chung-nhan" class="news-detail__toc-link">Ý nghĩa của chứng nhận</a>
          </li>
          <li class="news-detail__toc-item">
            <a href="#qua-trinh-danh-gia" class="news-detail__toc-link">Quá trình đánh giá</a>
          </li>
          <li class="news-detail__toc-item">
            <a href="#cam-ket-chat-luong" class="news-detail__toc-link">Cam kết với chất lượng</a>
          </li>
          <li class="news-detail__toc-item">
            <a href="#thong-tin-lien-he" class="news-detail__toc-link">Thông tin liên hệ</a>
          </li>
        </ul>
      </nav>

      <!-- Related Articles -->
      <div class="news-detail__related card">
        <h3 class="news-detail__related-title">Bài viết liên quan</h3>
        <div class="news-detail__related-list">

          <article class="news-detail__related-item">
            <a href="#" class="news-detail__related-link">
              <div class="news-detail__related-thumb">
                <img class="news-detail__related-image" src="./public/img/newsdetail02.jpg"
                  alt="Sinh viên khoa giành giải Nhất" loading="lazy">
              </div>
              <div class="news-detail__related-content">
                <span class="news-detail__related-badge">Thành tích</span>
                <h4 class="news-detail__related-heading">
                  Sinh viên khoa giành giải Nhất cuộc thi Lập trình toàn quốc 2024
                </h4>
                <time class="news-detail__related-date" datetime="2024-01-10">10/1/2024</time>
              </div>
            </a>
          </article>

          <hr class="separator" aria-hidden="true">

          <article class="news-detail__related-item">
            <a href="#" class="news-detail__related-link">
              <div class="news-detail__related-thumb">
                <img class="news-detail__related-image" src="./public/img/newsdetail03.jpg"
                  alt="Khánh thành phòng lab AI" loading="lazy">
              </div>
              <div class="news-detail__related-content">
                <span class="news-detail__related-badge">Cơ sở vật chất</span>
                <h4 class="news-detail__related-heading">
                  Khánh thành phòng lab AI và Machine Learning mới
                </h4>
                <time class="news-detail__related-date" datetime="2024-01-08">8/1/2024</time>
              </div>
            </a>
          </article>

          <hr class="separator" aria-hidden="true">

          <article class="news-detail__related-item">
            <a href="#" class="news-detail__related-link">
              <div class="news-detail__related-thumb">
                <img class="news-detail__related-image" src="./public/img/newsdetail04.jpg"
                  alt="Hội thảo khoa học quốc tế" loading="lazy">
              </div>
              <div class="news-detail__related-content">
                <span class="news-detail__related-badge">Sự kiện</span>
                <h4 class="news-detail__related-heading">
                  Hội thảo khoa học quốc tế về Trí tuệ nhân tạo 2024
                </h4>
                <time class="news-detail__related-date" datetime="2024-01-05">5/1/2024</time>
              </div>
            </a>
          </article>

        </div>
        <button class="news-detail__related-more btn" data-variant="outline">
          <span>Xem thêm</span>
          <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
        </button>
      </div>

    </aside>

  </div>

</div>

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "headline": "Khoa CNTT đạt chứng nhận ABET cho chương trình Công nghệ Phần mềm",
    "image": "./public/img/newsdetail01.png",
    "datePublished": "2024-01-15",
    "dateModified": "2024-01-15",
    "author": {
      "@type": "Organization",
      "name": "Ban biên tập Khoa CNTT"
    },
    "publisher": {
      "@type": "Organization",
      "name": "Khoa Công nghệ Thông tin - Trường Cao đẳng Kỹ thuật Cao Thắng",
      "logo": {
        "@type": "ImageObject",
        "url": "./public/img/logo.png"
      }
    },
    "description": "Khoa Công nghệ Thông tin chính thức nhận được chứng nhận ABET cho chương trình Công nghệ Phần mềm, đánh dấu mốc quan trọng khẳng định chất lượng đào tạo đạt tiêu chuẩn quốc tế."
  }
</script>