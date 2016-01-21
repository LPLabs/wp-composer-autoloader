<?php
/*
Plugin Name: WP Composer Autoloader
Plugin Group: PHP
Plugin URI: https://github.com/LPLabs/wp-composer-autoloader
Description: Use the Composer autoloader with WordPress
Version: 0.1.0
Author: Eric King
Author URI: http://webdeveric.com/
*/

defined('ABSPATH') || exit;

define('COMPOSER_AUTOLOADER_CACHE_KEY', 'composer-autoloader-path');

$autoload_path = wp_cache_get(COMPOSER_AUTOLOADER_CACHE_KEY);

if ($autoload_path === false) {
    $autoload_path = apply_filters('composer-autoloader-path', defined('COMPOSER_AUTOLOADER') ? COMPOSER_AUTOLOADER : getenv('COMPOSER_AUTOLOADER'));

    if ($autoload_path === false) {
        $autoload_file = apply_filters('composer-autoloader-file', '/vendor/autoload.php');

        $dir = ABSPATH;

        # Stop at one level above the document root
        $stop_dir = realpath((isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : $dir) . '/../');

        while (($autoload_path = realpath($dir . $autoload_file)) === false && $dir !== $stop_dir) {
            $dir = realpath($dir . '/../');
        }
    }

    wp_cache_set(COMPOSER_AUTOLOADER_CACHE_KEY, $autoload_path);
}

if ($autoload_path !== false && file_exists($autoload_path)) {
    if (is_readable($autoload_path)) {
        require_once $autoload_path;
    } else {
        add_action('admin_notices', function () use (&$autoload_path) {
            echo '<div class="error"><p><strong>Composer Autoloader:</strong> Unable to read <code>', $autoload_path, '</code>. Please check file permissions.</p></div>';
        });
    }
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p><strong>Composer Autoloader:</strong> Unable to find the autoloader. Are you using <a href="https://getcomposer.org/" target="_blank">Composer</a>?</p></div>';
    });
}
