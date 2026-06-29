<?php

namespace App\Cms;

final class EducationPageDefaults
{
  public static function hub(): array
  {
    return [
      'eyebrow' => 'Đào tạo tại Khoa Công nghệ thông tin',
      'title' => 'Chọn lộ trình phù hợp với bạn',
      'description' => 'Khám phá thông tin tuyển sinh, định hướng từng chương trình, chuẩn đầu ra và kế hoạch học tập được công bố từ các tài liệu đào tạo chính thức.',
      'links' => [
        ['icon' => 'fa-solid fa-user-graduate', 'title' => 'Thông tin tuyển sinh', 'description' => 'Tìm hiểu ngành đào tạo trước khi xem hướng dẫn đăng ký chính thức của Trường.', 'url' => 'dao-tao/tuyen-sinh', 'label' => 'Xem thông tin'],
        ['icon' => 'fa-solid fa-graduation-cap', 'title' => 'Chương trình đào tạo', 'description' => 'So sánh mục tiêu, định hướng nghề nghiệp và cấu trúc của ba chương trình.', 'url' => 'dao-tao/chuong-trinh-dao-tao', 'label' => 'Khám phá chương trình'],
        ['icon' => 'fa-solid fa-bullseye', 'title' => 'Chuẩn đầu ra', 'description' => 'Những năng lực sinh viên cần đạt khi tốt nghiệp và sau khi tham gia thị trường lao động.', 'url' => 'dao-tao/chuan-dau-ra', 'label' => 'Xem chuẩn đầu ra'],
        ['icon' => 'fa-solid fa-list-check', 'title' => 'Danh sách môn học', 'description' => 'Theo dõi các học phần theo từng học kỳ, gồm tín chỉ và thời lượng lý thuyết, thực hành.', 'url' => 'dao-tao/danh-sach-mon-hoc', 'label' => 'Xem kế hoạch học tập'],
      ],
      'programs_title' => 'Ba chương trình, nhiều hướng phát triển',
      'programs_description' => 'Các chương trình kết hợp kiến thức nền tảng với thực hành nghề nghiệp và được tổ chức trong sáu học kỳ.',
      'programs' => array_map(fn(array $program) => [
        'key' => $program['key'],
        'name' => $program['name'],
        'short_name' => $program['short_name'],
        'summary' => $program['summary'],
        'credits' => $program['credits'],
      ], self::programs()),
    ];
  }

  public static function admissions(): array
  {
    return [
      'eyebrow' => 'Thông tin tuyển sinh',
      'title' => 'Bắt đầu hành trình công nghệ tại Cao Thắng',
      'description' => 'Khoa Công nghệ thông tin đào tạo theo định hướng ứng dụng, chú trọng năng lực thực hành và khả năng thích nghi với môi trường nghề nghiệp.',
      'notice_title' => 'Thông tin đăng ký chính thức',
      'notice' => 'Chỉ tiêu, phương thức xét tuyển, mốc thời gian, học phí và hồ sơ được Trường Cao đẳng Kỹ thuật Cao Thắng công bố tập trung trên cổng tuyển sinh. Nội dung tại trang này giúp bạn định hướng chương trình, không thay thế thông báo tuyển sinh chính thức.',
      'cta_label' => 'Đến cổng tuyển sinh Cao Thắng',
      'cta_url' => 'https://caothang.edu.vn/tuyensinh/',
      'steps_title' => 'Tìm chương trình phù hợp',
      'steps' => [
        ['title' => 'Khám phá chương trình', 'description' => 'Đọc mục tiêu và định hướng nghề nghiệp của từng ngành.'],
        ['title' => 'Kiểm tra chuẩn đầu ra', 'description' => 'Xác định những kiến thức và kỹ năng bạn sẽ hình thành.'],
        ['title' => 'Xem kế hoạch học tập', 'description' => 'Tham khảo các môn học, tín chỉ và thời lượng thực hành.'],
        ['title' => 'Theo dõi thông báo chính thức', 'description' => 'Kiểm tra điều kiện và thời hạn đăng ký tại cổng tuyển sinh của Trường.'],
      ],
      'programs' => array_map(fn(array $program) => [
        'key' => $program['key'], 'name' => $program['name'], 'summary' => $program['summary'],
      ], self::programs()),
    ];
  }

