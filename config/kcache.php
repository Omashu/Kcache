<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// используемый драйвер по умолчанию
	'default' => Kcache::CACHE_DRIVER_MEMCACHE,
	// общий префикс имен ключей
	'prefix' => 'your_cache_prefix',

	// настройки драйверов
	'settings' => array(
		Kcache::CACHE_DRIVER_FILE => array(
			// директория хранения кеша
			'dir' => sys_get_temp_dir(),
			// префикс имен файлов
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