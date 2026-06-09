<?php
$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->get('/getKategori', 'Controller@getKategori');
$router->get('/getCerita', 'Controller@getCerita');
$router->get('/getCerita/{id}', 'Controller@getCeritaById');
$router->get('/getAction', 'Controller@getAction');
$router->get('/getAction', 'Controller@getAction');
$router->get('/getNotifikasi', 'Controller@getNotifikasi');
$router->get('/getNotifikasiUser/{user_id}', 'Controller@getNotifikasiByUser');
$router->get('/getUser', 'Controller@getUser');
$router->get('/getVersion', 'Controller@getVersion');
$router->get('/getSliders', 'Controller@getSliders');
$router->post('/register', 'Controller@register');
$router->post('/login', 'Controller@login');
$router->post('/notifikasiRead', 'Controller@markNotifikasiRead');
$router->post('/trxRead', 'Controller@trxRead');
$router->post('/trxVote', 'Controller@trxVote');
$router->post('/trxShare', 'Controller@trxShare');
$router->post('/insertCeritaPanjang', 'Controller@insertCeritaPanjang');
