CREATE TABLE IF NOT EXISTS `VIDEO_DATA` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `title` VARCHAR(255),
    `channel_name` VARCHAR(255),
    `channel_id` VARCHAR(255),
    `length_text` VARCHAR(10),
    `published_time_text` VARCHAR(20),
    `video_id` VARCHAR(20),
    `view_count_text` VARCHAR(20),
    `thumbnail_height` INT,
    `thumbnail_url` VARCHAR(255),
    `thumbnail_width` INT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`video_id`)
);