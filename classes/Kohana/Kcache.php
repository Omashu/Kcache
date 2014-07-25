<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Kcache {

	/**
	 * Drivers
	 */
	const CACHE_DRIVER_FILE 			= "file";
	const CACHE_DRIVER_MEMCACHE 	= "memcache";

	/**
	 * @var string имя драйвера
	 */
	protected $driver;

	/**
	 * @var object DklabBackend
	 */
	protected $cache;

	/**
	 * @var objects Kcache
	 */
	protected static $instances = array();

	/**
	 * Получить сущность кеширования
	 * @param string $group группа кеширования, драйвер
	 * @return object
	 */
	public static function instance($group = NULL) {

		$driver = $group;
		if (!in_array($group, array(Kcache::CACHE_DRIVER_FILE, Kcache::CACHE_DRIVER_MEMCACHE), true)) {
			// инициируем дефолтный кеш
			$driver = Kohana::$config->load("kcache.default");
		}

		// возврат существующего синглтона драйвера
		if (isset(Kcache::$instances[$driver])) {
			return Kcache::$instances[$driver];
		}

		if (!defined("DKLAB_CACHE_DIR")) {
			// подключаем DKlab
			define("DKLAB_CACHE_DIR", MODPATH . "kcache/vendor/DklabCache/");
			require_once Kohana::find_file("vendor", "DklabCache/Zend/Cache");
			require_once Kohana::find_file("vendor", "DklabCache/Cache/Backend/MemcachedMultiload");
			require_once Kohana::find_file("vendor", "DklabCache/Cache/Backend/TagEmuWrapper");
			require_once Kohana::find_file("vendor", "DklabCache/Cache/Backend/Profiler");
		}

		if ($driver === Kcache::CACHE_DRIVER_FILE) {
			require_once Kohana::find_file("vendor", "DklabCache/Zend/Cache/Backend/File");
			$configuration = Kohana::$config->load("kcache.settings." . Kcache::CACHE_DRIVER_FILE);
			if (is_null($configuration)) {
				throw new Kohana_Exception("Конфигурация драйвера кеширования не обнаружена");
			}

			$backend = new Zend_Cache_Backend_File(array(
				'cache_dir' => $configuration['dir'],
				'file_name_prefix'	=> $configuration['prefix'],
				'read_control_type' => $configuration['read_control_type'],
				'hashed_directory_level' => $configuration['hashed_directory_level'],
				'read_control' => $configuration['read_control'],
				'file_locking' => $configuration['file_locking'],
			));

			$backend = new Dklab_Cache_Backend_Profiler($backend, array(Kcache_CalcStats::factory($driver), "calc_stats"));

			return Kcache::$instances[$driver] = new Kcache($driver,$backend);
		} else if ($driver === Kcache::CACHE_DRIVER_MEMCACHE) {
			require_once Kohana::find_file("vendor", "DklabCache/Zend/Cache/Backend/Memcached");
			$configuration = Kohana::$config->load("kcache.settings." . Kcache::CACHE_DRIVER_MEMCACHE);
			if (is_null($configuration)) {
				throw new Kohana_Exception("Конфигурация драйвера кеширования не обнаружена");
			}

			$backend = new Dklab_Cache_Backend_MemcachedMultiload($configuration);
			$backend = new Dklab_Cache_Backend_TagEmuWrapper(new Dklab_Cache_Backend_Profiler($backend,array(Kcache_CalcStats::factory($driver), 'calc_stats')));
			return Kcache::$instances[$driver] = new Kcache($driver,$backend);
		} else {
			throw new Kohana_Exception("Неизвестный драйвер кеширования");
		}
	}

	public function __construct($driver,$cache) {
		$this->driver = $driver;
		$this->cache = $cache;

		// clean old cache
		if (rand(1,50) === 12) {
			$this->clean(Zend_Cache::CLEANING_MODE_OLD);
		}
	}

	/**
	 * Получить кеш по ключу
	 * @param string $str
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($str,$default=false) {
		if (Kohana::$profiling === TRUE) {
			$benchmark = Profiler::start('Kcache Gets', $this->driver . ": " . $str);
		}

		$str = md5(Kohana::$config->load('kcache.prefix') . $str);
		$load = $this->cache->load($str);
		$data = $default;
		if ($this->driver === Kcache::CACHE_DRIVER_FILE and $load !== false) {
			$data = unserialize($load);
		} else {
			$data = ($load === false) ? $default : $load;
		}

		if (Kohana::$profiling === TRUE AND isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $data;
	}

	/**
	 * Записать кеш по ключу
	 * @param string $str
	 * @param mixed $default
	 * @return mixed
	 */
	public function set($name,$data,$timelife = 60,array $tags = array()) {
		if (Kohana::$profiling === TRUE) {
			$benchmark = Profiler::start('Kcache Sets', $this->driver . ": " . $name);
		}

		$name = md5(Kohana::$config->load('kcache.prefix') . $name);
		if ($this->driver === Kcache::CACHE_DRIVER_FILE) {
			$data = serialize($data);
		}

		// fix max cache timelife
		if ($this->driver === Kcache::CACHE_DRIVER_MEMCACHE) {
			$days30 = 60*60*24*30;
			if ($timelife > $days30) {
				$timelife = $days30;
			}
		}

		if (Kohana::$profiling === TRUE AND isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $this->cache->save($data,$name,$tags,$timelife);
	}

	/**
	 * Очистка кеша
	 */
	public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array()) {
		return $this->cache->clean($mode, $tags);
	}
}