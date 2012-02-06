<?php
/**
 * Vulnero
 *
 * Helper to facilitate setting Acl requirements in
 * a controller and have those be passed to the
 * Vulnero_Controller_Plugin_Acl controller plugin.
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

class Vulnero_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Vulnero_Controller_Plugin_Acl
     */
    protected $_pluginAcl;

    /**
     * Constructor
     *
     * Register action stack plugin
     *
     * @return  Vulnero_Controller_Action_Helper
     */
    public function __construct()
    {
        $front = Zend_Controller_Front::getInstance();
        if (!$front->hasPlugin('Vulnero_Controller_Plugin_Acl')) {
            $this->_pluginAcl = new Vulnero_Controller_Plugin_Acl();
            $front->registerPlugin($this->_pluginAcl);
        } else {
            $this->_pluginAcl = $front->getPlugin('Vulnero_Controller_Plugin_Acl');
        }
    }

    /**
     * Sets a role requirement for the current action. If the requirement
     * isn't met by the current logged in user then they will be redirected
     * to the deny(module|controller|action).
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param   string          WordPress user role
     * @return  Vulnero_Controller_Action_Helper
     */
    public function assertHasRole($role)
    {
        $this->_pluginAcl->setRole($role);
        return $this;
    }

    /**
     * Adds a capability requirement for the current action. If the requirement
     * isn't met by the current logged in user then they will be redirected
     * to the deny(module|controller|action).
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param   string          WordPress capability
     * @return  Vulnero_Controller_Action_Helper
     */
    public function assertHasCapability($capability)
    {
        $this->_pluginAcl->addCapability($capability);
        return $this;
    }


    /**
     * Sets the module the Vulnero_Controller_Plugin_Acl should
     * redirect to if any of the access control list logic fails.
     *
     * @param   string              Module name
     * @return  Vulnero_Controller_Plugin_Acl
     */
    public function setDenyModule($module)
    {
        $this->_pluginAcl->setDenyModule($module);
        return $this;
    }

    /**
     * Sets the controller the Vulnero_Controller_Plugin_Acl should
     * redirect to if any of the access control list logic fails.
     *
     * @param   string              Controller name
     * @return  Vulnero_Controller_Plugin_Acl
     */
    public function setDenyController($controller)
    {
        $this->_pluginAcl->setDenyController($controller);
        return $this;
    }

    /**
     * Sets the action the Vulnero_Action_Plugin_Acl should
     * redirect to if any of the access control list logic fails.
     *
     * @param   string              Action name
     * @return  Vulnero_Action_Plugin_Acl
     */
    public function setDenyAction($action)
    {
        $this->_pluginAcl->setDenyAction($action);
        return $this;
    }

    /**
     * Check whether or not the current logged in WordPress user
     * has the required role.
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param   string                  WordPress role
     * @return  boolean
     */
    public function hasRole($role)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }
        $identity = $auth->getIdentity();

        return in_array($role, $identity->roles);
    }


    /**
     * Check whether or not the current logged in WordPress user
     * has all of a list of capabilities.
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param   array                   WordPress capabilities
     * @return  boolean
     */
    public function hasCapabilities(array $capabilities)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }
        $identity = $auth->getIdentity();

        foreach ($identity->allcaps as $item => $index) {
            if (false !== ($key = array_search($item, $capabilities))) {
                unset($capabilities[$key]);
                if (empty($capabilities)) {
                    break;
                }
            }
        }

        foreach ($identity->caps as $item => $index) {
            if (false !== ($key = array_search($item, $capabilities))) {
                unset($capabilities[$key]);
                if (empty($capabilities)) {
                    break;
                }
            }
        }
        return empty($capabilities);
    }
}
