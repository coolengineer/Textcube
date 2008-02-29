<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
	define('ROOT', '.'); 

	if (!empty($_SERVER['PRELOAD_CONFIG']) && file_exists('config.php'))
		require_once ROOT."/config.php";

	/* Retrieve Access Parameter Information. */
	$accessInfo = array(
		'host'     => $_SERVER['HTTP_HOST'],
		'fullpath' => str_replace('index.php', '', $_SERVER["REQUEST_URI"]),
		'position' => $_SERVER["SCRIPT_NAME"],
		'root'     => rtrim(str_replace('rewrite.php', '', $_SERVER["SCRIPT_NAME"]), 'index.php')
		);
	if (strpos($accessInfo['fullpath'],$accessInfo['root']) !== 0)
		$accessInfo['fullpath'] = $accessInfo['root'].substr($accessInfo['fullpath'], strlen($accessInfo['root']) - 1);
	// Workaround for compartibility with fastCGI / Other environment
	$accessInfo['input'] = ltrim(substr($accessInfo['fullpath'],
							     strlen($accessInfo['root']) + (defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)),
							     '/');
	$part = strtok($accessInfo['input'], '/');
	if (in_array($part, array('image','plugins','script','cache','skin','style','attach','thumbnail'))) {
		if (strpos($accessInfo['input'],'cache/backup') !== false) {
			header("HTTP/1.0 404 Not found");
			exit;
		}
		require_once ROOT.'/lib/function/file.php';
		dumpWithEtag(ltrim(rtrim($part == 'thumbnail' ?
							  preg_replace('/thumbnail/', 'cache/thumbnail', $accessInfo['input'], 1) :
							  $accessInfo['input']), '/'), '/');
		exit;
	}
	if (strtok($part, '?') == 'setup.php') {
		require 'setup.php';
		exit;
	}
	$accessInfo['URLfragment'] = explode('/',strtok($accessInfo['input'],'?'));
	unset($part);

	/* Check the existence of config.php (whether installed or not) */
	if (!file_exists('config.php')) {
		if (file_exists('.htaccess')) {
			print "<html><body>Remove '.htaccess' file first!</body></html>";
			exit;
		}
		print "<html><body><a id='setup' href='".rtrim($_SERVER["REQUEST_URI"],"/")."/setup.php'>Click to setup.</a></body></html>";
		exit;
	}

	/* Determine that which interface should be loaded. */
	require_once 'config.php';
	switch ($service['type']) {
		case 'path': // For path-based multi blog.
			array_splice($accessInfo['URLfragment'],0,1); 
			$pathPart = ltrim(rtrim(strtok(strstr($accessInfo['input'],'/'), '?'), '/'), '/');
			break;
		case 'single':
			$pathPart = (strpos($accessInfo['input'],'?') !== 0 ? ltrim(rtrim(strtok($accessInfo['input'], '?'), '/'), '/') : '');
			break;
		case 'domain': default: 
			$pathPart = ltrim(rtrim(strtok($accessInfo['fullpath'], '?'), '/'), '/');
			break;
	}
	$pathPart = strtok($pathPart,'&');
	
	/* Load interface. */
	$interfacePath = null;
	if (in_array($pathPart, array('favicon.ico','index.gif'))) {
		require_once 'interface/'.$pathPart.'.php';
		exit;
	}
	if (!empty($accessInfo['URLfragment']) &&
		in_array($accessInfo['URLfragment'][0],
				 array('api','archive','attachment','author','category','checkup','cover','entry','feeder','foaf','guestbook','keylog','location','logout','notice','page','plugin','pluginForOwner','search','suggest','sync','tag','ttxml')))
	{
		$pathPart = $accessInfo['URLfragment'][0];
		$interfacePath = 'interface/blog/'.$pathPart.'.php';
	}
	else if (is_numeric(strtok(end($accessInfo['URLfragment']), '&'))) {
		$pathPart = implode('/', array_slice($accessInfo['URLfragment'], 0, count($accessInfo['URLfragment']) - 1));
	}
	if (empty($interfacePath))
		$interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
	define('PATH', 'interface/'.(empty($pathPart) ? '' : $pathPart.'/'));
	unset($pathPart);

	if (!file_exists($interfacePath)) {
		header("HTTP/1.0 404 Not found");
		exit;
	}
	if (empty($service['debugmode'])) {
		@include_once $interfacePath;
	} else {
		include_once $interfacePath;
	}
?>
