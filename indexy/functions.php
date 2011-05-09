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

// Return string from last occurence of needle till end of string
function rstrstr($haystack, $needle, $start=0) {
	return substr($haystack, $start, strrpos($haystack, $needle));
}

// Add trailing slash if there is none
function addSlash($str) {
	return (substr($str, -1) == '/') ? $str : $str.'/';
}

// Remove trailing slash if there is one
function remSlash($str) {
	return (substr($str, -1) == '/') ? substr($str, 0, -1) : $str;
}

// Remove both trailing or ending slashes
function trimSlashes($str) {
	if (substr($str, -1) == '/') {
		$str = substr($str, 0, -1);
	}
	if (substr($str, 0, 1) == '/') {
		$str = substr($str, 1);
	}
	return $str;
}

/****************************************************************************
* Indexy-specific functions
*****************************************************************************/

// Determine necessary paths based on URL, file location and configuration
function indexy_get_paths($root_path, $theme) {
	
	// Create arrays
	$server = array();
	$client = array();
	
	// Client root is $root_path
	$client['root'] = $root_path;
	
	// Indexy location on server is directory name of this file
	$server['indexy'] = str_replace('\\', '/', dirname(__FILE__));
	
	// Store basename of Indexy folder for later
	$indexyFolderName = basename($server['indexy']);
	
	// Indexy on the client side is the client root plus the basename of the indexy directory
	$client['indexy'] = addSlash($client['root']).$indexyFolderName;
	
	// Root on the server is indexy directory minus indexy dir name (which is client value)
	$server['root'] = rstrstr($server['indexy'], '/'.$indexyFolderName);
	
	// Ttheme locations easy to determine from existing vars
	$client['theme'] = $client['indexy'] . '/themes/' . $theme;
	$server['theme'] = $server['indexy'] . '/themes/' . $theme;
	
	//$server['theme'] = $server['root'] . $client['theme'];
	
	// Store request host and URL
	$client['host'] = $_SERVER['HTTP_HOST'];
	$url = parse_url($_SERVER["REQUEST_URI"]);
	
	// Client request based on URL path
	$client['request'] = (strlen($url['path']) > 1) ? remSlash($url['path']) : $url['path'];
	$client['request'] = urldecode($client['request']);
	
	// Determine the server request based on the client request
	$server['request'] = ($client['root'] === '/') ? $server['root'] : rstrstr($server['root'], $client['root']);
	$server['request'].= remSlash($client['request']);
	
	// Segments array begins with client request
	$segments = $client['request'];
	
	// Check to see if the request starts with the same value as root, and remove it if so
	if (strpos($segments, $client['root']) === 0) {
		$segments = substr($segments, strlen($client['root']));
	}
	
	// Trim any beginning or leading slashes and prep vars for loop
	$segments = trim(trimSlashes($segments));
	$segments = strlen($segments) ? explode('/', $segments) : array();
	$client['segments'] = array();
	$full_segment = remSlash($client['root']);
	
	// Loop through segments, building full segment and pushing to final array
	foreach($segments as $segment) {
		$full_segment.= '/'.$segment;
		$client['segments'][] = array(
			'part' => $segment,
			'full' => $full_segment
		);
	}
	
	// pre($server);
	// pre($client);
	// die();
	
	// Package results and return
	return array(
		'server' => $server, 
		'client' => $client
	);
}

// Check if a directory is forbidden or not
function indexy_is_forbidden($dir, $forbidden=array(), $root_path='/') {
	if ($root_path != '/') {
		foreach($forbidden as $key=>$name) {
			$forbidden[$key] = remSlash($root_path) . $name;
		}
	}
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
function indexy_prepare_objects($objects, $client, $server, $root, $forbidden_paths=array(), $hidden_file_extensions=array(), $hidden_file_names=array()) {
	// Create vars
	$dirs = array();
	$files = array();
	$total_size = 0;
	// Directories
	foreach($objects['directories'] as $dir_name) {
		if (!indexy_is_forbidden(addSlash($client).$dir_name, $forbidden_paths, $root)) {
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