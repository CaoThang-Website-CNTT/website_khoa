-- ============================================================================
-- CAROUSEL SCHEMA
-- ============================================================================
DROP TABLE IF EXISTS `carousel_slides`;
DROP TABLE IF EXISTS `carousels`;
CREATE TABLE carousels (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 name VARCHAR(100) NOT NULL COMMENT 'Nhãn cho UI, e.g. "Landing Page"',
 slug VARCHAR(100) NOT NULL UNIQUE COMMENT 'Cho code, e.g. "landing_page"',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id),
 deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE carousel_slides (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 carousel_id INT UNSIGNED NOT NULL,
 title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề chính',
 title_highlight VARCHAR(255) DEFAULT NULL COMMENT 'Phần <span> được highlight ngay dưới tiêu đề',
 description TEXT DEFAULT NULL,
 image_path VARCHAR(500) NOT NULL COMMENT 'đường dẫn tương đối của hình ảnh',
 image_alt VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Alt text của hình ảnh',
 cta_label VARCHAR(100) DEFAULT NULL COMMENT 'Button label, NULL = không render button',
 cta_url VARCHAR(500) DEFAULT NULL,
 cta_variant VARCHAR(20) NOT NULL DEFAULT 'primary' COMMENT 'primary | secondary',
 custom_html MEDIUMTEXT DEFAULT NULL COMMENT 'Custom html',
 use_custom_html TINYINT(1) NOT NULL DEFAULT 0,
 sort_order SMALLINT NOT NULL DEFAULT 0 COMMENT 'ASC order, thấp nhất = render trước',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 PRIMARY KEY (id),
 CONSTRAINT fk_slide_carousel FOREIGN KEY (carousel_id) REFERENCES carousels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

INSERT INTO carousels (name, slug)
VALUES ('Landing Page', 'landing_page');

INSERT INTO carousel_slides
 (carousel_id, title, title_highlight, description, image_path, image_alt, cta_label, cta_url, cta_variant, sort_order, is_active)
VALUES
(
 1,
 'Môi trường học tập',
 'Chuyên nghiệp & Sáng tạo',
 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.',
 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655',
 'Lecture hall with students',
 'Tìm hiểu thêm',
 '#',
 'secondary',
 1,
 1
),
(
 1,
 'Công nghệ tiên tiến',
 'Hỗ trợ học tập 24/7',
 'Hệ thống học trực tuyến hiện đại, tài liệu số hóa đầy đủ, và phòng lab công nghệ cao giúp bạn học mọi lúc, mọi nơi.',
 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4',
 'Modern computer lab',
 'Khám phá ngay',
 '#',
 'primary',
 2,
 1
);