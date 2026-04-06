<?php
/**
 * Block template: heading / v1
 * 
 * Biến được inject bởi BlockRenderer::renderBlock() qua extract($data):
 * @var string $text   Nội dung tiêu đề
 * @var int    $level  Cấp độ heading: 2 | 3 | 4
 * 
 * ID được sinh từ text để dùng làm anchor cho Table of Contents.
 * Dùng mb_strtolower + regex để đảm bảo UTF-8 tiếng Việt không bị vỡ.
 */

$level = (int) ($level ?? 2);
$level = in_array($level, [2, 3, 4]) ? $level : 2; // fallback an toàn
$text = htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');

// Tạo anchor ID từ text: lowercase, bỏ ký tự đặc biệt, spaces → dấu gạch
$anchorId = mb_strtolower($text ?? '', 'UTF-8');
$anchorId = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $anchorId); // giữ chữ, số, space, gạch
$anchorId = preg_replace('/\s+/', '-', trim($anchorId));
$anchorId = 'heading-' . $anchorId;
?>
<h<?= $level ?> id="
  <?= $anchorId ?>" class="post-block post-block--heading post-block--h
  <?= $level ?>">
  <?= $text ?>
</h<?= $level ?>>