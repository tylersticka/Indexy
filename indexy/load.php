<?php

// Default configuration array
$defaults = array(
	'forbidden_paths' => array('/indexy'),
	'hidden_file_extensions' => array('php'),
	'hidden_file_names' => array('.htaccess', 'robots.txt'),
	'root_path' => '/',
	'theme' => 'default'
);

// Load and extract user configuration
require 'config.php';
$config = array_merge($defaults, $config);
extract($config);

// Include necessary helper functions
require 'functions.php';

// Instantiate data array for template
$data = array();

// Get paths
extract(indexy_get_paths(
	isset($root_path) ? $root_path : NULL, 
	isset($theme) ? $theme : NULL
));
$data['paths'] = $client;

// Determine if request is valid
if (!is_dir($server['request'])) {
	$data['error'] = 'HTTP/1.1 404 Not Found';
} else if (indexy_is_forbidden($client['request'], $forbidden_paths)) {
	$data['error'] = 'HTTP/1.1 403 Forbidden';
}

// Send HTTP error code or fetch directory files
if (array_key_exists('error',$data)) {
	header($data['error']);
} else {
	$data['objects'] = indexy_prepare_objects(
		get_directory_objects($server['request']),
		$client['request'],
		$server['request'],
		$forbidden_paths,
		$hidden_file_extensions,
		$hidden_file_names
	);	
}

// Display template
require 'libs/dwoo/dwooAutoload.php';
$dwoo = new Dwoo();
$dwoo->addPlugin('size_format', 'Dwoo_Plugin_size_format');
$dwoo->output($server['theme'].'/index.tpl', $data);