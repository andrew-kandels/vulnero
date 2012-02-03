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
        $this->setIconUrl(PLUGIN_BASE_URI . '/public/images/admin-icon.png')
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
        $form = new Form_AdminGeneral();

        if ($this->_request->isPost()) {
            $this->_onUpdate($form);
        }

        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $form->setDefaultValues($wordPress);

        $this->view->form = $form;
        $data = $wordPress->getPluginData();
        $this->view->version = $data['Version'];
    }

    /**
     * Process a form update.
     *
     * @param   Form_AdminGeneral               Form object
     * @return  void
     */
    protected function _onUpdate(Form_AdminGeneral $form)
    {
        // don't remove elements from the actual form so we can render them all
        // if it's invalid
        $form = clone $form;

        switch ($this->_request->getParam('cacheBackend')) {
            case 'Zend_Cache_Backend_Apc':
                if (!extension_loaded('apc')) {
                    throw new UnexpectedValueException('apc extension is not loaded');
                }
                $form->removeElement('cacheMemcacheHost');
                $form->removeElement('cacheMemcachePort');
                $form->removeElement('cacheFile');
                $form->removeElement('cacheXcacheUser');
                $form->removeElement('cacheXcachePassword');
                break;

            case 'Zend_Cache_Backend_Xcache':
                if (!extension_loaded('xcache')) {
                    throw new UnexpectedValueException('xcache extension is not loaded');
                }
                $form->removeElement('cacheMemcacheHost');
                $form->removeElement('cacheMemcachePort');
                $form->removeElement('cacheFile');
                break;

            case 'Zend_Cache_Backend_Memcached':
                if (!extension_loaded('memcache')) {
                    throw new UnexpectedValueException('memcache extension is not loaded');
                }
                $form->removeElement('cacheFile');
                $form->removeElement('cacheXcacheUser');
                $form->removeElement('cacheXcachePassword');
                break;

            case 'Zend_Cache_Backend_Libmemcached':
                if (!extension_loaded('memcached')) {
                    throw new UnexpectedValueException('memcached extension is not loaded');
                }
                $form->removeElement('cacheFile');
                $form->removeElement('cacheXcacheUser');
                $form->removeElement('cacheXcachePassword');
                break;

            case 'Zend_Cache_Backend_Sqlite':
                if (!extension_loaded('sqlite3')) {
                    throw new UnexpectedValueException('sqlite3 extension is not loaded');
                }
            case 'Zend_Cache_Backend_File':
            default:
                $form->removeElement('cacheMemcacheHost');
                $form->removeElement('cacheMemcachePort');
                $form->removeElement('cacheXcacheUser');
                $form->removeElement('cacheXcachePassword');
                break;
        }

        if (!$form->isValid($this->_request->getPost())) {
            return false;
        }

        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $values = $form->getValues();

        foreach ($values as $key => $value) {
            if (preg_match('/^bootstrap/', $key)) {
                $value = $value ? 'Yes' : 'No';
            }

            $wordPress->setCustomOption($key, $value);
        }

        return true;
    }
}
