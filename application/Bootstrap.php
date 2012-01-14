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
     * Note: Adding scripts or stylesheets to the head*() variety of methods
     * will only be rendered on Vulnero routes. To globally add stylesheets,
     * using the
     *
     * @return  void
     */
    protected function _initViewSettings()
    {
        $view = $this->bootstrap('view')
                     ->getResource('view');

        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');

        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8')
                         ->appendHttpEquiv('Content-Language', 'en_US');
    }

    /**
     * Registers and queues CSS stylesheets globally. If the stylesheet is
     * used only by your Vulnero routes, it's recommended to use the
     * headLink() view helper in your controller or viewSettings bootstrap.
     *
     * If no path information is provided they are assumed to be located in
     * your plugin's public/styles directory.
     *
     * @return  array
     */
    protected function _initStylesheets()
    {
        return array();
    }

    /**
     * Registers and queues JavaScript scripts globally. If the script is
     * used only by your Vulnero routes, it's recommended to use the
     * headScript() view helper in your controller or viewSettings bootstrap.
     *
     * If no path information is provided they are assumed to be located in
     * your plugin's public/scripts directory.
     *
     * @return  array
     */
    protected function _initScripts()
    {
        return array();
    }
}
