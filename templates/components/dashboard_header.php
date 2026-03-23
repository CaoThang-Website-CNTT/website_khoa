<!-- ========== header start ========== -->
<header class="header">
  <div class="container container-fluid">
    <div class="flex justify-between items-center">
      <div class="header-left flex items-center">
        <div class="menu-toggle-btn mr-2">
          <button id="menu-toggle" data-size="lg" data-variant="primary" class="btn">
            <i class="fa-solid fa-bars"></i>
            Menu
          </button>
        </div>
        <div class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm">
          <i class="fa-brands fa-sistrix"></i>

          <input class="search-bar__input" placeholder="Tìm kiếm..." autocomplete="off" autocorrect="off">
        </div>
      </div>
      <div class="header-right">
        <!-- notification/message/profile (kept) -->
        <div class="profile-box ml-12">
          <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile" data-toggle="dropdown"
            aria-expanded="false">
            <div class="profile-info">
              <div class="info">
                <div class="image">
                  <img src="<?= url('assets/images/profile/profile-image.png') ?>" alt="" />
                </div>
                <div>
                  <h6 class="font-medium">Adam Joe</h6>
                  <p>Admin</p>
                </div>
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>
<!-- ========== header end ========== -->