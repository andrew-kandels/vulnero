<?php
/**
 * Abstract object representation of a WordPress widget.
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
abstract class Vulnero_Widget extends WP_Widget
{
    /**
     * Widget name.
     * @var string
     */
    protected $_name;

    /**
     * Whether to print and filter the widget wrappers from WordPress such
     * as before_widget and such.
     * @var boolean
     */
    protected $_wrappers = true;

    /**
     * The rendered content, typically from a controller's view.
     * @var string
     */
    protected $_content = '';

    /**
     * Options form to render in the WordPress administration panel.
     * @var Zend_Form
     */
    protected $_form;

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
     * @param   string          Name of the widget (e.g.: About Vulnero)
     * @param   string          Description (shown in the administration panel to describe it)
     * @return  Vulnero_Widget
     */
    public function __construct($name, $description = '')
    {
        parent::WP_Widget(
            strtolower(get_class($this)),
            $this->_name = $name,
            array('description' => $description)
        );

        $frontController = Zend_Controller_Front::getInstance();
        $this->view = clone $frontController->getParam('view');
        $this->view->setScriptPath(APPLICATION_PATH . '/views/scripts/widgets');
    }

    /**
     * Gets the rendered output content to be displayed.
     *
     * @return  string              Content
     */
    public function getContent()
    {
        $name = str_replace('_', '-', strtolower(get_class()));
        return $this->view->render($name . '.phtml');
    }

    /**
     * Injects a form to be displayed in the WordPress administration panel.
     *
     * @param   Zend_Form       Form object
     * @return  Vulerno_Widget
     */
    public function setForm(Zend_Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Gets the form to be displayed in the WordPress administration panel.
     *
     * @return  Zend_Form       Form object
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Sets whether or not to print and filter the wrappers from WordPress such
     * as before_widget or widget_title.
     *
     * @param   boolean         New value
     * @return  Vulnero_Widget
     */
    public function setWrappers($b)
    {
        $this->_wrappers = (boolean) $b;
        return $this;
    }

    /**
     * Returns whether or not to print and filter the wrappers from WordPress such
     * as before_widget or widget_title.
     *
     * @return  boolean
     */
    public function getWrappers()
    {
        return $this->_wrappers;
    }

    /**
     * Echo the widget content.
     *
     * @param   array $args     Display arguments including before_title, after_title,
                                before_widget, and after_widget.
     * @param   array $instance The settings for the particular instance of the widget
     * @return  void
     */
    public final function widget(array $args, array $instance)
    {
        $stack = array();

        if ($this->_wrappers) {
            $stack[] = $args['before_widget'];

            if ($title = apply_filters('widget_title', $this->_name)) {
                $stack[] = $args['before_title'];
                $stack[] = $title;
                $stack[] = $args['after_title'];
            }
        }

        $stack[] = $this->getContent();

        if ($this->_wrappers) {
            $stack[] = $args['after_title'];
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
        if ($this->_form) {
            $this->_form->setDefaults($settings);
            echo $this->_form;
        }
    }
}