  public static function programsSection(): array
  {
    return [
      'eyebrow' => 'Chương trình đào tạo',
      'title' => 'Nền tảng vững, kỹ năng sát thực tế',
      'description' => 'Mở từng chương trình để xem mục tiêu, cấu trúc và hướng phát triển nghề nghiệp.',
      'programs' => self::programs(),
    ];
  }

  public static function outcomes(): array
  {
    return [
      'eyebrow' => 'Chuẩn đầu ra',
      'title' => 'Năng lực được hình thành qua chương trình',
      'description' => 'Mục tiêu chương trình mô tả năng lực sau 2–3 năm làm việc; chuẩn đầu ra là những gì sinh viên có khả năng thực hiện khi tốt nghiệp.',
      'programs' => array_map(fn(array $program) => [
        'key' => $program['key'], 'name' => $program['name'], 'short_name' => $program['short_name'],
        'source_year' => $program['outcomes_year'], 'updated_at' => '27/05/2026',
        'objectives' => $program['objectives'], 'outcomes' => $program['outcomes'],
      ], self::programs()),
    ];
  }

  public static function curriculum(): array
  {
    return [
      'eyebrow' => 'Danh sách môn học',
      'title' => 'Kế hoạch học tập theo từng học kỳ',
      'description' => 'Chọn chương trình và học kỳ để xem các học phần. Các con số phản ánh tài liệu chương trình áp dụng từ năm 2026.',
      'programs' => array_map(fn(array $program) => [
        'key' => $program['key'], 'name' => $program['name'], 'short_name' => $program['short_name'],
        'source_year' => '2026', 'updated_at' => '27/05/2026', 'credits' => $program['credits'],
        'semesters' => self::semesters($program['key']),
      ], self::programs()),
    ];
  }

  public static function repeaterBlueprints(): array
  {
    return [
      'links' => ['label' => 'Thẻ điều hướng', 'item' => ['icon' => 'fa-solid fa-link', 'title' => 'Mục mới', 'description' => '', 'url' => '', 'label' => 'Xem thêm']],
      'steps' => ['label' => 'Bước định hướng', 'item' => ['title' => 'Bước mới', 'description' => '']],
      'programs' => ['label' => 'Chương trình', 'item' => ['key' => 'chuong-trinh-moi', 'name' => 'Chương trình mới', 'short_name' => 'CT', 'summary' => '', 'source_year' => '2026', 'updated_at' => '', 'credits' => '0', 'duration' => '6 học kỳ', 'practice_ratio' => '', 'career' => '', 'objectives' => [], 'outcomes' => [], 'specializations' => [], 'semesters' => []]],
      'programs.*.objectives' => ['label' => 'Mục tiêu', 'item' => 'Mục tiêu mới'],
      'programs.*.outcomes' => ['label' => 'Chuẩn đầu ra', 'item' => 'Chuẩn đầu ra mới'],
      'programs.*.specializations' => ['label' => 'Hướng chuyên môn', 'item' => 'Hướng chuyên môn mới'],
      'programs.*.semesters' => ['label' => 'Học kỳ', 'item' => ['key' => '1', 'name' => 'Học kỳ 1', 'courses' => []]],
      'programs.*.semesters.*.courses' => ['label' => 'Học phần', 'item' => ['code' => '', 'name' => 'Học phần mới', 'credits' => '0', 'theory' => '0', 'practice' => '0']],
    ];
  }

