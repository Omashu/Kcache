Kohana-3.3 KCache module
=====================
Модуль кеширования с поддержкой тегов на основе DklabCache

Инсталляция модуля:
-------------------

	// выгрузите модуль в папку своих модулей
	`cd kohana/modules && git clone https://github.com/Omashu/Kcache.git kcache`

	// подключите модуль в фале bootstrap.php
	`'kcache' => MODPATH.'kcache',`

Проверьте файл конфигурации модуля `config/kcache.php`

Пример работы с модулем:
------------------------
	
	// получение кеша
	$result = Kcache::instance()->get("cache_key", false);
	if ($result !== false) {
		return $result;
	}

	$result = array(1,2,3);

	// установка кеша
	Kcache::instance()->get("cache_key", $result, Date::DAY, array(
		'tag',
		'tag1'
	));

Сброс кеша по моду:
-------------------

	Kcache::instance()->clean($mode, array $tags)
	
	Available modes are:
		`Zend_Cache::CLEANING_MODE_ALL`	=> remove all cache entries ($tags is not used)
		`Zend_Cache::CLEANING_MODE_OLD` => remove too old cache entries ($tags is not used)
		`Zend_Cache::CLEANING_MODE_MATCHING_TAG` => remove cache entries matching all given tags
		`Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG` => remove cache entries not {matching one of the given tags}

Сброс кеша по ключу:
--------------------

	Kcache::instance()->remove($cache_key)