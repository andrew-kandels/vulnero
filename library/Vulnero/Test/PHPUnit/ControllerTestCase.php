<?php
class Vulnero_Test_PHPUnit_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function appBootstrap()
    {
        $this->_application = new Vulnero_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/config/config.ini'
        );
        $this->_application->bootstrap();

        $front = Zend_Controller_Front::getInstance();
        if ($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
    }

    public function tearDown()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();

        $this->request->setPost(array());
        $this->request->setQuery(array());
    }

    /**
     * Dispatch the MVC
     *
     * If a URL is provided, sets it as the request URI in the request object.
     * Then sets test case request and response objects in front controller,
     * disables throwing exceptions, and disables returning the response.
     * Finally, dispatches the front controller.
     *
     * @param  string|null $url
     * @return void
     */
    public function dispatch($url = null)
    {
        $wordpress = new stdclass();
        $wordpress->request = 'helloworld';
        $callBack = $this->_application
            ->getBootstrap()
            ->bootstrap('onSendHeaders')
            ->getResource('onSendHeaders');
        $this->_request = call_user_func($callBack, $wordpress);
    }
}
