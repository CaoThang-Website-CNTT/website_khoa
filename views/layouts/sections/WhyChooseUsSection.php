<?php

namespace App\Views\Layouts\Sections;

use App\Core\ViewComponent;
use App\Views\Components\{SectionTitle};

class WhyChooseUsSection extends ViewComponent
{
  private array $outstanding_info = [
    ['icon' => ['file_name' => 'code_icon', 'alt' => 'Code Icon'], 'title' => 'Công nghệ tiên tiến', 'short' => 'Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT'],
    ['icon' => ['file_name' => 'people_icon', 'alt' => 'People Icon'], 'title' => 'Cộng đồng Mạnh mẽ', 'short' => 'Kết nối với 10,000+ sinh viên và cựu sinh viên trên toàn quốc'],
    ['icon' => ['file_name' => 'medal_icon', 'alt' => 'Medal Icon'], 'title' => 'Chất lượng Quốc tế', 'short' => 'Chương trình đạt chuẩn ABET và kiểm định quốc tế'],
    ['icon' => ['file_name' => 'rocket_icon', 'alt' => 'Rocket Icon'], 'title' => 'Khởi nghiệp', 'short' => 'Hỗ trợ ý tưởng startup và kết nối nhà đầu tư']
  ];

  private array $statistic_info = [
    ['title' => '20', 'sub_title' => 'Năm kinh nghiệm', 'short' => 'Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm 2003'],
    ['title' => '95%', 'sub_title' => 'Tỷ lệ việc làm', 'short' => 'Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp']
  ];

  private function render_outstanding_info(array $outstanding_info): string
  {
    $items = '';

    foreach ($outstanding_info as $info) {
      $items .= <<<HTML
        <div class="flex flex-col items-start justify-start flex-1 bg-white rounded-2xl p-6 border-gray" style="width: 266px">
          <div class="flex justify-center items-center rounded-full text-4xl mb-4 bg-accent p-3">
          {$this->icon($info['icon']['file_name'], ['alt' =>$info['icon']['alt']])}
          </div>
          <h4 class="text-base font-semibold text-foreground mb-2">{$info['title']}</h4>
          <p class="text-sm font-normal text-muted-foreground leading-5">{$info['short']}</p>
        </div>
      HTML;
    }

    return <<<HTML
    <div class="flex justify-center items-stretch self-stretch gap-6 mb-12">
      {$items}
    </div>
    HTML;
  }

  private function render_statistic_info(array $statisticInfo): string
  {
    $items = '';

    foreach ($statisticInfo as $info) {
      $items .= <<<HTML
        <div class="col-start-3 row-start-1 bg-accent-foreground rounded-2xl p-8 text-white flex flex-col gap-2 justify-center">
          <h2 class="text-7xl font-bold">{$info['title']}</h2>
          <p class="text-xl font-semibold">{$info['sub_title']}</p>
          <p class="text-base font-normal">{$info['short']}</p>
        </div>
      HTML;
    }

    return <<<HTML
    <div class="flex justify-center items-stretch gap-6 mb-12">
      {$items}
    </div>
    HTML;
  }

  public function render(): string
  {
    return <<<HTML
      <div class="flex flex-col items-center justify-center">
        <div class="grid grid-cols-3 grid-rows-2 gap-6 mb-12 self-stretch">
          <div class="overflow-hidden relative col-span-2 row-span-2 rounded-3xl image-wrapper" style="height: 480px">
            <img class="image" src="https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=750" alt="Trường Cao Thắng" class="absolute w-full h-full object-cover">
            <div class="absolute inset-0 bg-black-gradient flex flex-col justify-end items-start text-white p-8">
              <span class="rounded-full bg-accent-foreground text-center font-normal text-sm px-3 py-1 mb-3">Nổi bật</span>
              <h3 class="text-3xl font-semibold mb-4">Môi trường học tập hiện đại, sáng tạo</h3>
              <p class="text-base font-normal mb-4">Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú, không gian làm việc nhóm linh hoạt và hệ thống học tập trực tuyến tiên tiến.</p>
              <a href="#" class="text-base font-normal">Khám phá ngay
                {$this->icon('top_right_arrow_icon', ['alt' => 'Top right arrow Icon'])}
              </a>
            </div>
          </div>
          
          <!--statistic info-->
          <!-- {$this->render_statistic_info($this->statistic_info)} -->
          <div class="col-start-3 row-start-1 bg-accent-foreground rounded-2xl p-8 text-white flex flex-col gap-2 justify-center">
            <h2 class="text-7xl font-bold">{$this->statistic_info[0]['title']}</h2>
            <p class="text-xl font-semibold">{$this->statistic_info[0]['sub_title']}</p>
            <p class="text-base font-normal">{$this->statistic_info[0]['short']}</p>
          </div>

          <div class="col-start-3 row-start-2 bg-pink-gradient rounded-2xl p-8 text-white flex flex-col gap-2 justify-center">
            <h2 class="text-7xl font-bold">{$this->statistic_info[1]['title']}</h2>
            <p class="text-xl font-semibold">{$this->statistic_info[1]['sub_title']}</p>
            <p class="text-base font-normal">{$this->statistic_info[1]['short']}</p>
          </div>
        </div>
        <!--outstanding info-->
        {$this->render_outstanding_info($this->outstanding_info)}
        <!---->
        <div class="flex justify-center items-stretch self-stretch gap-6 mb-12" style="height: 280px">
          <div class="flex-1 overflow-hidden relative rounded-2xl image-wrapper">
            <img class="image object-fit" src="https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=600" alt="Trường Cao Thắng" class="absolute w-full h-full object-cover">
              <div class="absolute inset-0 bg-blue-gradient flex flex-col justify-end items-start text-white p-6">
                <h3 class="text-2xl font-semibold mb-2">Nghiên cứu & Phát triển</h3>
                <p class="text-sm font-normal leading-5">Tham gia các dự án nghiên cứu thực tế cùng giảng viên</p>
              </div>
          </div>
          
          <div class="flex-1 overflow-hidden relative rounded-2xl image-wrapper">
            <img class="image object-fit" src="https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=600" alt="Trường Cao Thắng" class="absolute w-full h-full object-cover">
              <div class="absolute inset-0 bg-green-gradient flex flex-col justify-end items-start text-white p-6">
                <h3 class="text-2xl font-semibold mb-2">Hợp tác Quốc tế</h3>
                <p class="text-sm font-normal leading-5">Cơ hội trao đổi sinh viên và học bổng du học</p>
              </div>
          </div>
        </div>
      </div>
    HTML;
  }
}
