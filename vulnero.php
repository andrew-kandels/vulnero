<?php
/*
Plugin Name: Vulnero
Plugin URI: http://andrewkandels.com/vulnero/
Description: Vulnero is a WordPress plugin that transforms WordPress into an object-oriented CMS by implementing a Zend Framework application that interfaces with its API.
Version: 0.1.0
Author: Andrew Kandels
Author URI: http://andrewkandels.com/
*/

/**
 * Vulnero
 *
 * WordPress entry-point file containing plug-in definition.
 *
 * Copyright (c) 2011, Andrew Kandels <me@andrewkandels.com>.
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
 * @copyright   2011 Andrew Kandels <me@andrewkandels.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://andrewkandels.com/vulnero
 */

ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

define('VULNERO_VERSION', '0.1.0');

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', 'development');
}

if (!defined('PROJECT_BASE_PATH')) {
    define('PROJECT_BASE_PATH', realpath(dirname(__FILE__)));
}

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', PROJECT_BASE_PATH . '/application');
}

set_include_path(implode(PATH_SEPARATOR, array(
    PROJECT_BASE_PATH . '/library',
    APPLICATION_PATH . '/widgets',
    APPLICATION_PATH . '/forms',
    APPLICATION_PATH,
    get_include_path()
)));

if (empty($autoLoader)) {
    require 'Zend/Loader/Autoloader.php';
    $autoLoader = Zend_Loader_Autoloader::getInstance();
    $autoLoader->setFallbackAutoloader(true);
    $autoLoader->suppressNotFoundWarnings(true);
}

// Called upon first activating the plugin
register_activation_hook(__FILE__, 'vulnero_activate');

$application = new Vulnero_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/config/config.ini'
);
$application->bootstrap();

// Unit testing
if (PHP_SAPI == 'cli') {
    Zend_Registry::set('application', $application);
}

// End of Zend Framework bootstrapping

/**
 * WordPress activate_{plugin name} hook
 * Called when the Vulnero plugin is activated for the first time.
 * This can't be called from the bootstrap so we have to resort to
 * using a Zend_Registry key.
 *
 * @return  void
 */
function vulnero_activate()
{
    Zend_Registry::set('plugin-activated', true);
}
