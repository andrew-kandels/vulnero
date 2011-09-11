<?php
/**
 * Vulnero controller plugin which injects the contents of the Zend_View
 * from the layout object into the Wordpress page template.
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
class Vulnero_Controller_Plugin_Router extends Zend_Controller_Plugin_Abstract
{
    /**
     * Called before Zend_Controller_Front calls on the router to evaluate the
     * request against the registered routes. Registers a callback for the WordPress
     * the_content() function, which is evaluated in most Wordpress page templates.
     *
     * @param   Zend_Controller_Request_Abstract
     * @return  void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        add_action('the_content', array($this, 'onTheContent'));
    }

    /**
     * Called from most WordPress page templates to retrieve the contents of the
     * body. We pass in our layout object's content placeholder which contains the
     * contents of our Zend_View.
     *
     * @return  string      Page body
     */
    public function onTheContent()
    {
        $layout = Zend_Layout::getMvcInstance();
        return $layout->content;
    }
}
