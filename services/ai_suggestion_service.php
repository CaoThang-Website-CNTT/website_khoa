<?php
namespace App\Services;

use RuntimeException;

interface AiProviderInterface
{
  public function generate(array $payload, array $schema): array;
}

class GeminiAiProvider implements AiProviderInterface
{
  public function __construct(
    private string $apiKey,
    private string $model,
    private float $temperature = 0.4,
  ) {
  }

  public function generate(array $payload, array $schema): array
  {
    if ($this->apiKey === '') {
      throw new RuntimeException('Chưa cấu hình GEMINI_API_KEY trong .env.local.');
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($this->model) . ':generateContent?key=' . rawurlencode($this->apiKey);
    $body = [
      'contents' => [
        [
          'role' => 'user',
          'parts' => [['text' => $payload['prompt']]],
        ]
      ],
      'generationConfig' => [
        'temperature' => $this->temperature,
        'responseMimeType' => 'application/json',
        'responseSchema' => $schema,
      ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      CURLOPT_TIMEOUT => 45,
    ]);

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($raw === false || $error) {
      throw new RuntimeException('Không thể kết nối Gemini API.');
    }

    $decoded = json_decode($raw, true);
    if ($status >= 400) {
      $message = $decoded['error']['message'] ?? 'Gemini API trả về lỗi.';
      throw new RuntimeException($message);
    }

    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $transaction = json_decode($text, true);
    if (!is_array($transaction)) {
      throw new RuntimeException('Gemini không trả về JSON hợp lệ.');
    }

    return $transaction;
  }
}

class AiTransactionValidator
{
  private const CMS_ACTIONS = ['cms.update_field', 'cms.add_repeater_item', 'cms.update_repeater_item', 'cms.remove_repeater_item'];
  private const POST_ACTIONS = ['post.update_meta', 'post.add_block', 'post.update_block', 'post.remove_block', 'post.reorder_blocks'];
  private const POST_BLOCKS = ['blocks/heading', 'blocks/paragraph', 'blocks/quote', 'blocks/image', 'blocks/list', 'blocks/table'];

  public function validate(array $transaction, array $requestPayload): array
  {
    $surface = $requestPayload['surface'] ?? '';
    if (!in_array($surface, ['cms', 'post'], true)) {
      throw new RuntimeException('Surface AI không hợp lệ.');
    }

    if (($transaction['surface'] ?? $surface) !== $surface || !isset($transaction['actions']) || !is_array($transaction['actions'])) {
      throw new RuntimeException('Cấu trúc phản hồi AI không hợp lệ.');
    }

    $transaction['version'] = 1;
    $transaction['surface'] = $surface;
    $transaction['summary'] = (string) ($transaction['summary'] ?? '');
    $transaction['warnings'] = array_values(array_filter($transaction['warnings'] ?? [], 'is_string'));
    $transaction['actions'] = array_values(array_map(
      fn($action, $index) => $this->validateAction((array) $action, $index, $requestPayload),
      $transaction['actions'],
      array_keys($transaction['actions'])
    ));

    return $transaction;
  }

  private function validateAction(array $action, int $index, array $requestPayload): array
  {
    $surface = $requestPayload['surface'];
    $allowed = $surface === 'cms' ? self::CMS_ACTIONS : self::POST_ACTIONS;
    $type = (string) ($action['type'] ?? '');
    if (!in_array($type, $allowed, true)) {
      throw new RuntimeException("Action AI không được hỗ trợ: {$type}");
    }

    $action['id'] = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($action['id'] ?? 'act_' . ($index + 1))) ?: 'act_' . ($index + 1);
    $action['label'] = trim((string) ($action['label'] ?? $type));
    $action['target'] = is_array($action['target'] ?? null) ? $action['target'] : [];
    $action['data'] = is_array($action['data'] ?? null) ? $action['data'] : [];

    return $surface === 'cms'
      ? $this->validateCmsAction($action, $requestPayload)
      : $this->validatePostAction($action);
  }

  private function validateCmsAction(array $action, array $requestPayload): array
  {
    $schema = $requestPayload['context']['schema'] ?? [];
    $sectionId = (string) ($action['target']['section_id'] ?? '');
    $path = (string) ($action['target']['path'] ?? '');
    $section = null;
    foreach (($schema['sections'] ?? []) as $candidate) {
      if (($candidate['id'] ?? '') === $sectionId) {
        $section = $candidate;
        break;
      }
    }
    if (!$section) {
      throw new RuntimeException("Section CMS không hợp lệ: {$sectionId}");
    }
    $editable = $section['editable_fields'] ?? [];
    $repeaters = array_keys($section['repeaters'] ?? []);
    $isEditable = in_array($path, $editable, true) || in_array($path, $repeaters, true);
    if (!$isEditable && !$this->pathMatchesPattern($path, $editable)) {
      throw new RuntimeException("Đường dẫn CMS không được phép chỉnh: {$path}");
    }
    return $action;
  }

  private function validatePostAction(array $action): array
  {
    $blockType = $action['data']['block']['type'] ?? $action['data']['type'] ?? null;
    if (in_array($action['type'], ['post.add_block', 'post.update_block'], true) && $blockType && !in_array($blockType, self::POST_BLOCKS, true)) {
      throw new RuntimeException("Block type không hợp lệ: {$blockType}");
    }
    if (isset($action['data']['rich_text'])) {
      $action['data']['rich_text'] = $this->sanitizeRichText($action['data']['rich_text']);
    }
    if (isset($action['data']['block']['data']['rich_text'])) {
      $action['data']['block']['data']['rich_text'] = $this->sanitizeRichText($action['data']['block']['data']['rich_text']);
    }
    return $action;
  }

