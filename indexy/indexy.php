<?php

class Indexy extends Mustache
{
	public $count = 0;
	public $directories = array();
	public $error = false;
	public $files = array();
	public $pages = array();
	public $segments = array();
	public $size = 0;
	
	protected $client;
	protected $objects = array();
	protected $server;
	
	protected $config = array(
		'enable_pages' => true,
		'errors' => array(
			403 => 'HTTP/1.1 403 Forbidden',
			404 => 'HTTP/1.1 404 Not Found'
		),
		'forbidden_paths' => array('/indexy'),
		'hidden_file_extensions' => array('php'),
		'hidden_file_names' => array('.htaccess', 'robots.txt'),
		'page_extension' => 'markdown',
		'root_path' => '/',
		'theme' => 'default',
		'theme_file' => 'index.mustache',
		'theme_segment' => '/themes/',
		'title' => 'Files on {{host_path}}'
	);
	
	protected static function add_slash($str) {
		return (substr($str, -1) == '/') ? $str : $str.'/';
	}
	
	// Taken from Dwoo's date_format plugin
	public static function date_format($value, $format='%b %e, %Y', $default=null) {
	
		if (!empty($value)) {
	        // convert if it's not a valid unix timestamp
	        if (preg_match('#^-?\d{1,10}$#', $value)===0) {
	            $value = strtotime($value);
	        }
	    } elseif (!empty($default)) {
	        // convert if it's not a valid unix timestamp
	        if (preg_match('#^-?\d{1,10}$#', $default)===0) {
	            $value = strtotime($default);
	        } else {
	            $value = $default;
	        }
	    } else {
	        return '';
	    }
	 
	    // Credits for that windows compat block to Monte Ohrt who made smarty's date_format plugin
	    if (DIRECTORY_SEPARATOR == '\\') {
	        $_win_from = array('%D',       '%h', '%n', '%r',          '%R',    '%t', '%T');
	        $_win_to   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
	        if (strpos($format, '%e') !== false) {
	            $_win_from[] = '%e';
	            $_win_to[]   = sprintf('%\' 2d', date('j', $value));
	        }
	        if (strpos($format, '%l') !== false) {
	            $_win_from[] = '%l';
	            $_win_to[]   = sprintf('%\' 2d', date('h', $value));
	        }
	        $format = str_replace($_win_from, $_win_to, $format);
	    }
	    return strftime($format, $value);
	
	}
	
	protected static function explode_segments($request_path, $root_path) {
		// Check to see if the request starts with the same value as root, and remove it if so
		if (strpos($request_path, $root_path) === 0) {
			$request_path = substr($request_path, strlen($root_path));
		}
		// Trim any beginning or leading slashes and prep vars for loop
		$request_path = trim(self::trim_slashes($request_path));
		$request_path = strlen($request_path) ? explode('/', $request_path) : array();
		$full_segment = self::rem_slash($root_path);
		$segments = array();
		// Loop through segments, building full segment and pushing to final array
		foreach($request_path as $segment) {
			$full_segment.= '/'.$segment;
			$segments[] = (object) array(
				'part' => $segment,
				'full' => $full_segment
			);
		}
		return $segments;
	}
	
	protected static function get_directory_objects($path) {
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
		return (object) array(
			'directories' => $dirs,
			'files' => $files
		);
	}
	
	public static function get_file_extension($file_name) {
	  return strtolower(substr(strrchr($file_name,'.'),1));
	}
	
	protected static function is_path_in_array($dir, $arr=array(), $root_path='/') {
		if ($root_path != '/') {
			foreach($arr as $key=>$name) {
				$arr[$key] = self::rem_slash($root_path) . $name;
			}
		}
		if (in_array($dir, $arr)) {
			return true;
		}
		foreach($arr as $dir2) {
			if (strpos($dir, $dir2) === 0) {
				return true;
			}
		}
		return false;
	}
	
	protected static function rem_slash($str) {
		return (substr($str, -1) == '/') ? substr($str, 0, -1) : $str;
	}
	
	public static function rstrstr($haystack, $needle, $start=0) {
		return substr($haystack, $start, strrpos($haystack, $needle));
	}
	
	public static function size_format($size, $units=array(' B', ' KB', ' MB', ' GB', ' TB')) {
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	}
	
	protected static function trim_slashes($str) {
		if (substr($str, -1) == '/') {
			$str = substr($str, 0, -1);
		}
		if (substr($str, 0, 1) == '/') {
			$str = substr($str, 1);
		}
		return $str;
	}
	
	function __construct($config) {
		parent::__construct();
		$this->config = (object) array_merge($this->config, $config);
		$this->parse_request();
		if ($this->error) {
			header($this->error);
		} else if (isset($this->server->page)) {
			$this->page = SmartyPants(Markdown(file_get_contents($this->server->page)));
		} else {
			$this->get_indexy_items();
		}
		$this->create_template_vars();
		$this->_template = file_get_contents($this->server->theme.'/'.$this->config->theme_file);
	}
	
	protected function create_template_vars() {
		// Extract and rename some object properties
		$this->extract_template_vars($this->client, '_path');
		$this->extract_template_vars($this->client, '_path_closed', true);
		// Is this root or not?
		$this->is_root = ($this->client->root === $this->client->request);
		// Helpful object count notice variables
		if (isset($this->count)) {
			$this->count_noun = ($this->count > 1) ? 'objects' : 'object';
			$this->count_verb = ($this->count > 1) ? 'are' : 'is';
		}
		// Render page title using Mustache
		$this->title = $this->render($this->config->title);
	}
	
