<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AiProviderException;
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

    if (($payload['surface'] ?? '') !== 'cms') {
      return $this->json(null, 422, 'AI hiện chỉ hỗ trợ CMS.');
    }

    if (trim((string) ($payload['idea'] ?? '')) === '' || trim((string) ($payload['path'] ?? '')) === '') {
      return $this->json(null, 422, 'Vui lòng nhập ý tưởng hoặc yêu cầu.');
    }

    try {
      return $this->json($this->service->suggest($payload), 200, 'Đã tạo gợi ý AI.');
    } catch (AiProviderException $e) {
      error_log('[AI Suggestions][Provider] ' . $e->getMessage());
      return $this->json(null, 502, $e->getMessage());
    } catch (RuntimeException $e) {
      return $this->json(null, 422, $e->getMessage());
    } catch (Throwable $e) {
      error_log('[AI Suggestions] ' . $e->getMessage());
      return $this->json(null, 500, 'Không thể tạo gợi ý AI. Vui lòng thử lại sau.');
    }
  }
}
