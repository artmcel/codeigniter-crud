<?php

defined('BASEPATH') OR define('BASEPATH', true);

// Definir constantes necesarias
defined('APPPATH') OR define('APPPATH', dirname(__FILE__) . '/../../');
defined('FCPATH') OR define('FCPATH', dirname(__FILE__) . '/../../');
defined('ENVIRONMENT') OR define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Autoloader para las clases sin namespace
spl_autoload_register(function($className){
    $classFiles = array(
        'ManagerMigration' => dirname(__FILE__) . "/Migration/ManagerMigration.php",
        'StageMigration' => dirname(__FILE__) . "/Migration/StageMigration.php"
    );
    
    if (isset($classFiles[$className])) {
        if (file_exists($classFiles[$className])) {
            include_once($classFiles[$className]);
        }
    }
    
    // Mantener el autoloader original para otras clases con namespace
    $parts = explode("\\", $className);
    
    if(count($parts) > 2 && $parts[0] == "Migrations" && $parts[1] == "Migration"){
        array_shift($parts);
        array_shift($parts);

        $path = dirname(__FILE__) . "/" . implode("/", $parts) . ".php";
        if(file_exists($path)){
            include_once($path);
        }
    }
});

// Mock básico de CodeIgniter para que funcione fuera del framework
if (!function_exists('get_instance')) {
    function &get_instance() {
        static $CI;
        if (!$CI) {
            $CI = new stdClass();
            $CI->db = null;
            $CI->load = new MockLoader();
            $CI->config = new MockConfig();
        }
        return $CI;
    }
}

// Mock del loader de CodeIgniter
class MockLoader {
    public function database($config = NULL, $return = FALSE) {
        if ($return) {
            return new MockDatabase($config);
        }
        $CI =& get_instance();
        $CI->db = new MockDatabase($config);
        return $CI->db;
    }
    
    public function config($file, $use_sections = FALSE) {
        return true;
    }
}

// Mock de configuración
class MockConfig {
    public function item($item, $index = '') {
        return null;
    }
}

// Mock básico de la base de datos
class MockDatabase {
    private $config;
    private $connection;
    private $_update_data;
    private $_where;
    private $_table;
    
    public function __construct($config = null) {
        if ($config) {
            $this->config = $config;
            $this->connect();
        }
    }
    
    private function connect() {
        $dsn = "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['char_set']}";
        
        try {
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = array()) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return new MockQueryResult($stmt);
        } catch (PDOException $e) {
            throw new Exception("Error en query: " . $e->getMessage());
        }
    }
    
    public function get_where($table, $where, $limit = null) {
        $sql = "SELECT * FROM `$table`";
        $params = array();
        
        if ($where) {
            $sql .= " WHERE ";
            $conditions = array();
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= implode(' AND ', $conditions);
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        try {
            return $this->query($sql, $params);
        } catch (Exception $e) {
            // Si la tabla no existe, retornar resultado vacío
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return new MockQueryResult(new MockEmptyStatement());
            }
            throw $e;
        }
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $fields) . "`) VALUES ($placeholders)";
        return $this->query($sql, $values);
    }
    
    public function update($table, $data) {
        $this->_update_data = $data;
        $this->_table = $table;
        return $this;
    }
    
    public function where($field, $value) {
        $this->_where = array($field => $value);
        
        // Ejecutar el update inmediatamente si tenemos datos pendientes
        if ($this->_update_data && $this->_table) {
            $fields = array_keys($this->_update_data);
            $values = array_values($this->_update_data);
            
            $sql = "UPDATE `{$this->_table}` SET `" . implode('` = ?, `', $fields) . "` = ?";
            
            if ($this->_where) {
                $sql .= " WHERE ";
                $conditions = array();
                foreach ($this->_where as $key => $val) {
                    $conditions[] = "`$key` = ?";
                    $values[] = $val;
                }
                $sql .= implode(' AND ', $conditions);
            }
            
            $result = $this->query($sql, $values);
            
            // Limpiar variables
            $this->_update_data = null;
            $this->_where = null;
            $this->_table = null;
            
            return $result;
        }
        
        return $this;
    }
    
    public function error() {
        if ($this->connection) {
            $errorInfo = $this->connection->errorInfo();
            return array('message' => $errorInfo[2]);
        }
        return array('message' => 'No connection');
    }
}

// Mock del resultado de query
class MockQueryResult {
    private $stmt;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    public function result() {
        if ($this->stmt instanceof MockEmptyStatement) {
            return array();
        }
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function row() {
        if ($this->stmt instanceof MockEmptyStatement) {
            return null;
        }
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function num_rows() {
        if ($this->stmt instanceof MockEmptyStatement) {
            return 0;
        }
        return $this->stmt->rowCount();
    }
}

// Mock para statements vacíos
class MockEmptyStatement {
    public function fetchAll() {
        return array();
    }
    
    public function fetch() {
        return null;
    }
    
    public function rowCount() {
        return 0;
    }
}