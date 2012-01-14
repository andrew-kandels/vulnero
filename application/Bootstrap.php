<?php
/**
 * Bootstraps your application by inheriting the Vulnero bootstrap
 * which sets up the core functionality.
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
class Bootstrap extends Vulnero_Application_Bootstrap_Bootstrap
{
    /**
     * Called each request when the plugin is loaded.
     *
     * @return  void
     */
    public function onPluginLoaded()
    {
        parent::onPluginLoaded();
    }

    /**
     * Called when the plugin is activated for the first time from
     * the WordPress administration panel.
     *
     * @return  void
     */
    public function onPluginActivated()
    {
        parent::onPluginActivated();
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

        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');
        $view->headTitle('My Project');
        $view->headTitle()->setSeparator(' - ');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8')
                         ->appendHttpEquiv('Content-Language', 'en_US');
    }

    /**
     * The following sections can be uncommented to entirely replace the
     * WordPress hooks from being registered. If you're looking to extend
     * or override functionality, declare one of the public on... methods
     * instead with a corresponding name (e.g.: _initWpHead becomes onWpHead).
     */
    // protected function _initWordPress() {}
    // protected function _initPageTemplate() {}
    // protected function _initWpFooter() {}
    // protected function _initWpHead() {}
    // protected function _initWpTitle() {}
    // protected function _initWpPrintStyles() {}
    // protected function _initConfig() {}
    // protected function _initRoutes() {}
    // protected function _initRouter() {}
    // protected function _initDb() {}
    // protected function _initAuthAdapter() {}
    // protected function _initCache() {}
    // protected function _initAdmin() {}
}
