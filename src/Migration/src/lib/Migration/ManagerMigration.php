<?php

use Migrations\Migration\Migration\StageMigration;

defined('BASEPATH') OR exit('No direct script access allowed');

class ManagerMigration {

    protected $CI;
    protected $config;

    public function __construct($config = null)
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        
        if ($config) {
            $this->setCredentials($config);
        } else {
            // Usar configuración desde variables de entorno de Docker
            $this->setCredentialsFromEnv();
        }
    }

    /**
     * Configurar credenciales desde variables de entorno de Docker
     */
    protected function setCredentialsFromEnv()
    {
        $config = (object) array(
            'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
            'DB_USERNAME' => getenv('DB_USERNAME') ?: 'root',
            'DB_PASSWORD' => getenv('DB_PASSWORD') ?: '',
            'DB_DATABASE' => getenv('DB_DATABASE') ?: 'test',
            'DB_PORT' => getenv('DB_PORT') ?: '3306',
            'DB_CHARSET' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'DB_COLLATION' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci'
        );
        
        $this->setCredentials($config);
    }

    public function setCredentials($config)
    {
        $this->config = $config;
        
        // Configurar la base de datos con los nuevos parámetros
        $db_config = array(
            'dsn'      => '',
            'hostname' => $config->DB_HOST,
            'username' => $config->DB_USERNAME,
            'password' => $config->DB_PASSWORD,
            'database' => $config->DB_DATABASE,
            'port'     => $config->DB_PORT,
            'dbdriver' => 'mysqli',
            'dbprefix' => '',
            'pconnect' => FALSE,
            'db_debug' => TRUE,
            'cache_on' => FALSE,
            'cachedir' => '',
            'char_set' => $config->DB_CHARSET,
            'dbcollat' => $config->DB_COLLATION,
            'swap_pre' => '',
            'encrypt'  => FALSE,
            'compress' => FALSE,
            'stricton' => FALSE,
            'failover' => array(),
            'save_queries' => TRUE
        );
        
        $this->CI->db = $this->CI->load->database($db_config, TRUE);
    }

    public function up($fresh = false, $seed = false, $stageTo = null)
    {
        if ($fresh) {
            $this->dropAll();
            $current = null;
        } else {
            $current = $this->getCurrentVersion();
        }
        
        $stages = $this->getStages($current, $stageTo);
        
        foreach ($stages as $stage) {
            $stage->apply();
            if ($seed) {
                $stage->seed();
            }
            $this->setCurrentVersion($stage->name);
        }
    }

    public function down($stageTo = null)
    {
        $current = $this->getCurrentVersion();
        $stages = $this->getStages($stageTo, $current);
        $stages = array_reverse($stages);
        
        foreach ($stages as $stage) {
            $stage->undo();
        }
        
        $this->setCurrentVersion($stageTo);
    }

    protected function dropAll()
    {
        $query = "SELECT table_name 
                  FROM information_schema.tables 
                  WHERE table_schema = ? AND table_name != 'ci_migration_status'";
        
        $tables = $this->CI->db->query($query, array($this->config->DB_DATABASE))->result();
        
        // Deshabilitar verificación de claves foráneas
        $this->CI->db->query("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            $table_name = isset($table->TABLE_NAME) ? $table->TABLE_NAME : $table->table_name;
            $this->CI->db->query("DROP TABLE IF EXISTS `{$table_name}`");
        }
        
        // Rehabilitar verificación de claves foráneas
        $this->CI->db->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    protected function getStages($from = null, $to = null)
    {
        $origins = array();
        $stages = array();
        $stagesObj = array();
        
        $origins[] = '/usr/local/migration/source'; // Ruta absoluta
        
        echo "Buscando en directorios: " . implode(', ', $origins) . "\n";
        
        foreach ($origins as $origin) {
            if (!is_dir($origin)) {
                echo "Directorio no existe: $origin\n";
                continue;
            }
            
            $moduleStages = scandir($origin);
            $moduleStages = array_diff($moduleStages, array('.', '..'));
            
            echo "Encontrados en $origin: " . implode(', ', $moduleStages) . "\n";
            
            foreach ($moduleStages as $moduleStage) {
                // Temporalmente comentar la validación de fecha para debug
                // if (!$this->isValidDateFormat($moduleStage)) {
                //     echo "Formato de fecha inválido para: $moduleStage\n";
                //     continue;
                // }
                
                if (!isset($stages[$moduleStage])) {
                    $stages[$moduleStage] = array();
                }
                $path = $origin . '/' . $moduleStage;
                $stages[$moduleStage][] = $path;
                echo "Agregando stage: $moduleStage en $path\n";
            }
        }
        
        // Ordenar por fecha (las claves ya están en formato YYYY_MM_DD)
        ksort($stages);
        
        echo "Stages ordenados: " . implode(', ', array_keys($stages)) . "\n";
        
        foreach ($stages as $k => $stage) {
            if ($from && $k <= $from) {
                echo "Saltando stage $k (anterior a $from)\n";
                continue;
            }
            if ($to && $k > $to) {
                echo "Saltando stage $k (posterior a $to)\n";
                continue;
            }
            foreach ($stage as $path) {
                echo "Creando StageMigration para: $path\n";
                $stagesObj[] = new StageMigration($path, $this);
            }
        }
        
        echo "Total de objetos StageMigration creados: " . count($stagesObj) . "\n";
        return $stagesObj;
    }

    /**
     * Validar formato de fecha YYYY_MM_DD_description
     */
    protected function isValidDateFormat($stageName)
    {
        // Patrón: YYYY_MM_DD_descripcion
        $pattern = '/^(\d{4})_(\d{2})_(\d{2})_(.+)$/';
        
        if (!preg_match($pattern, $stageName, $matches)) {
            return false;
        }
        
        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
        
        // Validar que sea una fecha válida
        return checkdate($month, $day, $year);
    }

    protected function getActiveModules()
    {
        $moduleList = array();
        $modulesDir = APPPATH . 'modules/';
        
        if (is_dir($modulesDir)) {
            $modules = scandir($modulesDir);
            $modules = array_diff($modules, array('.', '..'));
            
            // Si tienes una configuración de módulos activos
            $this->CI->load->config('migration', TRUE);
            $activeModules = $this->CI->config->item('active_modules', 'migration');
            
            if ($activeModules) {
                $activeModules = explode(',', $activeModules);
                foreach ($activeModules as $module) {
                    $module = trim($module);
                    if (in_array($module, $modules)) {
                        $moduleList[] = $module;
                    }
                }
            }
        }
        
        return $moduleList;
    }

    public function setCurrentVersion($version)
    {
        $data = array('version' => $version);
        
        // Verificar si existe un registro
        $existing = $this->CI->db->get_where('ci_migration_status', array('id' => 1))->row();
        
        if ($existing) {
            $this->CI->db->where('id', 1);
            $this->CI->db->update('ci_migration_status', $data);
        } else {
            $data['id'] = 1;
            $this->CI->db->insert('ci_migration_status', $data);
        }
    }

    public function getCurrentVersion($init = true)
    {
        try {
            // Primero intentar crear la tabla si no existe
            if ($init) {
                $this->initialize();
            }
            
            $query = $this->CI->db->get_where('ci_migration_status', array('id' => 1), 1);
            
            if ($query->num_rows() > 0) {
                $result = $query->row();
                return $result->version;
            } else {
                return null;
            }
        } catch (Exception $e) {
            echo "Error al obtener versión: " . $e->getMessage() . "\n";
            if ($init && strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "Creando tabla ci_migration_status...\n";
                $this->initialize();
                return null;
            } else {
                throw $e;
            }
        }
    }

    protected function initialize()
    {
        echo "Inicializando tabla de migraciones...\n";
        $sql = "CREATE TABLE IF NOT EXISTS `ci_migration_status` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `version` VARCHAR(100) NOT NULL,
            `lastUpdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        try {
            $this->CI->db->query($sql);
            echo "Tabla ci_migration_status creada exitosamente.\n";
        } catch (Exception $e) {
            echo "Error creando tabla ci_migration_status: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}