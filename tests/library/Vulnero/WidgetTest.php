<?php
class Vulnero_WidgetTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testConstruct()
    {
        $w = new Test_Widget();
        $this->assertEquals('test_widget', $w->getId());
        $this->assertEquals('Test Widget', $w->getTitle());
        $this->assertEquals(array('description' => 'Description'), $w->getDescription());
        $this->assertEquals('init', $w->getLastAction());
    }

    public function testGetRequestUri()
    {
        $_SERVER['REQUEST_URI'] = 'test';
        $w = new Test_Widget();
        $this->assertEquals('test', $w->getRequestUri());
    }

    public function testGetView()
    {
        $w = new Test_Widget();
        $this->assertNotEquals($this->_bootstrap->bootstrap('view')->getResource('view'), $w->getView());
        $this->assertTrue($w->getView() instanceof Zend_View);
    }

    public function testGetBootstrap()
    {
        $w = new Test_Widget();
        $this->assertEquals($this->_bootstrap, $w->getBootstrap());
    }

    public function testGetContent()
    {
        $w = new Test_Widget();
        $this->assertEquals('Test widget, used in unit tests.' . PHP_EOL, $w->getContent(array()));
    }

    public function testWidget()
    {
        $w = new Test_Widget();
        $w->setIsShown(false);
        ob_start();
        $w->widget(array(), array());
        $buf = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(empty($buf));

        $w->setIsShown(true);
        ob_start();
        $w->widget(
            array(
                'before_widget' => 'before widget',
                'before_title'  => 'before title',
                'after_title'   => 'after title',
                'after_widget'  => 'after widget',
            ),
            array()
        );
        $buf = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('before widgetbefore titleTest Widgetafter titleTest '
            . 'widget, used in unit tests.' . PHP_EOL . 'after widget', $buf
        );
    }

    public function testSetDrawTitle()
    {
        $w = new Test_Widget();
        $w->setDrawTitle(false);
        ob_start();
        $w->widget(
            array(
                'before_widget' => 'before widget',
                'before_title'  => 'before title',
                'after_title'   => 'after title',
                'after_widget'  => 'after widget',
            ),
            array()
        );
        $buf = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('before widgetTest widget, used in unit tests.' . PHP_EOL
            . 'after widget', $buf
        );
    }

    public function testSetDrawWrappers()
    {
        $w = new Test_Widget();
        $w->setDrawWrappers(false);
        ob_start();
        $w->widget(
            array(
                'before_widget' => 'before widget',
                'before_title'  => 'before title',
                'after_title'   => 'after title',
                'after_widget'  => 'after widget',
            ),
            array()
        );
        $buf = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('Test widget, used in unit tests.' . PHP_EOL, $buf
        );
    }

    public function testUpdate()
    {
        $w = new Test_Widget();
        $settings = $w->update(array('color' => 'red'), array('color' => 'blue', 'shade' => 'gray'));
        $this->assertEquals(array('color' => 'red', 'shade' => 'gray'), $settings);
    }
}

class Test_Widget extends Vulnero_Widget
{
    protected $_lastAction = null;
    protected $_title = 'Test Widget';
    protected $_description = 'Description';
    protected $_requestUri = null;
    protected $_isShown = true;

    public function setupAction(array $settings)
    {

    }

    public function displayAction(array $settings)
    {
        parent::displayAction($settings);
        $this->view->test = 'value';
    }

    protected function _init()
    {
        parent::_init();
        $this->_requestUri = $this->_getRequestUri();
        $this->_lastAction = 'init';

        ob_start();
        $this->form(array('color' => 'red'));
        $output = ob_get_contents();
        ob_end_clean();
    }

    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    public function getLastAction()
    {
        return $this->_lastAction;
    }

    public function setIsShown($v)
    {
        $this->_isShown = $v;
    }

    protected function _isShown()
    {
        if ($this->_isShown) {
            return parent::_isShown();
        } else {
            return $this->_isShown;
        }
    }

    public function get_field_id($id)
    {
        return $id;
    }

    public function get_field_name($name)
    {
        return $name;
    }
}

// mock WordPress class that won't exist bootstrapping outside of WP
class WP_Widget
{
    private $_testId;
    private $_testTitle;
    private $_testDescription;

    public function WP_Widget($id, $title, $description)
    {
        $this->_testId = $id;
        $this->_testTitle = $title;
        $this->_testDescription = $description;
    }

    public function getId()
    {
        return $this->_testId;
    }

    public function getTitle()
    {
        return $this->_testTitle;
    }

    public function getDescription()
    {
        return $this->_testDescription;
    }
}
