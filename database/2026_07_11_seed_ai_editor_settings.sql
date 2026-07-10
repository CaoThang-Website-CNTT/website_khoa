-- AI editor suggestion settings.
-- Import this after migrations when enabling CMS/Post AI suggestions.
-- API keys intentionally stay in .env.local, not in web_settings.

INSERT INTO `web_settings`
  (`key`, `group`, `group_label`, `type`, `value`, `label`, `description`, `autoload`, `is_locked`, `sort_order`, `created_at`, `updated_at`)
VALUES
  (
    'ai.system_instruction',
    'ai',
    'AI Settings',
    'text',
    'Viết nội dung tiếng Việt rõ ràng, trang trọng, phù hợp môi trường giáo dục.',
    'AI System Instruction',
    'Chỉ dẫn nền cho AI trước khi nhận yêu cầu cụ thể.',
    1,
    0,
    10,
    NOW(),
    NOW()
  ),
  (
    'ai.followup_prompt',
    'ai',
    'AI Settings',
    'text',
    'Ưu tiên nội dung ngắn gọn, dễ đọc, có cấu trúc.',
    'AI Follow-up Prompt',
    'Prompt bổ sung có thể tùy chỉnh trong Settings.',
    1,
    0,
    20,
    NOW(),
    NOW()
  ),
  (
    'ai.provider',
    'ai',
    'AI Settings',
    'string',
    'gemini',
    'AI Provider',
    'Provider AI đang sử dụng. v1 hỗ trợ gemini.',
    1,
    0,
    30,
    NOW(),
    NOW()
  ),
  (
    'ai.model',
    'ai',
    'AI Settings',
    'string',
    'gemini-1.5-flash',
    'AI Model',
    'Model Gemini dùng để tạo gợi ý.',
    1,
    0,
    40,
    NOW(),
    NOW()
  ),
  (
    'ai.temperature',
    'ai',
    'AI Settings',
    'float',
    '0.4',
    'AI Temperature',
    'Độ sáng tạo của phản hồi AI.',
    1,
    0,
    50,
    NOW(),
    NOW()
  )
ON DUPLICATE KEY UPDATE
  `group` = VALUES(`group`),
  `group_label` = VALUES(`group_label`),
  `type` = VALUES(`type`),
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `autoload` = VALUES(`autoload`),
  `is_locked` = VALUES(`is_locked`),
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = `web_settings`.`updated_at`;
