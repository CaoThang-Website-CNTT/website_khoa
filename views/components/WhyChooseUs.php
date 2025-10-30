<?php

namespace App\Views\Components;

use App\Core\ViewComponent;

class WhyChooseUs extends ViewComponent
{
  public function render(): string
  {
    return <<<HTML
    <section class="my-16">
      <div class="flex flex-col justify-center items-center gap-4 mb-12">
        <p class="text-sm inline-block bg-accent px-4 py-2 rounded-full text-accent-foreground text-center">Tại sao chọn chúng tôi</p>
        <h2 class="text-4xl font-semibold text-center text-foreground">Trải nghiệm Khoa CNTT Cao Thắng</h2>
        <p class="font-normal text-base text-center text-muted-foreground">Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực tiễn</p>
      </div>
      <div class="grid grid-cols-3 grid-rows-2 gap-6 mb-12">
        
        <div class="overflow-hidden relative col-span-2 row-span-2 rounded-3xl" style="height: 480px">
          <img src="https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=750" alt="Trường Cao Thắng" class="absolute">
          <div class="absolute inset-0 bg-black-gradient flex flex-col justify-end items-start text-white p-8">
            <span class="rounded-full bg-accent-foreground text-center font-normal text-sm px-3 py-1 mb-3">Nổi bật</span>
            <h3 class="text-3xl font-semibold mb-4">Môi trường học tập hiện đại, sáng tạo</h3>
            <p class="text-base font-normal mb-4">Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú, không gian làm việc nhóm linh hoạt và hệ thống học tập trực tuyến tiên tiến.</p>
            <a href="#" class="text-base font-normal">Khám phá ngay
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M5.83334 5.83325H14.1667V14.1666" stroke="white" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5.83334 14.1666L14.1667 5.83325" stroke="white" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          </div>
        </div>
        
        
        <div class="col-start-3 row-start-1 bg-accent-foreground rounded-2xl p-8 text-white flex flex-col gap-2 justify-center">
          <h2 class="text-7xl font-bold">22</h2>
          <p class="text-xl font-semibold">Năm kinh nghiệm</p>
          <p class="text-base font-normal">Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm 2003</p>
        </div>

        <div class="col-start-3 row-start-2 bg-pink-gradient rounded-2xl p-8 text-white flex flex-col gap-2 justify-center">
          <h2 class="text-7xl font-bold">95%</h2>
          <p class="text-xl font-semibold">Tỷ lệ việc làm</p>
          <p class="text-base font-normal">Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp</p>
        </div>
      </div>
      <!--stats-->
      <div class="flex justify-center items-stretch gap-6 mb-12">
        <div class="flex flex-col items-start justify-start flex-1 bg-white rounded-2xl p-6 border-gray" style="width: 266px">
          <div class="flex justify-center items-center rounded-full text-4xl mb-4 bg-accent p-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M16 18L22 12L16 6" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8 6L2 12L8 18" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h4 class="text-base font-semibold text-foreground mb-2">Công nghệ Tiên tiến</h4>
          <p class="text-sm font-normal text-muted-foreground leading-5">Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT</p>
        </div>

        <div class="flex flex-col items-start justify-start flex-1 bg-white rounded-2xl p-6 border-gray" style="width: 266px">
          <div class="flex justify-center items-center rounded-full text-4xl mb-4 bg-accent p-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M16 18L22 12L16 6" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8 6L2 12L8 18" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h4 class="text-base font-semibold text-foreground mb-2">Công nghệ Tiên tiến</h4>
          <p class="text-sm font-normal text-muted-foreground leading-5">Học</p>
        </div>
        <div class="flex flex-col items-start justify-start flex-1 bg-white rounded-2xl p-6 border-gray" style="width: 266px">
          <div class="flex justify-center items-center rounded-full text-4xl mb-4 bg-accent p-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M16 18L22 12L16 6" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8 6L2 12L8 18" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h4 class="text-base font-semibold text-foreground mb-2">Công nghệ Tiên tiến</h4>
          <p class="text-sm font-normal text-muted-foreground leading-5">Học</p>
        </div>
        <div class="flex flex-col items-start justify-start flex-1 bg-white rounded-2xl p-6 border-gray" style="width: 266px">
          <div class="flex justify-center items-center rounded-full text-4xl mb-4 bg-accent p-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M16 18L22 12L16 6" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8 6L2 12L8 18" stroke="#155DFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h4 class="text-base font-semibold text-foreground mb-2">Công nghệ Tiên tiến</h4>
          <p class="text-sm font-normal text-muted-foreground leading-5">Học</p>
        </div>
      </div>
      <!---->
      <div class="flex justify-center items-stretch gap-6 mb-12" style="height: 280px">
        <div class="flex-1 overflow-hidden relative rounded-2xl">
          <img src="https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=600" alt="Trường Cao Thắng" class="absolute">
            <div class="absolute inset-0 bg-blue-gradient flex flex-col justify-end items-start text-white p-6">
              <h3 class="text-2xl font-semibold mb-2">Nghiên cứu & Phát triển</h3>
              <p class="text-sm font-normal leading-5">Tham gia các dự án nghiên cứu thực tế cùng giảng viên</p>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden relative rounded-2xl">
          <img src="https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=600" alt="Trường Cao Thắng" class="absolute">
            <div class="absolute inset-0 bg-green-gradient flex flex-col justify-end items-start text-white p-6">
              <h3 class="text-2xl font-semibold mb-2">Nghiên cứu & Phát triển</h3>
              <p class="text-sm font-normal leading-5">Tham gia các dự án nghiên cứu thực tế cùng giảng viên</p>
            </div>
        </div>
      </div>
    </section>
    HTML;
  }
}
