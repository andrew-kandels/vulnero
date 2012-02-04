<?php
/**
 * Vulnero error controller which formats exceptions for display
 * output.
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
class ErrorController extends Zend_Controller_Action
{
    /**
     * Called from the application upon discovering an exception.
     *
     * @return  void
     */
    public function errorAction()
    {
        $content = null;
        $errors = $this->_getParam('error_handler');
        $exception = $errors->exception;
        $this->view->assign('exception', $exception);

        switch ($errors->type)
        {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                $this->view->message = <<<EOH
<h1>404 Page not found</h1>
<p>The page you requested was not found.</p>
EOH;
                break;

            default:
                // application error; display error page, but don't change
                // status code
                $this->view->message = <<<EOH
<h1>Error</h1>
<p>An unexpected error occurred with your request. Please try again later.</p>
EOH;
                break ;
        }

        // Clear previous content
        $this->getResponse()->clearBody();
        $this->view->content = $content;
        $this->view->formattedException = $this->view->exception;

        $this->view->showDetails = $this->_helper->acl->hasRole('manage_options') ||
            APPLICATION_ENV != 'production';
    }

    /**
     * Highlights source files within our application to make them easy to
     * visually separate from Zend library lines.
     *
     * @param   string      Backtrace string
     * @return  string
     */
    private function _formatBacktrace($value)
    {
        $value = preg_replace(
            '!.*/application/.*!',
            '<span class="highlight">$0</span>',
            $value
        );

        return $value;
    }
}
