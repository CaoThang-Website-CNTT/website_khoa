<?php
// $settings được extract từ render() - available tự động
$siteName = htmlspecialchars($settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin');
$desc = htmlspecialchars($settings['contact_description'] ?? '');
$address = htmlspecialchars($settings['contact_address'] ?? '');
$phone = htmlspecialchars($settings['contact_phone'] ?? '');
$email = htmlspecialchars($settings['contact_email'] ?? '');
$facebook = htmlspecialchars($settings['social_facebook'] ?? '');
$youtube = htmlspecialchars($settings['social_youtube'] ?? '');
$tiktok = htmlspecialchars($settings['social_tiktok'] ?? '');
$instagram = htmlspecialchars($settings['social_instagram'] ?? '');
$footerSections = array_slice(is_array($footerMenu ?? null) ? $footerMenu : [], 0, 2);
?>
<footer class="footer">
  <div class="footer__main-content container flex flex-col lg:flex-row gap-16 py-12 px-4">

    <!-- Brand & Description -->
    <div class="footer__info">
      <div class="footer__brand-group flex gap-3 mb-4 items-center">
        <div class="footer__logo overflow-hidden rounded-full shrink-0">
          <img class="footer__logo-image w-full h-full object-cover" src="<?= url('/public/img/faculty_logo.jpg') ?>"
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

    <?php foreach ($footerSections as $section): ?>
      <?php if (!$section->hasChildren()) continue; ?>
      <div class="footer__nav-group flex-1">
        <h3 class="footer__nav-title font-normal mb-4"><?= htmlspecialchars($section->label) ?></h3>
        <ul class="footer__nav-list">
          <?php foreach ($section->children as $item): ?>
            <?php
            $hasUrl = trim((string) $item->url) !== '' && $item->url !== '#';
            $itemUrl = $hasUrl ? url($item->url) : '#';
            ?>
            <li class="footer__nav-item text-sm font-normal mb-2">
              <a href="<?= htmlspecialchars($itemUrl) ?>" class="footer__nav-link link-hover--standout link-hover--underline">
                <?= htmlspecialchars($item->label) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

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

      <?php if ($facebook || $youtube || $tiktok || $instagram): ?>
        <h3 class="footer__nav-title font-normal mb-2">Theo dõi chúng tôi</h3>
        <ul class="footer__social-list flex gap-2 items-center">

          <?php if ($facebook): ?>
            <li class="footer__social-item">
              <a href="<?= $facebook ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer" aria-label="Facebook">
                <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
              </a>
            </li>
          <?php endif; ?>

          <?php if ($youtube): ?>
            <li class="footer__social-item">
              <a href="<?= $youtube ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer" aria-label="YouTube">
                <i class="fa-brands fa-youtube" aria-hidden="true"></i>
              </a>
            </li>
          <?php endif; ?>

          <?php if ($tiktok): ?>
            <li class="footer__social-item">
              <a href="<?= $tiktok ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer" aria-label="TikTok">
                <i class="fa-brands fa-tiktok" aria-hidden="true"></i>
              </a>
            </li>
          <?php endif; ?>

          <?php if ($instagram): ?>
            <li class="footer__social-item">
              <a href="<?= $instagram ?>" class="footer__social-link p-3 link-hover--standout" target="_blank"
                rel="noopener noreferrer" aria-label="Instagram">
                <i class="fa-brands fa-instagram" aria-hidden="true"></i>
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
