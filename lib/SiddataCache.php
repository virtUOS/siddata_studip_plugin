<?php


/**
 * Class SiddataCache
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 */
class SiddataCache
{
    /**
     * @var SiddataCrypt
     */
    private $crypter;

    /**
     * @var StudipCache|StudipCacheProxy|StudipNullCache
     */
    private $cache;

    /**
     * @var SiddataCache
     */
    private static $instance;

    /**
     * SiddataCache constructor.
     */
    private function __construct()
    {
        $this->crypter = new SiddataCrypt();
        $this->cache = StudipCacheFactory::getCache();
    }

    /**
     * Get singleton instance
     * @return SiddataCache
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Stores or updates data in the studip cache
     * @param string $type type of stored data
     * @param string $cache_data data to be cached
     */
    public function updateCache($type, $cache_data) {
        if (!$_SESSION['SIDDATA_cache_set']) {
            $this->invalidateCache();
            $_SESSION['SIDDATA_cache_set'] = true;
        }
        // holds all stored types
        $cache_types = $this->getCacheData("cached_types");
        if (!$cache_types) {
            $cache_types = [];
        }
        // if type is new in cache
        if (is_array($cache_types) and !in_array($type, $cache_types)) {
            $cache_types[] = $type;
        }
        $this->cache->write($this->cacheKey($type), $cache_data, 600); // 10 * 60 = 10 minutes
        $this->cache->write($this->cacheKey("cached_types"), $cache_types);
    }

    /**
     * Returns cached data of specified type
     * @param string $type type of stored data
     * @return string|false cached data
     */
    public function getCacheData($type) {
        return $this->cache->read($this->cacheKey($type));
    }

    /**
     * Expires all caches of active user
     */
    public function invalidateCache() {
        $cache_types = $this->getCacheData("cached_types");
        if (is_array($cache_types)) {
            foreach ($cache_types as $type) {
                $this->cache->expire($this->cacheKey($type));
            }
        }
        $this->cache->expire($this->cacheKey("cached_types"));
        unset($_SESSION['SIDDATA_cache_set']);
    }

    /**
     * Constructs key
     * @param string $type type of stored data
     * @return string key
     */
    private function cacheKey($type='') {
        $key = 'plugin/siddata/userdata/';

        // encrypt user id
        $uid = User::findCurrent()->id;
        $crypted = $this->crypter->std_encrypt($uid);

        $key .= $crypted;
        $key .= '/' . $type;
        return $key;
    }
}
