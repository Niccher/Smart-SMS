<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     */
    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => 'root_password',
        'database' => 'db_mpesa_analyzer',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Support full connection URLs (e.g. Railway MYSQL_URL or DATABASE_URL)
        $mysqlUrl = env('MYSQL_URL', getenv('MYSQL_URL') ?: (env('DATABASE_URL', getenv('DATABASE_URL'))));
        if ($mysqlUrl && ($parsed = parse_url($mysqlUrl))) {
            if (!empty($parsed['host'])) {
                $this->default['hostname'] = $parsed['host'];
            }
            if (!empty($parsed['port'])) {
                $this->default['port'] = (int) $parsed['port'];
            }
            if (isset($parsed['user'])) {
                $this->default['username'] = urldecode($parsed['user']);
            }
            if (isset($parsed['pass'])) {
                $this->default['password'] = urldecode($parsed['pass']);
            }
            if (!empty($parsed['path'])) {
                $this->default['database'] = ltrim($parsed['path'], '/');
            }
        }

        // Standard or individual environment variable overrides
        $this->default['hostname'] = env('database.default.hostname',
            getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: ($this->default['hostname'] ?? 'localhost')
        );
        $this->default['username'] = env('database.default.username',
            getenv('DB_USER') ?: getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: ($this->default['username'] ?? '')
        );
        $this->default['password'] = env('database.default.password',
            getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: ($this->default['password'] ?? '')
        );
        $this->default['database'] = env('database.default.database',
            getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: ($this->default['database'] ?? '')
        );
        $this->default['DBDriver'] = env('database.default.DBDriver', $this->default['DBDriver'] ?? 'MySQLi');
        if (isset($this->default['port'])) {
            $this->default['port'] = (int) env('database.default.port',
                getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: $this->default['port']
            );
        }
        if (isset($this->default['socket'])) {
            $this->default['socket'] = env('database.default.socket', '');
        }

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
