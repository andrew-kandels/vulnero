<?php
/**
 * WordPress admin panel form for this application.
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
class Form_AdminGeneral extends Zend_Form
{
    /**
     * Adds the default elements to the form.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->addElement('Select', 'environment', array(
            'label' => 'Application Environment:',
            'required' => true,
            'multiOptions' => array(
                'production' => 'Production',
                'development' => 'Development',
            ),
        ));

        if ($missing = $this->_getCacheBackends(true)) {
            $description = sprintf('The %s backend%s require%s additional drivers in order '
                . 'to be selected.',
                implode(', ', $missing),
                count($missing) != 1 ? 's' : '',
                count($missing) != 1 ? '' : 's'
            );
        } else {
            $description = null;
        }

        $this->addElement('Hidden', 'missing', array(
            'value' => json_encode(array_keys($missing)),
        ));

        $this->addElement('Select', 'cacheBackend', array(
            'label' => 'Cache Backend',
            'required' => true,
            'multiOptions' => $this->_getCacheBackends(),
            'description' => $description
        ));

        $this->addElement('Text', 'cacheMemcacheHost', array(
            'label' => 'Memcached Host Name',
            'required' => true,
            'description' => 'Memcached host name (defaults to 127.0.0.1)',
            'validators' => array(
                array('Hostname', true, Zend_Validate_Hostname::ALLOW_IP),
            ),
        ));

        $this->addElement('Text', 'cacheMemcachePort', array(
            'label' => 'Memcached Port',
            'required' => true,
            'description' => 'Memcached port (defaults to 11211)',
            'validators' => array(
                array('Digits', true),
            ),
        ));

        $this->addElement('Text', 'cacheXcacheUser', array(
            'label' => 'XCache User Name',
            'required' => true,
            'description' => 'Configured in /etc/xcache',
        ));

        $this->addElement('Password', 'cacheXcachePassword', array(
            'label' => 'XCache Password',
            'required' => true,
            'description' => 'Configured in /etc/xcache',
        ));

        $this->addElement('Text', 'cacheFile', array(
            'label' => 'Cache File Path',
            'required' => true,
            'description' => 'Where the cache files should be created on the filesystem.',
        ));

        $this->addElement('Text', 'cacheTtl', array(
            'label' => 'Cache Time-to-Live',
            'required' => true,
            'description' => 'Number of seconds which must elapse before cached content can expire.',
            'validators' => array(
                array('Digits', true),
            ),
        ));

        $this->addElement('Checkbox', 'bootstrapWidgets', array(
            'label' => 'Bootstrap widgets',
        ));

        $this->addElement('Checkbox', 'bootstrapRouting', array(
            'label' => 'Bootstrap routing',
        ));

        $this->addElement('Checkbox', 'bootstrapDatabase', array(
            'label' => 'Bootstrap database',
        ));

        $this->addElement('Checkbox', 'bootstrapAuth', array(
            'label' => 'Bootstrap authentication / access control',
        ));

        $this->addElement('Checkbox', 'attribution', array(
            'label' => 'Display attribution in footer:',
        ));

        $this->addElement('Button', 'save', array(
            'label' => 'Save Changes',
            'attribs' => array('type' => 'submit')
        ));
    }

    /**
     * Returns an array of Zend_Cache backends.
     * 
     * @param   boolean         Return only extensions that cannot be selected
     * @return  array           Suitable for multiOptions
     */
    protected function _getCacheBackends($showDisabled = false)
    {
        $return = array();

        if (!$showDisabled) {
            $return['Zend_Cache_Backend_File'] = 'File-based';
        }

        // requires php's sqlite3 extension be loaded
        $isLoaded = extension_loaded('sqlite3');
        if (!$showDisabled || (!$isLoaded && $showDisabled)) {
            $return['Zend_Cache_Backend_Sqlite'] = 'SQLite';
        }

        // requires php's xcache extension be loaded
        $isLoaded = extension_loaded('xcache');
        if (!$showDisabled || (!$isLoaded && $showDisabled)) {
            $return['Zend_Cache_Backend_Xcache'] = 'XCache';
        }

        // requires php's apc extension be loaded
        $isLoaded = extension_loaded('apc');
        if (!$showDisabled || (!$isLoaded && $showDisabled)) {
            $return['Zend_Cache_Backend_Apc'] = 'APC';
        }

        // required php's memcache extension be loaded
        $isLoaded = extension_loaded('memcache');
        if (!$showDisabled || (!$isLoaded && $showDisabled)) {
            $return['Zend_Cache_Backend_Memcached'] = 'Memcache';
        }

        // required php's memcache extension be loaded
        $isLoaded = extension_loaded('memcached');
        if (!$showDisabled || (!$isLoaded && $showDisabled)) {
            $return['Zend_Cache_Backend_Libmemcached'] = 'Memcached';
        }

        return $return;
    }

    /**
     * Loads the field values either by injecting defaults or by loading 
     * previously configured settings.
     * 
     * @param   Vulnero_WordPress               WordPress API
     * @return  void
     */
    public function setDefaultValues(Vulnero_WordPress $wordPress)
    {
        $this->getElement('environment')->setValue($wordPress->getCustomOption('environment'));

        if ($backend = $wordPress->getCustomOption($opt = 'cacheBackend')) {
            $this->getElement($opt)->setValue($backend);
        } else {
            $this->getElement($opt)->setValue('Zend_Cache_Backend_File');
        }

        if ($host = $wordPress->getCustomOption($opt = 'cacheMemcacheHost')) {
            $this->getElement($opt)->setValue($host);
        } else {
            $this->getElement($opt)->setValue('127.0.0.1');
        }

        if ($port = $wordPress->getCustomOption($opt = 'cacheMemcachePort')) {
            $this->getElement($opt)->setValue($port);
        } else {
            $this->getElement($opt)->setValue('11211');
        }

        if ($user = $wordPress->getCustomOption($opt = 'cacheXcacheUser')) {
            $this->getElement($opt)->setValue($user);
        }

        if ($password = $wordPress->getCustomOption($opt = 'cacheXcachePassword')) {
            $this->getElement($opt)->setValue($password);
        }

        if ($path = $wordPress->getCustomOption($opt = 'cacheFile')) {
            $this->getElement($opt)->setValue($path);
        } elseif ($backend == 'Zend_Cache_Backend_File') {
            $this->getElement($opt)->setValue(sys_get_temp_dir());
        } else {
            $this->getElement($opt)->setValue(tempnam(sys_get_temp_dir(), 'vulnero'));
        }

        if ($ttl = $wordPress->getCustomOption($opt = 'cacheTtl')) {
            $this->getElement($opt)->setValue($ttl);
        } else {
            $this->getElement($opt)->setValue('3600');
        }

        foreach (array('Widgets', 'Routing', 'Database', 'Auth') as $item) {
            $opt = 'bootstrap' . $item;
            if (in_array($wordPress->getCustomOption($opt), array('Yes', false))) {
                $this->getElement($opt)->setValue(true);
            } else {
                $this->getElement($opt)->setValue(false);
            }
        }

        if (false === ($attribution = $wordPress->getCustomOption('attribution'))) {
            $this->getElement('attribution')->setValue(true);
        } else {
            $this->getElement('attribution')->setValue($attribution);
        }
    }
}
