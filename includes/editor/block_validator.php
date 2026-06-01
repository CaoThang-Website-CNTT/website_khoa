<?php
namespace App\Editor;

class BlockValidationException extends \RuntimeException
{
}

/**
 * Validate block data trước khi đưa vào renderer.
 *
 * Schema tổng quát của một block:
 *   {
 *     id:      string,
 *     type:    string  (ví dụ "blocks/heading"),
 *     version: int,
 *     data: {
 *       rich_text: RichSegment[],
 *       meta:      object (tùy block type)
 *     }
 *   }
 */
final class BlockValidator
{
  /** Map type → required meta keys (những key bắt buộc phải tồn tại) */
  private const META_REQUIRED = [
    'blocks/heading' => [],               // level, align có default
    'blocks/paragraph' => [],
    'blocks/quote' => [],
    'blocks/list' => ['items'],
    'blocks/image' => [],               // url có thể rỗng (placeholder)
    'blocks/table' => ['rows'],
  ];

  /**
   * Validate và normalize toàn bộ payload content_json.
   *
   * @param  mixed  $payload  - output của json_decode(..., true)
   * @return array{ meta: array, blocks: array }
   *
   * @throws BlockValidationException
   */
  public static function validatePayload(mixed $payload): array
  {
    if (!is_array($payload)) {
      throw new BlockValidationException('Payload phải là một array.');
    }

    $blocks = $payload ?? [];

    if (!is_array($blocks)) {
      throw new BlockValidationException('Trường "blocks" phải là array.');
    }

    $normalized = [];

    foreach ($blocks as $index => $block) {
      try {
        $normalized[] = self::validateBlock($block);
      } catch (BlockValidationException $e) {
        throw new BlockValidationException(
          "Block[{$index}]: " . $e->getMessage(),
          previous: $e
        );
      }
    }

    return [
      'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
      'blocks' => $normalized,
    ];
  }

  /**
   * Validate và normalize một block đơn lẻ.
   *
   * @param  mixed  $block
   * @return array  Normalized block
   *
   * @throws BlockValidationException
   */
  public static function validateBlock(mixed $block): array
  {
    if (!is_array($block)) {
      throw new BlockValidationException('Block phải là array.');
    }

    $type = $block['type'] ?? null;

    if (!is_string($type) || !array_key_exists($type, self::META_REQUIRED)) {
      throw new BlockValidationException(
        "Block type không hợp lệ hoặc chưa được hỗ trợ: \"{$type}\"."
      );
    }

    $data = $block['data'] ?? [];

    if (!is_array($data)) {
      throw new BlockValidationException("Trường \"data\" phải là array.");
    }

    // Validate rich_text nếu có
    $richText = $data['rich_text'] ?? [];
    if (!is_array($richText)) {
      throw new BlockValidationException("Trường \"data.rich_text\" phải là array.");
    }

    // Validate các meta keys bắt buộc theo type
    $meta = $data['meta'] ?? [];
    if (!is_array($meta)) {
      throw new BlockValidationException("Trường \"data.meta\" phải là array.");
    }

    foreach (self::META_REQUIRED[$type] as $requiredKey) {
      if (!array_key_exists($requiredKey, $meta)) {
        throw new BlockValidationException(
          "Block type \"{$type}\" thiếu meta key bắt buộc: \"{$requiredKey}\"."
        );
      }
    }

    return [
      'id' => is_string($block['id'] ?? null) ? $block['id'] : '',
      'type' => $type,
      'version' => is_int($block['version'] ?? null) ? $block['version'] : 1,
      'data' => [
        'rich_text' => $richText,
        'meta' => $meta,
      ],
    ];
  }
}
