<?php
// Router script for built-in PHP server
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path = $parsed_url['path'];

// Remove leading slash
$path = ltrim($path, '/');

// Route /1 to api/adms-endpoint.php
if ($path === '1') {
    require __DIR__ . '/api/adms-endpoint.php';
    return true;
}

// Route /api to api/adms-endpoint.php
if ($path === 'api') {
    require __DIR__ . '/api/adms-endpoint.php';
    return true;
}

// Route /adms to api/adms-endpoint.php
if ($path === 'adms') {
    require __DIR__ . '/api/adms-endpoint.php';
    return true;
}

// Route /machine to machine.php
if ($path === 'machine') {
    require __DIR__ . '/machine.php';
    return true;
}

// If file exists, serve it
if (file_exists(__DIR__ . '/' . $path) && is_file(__DIR__ . '/' . $path)) {
    return false; // Let PHP serve the file
}

// Default routing for other requests
return false;
?>
