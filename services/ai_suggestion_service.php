<?php
namespace App\Services;

use RuntimeException;

class AiProviderException extends RuntimeException {}

class GeminiAiProvider
{
  public function __construct(private string $apiKey, private string $model, private float $temperature = 0.4) {}

  public function generate(string $prompt, string $systemInstruction): string
  {
    if ($this->apiKey === '') {
      throw new RuntimeException('Chưa cấu hình GEMINI_API_KEY trong .env.local.');
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/interactions';
    $body = [
      'model' => $this->model,
      'system_instruction' => $systemInstruction,
      'input' => $prompt,
      'generation_config' => ['temperature' => $this->temperature],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . $this->apiKey],
      CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      CURLOPT_TIMEOUT => 45,
    ]);
    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($raw === false || $error) throw new AiProviderException('Không thể kết nối Gemini API.');
    $decoded = json_decode($raw, true);
    if ($status >= 400) throw new AiProviderException($decoded['error']['message'] ?? 'Gemini API trả về lỗi.');

    $text = $decoded['output_text'] ?? '';
    if ($text === '' && isset($decoded['output']) && is_array($decoded['output'])) {
      foreach ($decoded['output'] as $item) {
        if (($item['type'] ?? '') === 'text' && is_string($item['text'] ?? null)) $text .= $item['text'];
        if (($item['type'] ?? '') === 'text' && is_string($item['content'] ?? null)) $text .= $item['content'];
        if (is_array($item['content'] ?? null)) {
          foreach ($item['content'] as $content) {
            if (is_string($content['text'] ?? null)) $text .= $content['text'];
          }
        }
      }
    }
    if ($text === '' && isset($decoded['steps']) && is_array($decoded['steps'])) {
      foreach ($decoded['steps'] as $step) {
        if (($step['type'] ?? '') === 'text' && is_string($step['text'] ?? null)) $text .= $step['text'];
        if (is_string($step['content'] ?? null)) $text .= $step['content'];
        if (is_array($step['content'] ?? null)) {
          foreach ($step['content'] as $content) {
            if (is_string($content['text'] ?? null)) $text .= $content['text'];
          }
        }
      }
    }
    if (!is_string($text) || trim($text) === '') throw new AiProviderException('Gemini không trả về nội dung văn bản.');
    return trim($text);
  }
}

class AiSuggestionService
{
  public function suggest(array $payload): array
  {
    if (($payload['surface'] ?? '') !== 'cms') throw new RuntimeException('AI hiện chỉ hỗ trợ CMS.');
    if (trim((string) ($payload['section_id'] ?? '')) === '' || trim((string) ($payload['path'] ?? '')) === '') {
      throw new RuntimeException('Thiếu section hoặc trường CMS cần gợi ý.');
    }

    $system = trim((string) ($_ENV['AI_SYSTEM_INSTRUCTION'] ?? ''));
    if ($system === '') $system = 'Viết nội dung tiếng Việt rõ ràng, trang trọng, phù hợp môi trường giáo dục.';
    $fixed = 'Bạn là trợ lý biên tập CMS. Chỉ trả về nội dung văn bản gợi ý cho đúng một trường được yêu cầu. Không trả JSON, markdown, danh sách hành động, tool instruction, hay lời giải thích ngoài nội dung có thể đặt vào trường.';
    $prompt = implode("\n\n", [
      'Ý tưởng/yêu cầu của biên tập viên: ' . trim((string) ($payload['idea'] ?? '')),
      'Kiểu yêu cầu: ' . trim((string) ($payload['mode'] ?? 'improve')),
      'Trường: ' . trim((string) ($payload['field_label'] ?? $payload['path'])),
      'Nội dung hiện tại: ' . (string) ($payload['current_value'] ?? ''),
      'Ngữ cảnh CMS (chỉ dùng để hiểu trang, không tạo thao tác): ' . json_encode($payload['context'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $provider = strtolower((string) ($_ENV['AI_PROVIDER'] ?? 'gemini'));
    if ($provider !== 'gemini') throw new RuntimeException('Hiện tại chỉ hỗ trợ provider Gemini.');
    $text = (new GeminiAiProvider(
      (string) ($_ENV['GEMINI_API_KEY'] ?? ''),
      (string) ($_ENV['AI_MODEL'] ?? 'gemini-3.5-flash'),
      (float) ($_ENV['AI_TEMPERATURE'] ?? '0.4'),
    ))->generate($prompt, $fixed . "\n\n" . $system);

    return ['surface' => 'cms', 'section_id' => (string) $payload['section_id'], 'path' => (string) $payload['path'], 'suggestion' => $text];
  }
}
