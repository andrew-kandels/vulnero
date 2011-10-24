<?php
/**
 * Vulnero bootstrap which sets up the majority of the application's
 * functionality and convenience objects.
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
class Vulnero_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Initializes Vulnero functionality:
     * 1) Routing: Registers the WordPress send_headers hook which is used
     *             by Vulnero to implement Zend routing.
     * 2) Layouts: Registers the Vulnero controller plugin which injects the
     *             content of your controller's view script into the layout
     *             (which is normally set to a WordPress page template from
     *             your theme).
     * 3) Admin:   Checks if the current user is logged into WordPress through
     *             the Vulnero_Controller_Plugin_Login controller plugin and
     *             updates the Zend_Auth identity if so.
     *
     * @return void
     */
    protected function _initWordPress()
    {
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        $frontController->registerPlugin(new Vulnero_Controller_Plugin_Router());
        $frontController->registerPlugin(new Vulnero_Controller_Plugin_Login());

        add_action('send_headers', $this->bootstrap('onSendHeaders')->getResource('onSendHeaders'));
        add_action('plugins_loaded', array($this, 'onPluginsLoaded'));
        add_action('wp_print_styles', array($this, 'onWpPrintStyles'));
        add_action('wp_footer', array($this, 'onWpFooter'));
        add_action('wp_head', array($this, 'onWpHead'));
        add_action('wp_title', array($this, 'onWpTitle'));
        add_action('admin_menu', array($this, 'onAdminMenu'));
    }

    protected function _initOnSendHeaders()
    {
        return array($this, 'onSendHeaders');
    }

    /**
     * WordPress send_headers hook
     * Note: Permalinks must be enabled in WordPress for Vulnero to extend routing.
     * Create a new Zend_Controller_Request_Http object with the WordPress
     * route. Upon failure, we assume the route isn't handled and let WordPress
     * deal with it.
     *
     * @param   WordPress       WordPress object which contains the route
     * @return  void
     */
    public function onSendHeaders($wordpress)
    {
        // Permalinks must be enabled in WordPress for request to be set
        if (isset($wordpress->request) && ($wpRequest = $wordpress->request)) {
            $frontController = $this->bootstrap('frontController')
                                    ->getResource('frontController');
            $routes          = $this->bootstrap('routes')
                                    ->getResource('routes');

            // Generate a new request object built from the WordPress route
            $options         = $this->getOptions();
            $uri             = $options['wordpress']['siteurl'] . '/' . $wpRequest;
            $uriObj          = Zend_Uri::factory($uri);
            $request         = new Zend_Controller_Request_Http($uriObj);
            $frontController->setRequest($request);

            try {
                // We need to capture the output so as to insert it only via the
                // WordPress the_content() hook so it's displayed in the correct
                // position on the page
                $frontController->returnResponse(true);
                $output = $frontController->dispatch();

                // Controller plugin injects content into the WordPress the_content() hook
                if ($router = $frontController->getPlugin('Vulnero_Controller_Plugin_Router')) {
                    $router->setPageContent($output);
                }

                $routeName = $frontController->getRouter()->getCurrentRouteName();

                // Application specified a WordPress route to wrap this request like a
                // layout.
                if (isset($routes->$routeName->wordpress->route)) {
                    // Processing will be passed to WordPress and our application
                    // content will be inserted into the the_content() hook via
                    // the router controller plugin
                    $wpRoute = $routes->$routeName->wordpress->route;

                    $wordpress->request = $wpRoute;
                    $wordpress->query_string = '';
                    $wordpress->matched_rule = '(' . preg_quote($wpRoute) . ')(/.*)$';
                    $wordpress->matched_query = 'pagename=' . urlencode($wpRoute) . '&page=';
                    $wordpress->query_vars = array(
                        'page' => '',
                        'pagename' => $wpRoute
                    );
                    $wordpress->extra_query_vars = array();
                } elseif (PHP_SAPI != 'cli') {
                    // Non-WordPress enabled route, end execution
                    echo $output;
                    exit(0);
                }
            } catch (Zend_Controller_Router_Exception $e) {
                // our application didn't answer the route, so it passes control
                // back to WordPress simply by doing nothing
            }

            return $frontController->getRequest();
        }
    }

    /**
     * WordPress wp_title hook
     * Change or alter the page title (if supported by the theme).
     *
     * @param   string
     * @return  string
     */
    public function onWpTitle($title)
    {
        return $title;
    }

    /**
     * WordPress wp_footer hook
     * Allows us to inject dynamic content (as a string) into the WordPress footer
     * (if supported by the theme).
     *
     * @return string
     */
    public function onWpFooter()
    {
        return '<p>Powered by <a href="http://www.vulnero.com/" target="_blank">Vulnero</a>';
    }

    /**
     * WordPress wp_head hook
     * Allows us to inject dynamic content (as a string) into the WordPress header
     * (if supported by the theme).
     *
     * @return string
     */
    public function onWpHead()
    {
        return '';
    }

    /**
     * WordPress wp_print_styles hook
     * Allows us to inject stylesheets into WordPress.
     *
     * @return void
     */
    public function onWpPrintStyles()
    {
        // wp_register_style('unique id', '/path/to/css');
    }

    /**
     * WordPress plugins_loaded hook
     * Allows our application to inject sidebar widgets, scripts or stylesheets
     * into WordPress (if our application doesn't handle the route).
     *
     * @return  void
     */
    public function onPluginsLoaded()
    {
//         wp_register_sidebar_widget(
//             'UNIQUE NAME',
//             'FRIENDLY TITLE',
//             array($this, 'function'),
//             array(
//                 'classname' => 'CSS CLASS',
//                 'description' => 'Friendly description'
//             )
//         );
    }

    /**
     * Retrieves our initialized Zend_Config object and saves it in
     * the registry for easy access.
     *
     * @return Zend_Config
     */
    protected function _initConfig()
    {
        return Zend_Registry::get('config');
    }

    /**
     * Initializes and caches the application/config/routes.ini routes
     * configuration file, which are routes that are answered by your
     * application and not by WordPress.
     *
     * @return Zend_Router_Route
     */
    protected function _initRoutes()
    {
        $cache = $this->bootstrap('cache')
                      ->getResource('cache');

        // Cache the routes configuration file to speed up processing
        if (!$routes = $cache->load('routes')) {
            $routes = new Zend_Config_Ini(APPLICATION_PATH . '/config/routes.ini');
            $cache->save($routes, 'routes');
        }

        return $routes;
    }

    /**
     * Initializes the Zend router to handle default routes (we want those
     * to fall back to WordPress) and to use the config/routes.ini config
     * file.
     *
     * @return Zend_Router_Route
     */
    protected function _initRouter()
    {
        $routes = $this->bootstrap('routes')
                       ->getResource('routes');
        $router = $this->bootstrap('frontController')
                       ->getResource('frontController')
                       ->getRouter();
        $router->removeDefaultRoutes();

        $router->addConfig($routes);

        return $router;
    }

    /**
     * Configures the Zend_View view scripts doctypes and encodings
     * and sets up our layout object to use WordPress page templates
     * instead of our own application layouts so we can benefit from the
     * WordPress themes' look and feel.
     *
     * @return  Zend_View
     */
    protected function _initViewSettings()
    {
        $view = $this->bootstrap('view')
                     ->getResource('view');

        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');
        $view->headTitle('Vulnero');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8')
                         ->appendHttpEquiv('Content-Language', 'en_US');

        return $view;
    }

    /**
     * Intializes a Zend_Db_Adapter_Pdo_Mysql database adapter for using the
     * connection details from WordPress and registers the adapter as the default
     * Zend table database and in the registry for quick access.
     *
     * This does result in a second connection from WordPress's internal $wpdb
     * connection; unfortunately we can't global wpdb and use it because it's a
     * resource from mysql_connect() versus PDO which most Zend components depend
     * on.
     *
     * @return  Zend_Db_Adapter_Pdo_Mysql
     */
    protected function _initDb()
    {
        if (defined('DB_HOST') &&
            defined('DB_USER') &&
            defined('DB_PASSWORD') &&
            defined('DB_NAME')) {
            $db = Zend_Db::factory('Pdo_Mysql', array(
                'host'      => DB_HOST,
                'username'  => DB_USER,
                'password'  => DB_PASSWORD,
                'dbname'    => DB_NAME
            ));
        } else {
            try {
                $db = Zend_Db::factory('Pdo_Sqlite', array(
                    'file' => PROJECT_BASE_PATH . '/sqlite.db'
                ));
            } catch (Exception $e) {
                $db = null;
            }
        }

        if ($db) {
            Zend_Registry::set('db', $db);
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
        }

        return $db;
    }

    /**
     * Initalizes a Zend_Auth_Adapter against the WordPress wp_users table
     * the application can share the same authentication source.
     *
     * @return  Zend_Auth_Adapter_DbTable
     */
    protected function _initAuthAdapter()
    {
        $config = $this->bootstrap('config')
                       ->getResource('config');

        if ($db = $this->bootstrap('db')->getResource('db')) {
            $authAdapter = new Zend_Auth_Adapter_DbTable($db);
            $authAdapter->setTableName($config->wordpress->tablePrefix . 'users')
                        ->setIdentityColumn('user_login')
                        ->setCredential('user_pass')
                        ->setCredentialTreatment('MD5(?)');
        } else {
            $authAdapter = null;
        }

        return $authAdapter;
    }

    /**
     * Initializes a cache adapter in the following order of precedence:
     * 1) application/config/config.ini section if available:
     *    Example: cache.backend.* (see config for details)
     * 2) W3 Super Cache Memcache connection
     * 3) APC (if installed)
     * 4) SQLite (if installed) in cache/cache.sqlite.db
     * 5) File based, in cache/cache.obj
     *
     * It is recommended to configure your caching connection in the config
     * file.
     *
     * @return  Zend
     */
    protected function _initCache()
    {
        $config = $this->bootstrap('config')
                       ->getResource('config');

        if (isset($config->cache->backend->memcached)) {
            $adapterName = 'Libmemcached';
            $options = $config->cache->backend->memcached->toArray();
        } elseif (isset($config->cache->backend->memcache)) {
            $adapterName = 'Memcached';
            $options = $config->cache->backend->memcache->toArray();
        } elseif (isset($config->cache->backend->apc)) {
            $adapterName = 'Apc';
            $options = $config->cache->backend->apc->toArray();
        } elseif (isset($config->cache->backend->xcache)) {
            $adapterName = 'Xcache';
            $options = $config->cache->backend->xcache->toArray();
        } elseif (isset($config->cache->backend->sqlite)) {
            $adapterName = 'Sqlite';
            $options = $config->cache->backend->sqlite->toArray();
        } elseif (isset($config->cache->backend->file)) {
            $adapterName = 'File';
            $options = $config->cache->backend->file->toArray();
        } else {
            // Auto-detect best available option
            if (extension_loaded('apc')) {
                $adapterName = 'Apc';
                $options = array();
            } elseif (extension_loaded('sqlite')) {
                $adapterName = 'Sqlite';
                $options = array(
                    'cache_db_complete_path' => PROJECT_BASE_PATH . '/cache/cache.sqlite.db'
                );
            } else {
                $adapterName = 'File';
                $options = array(
                    'cache_dir'     => PROJECT_BASE_PATH . '/cache',
                    'file_locking'  => true
                );
            }
        }

        if (isset($config->cache->frontend)) {
            $frontendOptions = $config->cache->frontend->toArray();
        } else {
            $frontendOptions = array(
                'lifetime' => 3600,
                'logging' => false,
                'automatic_serialization' => true
            );
        }

        $cache = Zend_Cache::factory('Core', $adapterName, $frontendOptions, $options);

        Zend_Registry::set('cache', $cache);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        Zend_Date::setOptions(array('cache' => $cache));
        Zend_Locale::setCache($cache);

        return $cache;
    }

    /**
     * Creates a Vulnero_Form_Admin or descendent object which implements
     * an inject method to convert and inject its elements into the WordPress
     * admin panel.
     *
     * @return  Vulnero_Form_Admin  $form
     */
    protected function _initAdmin()
    {
        return new Vulnero_Form_Admin_Default();
    }

    /**
     * WordPress admin_menu hook
     * Initializes an admin panel from a Zend_Form object and injects it into
     * WordPress.
     *
     * @return  Zend
     */
    public function onAdminMenu()
    {
        if ($form = $this->bootstrap('admin')->getResource('admin')) {
            $frontController = $this->bootstrap('frontController')
                                    ->getResource('frontController');
            $request         = new Zend_Controller_Request_Http();

            $view = new Vulnero_Admin_View();
            $view->form = $form;

            if ($request->isPost() && $form->isValid($request->getPost())) {
                // handle save
            }

            add_options_page(
                'Vulnero Title',        // Title
                'Vulnero Menu Title',   // Menu Title
                'manage_options',       // WordPress Access Level
                'vulnero',              // Plugin Name
                array($view, 'renderWordPress')
            );
        }
    }
}
