CREATE TABLE IF NOT EXISTS `huge`.`bulkupload` (
 `bulkupload_id` int(11) unsigned NOT NULL,
 `user_id` int(11) unsigned NOT NULL,
 `bulkupload_name` text NOT NULL,
 `bulkupload_value` text NOT NULL,
 PRIMARY KEY (`bulkupload_id`),
 INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;