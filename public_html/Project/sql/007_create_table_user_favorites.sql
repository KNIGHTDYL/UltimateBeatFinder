CREATE TABLE IF NOT EXISTS `USER_FAVORITES` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `user_id` INT,
    `video_id` VARCHAR(20),
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`),
    FOREIGN KEY (`video_id`) REFERENCES `VIDEO_DATA`(`video_id`),
    UNIQUE KEY (`user_id`, `video_id`)
);

-- SELECT USER_FAVORITES.user_id, Users.email, Users.username, USER_FAVORITES.video_id, VIDEO_DATA.title
-- FROM USER_FAVORITES
-- LEFT JOIN Users ON USER_FAVORITES.user_id = Users.id
-- LEFT JOIN VIDEO_DATA ON USER_FAVORITES.video_id = VIDEO_DATA.video_id;


SELECT 
    uf.id, uf.user_id, uf.video_id, 
    COALESCE(vd.title, 'N/A') AS title, 
    COALESCE(vd.channel_name, 'N/A') AS channel_name, 
    COALESCE(vd.view_count_text, 'N/A') AS view_count_text, 
    COALESCE(vd.length_text, 'N/A') AS length_text, 
    COALESCE(vd.published_time_text, 'N/A') AS published_time_text, 
    COALESCE(u.email, 'N/A') AS email,
    COALESCE(vd.thumbnail_url, '') AS thumbnail_url,
    u.email AS favorited_by
FROM USER_FAVORITES uf
LEFT JOIN VIDEO_DATA vd ON uf.video_id = vd.video_id
LEFT JOIN Users u ON uf.user_id = u.id
