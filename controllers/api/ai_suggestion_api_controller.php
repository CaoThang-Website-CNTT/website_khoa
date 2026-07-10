<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AiSuggestionService;
use RuntimeException;
use Throwable;

class AiSuggestionApiController extends Controller
{
  public function __construct(private AiSuggestionService $service)
  {
  }

  public function editorSuggestions(Request $request)
  {
    $payload = $request->json();
    if (!is_array($payload)) {
      return $this->json(null, 422, 'Payload AI không hợp lệ.');
    }

    if (!in_array($payload['surface'] ?? '', ['cms', 'post'], true)) {
      return $this->json(null, 422, 'Surface AI không hợp lệ.');
    }

    if (trim((string) ($payload['idea'] ?? '')) === '') {
      return $this->json(null, 422, 'Vui lòng nhập ý tưởng hoặc yêu cầu.');
    }

    try {
      return $this->json($this->service->suggest($payload), 200, 'Đã tạo gợi ý AI.');
    } catch (RuntimeException $e) {
      return $this->json(null, 422, $e->getMessage());
    } catch (Throwable $e) {
      error_log('[AI Suggestions] ' . $e->getMessage());
      return $this->json(null, 500, 'Không thể tạo gợi ý AI. Vui lòng thử lại sau.');
    }
  }
}
