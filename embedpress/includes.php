<?php
defined('ABSPATH') or die("No direct script access allowed.");

if (!defined('EMBEDPRESS_PLG_NAME')) {
    define('EMBEDPRESS_PLG_NAME', "embedpress");
}

if (!defined('EMBEDPRESS_PLG_VERSION')) {
    define('EMBEDPRESS_PLG_VERSION', "0.1");
}

if (!defined('EMBEDPRESS_PATH_BASE')) {
    define('EMBEDPRESS_PATH_BASE', plugin_dir_path(__FILE__));
}

if (!defined('EMBEDPRESS_PATH_CORE')) {
    define('EMBEDPRESS_PATH_CORE', EMBEDPRESS_PATH_BASE ."embedpress/");
}

if (!defined('EMBEDPRESS_PATH_LIBRARIES')) {
    define('EMBEDPRESS_PATH_LIBRARIES', EMBEDPRESS_PATH_BASE ."library/");
}

if (!defined('EMBEDPRESS_URL_ADMIN_ASSETS')) {
    define('EMBEDPRESS_URL_ADMIN_ASSETS', plugins_url(EMBEDPRESS_PLG_NAME) ."/embedpress/Layers/admin-assets/");
}

if (!defined('EMBEDPRESS_NAMESPACE')) {
    define('EMBEDPRESS_NAMESPACE', "\\EmbedPress");
}

if (!defined('EMBEDPRESS_AUTOLOADER_NAME')) {
    define('EMBEDPRESS_AUTOLOADER_NAME', "AutoLoader");
}

if (!defined('EMBEDPRESS_SHORTCODE')) {
    define('EMBEDPRESS_SHORTCODE', "embed");
}

// Run libraries's autoload script
require_once EMBEDPRESS_PATH_LIBRARIES ."autoload.php";
// Run the plugin autoload script
require_once EMBEDPRESS_PATH_BASE ."autoloader.php";
?>