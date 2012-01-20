<?php
/**
 * Abstract object representation of a WordPress admin page.
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

abstract class Vulnero_AdminPage implements Vulnero_AdminPage_Interface
{
    /**
     * Options style (default), displays in the options area
     * @var integer
     */
    const ADMIN_OPTIONS = 1;

    /**
     * Menu style, displays with its own bar.
     * @var integer
     */
    const ADMIN_MENU = 2;

    /**
     * Page (required) The text to be displayed in the title tags
     * of the page when the menu is selected
     * @var string
     */
    protected $_pageTitle;

    /**
     * Menu title (required) The on-screen name text for the menu
     * @var string
     */
    protected $_menuTitle;

    /**
     * Capability (required) The capability required for this menu
     * to be displayed to the user. For a list, see:
     *
     * http://codex.wordpress.org/Function_Reference/add_menu_page
     * @var string
     */
    protected $_capability = 'manage_options';

    /**
     * Unique identifier for the plugin. Defaults to class name.
     * @var string
     */
    protected $_menuSlug;

    /**
     * Url to a 16x16 icon file to display for the plugin.
     *
     * @var string
     */
    protected $_iconUrl;

    /**
     * Position in the admin panel bar.
     * @var integer
     */
    protected $_position;

    /**
     * Either ADMIN_OPTIONS (default) or ADMIN_MENU class constant
     * to determine how the page is displayed in the panel.
     * @var integer
     */
    protected $_type;

    /**
     * Bootstrap object for retrieving resources.
     * @var Vulnero_Application_Bootstrap_Bootstrap
     */
    protected $_bootstrap;

    /**
     * View script which is rendered to draw the admin page content.
     * @var Zend_View
     */
    protected $view;

    /**
     * Simulated request object for fetching post parameters and
     * processing forms.
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Rendered content, stored so view scripts can inject head*() items.
     * @var string
     */
    protected $_content;

    /**
     * Unique id dynamically assigned to the page by WordPress.
     * @var string
     */
    protected $_hook;

    /**
     * Constructs the object.
     *
     * @param   Vulnero_Application_Bootstrap_Bootstrap
     * @return  Vulnero_AdminPage
     */
    public function __construct(Vulnero_Application_Bootstrap_Bootstrap $bootstrap)
    {
        $this->_bootstrap = $bootstrap;

        $this->_request = new Zend_Controller_Request_Http();

        $this->view = clone $this->_bootstrap->bootstrap('view')
                                             ->getResource('view');
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()->exchangeArray(array());
        $this->view->headMeta()->exchangeArray(array());
        $this->view->headStyle()->exchangeArray(array());
        $this->view->setScriptPath(APPLICATION_PATH . '/views/scripts/admin-pages');

        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->_init();

        if (!$this->_menuSlug) {
            $this->_menuSlug = get_class($this);
        }

        switch ($this->_type) {
            case self::ADMIN_MENU:
                $this->_hook = $wordPress->addMenuPage(
                    $this->_pageTitle,
                    $this->_menuTitle,
                    $this->_capability,
                    $this->_menuSlug,
                    array($this, 'onRender'),
                    $this->_iconUrl,
                    $this->_position
                );
                break;

            case self::ADMIN_OPTIONS:
            default:
                $this->_type = self::ADMIN_OPTIONS;
                $this->_hook = $wordPress->addOptionsPage(
                    $this->_pageTitle,
                    $this->_menuTitle,
                    $this->_capability,
                    $this->_menuSlug,
                    array($this, 'onRender')
                );
                break;
        }

        $this->_content = $this->getContent();

        $wordPress->addAction('admin_head-' . $this->_hook, $this);
    }

    /**
     * Retrieves the WordPress assigned hook.
     *
     * @return string
     */
    public function getHook()
    {
        return $this->_hook;
    }

    /**
     * Prints the view's head* tags in the admin panel for your page.
     *
     * @return void
     */
    public function onAdminHead()
    {
        $components = array(
            $this->view->headMeta(),
            $this->view->headStyle(),
            $this->view->headLink(),
            $this->view->headScript()
        );

        echo implode(PHP_EOL, $components);
    }

    /**
     * Called to render the admin page view invoking the view
     * script and returning the output.
     *
     * @return string
     */
    public function getContent()
    {
        // Remove the prefixing widget text
        $name = str_replace('AdminPage_', '', get_class($this));

        // Convert hungarian notation to underscore
        $name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);

        // Convert any nesting to hyphens for file naming
        $name = str_replace('_', '-', strtolower($name));

        $config = $this->_bootstrap->getOptions();

        // Allow the parent to initialize the view like a controller
        $this->displayAction();

        return $this->view->render($name . '.' . $config['resources']['layout']['viewSuffix']);
    }

    /**
     * Renders the admin page and echoes the output.
     *
     * @return void
     */
    public function onRender()
    {
        echo $this->_content;
    }

    /**
     * Sets the position in the menu order this menu should appear. By
     * default, if this parameter is omitted, the menu will appear
     * at the bottom of the menu structure. The higher the number,
     * the lower its position in the menu. WARNING: if 2 menu items
     * use the same position attribute, one of the items may be
     * overwritten so that only one item displays!
     *
     * @param   integer                 Position, defaults to bottom of menu
     * @return  Vulnero_AdminPage
     */
    public function setPosition($position)
    {
        $this->_position = $position;
        return $this;
    }

    /**
     * Sets the icon url displayed on the left bar.
     *
     * @param   string          URL
     * @return  Vulnero_AdminPage
     */
    public function setIconUrl($url)
    {
        $this->_iconUrl = $url;
        return $this;
    }

    /**
     * Sets the type of menu to one of the ADMIN_* constants.
     *
     * @param   integer         Type, menu or options (default)
     * @return  Vulnero_AdminPage
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Initialization
     *
     * @return void
     */
    protected function _init()
    {
        // can be overridden in the parent class
    }
}
