<!-- ========== header start ========== -->
<header class="header">
  <div class="container container-fluid">
    <div class="flex justify-between items-center">
      <div class="header-left flex items-center">
        <div class="menu-toggle-btn mr-2">
          <button id="menu-toggle" data-size="lg" data-variant="primary" class="btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
              <path
                d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z" />
            </svg>
            Menu
          </button>
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