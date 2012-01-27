<?php
class Vulnero_AdminPageTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testConstruct()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        $this->assertEquals('init', $w->getLastAction());

        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertContains('onAdminHead', $wordPress->getActions());
        $this->assertTrue($w->getRequest() instanceof Zend_Controller_Request_Http);
        $this->assertTrue($w->getView() instanceof Zend_View);
        $this->assertEquals('stuff', $w->getView()->test);
        $this->assertEquals('Test_AdminPage', $w->getMenuSlug());
        $this->assertEquals(Vulnero_AdminPage::ADMIN_OPTIONS, $w->getType());
        $this->assertContains($w->getMenuSlug(), $wordPress->getAdminPages());
        $this->assertEquals('manage_options', $w->getCapability());
        $this->assertTrue((boolean) $w->getContent());
    }

    public function testOnAdminHead()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        $w->getView()->headScript()->appendScript('test.js');
        $w->getView()->headLink()->appendStylesheet('test.css');
        ob_start();
        $w->onAdminHead();
        $buf = ob_get_contents();
        ob_end_clean();
        $this->assertContains('<link href="test.css"', $buf);
        $this->assertContains('test.js', $buf);
    }

    public function testGetContent()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        $this->assertContains('This is a script referenced in a unit test.', $w->getContent());
    }

    public function testOnRender()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        ob_start();
        $w->onRender();
        $buf = ob_get_contents();
        ob_end_clean();
        $this->assertContains('This is a script referenced in a unit test.', $buf);
    }

    public function testSetPosition()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        $this->assertEquals(3, $w->setPosition(3)->getPosition());
    }

    public function testSetIconUrl()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        $this->assertEquals('test', $w->setIconUrl('test')->getIconUrl());
    }

    public function testSetType()
    {
        $w = new Test_AdminPage($this->_bootstrap);
        $this->assertEquals(
            Vulnero_AdminPage::ADMIN_MENU,
            $w->setType(Vulnero_AdminPage::ADMIN_MENU)->getType()
        );
    }
}

class Test_AdminPage extends Vulnero_AdminPage
{
    protected $_pageTitle = 'page title';
    protected $_menuTitle = 'menu title';

    protected $_lastAction;

    protected function _init()
    {
        $this->_lastAction = 'init';
    }

    public function getLastAction()
    {
        return $this->_lastAction;
    }

    public function displayAction()
    {
        $this->view->test = 'stuff';
    }

    public function getPageTitle() { return $this->_pageTitle; }
    public function getMenuTitle() { return $this->_menuTitle; }
    public function getCapability() { return $this->_capability; }
    public function getMenuSlug() { return $this->_menuSlug; }
    public function getIconUrl() { return $this->_iconUrl; }
    public function getPosition() { return $this->_position; }
    public function getType() { return $this->_type; }
    public function getBootstrap() { return $this->_bootstrap; }
    public function getView() { return $this->view; }
    public function getRequest() { return $this->_request; }
}
