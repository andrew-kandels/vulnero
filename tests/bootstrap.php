<?php
set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__),
    get_include_path()
)));

define('APPLICATION_ENV', 'test');

require_once(dirname(dirname(__FILE__)) . '/vulnero.php');
Zend_Registry::set('bootstrap', $application);

Zend_Session::$_unitTestEnabled = true;
Zend_Session::start();