  private static function programs(): array
  {
    return [
      [
        'key' => 'cntt', 'short_name' => 'CNTT', 'name' => 'Cao đẳng Công nghệ thông tin',
        'summary' => 'Phát triển giải pháp phần mềm, web, di động, mạng máy tính và trí tuệ nhân tạo ứng dụng.',
        'duration' => '6 học kỳ', 'credits' => '134', 'practice_ratio' => '59,8%', 'source_year' => '2026', 'outcomes_year' => '2026', 'updated_at' => '21/04/2026',
        'career' => 'Tham gia phân tích, thiết kế, triển khai và tư vấn các giải pháp công nghệ thông tin trong cơ quan, tổ chức và doanh nghiệp.',
        'objectives' => [
          'Là thành viên chủ chốt trong nhóm phân tích, thiết kế, triển khai, tư vấn các giải pháp và dự án công nghệ thông tin.',
          'Hoàn thiện kỹ năng và kiến thức các lĩnh vực công nghệ thông tin thông qua việc tiếp tục học tập nâng cao trình độ.',
          'Trở thành chuyên gia công nghệ thông tin có tinh thần đóng góp xã hội; tuân thủ đạo đức, pháp luật và nhận thức tác động của công nghệ đối với xã hội.',
        ],
        'outcomes' => [
          'Phân tích bài toán trong phạm vi rộng và áp dụng kiến thức cơ bản về công nghệ thông tin để xác định giải pháp.',
          'Thiết kế và triển khai các giải pháp công nghệ thông tin đáp ứng tập hợp yêu cầu trong ngữ cảnh cho trước.',
          'Giao tiếp hiệu quả trong môi trường làm việc chuyên nghiệp.',
          'Nhận thức trách nhiệm nghề nghiệp và đưa ra đánh giá dựa trên các nguyên tắc pháp lý, đạo đức.',
          'Làm việc hiệu quả với vai trò thành viên của nhóm.',
          'Áp dụng, tích hợp và quản lý bảo mật trong lĩnh vực công nghệ thông tin để đáp ứng nhu cầu người dùng.',
        ],
        'specializations' => ['Công nghệ lập trình ứng dụng Web', 'Công nghệ lập trình ứng dụng Di động', 'Mạng máy tính', 'Trí tuệ nhân tạo ứng dụng'],
      ],
      [
        'key' => 'qtm', 'short_name' => 'QTM', 'name' => 'Cao đẳng Quản trị mạng máy tính',
        'summary' => 'Thiết kế, triển khai, vận hành và bảo vệ hạ tầng mạng, máy chủ và dịch vụ hệ thống.',
        'duration' => '6 học kỳ', 'credits' => '134', 'practice_ratio' => '58,5%', 'source_year' => '2026', 'outcomes_year' => '2026', 'updated_at' => '27/05/2026',
        'career' => 'Làm việc trong quản trị hệ thống mạng, triển khai hạ tầng, bảo mật và tư vấn giải pháp tin học hóa cho tổ chức, doanh nghiệp.',
        'objectives' => [
          'Là nhân viên lành nghề trong công việc quản trị hệ thống mạng của tổ chức, doanh nghiệp.',
          'Là nhân viên lành nghề trong nhóm phân tích, thiết kế và triển khai các giải pháp hạ tầng hệ thống mạng.',
          'Là nhân viên lành nghề trong hoạt động tư vấn kỹ thuật, tư vấn giải pháp tin học hóa.',
        ],
        'outcomes' => [
          'Tuân thủ quy định về an toàn thông tin, bảo mật dữ liệu và đạo đức nghề nghiệp; có trách nhiệm trong công việc.',
          'Tự học, tìm hiểu tài liệu kỹ thuật và cập nhật công nghệ mới phục vụ chuyên môn.',
          'Giao tiếp, làm việc nhóm, trình bày vấn đề kỹ thuật và viết báo cáo chuyên môn hiệu quả.',
          'Sử dụng các phần mềm ứng dụng phục vụ công việc chuyên môn.',
          'Lắp ráp, cài đặt, cấu hình, bảo trì và xử lý sự cố máy tính, thiết bị mạng cơ bản.',
          'Phân tích yêu cầu để thiết kế, triển khai, vận hành và bảo trì hệ thống mạng.',
          'Phân tích, thiết kế, triển khai và bảo trì cơ sở dữ liệu, phần mềm, website và mạng máy tính.',
          'Áp dụng giải pháp bảo mật và an ninh mạng cơ bản để giám sát, bảo đảm an toàn hệ thống thông tin.',
        ],
        'specializations' => [],
      ],
      [
        'key' => 'scmt', 'short_name' => 'SCMT', 'name' => 'Cao đẳng Kỹ thuật sửa chữa, lắp ráp máy tính',
        'summary' => 'Lắp ráp, chẩn đoán, sửa chữa phần cứng và triển khai hệ thống máy tính, thiết bị mạng.',
        'duration' => '6 học kỳ', 'credits' => '131', 'practice_ratio' => '59,8%', 'source_year' => '2026', 'outcomes_year' => '2024', 'updated_at' => '27/05/2026',
        'career' => 'Xây dựng, bảo trì và sửa chữa hệ thống máy tính; tư vấn giải pháp kỹ thuật và lựa chọn thiết bị tin học.',
        'objectives' => [
          'Là nhân viên lành nghề trong xây dựng, bảo trì, sửa chữa hệ thống máy tính của cơ quan, tổ chức và doanh nghiệp.',
          'Là nhân viên lành nghề trong hoạt động tư vấn giải pháp kỹ thuật và lựa chọn thiết bị tin học.',
        ],
        'outcomes' => [
          'Có động cơ nghề nghiệp đúng đắn, tuân thủ chuẩn mực và đạo đức nghề nghiệp, chịu trách nhiệm với công việc.',
          'Có ý thức học tập và tự rèn luyện để nâng cao trình độ chuyên môn.',
          'Áp dụng hiệu quả kỹ năng giao tiếp, làm việc nhóm, trình bày vấn đề kỹ thuật, quản lý thời gian và viết báo cáo.',
          'Đọc hiểu tài liệu hướng dẫn tiếng Anh và sử dụng phần mềm văn phòng, đồ họa phục vụ công việc.',
          'Lắp ráp, cài đặt phần mềm và xử lý các sự cố thường gặp của máy tính.',
          'Phân tích, đánh giá, chẩn đoán sự cố; đưa ra giải pháp bảo trì, sửa chữa và thay thế phần cứng.',
          'Phân tích yêu cầu để thiết kế, triển khai, vận hành và bảo trì hệ thống máy tính.',
        ],
        'specializations' => [],
      ],
    ];
  }