  private function sanitizeRichText(mixed $segments): array
  {
    if (!is_array($segments))
      return [];
    return array_values(array_filter(array_map(function ($segment) {
      if (!is_array($segment) || !isset($segment['text']) || !is_string($segment['text']))
        return null;
      $marks = array_values(array_intersect($segment['marks'] ?? [], ['bold', 'italic', 'underline', 'link']));
      $safe = ['type' => in_array('link', $marks, true) ? 'link' : 'text', 'text' => $segment['text'], 'marks' => $marks];
      if (($safe['type'] ?? '') === 'link' && isset($segment['href']) && preg_match('/^(https?:\/\/|mailto:|\/)[^\s]*$/i', $segment['href'])) {
        $safe['href'] = $segment['href'];
      }
      return $safe;
    }, $segments)));
  }

  private function pathMatchesPattern(string $path, array $patterns): bool
  {
    foreach ($patterns as $pattern) {
      $regex = '/^' . str_replace('\*', '\d+', preg_quote((string) $pattern, '/')) . '$/';
      if (preg_match($regex, $path))
        return true;
    }
    return false;
  }
}

class AiSuggestionService
{
  public function __construct(private WebSettingsService $settingsService)
  {
  }

  public function suggest(array $requestPayload): array
  {
    $provider = strtolower((string) $this->setting('ai.provider', $_ENV['AI_PROVIDER'] ?? 'gemini'));
    if ($provider !== 'gemini') {
      throw new RuntimeException('Hiện tại chỉ hỗ trợ provider Gemini.');
    }

    $ai = new GeminiAiProvider(
      (string) ($_ENV['GEMINI_API_KEY'] ?? ''),
      (string) $this->setting('ai.model', $_ENV['AI_MODEL'] ?? 'gemini-1.5-flash'),
      (float) $this->setting('ai.temperature', '0.4'),
    );

    $transaction = $ai->generate([
      'prompt' => $this->buildPrompt($requestPayload),
    ], $this->responseSchema());

    return (new AiTransactionValidator())->validate($transaction, $requestPayload);
  }

  private function buildPrompt(array $payload): string
  {
    $fixed = 'Bạn là trợ lý biên tập cho website Khoa CNTT. Luôn trả về JSON đúng schema. Không trả markdown. Chỉ đề xuất hành động an toàn, phù hợp tiếng Việt, không tự bịa liên kết/số liệu quan trọng. Không xóa nội dung nếu người dùng không yêu cầu rõ.';
    $system = (string) $this->setting('ai.system_instruction', 'Viết nội dung tiếng Việt rõ ràng, trang trọng, phù hợp môi trường giáo dục.');
    $followup = (string) $this->setting('ai.followup_prompt', 'Ưu tiên nội dung ngắn gọn, dễ đọc, có cấu trúc.');
    return implode("\n\n", [
      "SYSTEM:\n{$fixed}\n{$system}",
      "FOLLOW_UP_PROMPT:\n{$followup}",
      'USER_IDEA: ' . (string) ($payload['idea'] ?? ''),
      'MODE: ' . (string) ($payload['mode'] ?? 'improve'),
      'SURFACE: ' . (string) ($payload['surface'] ?? ''),
      'CURRENT_CONTEXT_JSON: ' . json_encode($payload['context'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'Return a transaction using only the allowed action types for the selected surface.',
    ]);
  }

  private function setting(string $key, mixed $fallback): mixed
  {
    return $this->settingsService->getValue($key, $fallback) ?? $fallback;
  }

  private function responseSchema(): array
  {
    return [
      'type' => 'object',
      'properties' => [
        'version' => ['type' => 'integer'],
        'surface' => ['type' => 'string', 'enum' => ['cms', 'post']],
        'summary' => ['type' => 'string'],
        'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
        'actions' => [
          'type' => 'array',
          'items' => [
            'type' => 'object',
            'properties' => [
              'id' => ['type' => 'string'],
              'type' => ['type' => 'string'],
              'label' => ['type' => 'string'],
              'target' => [
                'type' => 'object',
                'properties' => [
                  'section_id' => ['type' => 'string'],
                  'path' => ['type' => 'string'],
                  'index' => ['type' => 'integer'],
                  'block_id' => ['type' => 'string'],
                  'after_id' => ['type' => 'string'],
                ],
              ],
              'data' => [
                'type' => 'object',
                'properties' => [
                  'value' => ['type' => 'string'],
                  'item' => ['type' => 'object'],
                  'values' => ['type' => 'object'],
                  'type' => ['type' => 'string'],
                  'block' => [
                    'type' => 'object',
                    'properties' => [
                      'type' => ['type' => 'string'],
                      'data' => ['type' => 'object'],
                    ],
                  ],
                  'ordered_ids' => ['type' => 'array', 'items' => ['type' => 'string']],
                ],
              ],
            ],
            'required' => ['id', 'type', 'label', 'target', 'data'],
          ],
        ],
      ],
      'required' => ['version', 'surface', 'summary', 'actions', 'warnings'],
    ];
  }
}
