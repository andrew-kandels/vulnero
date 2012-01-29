<?php
/**
 * Documentation widget which displays the table of contents from the
 * documentation page.
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
class Widget_Documentation extends Vulnero_Widget
{
    /**
     * @var string
     */
    protected $_title       = 'Documentation';

    /**
     * @var string
     */
    protected $_description = 'Displays the table of contents for Vulerno\'s documentation.';

    /**
     * Widget setup.
     */
    protected function _init()
    {
        if (isset($settings['title'])) {
            $this->_title = $settings['title'];
        }

        if ($page = get_page_by_path('documentation')) {
            $start = strpos($page->post_content, '<dl>');
            $end = strpos($page->post_content, '</dl>');
            $this->view->doc = '<dl class="doc-widget">' . substr($page->post_content, $start + 4, $end - $start + 5);
        } else {
            $this->view->doc = '';
        }

        $request = $this->_getRequestUri();
        $this->view->doc = str_replace(
            '<a href="' . $request . '">', 
            '<a href="' . $request . '" class="active">',
            $this->view->doc
        );
    }

    /**
     * Gets the request URI.
     *
     * @return string
     */
    protected function _getRequestUri()
    {
        // kill trailing slash
        $request = preg_replace('!/$!', '', parent::_getRequestUri());

        // most pages prefix with the category which can be stripped
        $request = str_replace('/documentation', '', $request);

        return $request;
    }

    /**
     * Don't show the documentation widget on the actual documentation page.
     *
     * @return boolean
     */
    protected function _isShown()
    {
        return preg_match('!^/documentation/.!', parent::_getRequestUri());
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
