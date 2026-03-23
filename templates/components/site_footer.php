<?php
// $settings được extract từ render() — available tự động
$siteName = htmlspecialchars($settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin');
$desc = htmlspecialchars($settings['contact.description'] ?? '');
$address = htmlspecialchars($settings['contact.address'] ?? '');
$phone = htmlspecialchars($settings['contact.phone'] ?? '');
$email = htmlspecialchars($settings['contact.email'] ?? '');
$facebook = htmlspecialchars($settings['social.facebook'] ?? '');
$youtube = htmlspecialchars($settings['social.youtube'] ?? '');
$instagram = htmlspecialchars($settings['social.instagram'] ?? '');
?>
<footer class="footer">
  <div class="footer__main-content container flex gap-16 py-12 px-4">

    <!-- Brand & Description -->
    <div class="footer__info">
      <div class="footer__brand-group flex gap-3 mb-4 items-center">
        <div class="footer__logo overflow-hidden rounded-full">
          <img class="footer__logo-image w-full h-full object-fit" src="<?= url('/public/img/faculty_logo.jpg') ?>"
            alt="Logo <?= $siteName ?>">
        </div>
        <div class="footer__brand-text flex flex-col justify-center">
          <div class="footer__faculty-name text-xl uppercase">
            <?= $siteName ?>
          </div>
          <div class="footer__uni-name font-normal uppercase">
            TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG
          </div>
        </div>
      </div>
      <?php if ($desc): ?>
        <p class="footer__description text-sm"><?= $desc ?></p>
      <?php endif; ?>
    </div>

    <!-- Quick links — từ menu system, giữ hardcode cho đến khi tích hợp menu -->
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

    <!-- Programs — hardcode cho đến khi có programs module -->
    <div class="footer__nav-group flex-1">
      <h3 class="footer__nav-title font-normal mb-4">Chương trình đào tạo</h3>
      <ul class="footer__nav-list">
        <li class="footer__nav-item text-sm font-normal mb-2">
          <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Công nghệ phần mềm</a>
        </li>
        <li class="footer__nav-item text-sm font-normal mb-2">
          <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Lập trình di động</a>
        </li>
        <li class="footer__nav-item text-sm font-normal mb-2">
          <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Lập trình website</a>
        </li>
        <li class="footer__nav-item text-sm font-normal mb-2">
          <a href="#" class="footer__nav-link link-hover--standout link-hover--underline">Trí tuệ nhân tạo</a>
        </li>
      </ul>
    </div>

    <!-- Contact & Social -->
    <div class="footer__nav-group flex-1">
      <h3 class="footer__nav-title font-normal mb-4">Liên hệ</h3>
      <ul class="footer__contact-list">

        <?php if ($address): ?>
          <li class="footer__contact-item mb-3 flex gap-3">
            <span class="footer__contact-icon">
              <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M13.3333 6.66683C13.3333 9.9955 9.64063 13.4622 8.40063 14.5328C8.28511 14.6197 8.14449 14.6667 7.99996 14.6667C7.85543 14.6667 7.71481 14.6197 7.59929 14.5328C6.35929 13.4622 2.66663 9.9955 2.66663 6.66683C2.66663 5.25234 3.22853 3.89579 4.22872 2.89559C5.22892 1.8954 6.58547 1.3335 7.99996 1.3335C9.41445 1.3335 10.771 1.8954 11.7712 2.89559C12.7714 3.89579 13.3333 5.25234 13.3333 6.66683Z"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                <path
                  d="M8 8.6665C9.10457 8.6665 10 7.77107 10 6.6665C10 5.56193 9.10457 4.6665 8 4.6665C6.89543 4.6665 6 5.56193 6 6.6665C6 7.77107 6.89543 8.6665 8 8.6665Z"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </span>
            <p class="footer__contact-text text-sm font-normal"><?= $address ?></p>
          </li>
        <?php endif; ?>

        <?php if ($phone): ?>
          <li class="footer__contact-item mb-3 flex gap-3">
            <span class="footer__contact-icon">
              <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_footer_phone)">
                  <path
                    d="M9.22137 11.0455C9.35906 11.1087 9.51417 11.1232 9.66117 11.0865C9.80816 11.0497 9.93826 10.964 10.03 10.8435L10.2667 10.5335C10.3909 10.3679 10.5519 10.2335 10.7371 10.1409C10.9222 10.0484 11.1264 10.0002 11.3334 10.0002H13.3334C13.687 10.0002 14.0261 10.1406 14.2762 10.3907C14.5262 10.6407 14.6667 10.9799 14.6667 11.3335V13.3335C14.6667 13.6871 14.5262 14.0263 14.2762 14.2763C14.0261 14.5264 13.687 14.6668 13.3334 14.6668C10.1508 14.6668 7.09853 13.4025 4.84809 11.1521C2.59766 8.90167 1.33337 5.84943 1.33337 2.66683C1.33337 2.31321 1.47385 1.97407 1.7239 1.72402C1.97395 1.47397 2.31309 1.3335 2.66671 1.3335H4.66671C5.02033 1.3335 5.35947 1.47397 5.60952 1.72402C5.85956 1.97407 6.00004 2.31321 6.00004 2.66683V4.66683C6.00004 4.87382 5.95185 5.07797 5.85928 5.26311C5.76671 5.44825 5.6323 5.6093 5.46671 5.7335L5.15471 5.9675C5.03232 6.06095 4.94605 6.19389 4.91057 6.34373C4.87508 6.49357 4.89256 6.65108 4.96004 6.7895C5.87116 8.64007 7.36966 10.1367 9.22137 11.0455Z"
                    stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
                <defs>
                  <clipPath id="clip0_footer_phone">
                    <rect width="16" height="16" fill="currentColor"></rect>
                  </clipPath>
                </defs>
              </svg>
            </span>
            <p class="footer__contact-text text-sm font-normal"><?= $phone ?></p>
          </li>
        <?php endif; ?>

        <?php if ($email): ?>
          <li class="footer__contact-item mb-3 flex gap-3">
            <span class="footer__contact-icon">
              <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M14.6667 4.6665L8.67271 8.4845C8.4693 8.60265 8.23827 8.66487 8.00304 8.66487C7.76782 8.66487 7.53678 8.60265 7.33337 8.4845L1.33337 4.6665"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                <path
                  d="M13.3334 2.6665H2.66671C1.93033 2.6665 1.33337 3.26346 1.33337 3.99984V11.9998C1.33337 12.7362 1.93033 13.3332 2.66671 13.3332H13.3334C14.0698 13.3332 14.6667 12.7362 14.6667 11.9998V3.99984C14.6667 3.26346 14.0698 2.6665 13.3334 2.6665Z"
                  stroke="#51A2FF" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </span>
            <p class="footer__contact-text text-sm font-normal"><?= $email ?></p>
          </li>
        <?php endif; ?>

      </ul>

      <?php if ($facebook || $youtube || $instagram): ?>
        <h3 class="footer__nav-title font-normal mb-2">Theo dõi chúng tôi</h3>
        <ul class="footer__social-list flex gap-2 items-center">

          <?php if ($facebook): ?>
            <li class="footer__social-item">
              <a href="<?= $facebook ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer">
                <svg aria-label="Facebook" width="16" height="16" viewBox="0 0 16 16" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 1.3335H9.99996C9.1159 1.3335 8.26806 1.68469 7.64294 2.30981C7.01782 2.93493 6.66663 3.78277 6.66663 4.66683V6.66683H4.66663V9.3335H6.66663V14.6668H9.33329V9.3335H11.3333L12 6.66683H9.33329V4.66683C9.33329 4.49002 9.40353 4.32045 9.52856 4.19542C9.65358 4.0704 9.82315 4.00016 9.99996 4.00016H12V1.3335Z"
                    stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </a>
            </li>
          <?php endif; ?>

          <?php if ($youtube): ?>
            <li class="footer__social-item">
              <a href="<?= $youtube ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer">
                <svg aria-label="YouTube" width="16" height="16" viewBox="0 0 16 16" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M1.66667 11.3333C1.20095 9.13551 1.20095 6.86449 1.66667 4.66667C1.72786 4.44347 1.8461 4.24005 2.00974 4.0764C2.17339 3.91276 2.37681 3.79453 2.6 3.73333C6.17564 3.14097 9.82437 3.14097 13.4 3.73333C13.6232 3.79453 13.8266 3.91276 13.9903 4.0764C14.1539 4.24005 14.2721 4.44347 14.3333 4.66667C14.7991 6.86449 14.7991 9.13551 14.3333 11.3333C14.2721 11.5565 14.1539 11.7599 13.9903 11.9236C13.8266 12.0872 13.6232 12.2055 13.4 12.2667C9.82438 12.8591 6.17563 12.8591 2.6 12.2667C2.37681 12.2055 2.17339 12.0872 2.00974 11.9236C1.8461 11.7599 1.72786 11.5565 1.66667 11.3333Z"
                    stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M6.66663 10L9.99996 8L6.66663 6V10Z" stroke="currentColor" stroke-width="1.33333"
                    stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </a>
            </li>
          <?php endif; ?>

          <?php if ($instagram): ?>
            <li class="footer__social-item">
              <a href="<?= $instagram ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer">
                <svg aria-label="Instagram" width="16" height="16" viewBox="0 0 16 16" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M10.6666 5.3335C11.7275 5.3335 12.7449 5.75492 13.4951 6.50507C14.2452 7.25521 14.6666 8.27263 14.6666 9.3335V14.0002H12V9.3335C12 8.97987 11.8595 8.64074 11.6094 8.39069C11.3594 8.14064 11.0202 8.00016 10.6666 8.00016C10.313 8.00016 9.97387 8.14064 9.72382 8.39069C9.47377 8.64074 9.33329 8.97987 9.33329 9.3335V14.0002H6.66663V9.3335C6.66663 8.27263 7.08805 7.25521 7.8382 6.50507C8.58834 5.75492 9.60576 5.3335 10.6666 5.3335Z"
                    stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M4.00004 6H1.33337V14H4.00004V6Z" stroke="currentColor" stroke-width="1.33333"
                    stroke-linecap="round" stroke-linejoin="round"></path>
                  <path
                    d="M2.66671 4.00016C3.40309 4.00016 4.00004 3.40321 4.00004 2.66683C4.00004 1.93045 3.40309 1.3335 2.66671 1.3335C1.93033 1.3335 1.33337 1.93045 1.33337 2.66683C1.33337 3.40321 1.93033 4.00016 2.66671 4.00016Z"
                    stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </a>
            </li>
          <?php endif; ?>

        </ul>
      <?php endif; ?>

    </div>
  </div>

  <div class="footer__copyright container py-12 px-4">
    <p class="footer__copyright-text text-sm">
      © <?= date('Y') ?> <?= $siteName ?> - Trường Cao Đẳng Kỹ Thuật Cao Thắng. All rights reserved.
    </p>
  </div>
</footer>