	protected function parse_request() {
		// Initialize public vars
		$this->client = new stdClass;
		$this->server = new stdClass;
		
		// Client root is $root_path
		$this->client->root = $this->config->root_path;
		
		// Indexy location on server is directory name of this file
		$this->server->indexy = str_replace('\\', '/', dirname(__FILE__));
		
		// Store basename of Indexy folder for later
		$folder_name = basename($this->server->indexy);
		
		// Indexy on the client side is the client root plus the basename of the indexy directory
		$this->client->indexy = self::add_slash($this->client->root).$folder_name;
		
		// Root on the server is indexy directory minus indexy dir name (which is client value)
		$this->server->root = self::rstrstr($this->server->indexy, '/'.$folder_name);
		
		// Ttheme locations easy to determine from existing vars
		$this->client->theme = $this->client->indexy . $this->config->theme_segment . $this->config->theme;
		$this->server->theme = $this->server->indexy . $this->config->theme_segment . $this->config->theme;
		
		// Store request host and URL
		$this->client->host = $_SERVER['HTTP_HOST'];
		$url = parse_url($_SERVER["REQUEST_URI"]);
		
		// Client request based on URL path
		$this->client->request = urldecode((strlen($url['path']) > 1) ? self::rem_slash($url['path']) : $url['path']);
		
		// Determine the server request based on the client request
		$this->server->request = ($this->client->root === '/') ? $this->server->root : self::rstrstr($this->server->root, $this->client->root);
		$this->server->request.= self::rem_slash($this->client->request);
		
		// Explode segments of request
		$this->segments = self::explode_segments($this->client->request, $this->client->root);
		
		// Check for errors or page content
		if (!is_dir($this->server->request)) {
			if($this->config->enable_pages) {
				$page_path = $this->server->request.'.'.$this->config->page_extension;
				if (is_file($page_path)) {
					// Shorten segments by one
					array_pop($this->segments);
					// Store page location
					$this->server->page = $page_path;
					$this->client->page = $this->client->request.'.'.$this->config->page_extension;
					// Store helpful page attributes
					$this->page_name = basename($this->client->page);
					$this->page_nice_name = basename($this->client->request);
					$this->page_name_url = rawurlencode($this->page_name);
					$this->page_nice_name_url = rawurlencode($this->page_nice_name);
				} else {
					$this->error = $this->config->errors[404];
				}
			} else {
				$this->error = $this->config->errors[404];
			}
		} else if (self::is_path_in_array($this->client->request, $this->config->forbidden_paths, $this->config->root_path)) {
			$this->error = $this->config->errors[403];
		}
		
		// Get parent of current request
		if (count($this->segments)) {
			$up = end($this->segments);
			$this->client->up = $up->full;
		} else {
			$this->client->up = $this->client->root;
		}
	}
	
	protected function get_indexy_items() {
		$objects = self::get_directory_objects($this->server->request);
		// Directories
		foreach($objects->directories as $dir_name) {
			if (!self::is_path_in_array(self::add_slash($this->client->request).$dir_name, $this->config->forbidden_paths, $this->client->root)) {
				$this->directories[] = new IndexyItem($dir_name, $this->server->request);
			}
		}
		// Files
		foreach ($objects->files as $file_name) {
			if (!in_array($file_name, $this->config->hidden_file_names) && !is_dir($file_name)) {
				$file = new IndexyItem($file_name, $this->server->request, $this->config->enable_pages, $this->config->page_extension);
				if (!in_array($file->extension, $this->config->hidden_file_extensions)) {
					$this->size+= $file->size;
					if ($this->config->enable_pages && $this->config->page_extension == $file->extension) {
						$this->pages[] = $file;
					} else {
						$this->files[] = $file;
					}
				}
			}
		}
		// Total count
		$this->count = count($this->directories) + count($this->files) + count($this->pages);
	}
	
	public function size_formatted() {
		return self::size_format($this->size);
	}
	
	protected function extract_template_vars($arr, $suffix='', $add_slash = false) {
		foreach($arr as $key=>$value) {
			$key.= $suffix;
			if ($add_slash && is_string($value)) {
				$value = self::add_slash($value);
			}
			$this->$key = $value;
		}
	}
	
}

class IndexyItem
{
	
	function __construct($file_name, $server_request) {
		$path = $server_request.'/'.$file_name;
		$this->name = $file_name;
		$this->name_url = rawurlencode($file_name);
		$this->mtime = filemtime($path);
		$this->is_dir = is_dir($path);
		if (!$this->is_dir) {
			$this->size = filesize($path);
			$this->extension = Indexy::get_file_extension($file_name);
			$this->nice_name = Indexy::rstrstr($file_name, '.'.$this->extension);
			$this->nice_name_url = rawurlencode($this->nice_name);
		}
	}
	
	public function mtime_formatted() {
		return Indexy::date_format($this->mtime, '%b %e, %Y %l:%M %p');
	}
	
	public function size_formatted() {
		return Indexy::size_format($this->size);
	}
	
}