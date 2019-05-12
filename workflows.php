<?php
/*
Plugin Name: Stem Workflows
Plugin URI: https://github.com/stem-press/workflows
Description: Workflows for Stem
Author: interfacelab
Version: 0.1.9
Author URI: http://interfacelab.io
*/


define('STEM_WORKFLOWS_DIR', dirname(__FILE__));

if (file_exists(STEM_WORKFLOWS_DIR.'/vendor/autoload.php')) {
	require_once STEM_WORKFLOWS_DIR.'/vendor/autoload.php';
}

add_action('heavymetal/app/packages/install', function() {
	new \Stem\Packages\Package(STEM_WORKFLOWS_DIR, 'Stem Workflows', 'Package providing workflows for Stem.');
});