<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'default' => Kcache::CACHE_DRIVER_MEMCACHE,
	'prefix' => 'your_cache_prefix', // общий префикс имен ключей
	'settings' => array(
		Kcache::CACHE_DRIVER_FILE => array(
			'dir' => sys_get_temp_dir(),
			'prefix' => 'your_cache_prefix',
			'read_control_type' => 'crc32',
			'hashed_directory_level' => 1,
			'read_control' => true,
			'file_locking' => true,
		),
		Kcache::CACHE_DRIVER_MEMCACHE => array(
			'servers' => array(
				'host' => 'localhost',
				'port' => '11211',
				'persistent' => true
			),
			'compression' => true,
		)
	)
);