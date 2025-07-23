<?php
namespace Migrations\Migration\Migration;

use Exception;
use Illuminate\Database\Capsule\Manager;
use PhpParser\Node\Stmt\TryCatch;

defined('BASEPATH') OR exit('No direct script access allowed');

class StageMigration {
    protected $_path = "";
    protected $_manager;
    public $name = "";

    public function __construct($path, $manager)
    {
        $this->_path = $path;
        $this->_manager = $manager;
        $this->name = basename($path);
    }

    public function apply()
    {
        $files = $this->getStructure();
        foreach($files as $f) {
            try {
                echo "Ejecutando $f...";
                $sql = file_get_contents($f);
                $this->executeSQL($sql);
                echo "OK\n";
            } catch(Exception $e) {
                echo "ERROR\n";
                throw new Exception("Error en archivo $f: " . $e->getMessage());
            }
        }
    }

    public function undo()
    {
        $files = $this->getStructure("down");
        $files = array_reverse($files);
        foreach($files as $f) {
            try {
                $sql = file_get_contents($f);
                $this->executeSQL($sql);
            } catch(Exception $e) {
                throw new Exception("Error en archivo $f: " . $e->getMessage());
            }
        }
    }

    public function seed()
    {
        $files = $this->getSeeds();
        foreach($files as $f) {
            try {
                echo "Seeding $f...";
                $sql = file_get_contents($f);
                $this->executeSQL($sql);
                echo "OK\n";
            } catch(Exception $e) {
                echo "ERROR\n";
                throw new Exception("Error en archivo $f: " . $e->getMessage());
            }
        }
    }

    protected function executeSQL($sql)
    {
        $CI =& get_instance();
        
        // Dividir por punto y coma para ejecutar múltiples statements
        $statements = explode(';', $sql);
        
        foreach($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $result = $CI->db->query($statement);
                if (!$result) {
                    throw new Exception("Error ejecutando SQL: " . $CI->db->error()['message']);
                }
            }
        }
    }

    protected function getStructure($orientation = "up")
    {
        $structure_path = $this->_path . "/$orientation/structure";
        
        echo "Buscando archivos en: $structure_path\n";
        
        if(!file_exists($structure_path)) {
            echo "Directorio no existe: $structure_path\n";
            return array();
        }
        
        $dir = scandir($structure_path);
        $dir = array_diff($dir, array('.', '..'));
        
        echo "Archivos encontrados: " . implode(', ', $dir) . "\n";
        
        $files = array();
        foreach($dir as $d) {
            $files[] = $structure_path . "/" . $d;
        }
        
        // Ordenar archivos para ejecutar en orden correcto
        sort($files);
        
        echo "Archivos ordenados: " . implode(', ', $files) . "\n";
        return $files;
    }

    protected function getSeeds()
    {
        $seeder_path = $this->_path . "/up/seeder";
        
        if(!file_exists($seeder_path)) {
            return array();
        }
        
        $dir = scandir($seeder_path);
        $dir = array_diff($dir, array('.', '..'));
        
        $files = array();
        foreach($dir as $d) {
            $files[] = $seeder_path . "/" . $d;
        }
        
        // Ordenar archivos para ejecutar en orden correcto
        sort($files);
        
        return $files;
    }

    /**
     * Método auxiliar para validar archivos SQL
     */
    protected function isValidSQLFile($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return strtolower($extension) === 'sql';
    }

    /**
     * Método para obtener información del stage
     */
    public function getInfo()
    {
        return array(
            'name' => $this->name,
            'path' => $this->_path,
            'up_files' => count($this->getStructure('up')),
            'down_files' => count($this->getStructure('down')),
            'seed_files' => count($this->getSeeds())
        );
    }
}