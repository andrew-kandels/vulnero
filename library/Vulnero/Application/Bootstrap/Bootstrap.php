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
abstract class Vulnero_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Initializes the basic hooks for core functionality such as
     * the WordPress plugins_loaded hook for trapping when the
     * plugin is loaded each request as well as dealing with calling the
     * onPluginActivated method when the plugin is activated in the
     * admin panel.
     *
     * @return void
     */
    protected function _initWordPress()
    {
        // Setup WordPress hooks and filters we'll subscribe to
        add_action('plugins_loaded', array($this, 'onPluginLoaded'));

        $registry = Zend_Registry::getInstance();
        if (isset($registry['plugin-activated'])) {
            $this->onPluginActivated();
        }

        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        $frontController->setParam('view', $this->bootstrap('view')
                                                ->getResource('view'));
    }

    /**
     * WordPress activate_{plugin name} hook
     * Called when the plugin is activated for the first time.
     *
     * @return void
     */
    public function onPluginActivated()
    {
    }

    /**
     * WordPress plugins_loaded hook
     * Allows our application to inject sidebar widgets, scripts or stylesheets
     * into WordPress (if our application doesn't handle the route).
     *
     * @return  void
     */
    public function onPluginLoaded()
    {
    }

    /**
     * Initializes any Vulnero_Widget classes located in application/widgets.
     *
     * @return  void
     */
    protected function _initWpWidgets()
    {
        add_action('widgets_init', array($this, 'onWpWidgetsInit'));
    }

    /**
     * WordPress widgets_init hook
     * Registers plugin widgets
     *
     * @return  void
     */
    public function onWpWidgetsInit()
    {
        $cache = $this->bootstrap('cache')
                      ->getResource('cache');

        if (!$widgets = $cache->load('widgets')) {
            // Automatically detect and load any widget classes, caching the work
            $widgets = array();

            $di = new DirectoryIterator(APPLICATION_PATH . '/widgets/Widget');
            foreach ($di as $item) {
                if ($item->isFile() && substr($item->getFilename(), -4) == '.php') {
                    $widgets[] = 'Widget_' . substr($item->getFilename(), 0, -4);
                }
            }

            $cache->save($widgets, 'widgets');
        }

        foreach ($widgets as $widget) {
            register_widget($widget);
        }
    }

    /**
     * Overrides your theme's home and page template files with the wordpress-page.php
     * file in the plugin directory for all routes handled by Vulnero.
     *
     * @return  void
     */
    protected function _initWpTemplates()
    {
        add_filter('home_template', array($this, 'onWpHomeTemplate'));
        add_filter('page_template', array($this, 'onWpPageTemplate'));
        add_filter('single_template', array($this, 'onWpSingleTemplate'));
    }

    /**
     * WordPress home_template filter
     *
     * @return  void
     */
    public function onWpHomeTemplate()
    {
        return $this->onWpTemplates('home');
    }

    /**
     * WordPress page_template filter
     *
     * @return  void
     */
    public function onWpPageTemplate()
    {
        return $this->onWpTemplates('page');
    }

    /**
     * WordPress single_template filter
     *
     * @return  void
     */
    public function onWpSingleTemplate()
    {
        return $this->onWpTemplates('single');
    }

    /**
     * WordPress home_template filter
     * Called when WordPress attempts to locate a template (e.g.: home.php) in the
     * theme. If this is a valid WordPress request, we're going to override it and
     * instead specify that the template is wordpress-template.php in the plugin's
     * directory.
     *
     * @param   string          Template type (e.g.: home, page)
     * @return  void
     */
    public function onWpTemplates($template)
    {
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        if (($router = $frontController->getPlugin('Vulnero_Controller_Plugin_Router')) &&
            $router->hasPageContent()) {
            return PROJECT_BASE_PATH . '/wordpress-template.php';
        } else {
            return locate_template(array($template));
        }
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

                // Wrap in WordPress or render stand-alone?
                $isWpRoute = isset($routes->$routeName->wordpress)
                    ? (boolean) $routes->$routeName->wordpress
                    : true;

                // Application specified a WordPress route to wrap this request like a
                // layout.
                if ($isWpRoute) {
                    // Processing will be passed to WordPress and our application
                    // content will be inserted into the the_content() hook via
                    // the router controller plugin
                    $wpRoute = '';

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
     * WordPress wp_footer action
     * Adds a link to the Vulnero project.
     *
     * @return  void
     */
    protected function _initWpFooter()
    {
        add_action('wp_footer', array($this, 'onWpFooter'));
    }

    /**
     * WordPress wp_footer hook
     * Allows us to inject dynamic content into the WordPress footer
     * (if supported by the theme).
     *
     * @return void
     */
    public function onWpFooter()
    {
        echo '<p>Powered by <a href="http://www.vulnero.com/" target="_blank">Vulnero</a>';
    }

    /**
     * WordPress wp_head hook
     * Not used by default, allows the application to inject custom headers.
     *
     * @return string
     */
    public function _initWpHead()
    {
        add_action('wp_head', array($this, 'onWpHead'));
    }

    /**
     * WordPress wp_head hook
     * Allows us to inject dynamic content into the WordPress header
     * (if supported by the theme).
     *
     * @return  void
     */
    public function onWpHead()
    {
        $view = $this->bootstrap('view')
                     ->getResource('view');

        $components = array(
            $view->headMeta(),
            $view->headStyle(),
            $view->headLink(),
            $view->headScript()
        );

        echo implode(PHP_EOL, $components);
    }

    /**
     * WordPress wp_title hook
     * Sets the page title in the header.
     *
     * @return string
     */
    public function _initWpTitle()
    {
        add_filter('wp_title', array($this, 'onWpTitle'));
    }

    /**
     * WordPress wp_title hook
     * Change or alter the page title (if supported by the theme).
     *
     * @return  string
     */
    public function onWpTitle()
    {
        $view = $this->bootstrap('view')
                     ->getResource('view');
        return strip_tags($view->headTitle());
    }

    /**
     * Initializes the wp_print_styles hook for injecting CSS stylesheets.
     *
     * @return  void
     */
    protected function _initWpPrintStyles()
    {
        add_action('wp_print_styles',   array($this, 'onWpPrintStyles'));
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
     * application and not by WordPress. Also initializes the send_headers
     * hook which traps all requests so that we can check if they should
     * be handled by Vulnero.
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
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        $frontController->registerPlugin(new Vulnero_Controller_Plugin_Router());

        $routes = $this->bootstrap('routes')
                       ->getResource('routes');
        $router = $this->bootstrap('frontController')
                       ->getResource('frontController')
                       ->getRouter();
        $router->removeDefaultRoutes();

        $router->addConfig($routes);

        add_action('send_headers', array($this, 'onSendHeaders'));

        return $router;
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
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        $frontController->registerPlugin(new Vulnero_Controller_Plugin_Login());

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
     * Can be overridden by a child class to implement admin panel functionality
     * by returning a Zend_Form object.
     *
     * @return  Vulnero_Form_Admin  $form
     */
    protected function _initAdmin()
    {
        add_action('admin_menu', array($this, 'onAdmin'));

        return null;
    }

    /**
     * WordPress admin_menu hook
     * Initializes an admin panel from a Zend_Form object and injects it into
     * WordPress.
     *
     * @return  Zend
     */
    public function onAdmin()
    {
        if (0) {
            $frontController = $this->bootstrap('frontController')
                                    ->getResource('frontController');

            $request         = new Zend_Controller_Request_Http();

            $view = new Vulnero_Admin_View();
            $view->form = $form;

            if ($request->isPost() && $form->isValid($request->getPost())) {
                $view->success = true;
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
