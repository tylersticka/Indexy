<?php

$redirects = array(
	'application'	=>	array('exe', 'msi'),
	'archive'		=>	array('zip', 'gz', 'tar', 'rar', '7z'),
	'balloon'		=>	array('cbr', 'cbz'),
	'book'			=>	array('epub', 'mobi'),
	'calendar'		=>	array('ical'),
	'code'			=>	array('html', 'htm', 'js', 'py'),
	'database'		=>	array('sql'),
	'excel'			=>	array('xls', 'xlsx'),
	'film'			=>	array('mov', 'mp4', 'mpg', 'mpeg', 'wmv', 'avi'),
	'flash'			=>	array('swf', 'flv'),
	'font'			=>	array('ttf', 'otf', 'pfb', 'pfm'),
	'game'			=>	array('nes', 'smc'),
	'illustrator'	=>	array('ai', 'eps', 'svg'),
	'image'			=>	array('jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'tif', 'png'),
	'music'			=>	array('mp3', 'm4a', 'flac', 'aac', 'wav', 'wma'),
	'powerpoint'	=>	array('ppt', 'pptx'),
	'text'			=>	array('txt', 'rtf'),
	'word'			=>	array('doc', 'docx')
);

$default = 'file';
$ext = '.png';
$path = 'icons/';
$type = 'image/png';

$icons = array();

foreach($_GET as $key=>$value) {
	if (strlen($key) > 0) {
		$icons[] = $path.$key.$ext;
		foreach($redirects as $route=>$arr) {
			if ($key===$route || in_array($key, $arr)) {
				$icons[] = $path.$route.$ext;
				break;
			}
		}
		break;
	}
}

$icons[] = $path.$default.$ext;

foreach($icons as $icon) {
	if (file_exists($icon)) {
		header('Content-Type:'.$type);
		header('Content-Length: ' . filesize($icon));
		readfile($icon);
		exit(0);
	}
}

header('HTTP/1.1 404 Not Found');
exit(0);