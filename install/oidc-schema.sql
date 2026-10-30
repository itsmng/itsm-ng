CREATE TABLE IF NOT EXISTS `glpi_oidcproviders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `issuer` varchar(1024) COLLATE utf8_bin NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `scope` text,
  `proxy` varchar(255) DEFAULT NULL,
  `cert` varchar(255) DEFAULT NULL,
  `logout_redirect` varchar(1024) DEFAULT NULL,
  `link_method` varchar(32) NOT NULL DEFAULT 'none',
  `login_claim` varchar(255) NOT NULL DEFAULT '/preferred_username',
  `login_claim_source` varchar(16) NOT NULL DEFAULT 'userinfo',
  `email_claim` varchar(255) NOT NULL DEFAULT '/email',
  `email_claim_source` varchar(16) NOT NULL DEFAULT 'userinfo',
  `allow_provisioning` tinyint(1) NOT NULL DEFAULT 0,
  `use_pkce` tinyint(1) NOT NULL DEFAULT 1,
  `required_plugin` varchar(100) NOT NULL DEFAULT '',
  `is_legacy` tinyint(1) NOT NULL DEFAULT 0,
  `legacy_link_users` tinyint(1) NOT NULL DEFAULT 0,
  `legacy_login_fallback` varchar(16) NOT NULL DEFAULT 'sub',
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_oidcmappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oidcproviders_id` int(11) NOT NULL,
  `claim_source` varchar(16) NOT NULL DEFAULT 'userinfo',
  `claim_path` varchar(255) NOT NULL,
  `target` varchar(100) NOT NULL,
  `sync_mode` varchar(16) NOT NULL DEFAULT 'always',
  `missing_policy` varchar(16) NOT NULL DEFAULT 'preserve',
  `transform` varchar(16) NOT NULL DEFAULT 'none',
  `lookup_mode` varchar(16) NOT NULL DEFAULT 'id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_target` (`oidcproviders_id`,`target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_oidcidentities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oidcproviders_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `issuer` varchar(1024) COLLATE utf8_bin DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `identity_key` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `needs_sync` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_identity` (`oidcproviders_id`,`identity_key`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_oidcgroupusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oidcproviders_id` int(11) NOT NULL,
  `groups_users_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_membership` (`oidcproviders_id`,`groups_users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
