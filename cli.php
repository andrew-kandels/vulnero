<?php
/**
 * Vulnero
 *
 * Alternative execution path to vulnero.php which avoids WordPress
 * by mocking it's hooks and actions with dummies. Used for unit
 * testing and command-line scripts that need the bootstrapping.
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

require_once dirname(__FILE__) . '/vulnero.php';

// Begin WordPress mock functions
function register_activation_hook($file, $callBack) {}
function add_action($hook, array $callBack) {}
function add_filter($filter, array $callBack) {}

function get_bloginfo($name)
{
    switch ($name) {
        case 'siteurl': return 'http://localhost';
        case 'name': return 'Test';
        case 'description': return 'Fake blog for testing.';
        case 'wpurl': return 'http://localhost';
        case 'siteurl': return 'http://localhost';
        case 'admin_email': return 'me@domain.com';
        case 'charset': return 'utf8';
        case 'version': return '1.0.0';
        case 'html_type': return '';
        case 'text_direction': return '';
        case 'language': return 'en_US';
        case 'stylesheet_url': return '';
        case 'stylesheet_directory': return '';
        case 'template_url': return '';
        case 'pingback_url': return '';
        case 'tags': return '';
        case 'categories': return '';
        case 'template': return '';
        default:
            throw new InvalidArgumentException($name . ' is not a valid argument to get_bloginfo.');
    }
}

function get_theme_root()
{
    return PROJECT_BASE_PATH;
}

function get_template()
{
    return 'tests';
}

function get_tags()
{
    return array();
}

function wp_get_post_categories()
{
    return array();
}
