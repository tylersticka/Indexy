<?php

/****************************************************************************
* Utilities
*****************************************************************************/

// Find files and folders in a directory
function get_directory_objects($path) {
	// Create arrays
	$dirs = array();
	$files = array();
	// Collect files from path
	$dir = dir($path);
	while ($entry = $dir->read()) {
		if ($entry === '.' || $entry === '..') {
			continue;
		}
		if (is_dir($path.'/'.$entry)) {
			$dirs[] = $entry;
		} else {
			$files[] = $entry;
		}
	}
	$dir->close();
	natcasesort($dirs);
	natcasesort($files);
	return array(
		'directories' => $dirs,
		'files' => $files
	);
}

// Returns file extension
function get_file_extension($file_name) {
  return strtolower(substr(strrchr($file_name,'.'),1));
}

// Helpful debugging function, outputs recursively inside of pre tags
function pre($stuff) {
	echo '<pre>';
	print_r($stuff);
	echo '</pre>';
}

// strstr for last occurence in string
function rstrstr($haystack,$needle, $start=0) {
	return substr($haystack, $start,strpos($haystack, $needle));
}

/****************************************************************************
* Indexy-specific functions
*****************************************************************************/

// Determine necessary paths based on URL, file location and configuration
function indexy_get_paths($root_path='/', $theme='default') {
	// Create arrays
	$server = array();
	$client = array();
	// Indexy
	$server['indexy'] = str_replace('\\', '/', dirname(__FILE__));
	$client['indexy'] = '/'.basename($server['indexy']);
	// Root
	$server['root'] = rstrstr($server['indexy'], $client['indexy']);
	$client['root'] = $root_path;
	// Theme
	$client['theme'] = $client['indexy'] . '/themes/' . $theme;
	$server['theme'] = $server['root'] . $client['theme'];
	// Request
	$client['host'] = $_SERVER['HTTP_HOST'];
	$url = parse_url($_SERVER["REQUEST_URI"]);
	$client['request'] = $url['path'];
	if (substr($client['request'], -1) == '/') {
		$client['request'] = substr($client['request'], 0, -1);
	}
	$server['request'] = $server['root'] . $client['request'];
	if (strlen($client['request']) < 1) {
		$client['request'] = '/';
	}
	// Segments
	$segments = strstr($client['request'], $client['root']);
	if (substr($segments, -1) == '/') {
		$segments =  substr($segments, 0, -1);
	}
	if (substr($segments, 0, 1) == '/') {
		$segments = substr($segments, 1);
	}
	$segments = explode('/', $segments);
	$client['segments'] = array();
	$full_segment = $client['root'] === '/' ? '' : $client['root'];
	foreach($segments as $segment) {
		$full_segment.= '/'.$segment;
		$client['segments'][] = array(
			'part' => $segment,
			'full' => $full_segment
		);
	}
	//$client['segments'] = explode('/', $segments);
	//$client['segments'] = explode('/', strstr($client['request'], $client['root']));
	//pre($client['segments']);
	// Package results and return
	return array(
		'server' => $server, 
		'client' => $client
	);
}

// Check if a directory is forbidden or not
function indexy_is_forbidden($dir, $forbidden=array()) {
	if (in_array($dir, $forbidden)) {
		return true;
	}
	foreach($forbidden as $dir2) {
		if (strpos($dir, $dir2) === 0) {
			return true;
		}
	}
	return false;
}

// Prepare object arrays for their eventual journey
function indexy_prepare_objects($objects, $client, $server, $forbidden_paths=array(), $hidden_file_extensions=array(), $hidden_file_names=array()) {
	// Create vars
	$dirs = array();
	$files = array();
	$total_size = 0;
	// Directories
	foreach($objects['directories'] as $dir_name) {
		if (!in_array($client.$dir_name, $forbidden_paths)) {
			$dirs[] = indexy_prepare_object($dir_name, $server);
		}
	}
	// Files
	foreach($objects['files'] as $file_name) {
		if (!in_array($file_name, $hidden_file_names) && !is_dir($file_name)) {
			$file = indexy_prepare_object($file_name, $server);
			if (!in_array($file['extension'], $hidden_file_extensions)) {
				$total_size+= $file['size'];
				$files[] = $file;
			}
		}
	}
	return array(
		'directories' => $dirs,
		'files' => $files,
		'size' => $total_size,
		'count' => count($dirs) + count($files)
	);
}

// Prepare an individual file or directory object
function indexy_prepare_object($file_name, $server) {
	$path = $server.'/'.$file_name;
	$file = array(
		'name' => $file_name,
		//'url_name' => rawurlencode($file_name),
		'mtime' => filemtime($path)
	);
	if (!is_dir($path)) {
		$file['size'] = filesize($path);
		$file['extension'] = get_file_extension($file_name);
	}
	return $file;
}

/****************************************************************************
* Dwoo Plugins
*****************************************************************************/

function Dwoo_Plugin_size_format(Dwoo $dwoo, $size) {
	$units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}