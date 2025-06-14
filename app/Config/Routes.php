<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\UsersController;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('users', [UsersController::class, 'index']);
$routes->get('users/create', [UsersController::class, 'create']);
$routes->post('users/create', [UsersController::class, 'create']);
$routes->get('users/edit/(:num)', [UsersController::class, 'edit']);
$routes->post('users/edit/(:num)', [UsersController::class, 'edit']);
$routes->get('users/delete/(:num)', [UsersController::class, 'delete']);
