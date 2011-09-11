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
class Vulnero_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
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

        add_action('send_headers', array($this, 'onSendHeaders'));
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
        if ($wpRequest = $wordpress->request) {
            $frontController = $this->bootstrap('frontController')
                                    ->getResource('frontController');
            $zfRequest       = $frontController->getRequest();
            $router          = $this->bootstrap('router')
                                    ->getResource('router');

            $uri             = get_bloginfo('siteurl') . '/' . $wpRequest;
            $uriObj          = Zend_Uri::factory($uri);

            $frontController->setRequest(
                new Zend_Controller_Request_Http($uriObj)
            );

            try {
                $frontController->dispatch();
                exit(0);
            } catch (Zend_Controller_Router_Exception $e) {
                // let WordPress handle the route
            }
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
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
        return $config;
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
        $router = $this->bootstrap('frontController')
                       ->getResource('frontController')
                       ->getRouter();
        $router->removeDefaultRoutes();
        $router->addConfig(
            new Zend_Config_Ini(APPLICATION_PATH . '/config/routes.ini')
        );

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

        Zend_Layout::startMvc(array(
            'layoutPath' => sprintf('%s/%s', get_theme_root(), get_template())
        ));

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
        $db = Zend_Db::factory('Pdo_Mysql', array(
            'host'      => DB_HOST,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'dbname'    => DB_NAME
        ));

        Zend_Registry::set('db', $db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);

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
        $db     = $this->bootstrap('db')
                       ->getResource('db');
        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $authAdapter->setTableName($config->wordpress->tablePrefix . 'users')
                    ->setIdentityColumn('user_login')
                    ->setCredential('user_pass')
                    ->setCredentialTreatment('MD5(?)');

        return $authAdapter;
    }
}
