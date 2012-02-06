<?php
/**
 * Vulnero extended Zend_Application class designed to cache the config
 * file either in APC (if available) or as a file.
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
class Vulnero_Application extends Zend_Application
{
    /**
     * Creates and caches Zend_Config. This can't be done in the bootstrap
     * because the application requires a configuration in order to be
     * created.
     *
     * @param   string      Config file
     * @return  array
     */
    protected function _loadConfig($file)
    {
        $frontendOptions = array(
            'automatic_serialization'   => true,
            'master_file'               => $file,
            'cache_id_prefix'           => APPLICATION_ENV,
        );

        if (extension_loaded('apc')) {
            $cache = Zend_Cache::factory('File', 'Apc', $frontendOptions);
        } else {
            $cache = Zend_Cache::factory('File', 'File', $frontendOptions, array(
                'cache_dir'     => PLUGIN_BASE_PATH . '/cache',
                'file_locking'  => true
            ));
        }

        // don't cache the config.ini on non-production environments so config
        // changes are immediate
        if (APPLICATION_ENV != 'production' || (!$config = $cache->load('config'))) {
            $config = parent::_loadConfig($file);

            // Initialize WordPress configuration values
            $config = $this->_initWordPress($config);

            if (APPLICATION_ENV != 'test') {
                $cache->save($config, 'config');
            }
        }

        return $config;
    }

    /**
     * Inserts configuration settings into the global, cached Zend_Config
     * objects detected from WordPress API functions.
     *
     * @param   array   $config
     * @return  void
     */
    protected function _initWordPress(array $config)
    {
        $wordPress = new Vulnero_WordPress();

        $config['wordpress'] = array(
            'name'                  => $wordPress->getBlogInfo('name'),
            'description'           => $wordPress->getBlogInfo('description'),
            'wpurl'                 => $wordPress->getBlogInfo('wpurl'),
            'siteurl'               => $wordPress->getBlogInfo('siteurl'),
            'admin_email'           => $wordPress->getBlogInfo('admin_email'),
            'charset'               => $wordPress->getBlogInfo('charset'),
            'version'               => $wordPress->getBlogInfo('version'),
            'html_type'             => $wordPress->getBlogInfo('html_type'),
            'text_direction'        => $wordPress->getBlogInfo('text_direction'),
            'language'              => $wordPress->getBlogInfo('language'),
            'stylesheet_url'        => $wordPress->getBlogInfo('stylesheet_url'),
            'stylesheet_directory'  => $wordPress->getBlogInfo('stylesheet_directory'),
            'template_url'          => $wordPress->getBlogInfo('template_url'),
            'pingback_url'          => $wordPress->getBlogInfo('pingback_url'),
            'tags'                  => array(),
            'categories'            => array(),
            'template'              => $wordPress->getTemplate(),
        );

        // Store WordPress tags in a convenience array
        $tags = $wordPress->getTags();
        foreach ($tags as $tag) {
            $config['wordpress']['tags'][] = $tag;
        }

        // Store WordPress categories in a convenience array
        $categories = $wordPress->getPostCategories();
        foreach ($categories as $category) {
            $obj = $wordPress->getCategory($category);
            $config['wordpress']['categories'] = $obj;
        }

        return $config;
    }
}
