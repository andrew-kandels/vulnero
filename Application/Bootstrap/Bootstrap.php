<?php
/**
 * Vulnero bootstrap which sets up the majority of the application's
 * functionality and convenience objects.
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
class Vulnero_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * The name of the default page template file that may optionally
     * exist in the plugin directory to overwrite the WordPress default.
     * @var string
     */
    const WORDPRESS_PAGE_TEMPLATE = 'page.php';

    /**
     * Initialize Vulnero_WordPress, which is the single object for
     * interfacing with the WordPress API either in mock mode for
     * unit tests or cli scripts or against the actual API functions.
     *
     * Secondly, register some of the required actions, filters and
     * hooks Vulnero needs for proper operation.
     *
     * @return void
     */
    protected function _initWordPress()
    {
        $wordPress = new Vulnero_WordPress($this);
        $wordPress->addAction('plugins_loaded')
                  ->addFilter('wp_title')
                  ->addAction('wp_footer')
                  ->addAction('wp_head', 2)
                  ->registerActivationHook();

        // The view needs to be saved so that widgets can get ahold of it externally
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController')
                                ->setParam('bootstrap', $this);

        $frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
            'module' => 'default',
            'controller' => 'error',
            'action' => 'error'
        )));
        $frontController->throwExceptions(true);

        return $wordPress;
    }

    /**
     * Initializes any Vulnero_Widget classes located in application/widgets.
     *
     * @return  void
     */
    protected function _initWidgets()
    {
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if ('Yes' == $wordPress->getCustomOption('bootstrapWidgets', 'Yes')) {
            $this->bootstrap('wordPress')
                 ->getResource('wordPress')
                 ->addAction('widgets_init');
        }
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
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if ('Yes' == $wordPress->getCustomOption('bootstrapRouting', 'Yes')) {
            $cache = $this->bootstrap('cache')
                          ->getResource('cache');

            // Cache the routes configuration file to speed up processing
            if (!$routes = $cache->load('routes')) {
                $routes = new Zend_Config_Ini(APPLICATION_PATH . '/config/routes.ini');
                $cache->save($routes);
            }

            return $routes;
        }
    }

    /**
     * Initializes the Zend router to handle default routes (we want those
     * to fall back to WordPress) and to use the config/routes.ini config
     * file.
     *
     * @return Zend_Controller_Router_Rewrite
     */
    protected function _initRouter()
    {
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if ('Yes' == $wordPress->getCustomOption('bootstrapRouting', 'Yes')) {
            $routes = $this->bootstrap('routes')
                           ->getResource('routes');
            $router = $this->bootstrap('frontController')
                           ->getResource('frontController')
                           ->getRouter();

            // setup the router to better work with our module-less setup
            $router->removeDefaultRoutes()
                   ->setGlobalParam('module', 'default')
                   ->addConfig($routes);

            // this is where the routing takes place
            $this->bootstrap('wordPress')
                 ->getResource('wordPress')
                 ->addAction('send_headers');

            return $router;
        }
    }

    /**
     * Retrieves our initialized Zend_Config object and saves it in
     * the registry for easy access.
     *
     * @return Zend_Config
     */
    protected function _initConfig()
    {
        return new Zend_Config($this->getOptions());
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
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if ('Yes' == $wordPress->getCustomOption('bootstrapDatabase', 'Yes')) {
            $wordPress = $this->bootstrap('wordPress')
                              ->getResource('wordPress');

            if ($db = $wordPress->getDatabase()) {
                Zend_Db_Table_Abstract::setDefaultAdapter($db);
            }

            return $db;
        }
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
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        $config = $this->getOptions();

        switch($adapter = $wordPress->getCustomOption('cacheBackend', $this->_getBestCacheAdapter())) {
            case 'Zend_Cache_Backend_Xcache':
                $options = array();
                if ($user = $wordPress->getCustomOption('cacheXcacheUser')) {
                    $options['user'] = $user;
                }
                if ($password = $wordPress->getCustomOption('cacheXcachePassword')) {
                    $options['password'] = $password;
                }
                break;

            case 'Zend_Cache_Backend_Memcached':
                $options = array(
                    'servers' => array(array(
                        'host' => $wordPress->getCustomOption('cacheMemcacheHost', '127.0.0.1'),
                        'port' => $wordPress->getCustomOption('cacheMemcachePort', '11211'),
                    )),
                    'compression' => false,
                    'compatibility' => true,
                );
                break;

            case 'Zend_Cache_Backend_Libmemcached':
                $options = array('servers' => array(array(
                    'host' => $wordPress->getCustomOption('cacheMemcacheHost', '127.0.0.1'),
                    'port' => $wordPress->getCustomOption('cacheMemcachePort', '11211'),
                )));
                break;

            case 'Zend_Cache_Backend_Apc':
                $options = array();
                break;

            case 'Zend_Cache_Backend_Sqlite':
                $file = $wordPress->getCustomOption(
                    'cacheFile', 
                    tempnam(sys_get_temp_dir(), 'vulnero')
                );

                $options = array(
                    'cache_db_complete_path' => $file
                );
                break;

            case 'Zend_Cache_Backend_File':
            default:
                $dir = $wordPress->getCustomOption(
                    'cacheFile', 
                    tempnam(sys_get_temp_dir(), 'vulnero')
                );

                if (is_file($dir)) {
                    unlink($dir);
                }
                
                if (!is_dir($dir)) {
                    mkdir($dir, 0775);
                }

                $adapter = 'Zend_Cache_Backend_File';
                $options = array(
                    'file_locking' => false,
                    'cache_dir'    => $dir 
                );
                break;
        }

        $frontendOptions = isset($config['cache']['frontend'])
            ? $config['cache']['frontend']
            : array();

        if (!isset($frontendOptions['lifetime'])) {
            $frontendOptions['lifetime'] = $wordPress->getCustomOption('cacheTtl', 3600);
        }
        $frontendOptions['automatic_serialization'] = true;

        $adapter = str_replace('Zend_Cache_Backend_', '', $adapter);
        $cache = Zend_Cache::factory('Core', $adapter, $frontendOptions, $options);

        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        Zend_Date::setOptions(array('cache' => $cache));
        Zend_Locale::setCache($cache);

        return $cache;
    }

    /**
     * Attempts to identify the best caching option if none is yet 
     * set in the admin page.
     *
     * @return  string          Zend_Cache_Backend class name
     */
    protected function _getBestCacheAdapter()
    {
        if (extension_loaded('apc')) {
            return 'Zend_Cache_Backend_Apc';
        } elseif (extension_loaded('xcache')) {
            return 'Zend_Cache_Backend_Xcache';
        } elseif (extension_loaded('memcached')) {
            return 'Zend_Cache_Backend_Libmemcached';
        } elseif (extension_loaded('memcache')) {
            return 'Zend_Cache_Backend_Memcached';
        } elseif (extension_loaded('sqlite3')) {
            return 'Zend_Cache_Backend_Sqlite';
        } else {
            return 'Zend_Cache_Backend_File';
        }
    }

    /**
     * Can be overridden by a child class to implement admin panel functionality
     * by returning a Zend_Form object.
     *
     * @return  Vulnero_Form_Admin  $form
     */
    protected function _initAdmin()
    {
        $this->bootstrap('wordPress')
             ->getResource('wordPress')
             ->addAction('admin_menu');

        return null;
    }

    /**
     * Sets up global view parameters and defaults.
     *
     * @return  void
     */
    protected function _initViewSettings()
    {
        $view = $this->bootstrap('view')
                     ->getResource('view');

        $view->wordPress = $this->bootstrap('wordPress')
                                ->getResource('wordPress');

        return $view;
    }

    /********************************************************************************/
    //
    //          Begin WordPress API Callback Methods
    //
    /********************************************************************************/

    /**
     * WordPress plugins_loaded hook
     * Called when the plugin is loaded as part of the WordPress initialization.
     * We use this opportunity to load the wordpress user object into the
     * Zend_Auth identity.
     *
     * @return  void
     */
    public function onPluginsLoaded()
    {
        // inject the WordPress identity into the Zend_Auth singleton
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if ('Yes' == $wordPress->getCustomOption('bootstrapAuth', 'Yes')) {
            $adapter = new Vulnero_Auth_Adapter_WordPress($wordPress);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);

            // register an action helper to enforce any Acl requirements
            Zend_Controller_Action_HelperBroker::addHelper(new Vulnero_Controller_Action_Helper_Acl());
        }
    }

    /**
     * WordPress plugin activated
     * Called when the plugin is activated for the first time.
     *
     * @return  void
     */
    public function onPluginActivated()
    {
        $this->bootstrap('wordPress')
             ->getResource('wordPress')
             ->setCustomOption('attribution', true);
    }

    /**
     * WordPress wp_title filter
     * Change or alter the page title (if supported by the theme).
     *
     * @return  string
     */
    public function onWpTitle($title)
    {
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        // only set the title if we own the route
        if ($frontController->getParam('isVulneroRoute')) {
            $view = $this->bootstrap('view')
                         ->getResource('view');
            return strip_tags($view->headTitle()) . ' - ';
        } else {
            return $title;
        }
    }

    /**
     * WordPress wp_footer action
     * Allows us to inject dynamic content into the WordPress footer
     * (if supported by the theme).
     *
     * @return void
     */
    public function onWpFooter()
    {
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if ($wordPress->getCustomOption('attribution')) {
            echo '<p>Powered by <a href="http://www.vulnero.com/" target="_blank">Vulnero</a> '
                . 'and the <a href="http://framework.zend.com" target="_blank">Zend Framework</a>.';
        }
    }

    /**
     * WordPress wp_head action
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
     * WordPress widgets_init action
     * Registers plugin widgets
     *
     * @return  void
     */
    public function onWidgetsInit()
    {
        $cache     = $this->bootstrap('cache')
                          ->getResource('cache');
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');

        if (!$widgets = $cache->load('widgets')) {
            // Automatically detect and load any widget classes, caching the work
            $widgets = array();

            if (is_dir($path = APPLICATION_PATH . '/widgets/Widget')) {
                $di = new DirectoryIterator($path);
                foreach ($di as $item) {
                    if ($item->isFile() && substr($item->getFilename(), -4) == '.php') {
                        $widgets[] = 'Widget_' . substr($item->getFilename(), 0, -4);
                    }
                }
            }

            $cache->save($widgets, 'widgets');
        }

        foreach ($widgets as $widget) {
            $wordPress->registerWidget($widget);
        }
    }

    /**
     * WordPress page_template filter
     * We override the current template's page.php implementation only
     * if there's a page.php file present in the plugin directory and
     * we're rendering a Vulnero route.
     *
     * @return  void
     */
    public function onPageTemplate()
    {
        return $this->_onTemplate('page.php');
    }

    /**
     * WordPress *_template filter
     * Called when WordPress attempts to locate a template (e.g.: home.php) in the
     * theme. If this is a valid WordPress request, we're going to override it and
     * instead specify that the template is wordpress-template.php in the plugin's
     * directory.
     *
     * @param   string          Template type (e.g.: home, page)
     * @return  void
     */
    protected function _onTemplate($template)
    {
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');
        if (file_exists(PLUGIN_BASE_PATH . '/' . self::WORDPRESS_PAGE_TEMPLATE)) {
            return PLUGIN_BASE_PATH . '/' . self::WORDPRESS_PAGE_TEMPLATE;
        } else {
            return $wordPress->locateTemplate($template);
        }
    }

    /**
     * Returns TRUE if comments are open for the current post. We disable comments
     * for all Vulnero routes.
     *
     * @param   boolean     Open?
     * @return  boolean
     */
    public function onCommentsOpen($open)
    {
        return false;
    }

    /**
     * Returns TRUE if pings are open for the current post. We disable pings
     * for all Vulnero routes.
     *
     * @param   boolean     Open?
     * @return  boolean
     */
    public function onPingsOpen($open)
    {
        return false;
    }

    /**
     * WordPress expects a page to render the comments area. For Vulnero
     * routes, we override this to an empty file. Tacky, but it works.
     *
     * @return  string          File path
     */
    public function onCommentsTemplate()
    {
        return PLUGIN_BASE_PATH . '/empty-page.php';
    }

    /**
     * Disables the printing of next/previous page links on Vulnero
     * routes.
     *
     * @param   array               WordPress before/after, etc. strings
     * @return  array
     */
    public function onWpLinkPagesArgs(array $args)
    {
        $args['echo'] = 0;
        return $args;
    }

    /**
     * WordPress send_headers action
     * Create a new Zend_Controller_Request_Http object with the WordPress
     * route. Upon failure, we assume the route isn't handled and let WordPress
     * deal with it.
     *
     * @param   WordPress       WordPress object which contains the route
     * @return  void
     */
    public function onSendHeaders($wp)
    {
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');

        // Permalinks must be enabled in WordPress for request to be set
        if (isset($wp->request) && ($wpRequest = $wp->request)) {
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

                try {
                    $response = $frontController->dispatch();
                } catch (Zend_Controller_Router_Exception $e) {
                    // route not found, pass to WordPress to handle
                    throw $e;
                } catch (Exception $e) {
                    // Route found, exception thrown:
                    // We can't forward because the frontController has already dispatched the route, 
                    // so we have to forcibly load the error controller and route it outside of the
                    // regular dispatching.
                    $plugin = $frontController->getPlugin('Zend_Controller_Plugin_ErrorHandler');
                    $response = $plugin->getResponse();
                    $response->setException($e);
                    $plugin->postDispatch($request);

                    include_once(APPLICATION_PATH . '/controllers/ErrorController.php');
                    $controller = new ErrorController($request, $response, array());
                    $controller->run();
                }

                // Controller plugin injects content into the WordPress the_content() hook
                $frontController->setParam('response', $response)
                                ->setParam('isVulneroRoute', true);

                // Our layout is the WordPress page template
                $layout = $this->bootstrap('layout')
                               ->getResource('layout')
                               ->disableLayout();

                // Disable comments, links, pings and other display oddities for our own routes
                $wordPress->addFilter('comments_open')
                          ->addFilter('pings_open')
                          ->addFilter('comments_template')
                          ->addFilter('wp_link_pages_args')
                          ->addFilter('page_template')
                          ->addAction('the_content');

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

                    // get the id of the front page, we're going to pretend we're it
                    $pageId = $wordPress->getOption('page_on_front');

                    // trick WordPress into thinking the request is valid
                    $wp->request = $wpRoute;
                    $wp->query_string = '';
                    $wp->matched_rule = '(' . preg_quote($wpRoute) . ')(/.*)$';
                    $wp->matched_query = 'pagename=' . urlencode($wpRoute) . '&page=' . $pageId;
                    $wp->query_vars = array(
                        'page' => $pageId,
                        'pagename' => $wpRoute
                    );
                    $wp->extra_query_vars = array();
                } elseif (PHP_SAPI != 'cli') {
                    // Non-WordPress enabled route, end execution
                    echo $response->getBody();
                    exit(0);
                }
            } catch (Zend_Controller_Router_Exception $e) {
                // our application didn't answer the route, so it passes control
                // back to WordPress simply by doing nothing
                $frontController->setParam('isVulneroRoute', false);
            }

            return $frontController->getRequest();
        }
    }

    /**
     * WordPress the_content action
     * Display our rendered view if we handled the route, otherwise
     * just return the output as is.
     *
     * @param   string      Current content
     * @return  void
     */
    public function onTheContent($content)
    {
        $frontController = $this->bootstrap('frontController')
                                ->getResource('frontController');
        if ($frontController->getParam('isVulneroRoute')) {
            return $frontController->getParam('response')->getBody();
        } else {
            return $content;
        }
    }

    /**
     * WordPress admin_menu action
     * Scans the application/admin-pages directory for objects extending
     * Vulnero_AdminPage and registers them with WordPress by instantiating
     * them.
     *
     * @return void
     */
    public function onAdminMenu()
    {
        $cache     = $this->bootstrap('cache')
                          ->getResource('cache');
        $wordPress = $this->bootstrap('wordPress')
                          ->getResource('wordPress');

        if (!$pages = $cache->load('adminpages')) {
            // Automatically detect and load any admin page classes, caching the work
            $pages = array();

            if (is_dir($path = APPLICATION_PATH . '/admin-pages/AdminPage')) {
                $di = new DirectoryIterator($path);
                foreach ($di as $item) {
                    if ($item->isFile() && substr($item->getFilename(), -4) == '.php') {
                        $pages[] = 'AdminPage_' . substr($item->getFilename(), 0, -4);
                    }
                }
            }

            $cache->save($pages, 'adminpages');
        }

        foreach ($pages as $page) {
            $obj = new $page($this);
        }
    }
}
