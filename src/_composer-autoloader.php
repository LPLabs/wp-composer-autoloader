<?php
/*
Plugin Name: WP Composer Autoloader
Plugin Group: PHP
Plugin URI: https://github.com/LPLabs/wp-composer-autoloader
Description: Use the Composer autoloader with WordPress
Version: 0.2.1
Author: Eric King
Author URI: http://webdeveric.com/
*/

defined('ABSPATH') || exit;

// This should be the absolute file system path to the autoloader.
$autoload_path = defined('COMPOSER_AUTOLOADER') ? COMPOSER_AUTOLOADER : getenv('COMPOSER_AUTOLOADER');

if (! $autoload_path) {
    $autoload_path = apply_filters('composer-autoloader-path', wp_cache_get('composer-autoloader-path'));

    if (! $autoload_path) {
        $autoload_file = apply_filters('composer-autoloader-file', '/vendor/autoload.php');

        $dir = ABSPATH;

        // Stop at one level above the document root
        $stop_dir = realpath((isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : $dir) . '/../');

        while (($autoload_path = realpath($dir . $autoload_file)) === false && $dir !== $stop_dir) {
            $dir = realpath($dir . '/../');
        }

        if ($autoload_path) {
            wp_cache_set('composer-autoloader-path', $autoload_path);
        }
    }
}

if ($autoload_path && file_exists($autoload_path)) {
    if (is_readable($autoload_path)) {
        require_once $autoload_path;
    } else {
        add_action('admin_notices', function () use ($autoload_path) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>Composer Autoloader:</strong> Unable to read <code>', $autoload_path, '</code>. Please check file permissions.</p></div>';
        });
    }
} else {
    add_action('admin_notices', function () use ($autoload_path) {
        echo '<div class="notice notice-error is-dismissible"><p><strong>Composer Autoloader:</strong> ';

        if (defined('COMPOSER_AUTOLOADER')) {
            echo 'The <code title="', COMPOSER_AUTOLOADER, '">COMPOSER_AUTOLOADER</code> constant is wrong!';
        } elseif (getenv('COMPOSER_AUTOLOADER')) {
            echo 'The <code title="', COMPOSER_AUTOLOADER, '">COMPOSER_AUTOLOADER</code> environment variable is wrong!';
        } else {
            echo 'Unable to find the autoloader. Are you using <a href="https://getcomposer.org/">Composer</a>?';
        }

        echo '</p></div>';
    });
}
