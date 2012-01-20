<?php
/**
 * Abstract object representation of a WordPress widget.
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
abstract class Vulnero_Widget extends WP_Widget implements Vulnero_Widget_Interface
{
    /**
     * Widget title (defined in the parent class).
     * @var string
     */
    protected $_title;

    /**
     * Widget description (optional).
     * @var string
     */
    protected $_description = '';

    /**
     * Whether to print and filter the widget wrappers from WordPress such
     * as before_widget and such.
     * @var boolean
     */
    protected $_drawWrappers = true;

    /**
     * Whether to print the title when rendering the widget.
     * @var boolean
     */
    protected $_drawTitle = true;

    /**
     * Stores the Zend_Application bootstrap for retrieving views and
     * other bootstrapped items outside of the application flow.
     * @var Vulnero_Application_Bootstrap_Bootstrap
     */
    protected $_bootstrap;

    /**
     * The view object to render the widget's contents. It should be
     * placed in application/views/scripts/widgets/your-widget-name.phtml.
     * Inherits properties from your bootstrapped view automatically.
     * @var Zend_View
     */
    protected $view;

    /**
     * Instantiates a new widget and registers it with WordPress.
     *
     * @return  Vulnero_Widget
     */
    public function __construct()
    {
        parent::WP_Widget(
            strtolower(get_class($this)),
            $this->_title,
            array('description' => $this->_description)
        );

        $frontController  = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $frontController->getParam('bootstrap');
        $this->view       = clone $this->_bootstrap->bootstrap('view')->getResource('view');
        $this->view->setScriptPath(APPLICATION_PATH . '/views/scripts/widgets');

        $this->_init();
    }

    /**
     * Returns the widget's view object.
     *
     * @return  Zend_View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Returns the bootstrap for requesting resources.
     *
     * @return Vulnero_Application_Bootstrap_Bootstrap
     */
    public function getBootstrap()
    {
        return $this->_bootstrap;
    }

    /**
     * Returns the page request URI string.
     *
     * @return  string|boolean          False on failure, request uri on success
     */
    protected function _getRequestUri()
    {
        return isset($_SERVER['REQUEST_URI'])
            ? $_SERVER['REQUEST_URI']
            : false;
    }

    /**
     * Called when the widget has been initialized with WordPress.
     *
     * @return  void
     */
    protected function _init()
    {
        // optionally implemented by parent class
    }

    /**
     * Gets the rendered output content to be displayed.
     *
     * @return  string              Content
     */
    public function getContent()
    {
        // Remove the prefixing widget text
        $name = str_replace('Widget_', '', get_class($this));

        // Convert hungarian notation to underscore
        $name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);

        // Convert any nesting to hyphens for file naming
        $name = str_replace('_', '-', strtolower($name));

        // Allow the parent to initialize the view like a controller
        $this->displayAction();

        // Append the view extension
        $config = $this->_bootstrap->getOptions();
        return $this->view->render($name . '.' . $config['resources']['layout']['viewSuffix']);
    }

    /**
     * Sets whether the title is drawn above the widget. Wrappers must
     * be drawn for this to occur.
     *
     * @param   string
     * @return  Vulnero_Widget
     */
    public function setDrawTitle($b)
    {
        $this->_drawTitle = $b;
        return $this;
    }

    /**
     * Sets whether or not to print and filter the wrappers from WordPress such
     * as before_widget or widget_title.
     *
     * @param   boolean         New value
     * @return  Vulnero_Widget
     */
    public function setDrawWrappers($b)
    {
        $this->_drawWrappers = (boolean) $b;
        return $this;
    }

    /**
     * Echo the widget content.
     *
     * @param   array $args     Display arguments including before_title, after_title,
                                before_widget, and after_widget.
     * @param   array $instance The settings for the particular instance of the widget
     * @return  void
     */
    public function widget(array $args, array $instance)
    {
        if (!$this->_isShown()) {
            return;
        }

        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');

        $stack = array();

        if ($this->_drawWrappers) {
            $stack[] = $args['before_widget'];

            if ($this->_drawTitle && $title = $wordPress->applyFilters('widget_title', $this->_title)) {
                $stack[] = $args['before_title'];
                $stack[] = $title;
                $stack[] = $args['after_title'];
            }
        }

        $stack[] = $this->getContent();

        if ($this->_drawWrappers) {
            $stack[] = $args['after_widget'];
        }

        echo implode('', $stack);
    }

    /**
     * An administrative user is saving options for our widget.
     * Typically this is overriden and handles validation of user-generated
     * values.
     *
     * @param   array       Changed settings
     * @param   array       Current settings
     * @return  array       Final settings
     */
    public function update(array $newSettings, array $oldSettings)
    {
        foreach ($newSettings as $key => $value) {
            $oldSettings[$key] = $newSettings[$key];
        }

        return $oldSettings;
    }

    /**
     * If a Zend_Form object was given to the constructor then display
     * it in the WordPress administration panel to configure our widget.
     *
     * @param   array       Current settings
     * @return  void
     */
    public function form(array $settings)
    {
        // @todo
    }

    /**
     * Whether the widget should be displayed and rendered. Typically some logic
     * is performed to qualify the widget for the current route.
     *
     * @return  boolean
     */
    protected function _isShown()
    {
        return true;
    }
}
