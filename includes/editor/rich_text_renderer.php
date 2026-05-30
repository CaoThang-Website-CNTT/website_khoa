<?php
namespace App\Editor;

/**
 * Render RichSegment[] (từ block data.rich_text) thành inline HTML.
 *
 * Schema một segment (v2):
 *   { type: 'text'|'link', text: string, marks: string[], href?: string }
 *
 * Marks được phép: bold, italic, underline, link
 * (mirror BlockSerializer::ALLOWED_MARKS bên JS)
 */
final class RichTextRenderer
{
    private const ALLOWED_MARKS = ['bold', 'italic', 'underline', 'link'];

    /**
     * Render mảng segments thành chuỗi HTML inline.
     * Input là array đã decode từ JSON — không throw exception,
     * trả về empty string nếu input rỗng/không hợp lệ.
     *
     * @param  array<int, array<string, mixed>>  $segments
     */
    public static function render(array $segments): string
    {
        if (empty($segments)) {
            return '';
        }

        $html = '';

        foreach ($segments as $seg) {
            if (!isset($seg['text']) || !is_string($seg['text']) || $seg['text'] === '') {
                continue;
            }

            $text  = htmlspecialchars($seg['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $marks = self::sanitizeMarks($seg['marks'] ?? []);
            $type  = $seg['type'] ?? 'text';
            $href  = isset($seg['href']) ? self::sanitizeHref($seg['href']) : '';

            $html .= self::wrapWithMarks($text, $marks, $type, $href);
        }

        return $html;
    }

    /**
     * Wrap text với các inline tag tương ứng với marks.
     * Thứ tự wrap: link (ngoài cùng) → bold → italic → underline
     */
    private static function wrapWithMarks(
        string $text,
        array  $marks,
        string $type,
        string $href
    ): string {
        $html = $text;

        if (in_array('underline', $marks, true)) {
            $html = "<u>{$html}</u>";
        }

        if (in_array('italic', $marks, true)) {
            $html = "<em>{$html}</em>";
        }

        if (in_array('bold', $marks, true)) {
            $html = "<strong>{$html}</strong>";
        }

        // Link: type === 'link' hoặc mark 'link' đều render thành <a>
        if ($type === 'link' || in_array('link', $marks, true)) {
            if ($href !== '') {
                $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $html = "<a href=\"{$safeHref}\" target=\"_blank\" rel=\"noopener noreferrer\">{$html}</a>";
            }
        }

        return $html;
    }

    /**
     * Lọc marks — chỉ giữ marks trong whitelist.
     *
     * @param  mixed  $marks
     * @return string[]
     */
    private static function sanitizeMarks(mixed $marks): array
    {
        if (!is_array($marks)) {
            return [];
        }

        return array_values(
            array_filter($marks, fn($m) => is_string($m) && in_array($m, self::ALLOWED_MARKS, true))
        );
    }

    /**
     * Sanitize href — reject javascript: và các scheme nguy hiểm.
     * Mirror logic của BlockSerializer.#sanitizeHref bên JS.
     */
    private static function sanitizeHref(mixed $href): string
    {
        if (!is_string($href)) {
            return '';
        }

        $href = trim($href);

        // Chỉ chấp nhận http/https/mailto/relative path
        if (!preg_match('#^(https?://|mailto:|/)[^\s]*$#i', $href)) {
            return '';
        }

        return $href;
    }
}
