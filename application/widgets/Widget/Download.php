<?php
/**
 * Widget which links to download and to view the source on GitHub.
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
class Widget_Download extends Vulnero_Widget
{
    /**
     * @var string
     */
    protected $_title       = 'Download';

    /**
     * @var string
     */
    protected $_description = 'Buttons for downloading the Vulnero plugin and for viewing it on GitHub.';

    /**
     * Renders the contents of the widget in its view. The widget itself
     * serves as a controller.
     *
     * @param   array               WordPress widget settings
     * @return  void
     */
    public function displayAction(array $settings)
    {
        $config = $this->_bootstrap->getOptions();

        if (isset($settings['title'])) {
            $this->_title = $settings['title'];
        }

        $this->view->githubUrl = $config['github']['url'];
        $this->view->zipFile = $config['github']['zip']['url'];
        $data = $this->_bootstrap->bootstrap('wordPress')
                                 ->getResource('wordPress')
                                 ->getPluginData();
        $this->view->version = $data['Version'];
    }

    /**
     * Renders the setup form which appears under the widget title when
     * drug onto a sidebar in the WordPress administration panel's widget
     * setup area.
     *
     * @param   array           Existing setting values
     * @return  void
     */
    public function setupAction(array $settings)
    {
        $this->view->settings = $settings;
    }
}
