<?php
class Controller_DefaultControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = Zend_Registry::get('bootstrap');
        parent::setUp();

        $this->_frontController->setDefaultModule('default');
        $this->_frontController->setControllerDirectory(APPLICATION_PATH . '/controllers');
    }

    public function tearDown()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();

        $this->request->setPost(array());
        $this->request->setQuery(array());
    }

    public function testIndex()
    {
        $wordpress = new stdclass();
        $wordpress->request = '/helloworld';

        $this->bootstrap->bootstrap()->onSendHeaders($wordpress);

        $this->assertController('default');
        $this->assertAction('index');
        //$this->assertQueryContentContains();
    }
}
