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
     * Keeps track of mock activation hook injections.
     * @var array
     */
    protected $_activationHooks = array();

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
     * WordPress register_activation_hook() function.
     *
     * @param   string          File
     * @param   string          Function in File
     * @return  Vulnero_WordPress
     */
    public function registerActivationHook($file, $func)
    {
        if ($this->_isMock) {
            $this->_activationHooks[] = array(
                'file' => $file,
                'func' => $func
            );
        } elseif (!function_exists('register_activation_hook')) {
            throw new RuntimeException('WordPress register_activation_hook() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            register_activation_hook($file, $func);
        }

        return $this;
    }

    /**
     * WordPress add_action() function.
     * Callback is generated based on the action name, wp_head becomes onWpHead.
     *
     * @param   string          Action name
     * @param   mixed           Optional third parameter to add_action, such as priority
     * @return  Vulnero_WordPress
     */
    public function addAction($action, $param = null)
    {
        if ($this->_isMock) {
            $this->_actions[] = $action;
        } elseif (!function_exists('add_action')) {
            throw new RuntimeException('WordPress add_action() not detected, '
                . 'cannot execute Vulnero outside of WordPress environment.'
            );
        } else {
            add_action($action, $this->_getCallback($action), $param);
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
            $this->_filters[] = $filter;
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
     * Converts a action/filter/hook from underscore notation
     * to a callback form.
     *
     * @param   string              Name
     * @return  string
     */
    protected function _getCallback($name)
    {
        $parts = explode('_', $name);
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
            return '';
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
            $this->_widgets[] = new $widget();
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
     * @return  array
     */
    public function getActivationHooks()
    {
        return $this->_activationHooks;
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
            return PROJECT_BASE_PATH;
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
}
