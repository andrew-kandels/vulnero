<?php
/**
 * Vulnero
 *
 * Zend_Auth_Adapter for authenticating and retrieving the current
 * WordPress user as per the WordPress session.
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

class Vulnero_Auth_Adapter_WordPress implements Zend_Auth_Adapter_Interface
{
    /**
     * WordPress user identity.
     * @var WP_User
     */
    protected $_identity;

    /**
     * Constructs the authentication adapter with an instance of Vulnero_WordPress
     * for accessing the WordPress API functions we'll need to retrieve and
     * confirm the current user identity.
     *
     * @param   Vulnero_WordPress       WordPress API
     * @return  Vulnero_Auth_Adapter_WordPress
     */
    public function __construct(Vulnero_WordPress $wordPress)
    {
        $this->_identity = $wordPress->getCurrentUser();
    }

    /**
     * Returns a successful identity as part of the Zend_Auth_Result
     * process if the user is currently logged into WordPress.
     *
     * @return  Zend_Auth_Result
     */
    public function authenticate()
    {
        if ($this->_identity) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $this->_identity,
                array()
            );
        } else {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                array('not logged in')
            );
        }
    }
}
