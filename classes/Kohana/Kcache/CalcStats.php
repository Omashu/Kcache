<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Kcache_CalcStats {

	/**
	 * Статистка использования драйвера
	 */
	protected $stats = array(
		'time' 			=> 0,
		'count' 		=> 0,
		'count_get' => 0,
		'count_set' => 0,
	);

	/**
	 * Драйвер
	 */
	protected $driver;

	/**
	 * Сущности драйверов
	 */
	protected static $instances = array();

	/**
	 * Создать сущность драйвера
	 * @param string $driver file|memcache
	 * @return object Kcache_CalcStats
	 */
	public static function factory($driver) {
		if (isset(Kcache_CalcStats::$instances[$driver])) {
			return Kcache_CalcStats::$instances[$driver];
		}

		Kcache_CalcStats::$instances[$driver] = new Kcache_CalcStats();
		Kcache_CalcStats::$instances[$driver]->driver = $driver;

		return Kcache_CalcStats::$instances[$driver];
	}

	/**
	 * Проход по всем по сущностям и возврат в коллбэк драйвер и сущность статистики
	 * @param object $callback your function
	 * @return int вернет кол-во обработанных драйверов
	 */
	public static function each($callback)
	{
		$return = 0;
		foreach (Kcache_CalcStats::$instances as $key => $value) {
			$callback($key,$value);
			$return++;
		}

		return $return;
	}

	/**
	 * Получение статистики
	 * @return array
	 */
	public function stats() {
		return $this->stats;
	}

	/**
	 * Принимает данные от драйвера кеширования, ведет подсчет статистики
	 * @return void
	 */
	public function calc_stats($time,$method) {
		$this->stats['time'] += $time;
		$this->stats['count']++;

		if ($method === 'Dklab_Cache_Backend_Profiler::load') {
			$this->stats['count_get']++;
		}
		
		if ($method === 'Dklab_Cache_Backend_Profiler::save') {
			$this->stats['count_set']++;
		}
	}
}