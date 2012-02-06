<?php
/**
 * Vulnero
 *
 * Defines the WordPress access control list resources and roles
 * as a Zend_Acl.
 *
 * *** NOT PRESENTLY USED BY ANY DEFAULT VULNERO BEHAVIOR. ***
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

class Vulnero_Acl extends Zend_Acl
{
    protected static $_wpAcl = array(
        1 => array(
            'super admin',
            array(
                'manage_network', 'manage_sites', 'manage_network_users',
                'manage_network_themes', 'manage_network_options',
            ),
        ),
        2 => array(
            'administrator',
            array(
                'activate_plugins', 'add_users', 'create_users',
                'delete_plugins',
                'delete_users', 'edit_dashboard', 'edit_files',
                'edit_published_pages',
                'edit_theme_options', 'edit_users', 'export',
                'import', 'list_users',
                'manage_options', 'promote_users',
                'remove_users', 'switch_themes', 'unfiltered_upload',
                'update_core', 'update_plugins', 'update_themes', 'install_plugins',
                'install_themes', 'delete_themes', 'edit_plugins', 'edit_themes',
            ),
        ),
        3 => array(
            'editor',
            array(
                'delete_others_pages', 'delete_others_posts', 'delete_pages',
                'delete_private_pages', 'delete_private_posts',
                'delete_published_pages', 'edit_others_pages',
                'edit_others_posts', 'edit_pages', 'edit_private_pages',
                'edit_private_posts', 'edit_published_posts',
                'manage_categories', 'manage_links', 'moderate_comments', 'publish_pages',
                'read_private_pages', 'read_private_posts',
                'unfiltered_html',
            ),
        ),
        4 => array(
            'author',
            array(
                'delete_published_posts', 'edit_published_posts', 'publish_posts',
                'upload_files',
            ),
        ),
        5 => array(
            'contributor',
            array(
                'delete_posts', 'edit_posts',
            ),
        ),
        6 => array(
            'subscriber',
            array(
                'read',
            ),
        ),
    );

    /**
     * Adds the roles and routes to setup the Acl.
     *
     * @return  Vulnero_Acl
     */
    public function __construct()
    {
        // add the roles with nesting
        for ($i1 = count(self::$_wpAcl) - 1; $i1 >= 0; $i1--) {
            $parents = array();
            for ($i2 = $i1 - 1; $i2 >= 0; $i2--) {
                $parents[] = self::$_wpAcl[$i2][0];
            }
            if (empty($parents)) {
                $parents = null;
            }
            $this->addRole(new Zend_Acl_Role(self::$_wpAcl[$i1][0]), $parents);
        }

        // allow and deny the resources to each role
        for ($i1 = count(self::$_wpAcl) - 1; $i1 >= 0; $i1--) {
            foreach (self::$_wpAcl[$i1][1] as $resource) {
                $this->allow(self::$_wpAcl[$i1][0], $resource);
            }
        }
    }
}