  private static function semesters(string $program): array
  {
    $data = match ($program) {
      'cntt' => [
        1 => ['Pháp luật|2|30|0', 'Toán cao cấp|3|45|0', 'Toán rời rạc và Lý thuyết đồ thị|3|21|24', 'Phần cứng máy tính|3|30|15', 'Nhập môn lập trình|5|57|18', 'Tin học ứng dụng|3|16|29', 'Thực tập Phần cứng máy tính|1|0|45', 'Thực tập Nhập môn lập trình|2|0|90'],
        2 => ['Cơ sở dữ liệu|5|43|32', 'Cấu trúc dữ liệu và giải thuật|3|26|19', 'Mạng máy tính|3|36|9', 'Thiết kế Web|3|30|15', 'Thực tập Thiết kế Web|1|0|45', 'Thực tập Cấu trúc dữ liệu và giải thuật|1|0|45', 'Thực tập Mạng máy tính|1|0|45', 'Thực tập Cơ sở dữ liệu|1|0|45'],
        3 => ['Giáo dục chính trị 1|3|45|0', 'Tiếng Anh 3|5|75|0', 'Vật lý đại cương|4|45|15', 'Cơ sở dữ liệu NoSQL|2|14|16', 'Quản trị hệ thống mạng Windows|3|30|15', 'Phương pháp lập trình hướng đối tượng|3|30|15', 'Lập trình Website cơ bản|3|15|30', 'Thực tập Quản trị hệ thống mạng Windows|1|0|45', 'Thực tập Phương pháp lập trình hướng đối tượng|1|0|45'],
        4 => ['Giáo dục chính trị 2|2|30|0', 'Lập trình Windows và đồ án môn học|5|35|40', 'Thực tập Lập trình Windows|1|0|45', '[Web/Di động] Phân tích thiết kế hệ thống thông tin|4|30|30', '[Web/Di động/AI] Lập trình ứng dụng với Python|6|40|50', '[Web/Di động] Lập trình ứng dụng với Nodejs|3|30|15', '[Web/Di động] Công nghệ phần mềm|4|35|25', '[Mạng] Lập trình Python|3|21|24', '[Mạng] Hệ điều hành Linux|3|27|18', '[Mạng] Dịch vụ mạng|5|39|36', '[Mạng] Cấu hình và quản trị thiết bị mạng Cisco|6|60|30', '[AI] Cơ sở trí tuệ nhân tạo|4|40|20', '[AI] Trực quan hóa dữ liệu|3|20|25'],
        5 => ['Tiếng Anh chuyên ngành CNTT|3|45|0', '[Web] Kiểm thử phần mềm|4|35|25', '[Web] Lập trình Backend|3|30|15', '[Web] Công nghệ lập trình Web và triển khai hệ thống|6|45|45', '[Web] Lập trình Website nâng cao|6|50|40', '[Web] Lập trình Front End|5|45|30', '[Di động] Kiểm thử phần mềm|4|35|25', '[Di động] Lập trình Backend|3|30|15', '[Di động] Lập trình ứng dụng trên thiết bị di động|6|50|40', '[Di động] Lập trình Website nâng cao|6|45|45', '[Di động] Công nghệ lập trình đa nền tảng|5|45|30', '[Mạng] Thiết kế hệ thống mạng|5|37|38', '[Mạng] Bảo mật thiết bị mạng Cisco|3|25|20', '[Mạng] Điện toán đám mây|3|25|20', '[Mạng] Quản lý hệ thống Web và Mail Server|3|27|18', '[Mạng] An ninh mạng|5|39|36', '[Mạng] Quản trị mạng Linux|5|40|35', '[AI] Khai phá dữ liệu và ứng dụng|4|35|25', '[AI] Công nghệ phần mềm|4|35|25', '[AI] Lập trình Website nâng cao|6|45|45', '[AI] Học máy|5|45|30', '[AI] Xử lý ngôn ngữ tự nhiên và thị giác máy tính|5|50|25'],
        6 => ['[Web] Đồ án lập trình Web|1|0|30', '[Di động] Đồ án lập trình di động|1|0|30', '[Mạng] Đồ án Quản trị hệ thống mạng|1|0|30', '[AI] Đồ án trí tuệ nhân tạo ứng dụng|1|0|30', 'Thực tập tốt nghiệp|6|0|480', 'Đồ án tốt nghiệp|4|0|240'],
      ],
      'qtm' => [
        1 => ['Pháp luật|2|30|0', 'Lắp ráp, cài đặt máy tính|4|45|45', 'Nhập môn lập trình|4|41|19', 'Tin học ứng dụng|5|30|45', 'Mạng máy tính|4|41|19', 'Thực tập Mạng máy tính|1|0|45', 'Thực tập Nhập môn lập trình|1|0|45'],
        2 => ['Cơ sở dữ liệu|4|41|19', 'Kỹ thuật lập trình|5|30|45', 'Quản trị hệ thống mạng|4|40|50', 'Đồ họa ứng dụng|5|39|51', 'Thiết kế Web|4|30|60'],
        3 => ['Giáo dục chính trị 1|3|45|0', 'Tiếng Anh chuyên ngành CNTT|3|45|0', 'Cấu trúc dữ liệu và giải thuật|5|30|45', 'Hệ quản trị cơ sở dữ liệu|5|30|45', 'Các dịch vụ mạng|6|39|51', 'Hệ điều hành Linux|5|30|45'],
        4 => ['Giáo dục chính trị 2|2|30|0', 'Tiếng Anh 3|5|75|0', 'Thiết kế và quản lý hệ thống mạng|4|39|51', 'Lập trình Web PHP|6|30|60', 'Quản lý hệ thống Web và Mail Server|3|27|18', 'Cấu hình và quản trị thiết bị mạng Cisco|4|40|50'],
        5 => ['Bảo mật hệ thống mạng|3|15|30', 'Quản trị hệ thống mạng Linux|6|30|60', 'Chuyên đề CMS - mã nguồn mở|3|15|30', 'Lập trình Windows|5|50|40', 'An ninh mạng|5|30|45', 'Điện toán đám mây|3|25|20'],
        6 => ['Thực tập tốt nghiệp|7|0|560', 'Thi tốt nghiệp lý thuyết nghề|1|15|0', 'Thi tốt nghiệp thực hành nghề|1|0|35'],
      ],
      default => [
        1 => ['Pháp luật|2|30|0', 'Mạng máy tính|4|41|19', 'Tin học ứng dụng|5|30|45', 'Lắp ráp, cài đặt máy tính|4|45|45', 'Thực tập Mạng máy tính|1|0|45', 'Điện tử cơ bản|4|56|4', 'Thực tập Điện tử cơ bản|2|0|70'],
        2 => ['Nhập môn lập trình|4|41|19', 'Xử lý sự cố phần mềm máy tính|3|25|20', 'Quản trị hệ thống mạng|6|40|50', 'Sửa chữa phần cứng máy tính 1|4|42|48', 'Thực tập Nhập môn lập trình|1|0|45', 'Kỹ thuật xung số|4|56|4', 'Thực tập Hàn tay điện tử IPC|1|0|35'],
        3 => ['Tiếng Anh chuyên ngành CNTT|3|45|0', 'Lập trình nhúng|5|30|45', 'Kiến trúc máy tính|3|30|15', 'Sửa chữa phần cứng máy tính 2|4|42|48', 'Các dịch vụ mạng|5|30|45', 'Đồ án mạch điện tử|2|0|60', 'Thực tập Vẽ và mô phỏng mạch điện tử|2|0|70'],
        4 => ['Giáo dục chính trị 1|3|45|0', 'Tiếng Anh 3|5|75|0', 'Thiết kế và quản lý hệ thống mạng|5|30|45', 'Hệ điều hành Linux|5|30|45', 'Đồ họa ứng dụng|5|30|45', 'Sửa chữa màn hình, máy in|3|42|3', 'Thực tập Sửa chữa màn hình, máy in|2|0|70'],
        5 => ['Giáo dục chính trị 2|2|30|0', 'Cấu hình và quản trị thiết bị mạng Cisco|4|30|45', 'Chuyên đề thiết bị di động|3|25|20', 'Thiết kế mẫu|5|30|45', 'Thiết bị đầu cuối|3|42|3', 'Thực tập Thiết bị đầu cuối|2|0|70'],
        6 => ['Thực tập tốt nghiệp|7|0|560', 'Thi tốt nghiệp lý thuyết nghề|1|15|0', 'Thi tốt nghiệp thực hành nghề|1|0|35'],
      ],
    };

    return array_map(function (array $rows, int $semester): array {
      return [
        'key' => (string) $semester,
        'name' => 'Học kỳ ' . $semester,
        'courses' => array_map(function (string $row): array {
          [$name, $credits, $theory, $practice] = explode('|', $row);
          return ['code' => '', 'name' => $name, 'credits' => $credits, 'theory' => $theory, 'practice' => $practice];
        }, $rows),
      ];
    }, $data, array_keys($data));
  }
}
