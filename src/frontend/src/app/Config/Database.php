<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [];

    public function __construct()
    {
        parent::__construct();

        // Configurar variables desde entorno
        $config = (object) array(
            'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
            'DB_USERNAME' => getenv('DB_USERNAME') ?: 'root',
            'DB_PASSWORD' => getenv('DB_PASSWORD') ?: '',
            'DB_DATABASE' => getenv('DB_DATABASE') ?: 'test',
            'DB_PORT' => getenv('DB_PORT') ?: '3306',
            'DB_CHARSET' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'DB_COLLATION' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci'
        );

        // Configurar conexión por defecto con variables de entorno
        $this->default = [
            'DSN'          => '',
            'hostname'     => $config->DB_HOST,
            'username'     => $config->DB_USERNAME,
            'password'     => $config->DB_PASSWORD,
            'database'     => $config->DB_DATABASE,
            'DBDriver'     => 'MySQLi',
            'DBPrefix'     => '',
            'pConnect'     => false,
            'DBDebug'      => true,
            'charset'      => $config->DB_CHARSET,
            'DBCollat'     => $config->DB_COLLATION,
            'swapPre'      => '',
            'encrypt'      => false,
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => (int)$config->DB_PORT,
            'numberNative' => false,
            'foundRows'    => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];

        // Configurar conexión de tests
        $this->tests = [
            'DSN'         => '',
            'hostname'    => $config->DB_HOST,
            'username'    => $config->DB_USERNAME,
            'password'    => $config->DB_PASSWORD,
            'database'    => $config->DB_DATABASE . '_test',
            'DBDriver'    => 'MySQLi',
            'DBPrefix'    => 'db_',
            'pConnect'    => false,
            'DBDebug'     => true,
            'charset'     => $config->DB_CHARSET,
            'DBCollat'    => $config->DB_COLLATION,
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => false,
            'failover'    => [],
            'port'        => (int)$config->DB_PORT,
            'foreignKeys' => true,
            'busyTimeout' => 1000,
            'dateFormat'  => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
