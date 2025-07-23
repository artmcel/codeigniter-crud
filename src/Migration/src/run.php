<?php
#!/usr/local/bin/php

// Cargar solo el autoloader necesario
require_once("lib/autoload.php");

// Crear instancia del manager (sin namespaces ni configuración externa)
$manager = new ManagerMigration();

$fresh = false;
$to = false;
$seed = false;
$down = false;

foreach($argv as $arg){
    $parts = explode("=", $arg, 2);
    switch($parts[0]){
        case "--fresh":
            $fresh = true;
            break;
        case "--down":
            $down = true;
            break;
        case "--seed":
            $seed = true;
            break;
        case "--to":
            $to = isset($parts[1]) ? $parts[1] : false;
            break;
    }
}

echo "=== Migration System ===\n";
echo "Fresh: " . ($fresh ? 'Yes' : 'No') . "\n";
echo "Seed: " . ($seed ? 'Yes' : 'No') . "\n";
echo "To: " . ($to ? $to : 'Latest') . "\n";
echo "Down: " . ($down ? 'Yes' : 'No') . "\n";
echo "========================\n\n";

try {
    if($down){
        echo "Running DOWN migrations...\n";
        $manager->down($to);
        echo "DOWN migrations completed successfully!\n";
    } else {
        echo "Running UP migrations...\n";
        $manager->up($fresh, $seed, $to);
        echo "UP migrations completed successfully!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
