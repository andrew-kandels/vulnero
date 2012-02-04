<?php
/**
 * Vulnero
 *
 * Middle-man for routing WordPress API requests in an object-oriented
 * fashion. The WordPress globals can be mocked for situations where
 * WordPress isn't directly invoked (such as unit tests or command
 * line scripts).
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

class Vulnero_WordPress
{
    /**
     * @var integer
     */
    const WP_OPTION_MAX_LENGTH = 64;

    /**
     * @var array
     */
    protected $_pluginData;

    /**
     * Mock options for get_bloginfo()
     * @var array
     */
    protected static $_blogInfo = array(
        'siteurl'               => 'http://localhost',
        'name'                  => 'Test',
        'description'           => 'Fake blog for testing.',
        'wpurl'                 => 'http://localhost',
        'siteurl'               => 'http://localhost',
        'admin_email'           => 'me@domain.com',
        'charset'               => 'utf8',
        'version'               => '1.0.0',
        'html_type'             => '',
        'text_direction'        => '',
        'language'              => 'en_US',
        'stylesheet_url'        => '',
        'stylesheet_directory'  => '',
        'template_url'          => '',
        'pingback_url'          => '',
        'tags'                  => '',
        'categories'            => '',
        'template'              => '',
    );

    /**
     * Mock options for get_options()
     * @var array
     */
    protected static $_options = array(
        'page_on_front'         => 1,
    );

    /**
     * Whether to mock the actual functions with convincing fakes.
     * This will be done automatically if PHP_SAPI mode is CLI,
     * which is the case for command-line scripts and unit tests.
     * @var boolean
     */
    protected $_isMock = false;

    /**
     * Delegate object for sending the WordPress callbacks.
     * @var Vulnero_Application_Bootstrap_Bootstrap
     */
    protected $_delegate;

    /**
     * Tracks if the activation hook has been registered.
     * @var boolean
     */
    protected $_activationHook = false;

    /**
     * Keeps track of mock filter injections.
     * @var array
     */
    protected $_filters = array();

    /**
     * Keeps track of mock action injections.
     * @var array
     */
    protected $_actions = array();

    /**
     * Keeps track of mock widget injections.
     * @var array
     */
    protected $_widgets = array();

    /**
     * Keeps track of mock sidebar injections.
     * @var array
     */
    protected $_sidebars = array();

    /**
     * Keeps track of injected admin pages.
     * @var array
     */
    protected $_adminPages = array();

    /**
     * Instantiate the class.
     *
     * @return  Vulnero_WordPress
     */
    public function __construct(Vulnero_Application_Bootstrap_Bootstrap $delegate = null)
    {
        $this->_delegate = $delegate;

        // unit tests, cli scripts
        if (PHP_SAPI == 'cli') {
            $this->_isMock = true;
        }
    }

    /**
     * Sets mock mode, which emulates the WordPress API responses for
     * testing and CLI scripts.
     *
     * @param   boolean             Enabled
     * @return  Vulnero_WordPress
     */
    public function setIsMock($mock)
    {
        $this->_isMock = (bool) $mock;
        return $this;
    }

    /**
     * WordPress register_activation_hook() function.
     *
     * @return  Vulnero_WordPress
     */
    public function registerActivationHook()
    {
        if ($this->_isMock) {
            $this->_activationHook = true;
        } elseif (!function_exists('register_activation_hook')) {
            throw new RuntimeException('WordPress register_activation_hook() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            register_activation_hook(
                PLUGIN_BASE_PATH . '/vulnero.php',
                $this->_getCallback('plugin_activated')
            );
        }

        return $this;
    }

    /**
     * WordPress add_action() function.
     * Callback is generated based on the action name, wp_head becomes onWpHead.
     *
     * @param   string          Action name
     * @param   mixed           Optional third parameter to add_action
     * @return  Vulnero_WordPress
     */
    public function addAction($action, $param = null)
    {
        if (is_object($param)) {
            $old = $this->getDelegate();
            $this->setDelegate($param);
            $param = null;
        }

        if ($this->_isMock) {
            $callBack = $this->_getCallback($action);
            $this->_actions[] = $callBack[1];
        } elseif (!function_exists('add_action')) {
            throw new RuntimeException('WordPress add_action() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            add_action($action, $this->_getCallback($action), $param);
        }

        if (isset($old)) {
            $this->_delegate = $old;
        }

        return $this;
    }

    /**
     * WordPress add_filter() function.
     * Callback is generated based on the filter name, wp_head becomes onWpHead.
     *
     * @param   string          Filter name
     * @return  Vulnero_WordPress
     */
    public function addFilter($filter)
    {
        if ($this->_isMock) {
            $callBack = $this->_getCallback($filter);
            $this->_filters[] = $callBack[1];
        } elseif (!function_exists('add_filter')) {
            throw new RuntimeException('WordPress add_filter() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            add_filter($filter, $this->_getCallback($filter));
        }

        return $this;
    }

    /**
     * Sets the delegate object which should receive action/filter callbacks.
     *
     * @param   Object
     * @return  Vulnero_AdminPage
     */
    public function setDelegate($obj)
    {
        $this->_delegate = $obj;
        return $this;
    }

    /**
     * Gets the delegate object which should receive action/filter callbacks.
     *
     * @return  Object
     */
    public function getDelegate()
    {
        return $this->_delegate;
    }

    /**
     * Converts a action/filter/hook from underscore notation
     * to a callback form.
     *
     * @param   string              Name
     * @return  string
     */
    protected function _getCallback($name)
    {
        $parts = explode('_', preg_replace('/-.*/', '', $name));
        return array(
            $this->_delegate,
            'on' . implode('', array_map(create_function('$a', 'return ucfirst($a);'), $parts))
        );
    }

    /**
     * WordPress get_sidebar() function.
     * Echoes sidebar content.
     *
     * @return  Vulnero_WordPress
     */
    public function getSidebar()
    {
        if ($this->_isMock) {
            // do nothing
        } elseif (!function_exists('get_sidebar')) {
            throw new RuntimeException('WordPress get_sidebar() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            get_sidebar();
        }

        return $this;
    }

    /**
     * WordPress register_widget() function.
     *
     * @param   string                  Class name
     * @return  Vulnero_WordPress
     */
    public function registerWidget($widget)
    {
        if ($this->_isMock) {
            $this->_widgets[] = $widget;
        } elseif (!function_exists('register_widget')) {
            throw new RuntimeException('WordPress register_widget() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            register_widget($widget);
        }
    }

    /**
     * In mock mode, items are saved as they are added and the WordPress
     * API is ignored as it likely doesn't exist. This returns a list of
     * the items in order of their registration.
     *
     * @return  boolean
     */
    public function hasActivationHook()
    {
        return $this->_activationHook;
    }

    /**
     * In mock mode, items are saved as they are added and the WordPress
     * API is ignored as it likely doesn't exist. This returns a list of
     * the items in order of their registration.
     *
     * @return  array
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * In mock mode, items are saved as they are added and the WordPress
     * API is ignored as it likely doesn't exist. This returns a list of
     * the items in order of their registration.
     *
     * @return  array
     */
    public function getWidgets()
    {
        return $this->_widgets;
    }

    /**
     * In mock mode, items are saved as they are added and the WordPress
     * API is ignored as it likely doesn't exist. This returns a list of
     * the items in order of their registration.
     *
     * @return  array
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * In mock mode, items are saved as they are added and the WordPress
     * API is ignored as it likely doesn't exist. This returns a list of
     * the items in order of their registration.
     *
     * @return  array
     */
    public function getSidebars()
    {
        return $this->_sidebars;
    }

    /**
     * WordPress get_bloginfo() function
     *
     * @param   string          Name
     * @return  string
     */
    public function getBlogInfo($name)
    {
        if ($this->_isMock) {
            if (isset(self::$_blogInfo[$name])) {
                return self::$_blogInfo[$name];
            } else {
                return null;
            }
        } elseif (!function_exists('get_bloginfo')) {
            throw new RuntimeException('WordPress get_bloginfo() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return get_bloginfo($name);
        }
    }

    /**
     * WordPress get_option() function
     *
     * @param   string          Name
     * @return  string
     */
    public function getOption($name)
    {
        if ($this->_isMock) {
            if (isset(self::$_options[$name])) {
                return self::$_options[$name];
            } else {
                return null;
            }
        } elseif (!function_exists('get_option')) {
            throw new RuntimeException('WordPress get_option() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return get_option($name);
        }
    }

    /**
     * WordPress get_theme_root() function
     *
     * @return string
     */
    public function getThemeRoot()
    {
        if ($this->_isMock) {
            return PLUGIN_BASE_PATH;
        } elseif (!function_exists('wp_get_post_categories')) {
            throw new RuntimeException('WordPress wp_get_post_categories() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return get_theme_root();
        }
    }

    /**
     * WordPress get_template() function
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->_isMock) {
            return array();
        } elseif (!function_exists('get_template')) {
            throw new RuntimeException('WordPress get_template() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return get_template();
        }
    }

    /**
     * WordPress get_tags() function
     *
     * @return array
     */
    public function getTags()
    {
        if ($this->_isMock) {
            return array();
        } elseif (!function_exists('get_tags')) {
            throw new RuntimeException('WordPress get_tags() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return get_tags();
        }

    }

    /**
     * WordPress get_category() function
     *
     * @param   name
     * @return  stdclass
     */
    public function getCategory($name)
    {
        if ($this->_isMock) {
            return new stdclass();
        } elseif (!function_exists('get_category')) {
            throw new RuntimeException('WordPress get_category() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return get_category($name);
        }
    }

    /**
     * WordPress wp_get_post_categories() function
     *
     * @return array
     */
    public function getPostCategories()
    {
        if ($this->_isMock) {
            return array();
        } elseif (!function_exists('wp_get_post_categories')) {
            throw new RuntimeException('WordPress wp_get_post_categories() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return wp_get_post_categories();
        }
    }

    /**
     * Returns the WordPress database connection. In mock mode, it creates a
     * temporary Sqlite database.
     *
     * @return  Zend_Db_Adapter_Abstract
     */
    public function getDatabase()
    {
        if ($this->_isMock && !defined('DB_HOST')) {
            $lines = file(PLUGIN_BASE_PATH . '/../../../wp-config.php');
            foreach ($lines as $index => $line) {
                if (preg_match('/define\(\'DB_([^\']+)\',\s*\'([^\']+)\'\);/', $line, $matches)) {
                    define('DB_' . $matches[1], $matches[2]);
                }
            }
        } elseif (!defined('DB_HOST')) {
            throw new RuntimeException('WordPress constant DB_HOST not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        }

        return Zend_Db::factory('Pdo_Mysql', array(
            'host'      => DB_HOST,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'dbname'    => DB_NAME
        ));
    }

    /**
     * WordPress locate_template function.
     *
     * @param   string          Template name
     * @return  string          Template path
     */
    public function locateTemplate($template)
    {
        if ($this->_isMock) {
            return array(realpath(PLUGIN_BASE_PATH . '/../../themes') . '/page.php');
        } elseif (!function_exists('locate_template')) {
            throw new RuntimeException('WordPress locate_template not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            // allow templates to exist in the plugin folder
            if (file_exists(PLUGIN_BASE_PATH . '/' . $template)) {
                return PLUGIN_BASE_PATH . '/' . $template;
            } else {
                return locate_template(array($template));
            }
        }
    }

    /**
     * WordPress apply_filters function.
     *
     * @param   string          Filter name
     * @param   string          Text to filter
     * @return  string
     */
    public function applyFilters($filter, $text)
    {
        if ($this->_isMock) {
            $this->_filters[] = $filter;
            return $text;
        } elseif (!function_exists('apply_filters')) {
            throw new RuntimeException('WordPress apply_filters not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return apply_filters($filter, $text);
        }
    }

    /**
     * WordPress add_menu_page function.
     *
     * @param   string              Page title
     * @param   string              Menu title
     * @param   string              Capability
     * @param   string              Menu slug
     * @param   string              Callback function
     * @param   string              Icon URL
     * @param   string              Position in the panel
     * @return  Vulnero_WordPress
     */
    public function addMenuPage($pageTitle, $menuTitle, $capability, $menuSlug,
        $callBack, $iconUrl, $position)
    {
        if ($this->_isMock) {
            return ($this->_adminPages[] = $menuSlug);
        } elseif (!function_exists('add_menu_page')) {
            throw new RuntimeException('WordPress add_menu_page not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return add_menu_page(
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                $callBack,
                $iconUrl,
                $position
            );
        }
    }

    /**
     * WordPress add_options_page function.
     *
     * @param   string              Page title
     * @param   string              Menu title
     * @param   string              Capability
     * @param   string              Menu slug
     * @param   string              Callback function
     * @return  string              Hook
     */
    public function addOptionsPage($pageTitle, $menuTitle, $capability, $menuSlug, $callBack)
    {
        if ($this->_isMock) {
            return ($this->_adminPages[] = $menuSlug);
        } elseif (!function_exists('add_options_page')) {
            throw new RuntimeException('WordPress add_options_page not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            return add_options_page(
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                $callBack
            );
        }
    }

    /**
     * Returns all registered admin and option pages.
     *
     * @return array
     */
    public function getAdminPages()
    {
        return $this->_adminPages;
    }

    /**
     * Prefix the name of the option with something plugin specific to sandbox
     * our settings from other plugins and WordPress internals.
     *
     * @param   string              Option name
     * @return  string              Sanitized name
     */
    protected function _getSanitizedOptionName($name)
    {
        $pluginDir = basename(PLUGIN_BASE_PATH);
        $name = $pluginDir . '_' . $name;

        if (strlen($name) > self::WP_OPTION_MAX_LENGTH) {
            throw new UnexpectedValueException('WordPress option length is limited to '
                . (self::WP_OPTION_MAX_LENGTH - strlen($pluginDir) - 1) . ' characters (prefix '
                . 'excluded).'
            );
        }

        return $name;
    }

    /**
     * WordPress get_option hook for retrieving
     * custom project-level options in WordPress which are persisted
     * to the WordPress database through its API.
     *
     * @param   string              Option name
     * @param   mixed               Value to return if no option is found, defaults to false
     * @return  mixed               Boolean false if not found
     */
    public function getCustomOption($name, $defaultValue = false)
    {
        $name = $this->_getSanitizedOptionName($name);

        if ($this->_isMock) {
            $db = $this->getDatabase()->getConnection();

            $stmt = $db->prepare('SELECT option_value FROM wp_options WHERE option_name = ?');
            $stmt->execute(array($name));
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $value = $row[0];
            } else {
                $value = null;
            }
        } elseif (!function_exists('get_option')) {
            throw new RuntimeException('WordPress get_option not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            $value = get_option($name);
        }

        if (!$value) {
            return $defaultValue;
        }

        if (preg_match('/^php:(.*)/', $value, $matches)) {
            return unserialize($matches[1]);
        } else {
            return $value;
        }
    }

    /**
     * WordPress add_option, update_option hook for adding or updating
     * custom project-level options in WordPress which are persisted
     * to the WordPress database through its API.
     *
     * @param   string              Option name
     * @param   mixed               Option value, serialized if not a scalar
     * @return  Vulnero_WordPress
     */
    public function setCustomOption($name, $value)
    {
        $name = $this->_getSanitizedOptionName($name);

        // serialize non-scalar values
        if (!is_scalar($value)) {
            $value = 'php:' . serialize($value);
        }

        if ($this->_isMock) {
            $db = $this->getDatabase()->getConnection();

            try {
                $stmt = $db->prepare('INSERT INTO wp_options (option_value, option_name) VALUES (?, ?)');
                $stmt->execute(array($value, $name));
            } catch (PDOException $e) {
                // duplicate key, update instead
                if ($e->getCode() == 23000) {
                    $stmt = $db->prepare('UPDATE wp_options SET option_value = ? WHERE option_name = ?');
                    $stmt->execute(array($value, $name));
                } else {
                    throw $e;
                }
            }
        } elseif (!function_exists('add_option')) {
            throw new RuntimeException('WordPress add_option not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } elseif (get_option($name) === false) {
            add_option($name, $value);
        } else {
            update_option($name, $value);
        }

        return $this;
    }

    /**
     * WordPress wp_get_current_user function
     * Returns the user record if logged in.
     *
     * @return  WP_User|boolean         User or FALSE if not logged in
     */
    public function getCurrentUser()
    {
        if ($this->_isMock) {
            return (object) array(
                'data'      => (object) array(
                                   'ID' => '1',
                                   'user_login'            => 'tester',
                                   'user_pass'             => 'randomhash',
                                   'user_nicename'         => 'tester',
                                   'user_email'            => 'tester@vulnero.com',
                                   'user_url'              => 'http://www.vulnero.com',
                                   'user_registered'       => '1997-01-01 12:00:00',
                                   'user_activation_key'   => null,
                                   'user_status'           => 0,
                                   'display_name'          => 'Mr. Tester',
                               ),
                'ID'        => 1,
                'caps'      => array(
                                   'administrator'         => 1
                               ),
                'cap_key'   => 'wp_capabilities',
                'roles'     => array(
                                   'administrator'
                               ),
                'allcaps'   => array(
                                   'switch_themes' => 1,
                                   'edit_themes' => 1,
                                   'activate_plugins' => 1,
                                   'edit_plugins' => 1,
                                   'edit_users' => 1,
                                   'edit_files' => 1,
                                   'manage_options' => 1,
                                   'moderate_comments' => 1,
                                   'manage_categories' => 1,
                                   'manage_links' => 1,
                                   'upload_files' => 1,
                                   'import' => 1,
                                   'unfiltered_html' => 1,
                                   'edit_posts' => 1,
                                   'edit_others_posts' => 1,
                                   'edit_published_posts' => 1,
                                   'publish_posts' => 1,
                                   'edit_pages' => 1,
                                   'read' => 1,
                                   'level_10' => 1,
                                   'level_9' => 1,
                                   'level_8' => 1,
                                   'level_7' => 1,
                                   'level_6' => 1,
                                   'level_5' => 1,
                                   'level_4' => 1,
                                   'level_3' => 1,
                                   'level_2' => 1,
                                   'level_1' => 1,
                                   'level_0' => 1,
                                   'edit_others_pages' => 1,
                                   'edit_published_pages' => 1,
                                   'publish_pages' => 1,
                                   'delete_pages' => 1,
                                   'delete_others_pages' => 1,
                                   'delete_published_pages' => 1,
                                   'delete_posts' => 1,
                                   'delete_others_posts' => 1,
                                   'delete_published_posts' => 1,
                                   'delete_private_posts' => 1,
                                   'edit_private_posts' => 1,
                                   'read_private_posts' => 1,
                                   'delete_private_pages' => 1,
                                   'edit_private_pages' => 1,
                                   'read_private_pages' => 1,
                                   'delete_users' => 1,
                                   'create_users' => 1,
                                   'unfiltered_upload' => 1,
                                   'edit_dashboard' => 1,
                                   'update_plugins' => 1,
                                   'delete_plugins' => 1,
                                   'install_plugins' => 1,
                                   'update_themes' => 1,
                                   'install_themes' => 1,
                                   'update_core' => 1,
                                   'list_users' => 1,
                                   'remove_users' => 1,
                                   'add_users' => 1,
                                   'promote_users' => 1,
                                   'edit_theme_options' => 1,
                                   'delete_themes' => 1,
                                   'export' => 1,
                                   'administrator' => 1,
                               ),
                'filter'    => null
            );
        } elseif (!function_exists('wp_get_current_user')) {
            throw new RuntimeException('WordPress wp_get_current_user not defined, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            $user = wp_get_current_user();

            // if not logged in we expect to see a false return
            if (!$user->ID) {
                return false;
            }

            return (object) $user;
        }
    }

    /**
     * Returns plugin data as it's registered with WordPress, which 
     * includes the version, name, author and so on
     *
     * @see     http://core.trac.wordpress.org/browser/tags/3.3.1//wp-admin/includes/plugin.php
     * @return  string      Version
     */
    public function getPluginData()
    {
        $pluginData = array();

        if ($this->_isMock) {
            $lines = file(PLUGIN_BASE_PATH . '/wordpress-plugin.php');
            $version = 'unknown';
            foreach ($lines as $line) {
                if (preg_match('/^Version: (.*)/', $line, $matches)) {
                    $version = trim($matches[1]);
                }
            }

            $pluginData = array(
                'Name' => 'vulnero',
                'PluginURI' => 'http://www.vulnero.com/',
                'Version' => $version,
                'Description' => 'WordPress Plugin',
                'Author' => 'Andrew P. Kandels',
                'AuthorURI' => 'http://andrewkandels.com/',
                'TextDomain' => 'Text Domain',
                'DomainPath' => 'Domain Path',
                'Network' => 'Network',
                // deprecated
                '_siteWide' => 'Site Wide Only',
            );
        } else {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            if (!function_exists('get_plugins')) {
                throw new RuntimeException('WordPress get_plugins not defined, '
                    . 'cannot execute Vulnero outside of WordPress environment.'
                );
            }

            $plugins = get_plugins('/' . plugin_basename(dirname(__FILE__) . '/../..'));
            if (isset($plugins['wordpress-plugin.php'])) {
                $pluginData = $plugins['wordpress-plugin.php'];
            }
        }

        return $pluginData;
    }
}

