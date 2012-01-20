<?php
/**
 * Primary admin panel for the website.
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
class AdminPage_General extends Vulnero_AdminPage
{
    /**
     * Title displayed between the title tags
     * @var string
     */
    protected $_pageTitle   = 'Setup';

    /**
     * Menu title displayed on the left bar
     * @var string
     */
    protected $_menuTitle   = 'Vulnero';

    /**
     * Initializes certain object properties.
     *
     * @return void
     */
    protected function _init()
    {
        $this->setIconUrl(PROJECT_BASE_URI . '/public/images/admin-icon.png')
             ->setPosition(3)
             ->setType(Vulnero_AdminPage::ADMIN_MENU);
    }

    /**
     * Renders the contents of the widget in its view. The widget itself
     * serves as a controller.
     *
     * @return  void
     */
    public function displayAction()
    {
        $config = $this->_bootstrap->getOptions();

        $form = new Form_AdminGeneral();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $values = $form->getValues();
        } else {
            $form->populate($this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->version = VULNERO_VERSION;
    }
}
