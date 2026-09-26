<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * - `CodeIgniter\Session\Handlers\FileHandler`
     * - `CodeIgniter\Session\Handlers\DatabaseHandler`
     * - `CodeIgniter\Session\Handlers\MemcachedHandler`
     * - `CodeIgniter\Session\Handlers\RedisHandler`
     *
     * @phpstan-var class-string<BaseHandler>
     */
    public string $driver = \CodeIgniter\Session\Handlers\DatabaseHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     *
     * The session cookie name, must contain only [0-9a-z_-] characters
     */
    public string $cookieName = 'mpesa_analyzer_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     */
    public int $expiration = 2592000;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to and is driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' driver, it's a table name.
     * Please read up the manual for the format with other session drivers.
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     */
    public string $savePath = 'ci_sessions';

    /**
     * --------------------------------------------------------------------------
     * Session Match IP
     * --------------------------------------------------------------------------
     *
     * Whether to match the user's IP address when reading the session data.
     *
     * WARNING: If you're using the database driver, don't forget to update
     *          your session table's PRIMARY KEY when changing this setting.
     */
    public bool $matchIP = false;

    /**
     * --------------------------------------------------------------------------
     * Session Time to Update
     * --------------------------------------------------------------------------
     *
     * How many seconds between CI regenerating the session ID.
     */
    public int $timeToUpdate = 300;

    /**
     * --------------------------------------------------------------------------
     * Session Regenerate Destroy
     * --------------------------------------------------------------------------
     *
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     */
    public bool $regenerateDestroy = false;

    /**
     * --------------------------------------------------------------------------
     * Session Database Group
     * --------------------------------------------------------------------------
     *
     * DB Group for the database session.
     */
    public ?string $DBGroup = null;

    /**
     * Cache for Redis probe status within the current request lifecycle.
     */
    private static array $redisProbeCache = [];

    /**
     * Fast non-blocking socket probe to test if Redis is accepting connections.
     * Default timeout is 50ms (0.05s).
     */
    public static function isRedisAlive(?string $host = null, int $port = 6379, float $timeout = 0.05): bool
    {
        $host = $host ?: (env('REDIS_HOST') ?: getenv('REDIS_HOST') ?: '127.0.0.1');
        $port = $port ?: (int) (env('REDIS_PORT') ?: getenv('REDIS_PORT') ?: 6379);

        if (!extension_loaded('redis')) {
            return false;
        }

        $cacheKey = "{$host}:{$port}";
        if (array_key_exists($cacheKey, self::$redisProbeCache)) {
            return self::$redisProbeCache[$cacheKey];
        }

        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($fp !== false) {
            fclose($fp);
            self::$redisProbeCache[$cacheKey] = true;
            return true;
        }

        self::$redisProbeCache[$cacheKey] = false;
        return false;
    }

    public function __construct()
    {
        parent::__construct();

        $redisHost = env('REDIS_HOST') ?: getenv('REDIS_HOST');
        $redisPort = (int) (env('REDIS_PORT') ?: getenv('REDIS_PORT') ?: 6379);

        if ($redisHost && self::isRedisAlive($redisHost, $redisPort)) {
            $this->driver   = \CodeIgniter\Session\Handlers\RedisHandler::class;
            $this->savePath = "tcp://{$redisHost}:{$redisPort}";
        } else {
            // High-Availability Fallback: Gracefully store sessions in MySQL ci_sessions
            $this->driver   = \CodeIgniter\Session\Handlers\DatabaseHandler::class;
            $this->savePath = 'ci_sessions';

            if ($redisHost) {
                log_message('notice', "Redis ({$redisHost}:{$redisPort}) is offline; session driver dynamically engaged MySQL ci_sessions fallback.");
            }
        }
    }
}
