SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

INSERT INTO `groups` (`id`, `caption`) VALUES
(1, 'Admins'),
(2, 'Authors'),
(3, 'Authenticated'),
(4, 'Guests');

CREATE TABLE IF NOT EXISTS `messages` (
  `type` varchar(10) COLLATE utf8_bin NOT NULL,
  `message` varchar(255) COLLATE utf8_bin NOT NULL,
  `show_times` int(10) unsigned NOT NULL,
  PRIMARY KEY (`type`,`message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `permissions` (
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `module_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `action_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`,`module_name`,`action_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO `permissions` (`group_id`, `module_name`, `action_name`) VALUES
(1, 'Blog', 'addPost'),
(1, 'Blog', 'deletePost'),
(1, 'Blog', 'editPost'),
(1, 'Blog', 'editPreview'),
(1, 'Blog', 'editText'),
(1, 'Blog', 'goToFeed'),
(1, 'Blog', 'showPost'),
(1, 'Blog', 'showPostAddForm'),
(1, 'Blog', 'showPostEditForm'),
(1, 'Blog', 'showPosts'),
(1, 'Blog', 'showPostsByAuthor'),
(1, 'Blog', 'showPostsByTag'),
(1, 'Site', 'changePermissions'),
(1, 'Site', 'editProfile'),
(1, 'Site', 'profileForm'),
(1, 'Site', 'showPermissionsList'),
(1, 'Site', 'showUsers'),
(1, 'Site', 'userInfo');

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `date` int(11) unsigned NOT NULL,
  `edit_date` int(11) unsigned NOT NULL,
  `header` varchar(255) COLLATE utf8_bin NOT NULL,
  `preview` text COLLATE utf8_bin NOT NULL,
  `text` text COLLATE utf8_bin NOT NULL,
  `tags` varchar(255) COLLATE utf8_bin NOT NULL,
  `allow_comments` enum('yes','no') COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=51 ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `password` varchar(1000) COLLATE utf8_bin NOT NULL,
  `openid` varchar(512) COLLATE utf8_bin NOT NULL,
  `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `avatar` varchar(255) COLLATE utf8_bin NOT NULL,
  `registration_date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=73 ;

INSERT INTO `users` (`id`, `login`, `password`, `openid`, `email`, `name`, `avatar`, `registration_date`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '', '', '', '/modules/site/view/yakoder/images/user.png', 0);

CREATE TABLE IF NOT EXISTS `users_groups` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `users_groups` (`user_id`, `group_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);

ALTER TABLE `users_groups`
  ADD CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;
