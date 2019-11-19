CREATE TABLE IF NOT EXISTS `hots` (
    `id` INT NOT NULL AUTO_INCREMENT COMMENT "自增id",
    `rank` INT NOT NULL DEFAULT 0 COMMENT "变化次数",
    `version` int(11) NOT NULL DEFAULT 0 COMMENT '数据版本号',
    `create_time` DATETIME NULL DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME NULL DEFAULT NULL COMMENT '更新时间',
    `tieba_name` CHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "" COMMENT "贴吧名称",
    `hot` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "" COMMENT "热帖",
    PRIMARY KEY(`id`),
    UNIQUE KEY `tieba_hot` (`tieba_name`,`hot`)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT COMMENT "用户自增id",
    `rank` INT NOT NULL DEFAULT 0 COMMENT "变化次数",
    `user_name` CHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "" COMMENT "用户名",
    `version` int(11) NOT NULL DEFAULT 0 COMMENT '数据版本号',
    `create_time` DATETIME NULL DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME NULL DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY(`id`),
    UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT NOT NULL AUTO_INCREMENT COMMENT "自增id",
    `version` int(11) NOT NULL DEFAULT 0 COMMENT '数据版本号',
    `create_time` DATETIME NULL DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME NULL DEFAULT NULL COMMENT '更新时间',
    `tieba_name` CHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "" COMMENT "贴吧名称",
    `post` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "" COMMENT "帖",
    PRIMARY KEY(`id`),
    UNIQUE KEY `tie` (`tieba_name`,`post`)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;