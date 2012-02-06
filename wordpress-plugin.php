<?php
/*
Plugin Name: MyApp
Plugin URI: http://appwebsite.com/
Description: My app which is built on Vulnero, a WordPress plugin that transforms WordPress into an object-oriented CMS by implementing a Zend Framework application that interfaces with its API.
Version: 0.1.1
Author: Your Name
Author URI: http://authorwebsite.com/
*/

/**
 * My App is based on Vulnero
 *
 * WordPress entry-point file containing plug-in definition.
 *
 * Copyright (c) 2012, Andrew Kandels <me@andrewkandels.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    WordPress
 * @package     vulnero
 * @author      Andrew Kandels <me@andrewkandels.com>
 * @copyright   2012 Andrew Kandels <me@andrewkandels.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.vulnero.com
 */

// Example bootstrap (replace this with your own):

if (!defined('PLUGIN_BASE_URI')) {
    $baseUrl = defined('WP_PLUGIN_URL')
        ? WP_PLUGIN_URL
        : '/wp-content/plugins';
    define('PLUGIN_BASE_URI', $baseUrl . '/' . basename(dirname(__FILE__)));
}

if (!defined('APPLICATION_ENV')) {
    if ($env = getenv('APPLICATION_ENV')) {
        define('APPLICATION_ENV', $env);
    } elseif (function_exists('get_option') && 
             ($env = get_option(basename(dirname(__FILE__)) . '_environment'))) {
        define('APPLICATION_ENV', $env);
    } else {
        define('APPLICATION_ENV', 'development');
    }
}

if (!defined('PLUGIN_BASE_PATH')) {
    define('PLUGIN_BASE_PATH', realpath(dirname(__FILE__)));
}

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', PLUGIN_BASE_PATH . '/application');
}

set_include_path(implode(PATH_SEPARATOR, array(
    PLUGIN_BASE_PATH . '/library',
    APPLICATION_PATH . '/widgets',
    APPLICATION_PATH . '/admin-pages',
    APPLICATION_PATH . '/forms',
    APPLICATION_PATH,
    get_include_path()
)));

require 'Zend/Loader/Autoloader.php';
$autoLoader = Zend_Loader_Autoloader::getInstance();
$autoLoader->setFallbackAutoloader(true);
$autoLoader->suppressNotFoundWarnings(true);

$application = new Vulnero_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/config/config.ini'
);
$application->bootstrap();
