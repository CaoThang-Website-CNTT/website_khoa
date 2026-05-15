<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CarouselService;

class CarouselApiController extends Controller
{
    protected CarouselService $_carouselService;

    public function __construct(CarouselService $carouselService)
    {
        $this->_carouselService = $carouselService;
    }

    public function sortSlides(Request $request, int $carousel_id)
    {
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return $this->json(data: null, message: 'Dữ liệu không hợp lệ!', status: 400);
        }

        $success = $this->_carouselService->sortSlides($ids);
        if ($success) {
            return $this->json(data: null, message: 'Thứ tự slide đã được cập nhật thành công!', status: 200);
        }
        return $this->json(data: null, message: 'Lỗi lưu trữ.', status: 500);
    }
}
?>
