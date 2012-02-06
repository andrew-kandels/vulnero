<?php
/**
 * Vulnero
 *
 * Plugin called just before routing a request to a
 * controller to verify that any access or authentication
 * requirements are met, and if not, relay the request
 * to the designated location or error handler.
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

class Vulnero_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var string
     */
    protected $_denyModule = 'default';

    /**
     * @var string
     */
    protected $_denyController = 'error';

    /**
     * @var string
     */
    protected $_denyAction = 'error';

    /**
     * @var array
     */
    protected $_capabilities = array();

    /**
     * @var string
     */
    protected $_role;

    /**
     * Called after routing the request to the controller. Verifies
     * the current WordPress user has the required roles and
     * capabilities to access the resource or redirects them elsewhere.
     *
     * @param   Zend_Controller_Request_Abstract            Request object
     * @return  void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $hasPermission = $this->_hasRole() && $this->_hasCapabilities();

        $route = array(
            $request->getModuleName($this->_denyModule),
            $request->getControllerName($this->_denyController),
            $request->getActionName($this->_denyAction)
        );
        
        $target = array(
             $this->_denyModule,
             $this->_denyController,
             $this->_denyAction
        );

        // prevent infinite looping
        $isOwnRoute = ($route == $target);

        if (!$isOwnRoute && !$hasPermission) {
            $request->setDispatched(false);

            $request->setModuleName($this->_denyModule)
                    ->setControllerName($this->_denyController)
                    ->setActionName($this->_denyAction);

            $error = new Zend_Controller_Plugin_ErrorHandler();
            $error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $error->request = clone($request);
            $error->exception = new Zend_Acl_Exception('Access Denied');
            $request->setParam('error_handler', $error);
        }
    }

    /**
     * Adds a capability requirement.
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param   string          Capability
     * @return  Vulnero_Controller_Plugin_Acl
     */
    public function addCapability($capability)
    {
        if (!in_array($capability, $this->_capabilities)) {
            $this->_capabilities[] = $capability;
        }

        return $this;
    }

    /**
     * Sets a required role.
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param   string          Role
     * @return  Vulnero_Controller_Plugin_Acl
     */
    public function setRole($role)
    {
        $this->_role = $role;

        return $this;
    }

    /**
     * Sets the module we should
     * redirect to if any of the access control list logic fails.
     *
     * @param   string              Module name
     * @return  Vulnero_Controller_Plugin_Acl
     */
    public function setDenyModule($module)
    {
        $this->_denyModule = $module;
        return $this;
    }

    /**
     * Sets the controller we should
     * redirect to if any of the access control list logic fails.
     *
     * @param   string              Controller name
     * @return  Vulnero_Controller_Plugin_Acl
     */
    public function setDenyController($controller)
    {
        $this->_denyController = $controller;
        return $this;
    }

    /**
     * Sets the action we should
     * redirect to if any of the access control list logic fails.
     *
     * @param   string              Action name
     * @return  Vulnero_Action_Plugin_Acl
     */
    public function setDenyAction($action)
    {
        $this->_denyAction = $action;
        return $this;
    }

    /**
     * Check whether or not the current logged in WordPress user
     * has the required role.
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @return  boolean
     */
    protected function _hasRole()
    {
        if (!$this->_role) {
            return true;
        }

        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }
        $identity = $auth->getIdentity();

        return in_array($this->_role, $identity->roles);
    }


    /**
     * Check whether or not the current logged in WordPress user
     * has all of a list of capabilities.
     *
     * See: http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @return  boolean
     */
    protected function _hasCapabilities()
    {
        if (!$capabilities = $this->_capabilities) {
            return true;
        }

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
