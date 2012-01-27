<?php
/**
 * Vulnero
 *
 * Tests the DefaultController and its two default routes.
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

class Vulnero_Test_PHPUnit_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * @var Vulnero_Application_Bootstrap_Bootstrap
     */
    protected $_bootstrap;

    /**
     * @var Vulnero_Application
     */
    protected $_application;

    /**
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     * @var Vulnero_WordPress
     */
    protected $_wordPress;

    /**
     * Create a fresh application for every test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_application = new Vulnero_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/config/config.ini'
        );

        $this->bootstrap = array($this, 'appBootstrap');

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        parent::setUp();
    }

    /**
     * Zend will call this method each test to bootstrap our application.
     *
     * @return void
     */
    public function appBootstrap()
    {
        $this->_application->bootstrap();

        // keep track of a few convenience properties
        $this->_bootstrap       = $this->_application->getBootstrap();
        $this->_frontController = $this->_bootstrap->getResource('frontController');
        $this->_wordPress       = $this->_bootstrap->getResource('wordPress');

        $this->_bootstrap->onPluginsLoaded();
    }

    /**
     * Dispatch the MVC
     *
     * Simulate a WordPress request.
     *
     * If a URL is provided, sets it as the request URI in the request object.
     * Then sets test case request and response objects in front controller,
     * disables throwing exceptions, and disables returning the response.
     * Finally, dispatches the front controller.
     *
     * @param  string|null              URL to route to
     * @return stdclass                 WordPress simulating request
     */
    public function dispatch($url = null)
    {
        $wordpress = new stdclass();
        $wordpress->request = $url;

        $this->_request = $this->_bootstrap->onSendHeaders($wordpress);

        return $wordpress;
    }
}
