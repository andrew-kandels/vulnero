<?php
class BootstrapTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testInitWordPress()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertTrue($wordPress instanceof Vulnero_WordPress);

        $this->assertContains('onPluginsLoaded', $wordPress->getActions());
        $this->assertContains('onWpFooter', $wordPress->getActions());
        $this->assertContains('onWpHead', $wordPress->getActions());
        $this->assertContains('onWidgetsInit', $wordPress->getActions());
        $this->assertContains('onSendHeaders', $wordPress->getActions());
        $this->assertContains('onAdminMenu', $wordPress->getActions());

        $this->assertContains('onWpTitle', $wordPress->getFilters());

        $this->assertTrue($this->_frontController->getParam('bootstrap') instanceof
            Vulnero_Application_Bootstrap_Bootstrap
        );
    }

    public function testInitWidgets()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertContains('onWidgetsInit', $wordPress->getActions());
    }

    public function testInitRoutes()
    {
        $cache     = $this->_bootstrap->bootstrap('cache')
                                      ->getResource('cache');
        $routes    = $this->_bootstrap->bootstrap('routes')
                                      ->getResource('routes');
        $this->assertEquals($routes->toArray(), $cache->load('routes')->toArray());
        $this->assertTrue($routes instanceof Zend_Config);
    }

    public function testInitRouter()
    {
        $router    = $this->_bootstrap->bootstrap('router')
                                      ->getResource('router');
        $routes    = $this->_bootstrap->bootstrap('routes')
                                      ->getResource('routes');
        $this->assertEquals($this->_frontController->getRouter(), $router);
        $this->assertFalse($router->hasRoute('default'));

        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertContains('onSendHeaders', $wordPress->getActions());
        $this->assertTrue($router instanceof Zend_Controller_Router_Rewrite);
    }

    public function testInitConfig()
    {
        $config = $this->_bootstrap->bootstrap('config')
                                   ->getResource('config');
        $this->assertTrue($config instanceof Zend_Config);
        $this->assertEquals($this->_bootstrap->getOptions(), $config->toArray());
    }

    public function testInitDb()
    {
        $db = $this->_bootstrap->bootstrap('db')
                               ->getResource('db');
        $this->assertTrue($db instanceof Zend_Db_Adapter_Pdo_Mysql);
        $this->assertEquals(Zend_Db_Table_Abstract::getDefaultAdapter(), $db);
        $this->assertTrue($db->query('SELECT 1') instanceof Zend_Db_Statement_Pdo);
    }

    public function testAuthAdapter()
    {
        // @todo
    }

    public function testInitCache()
    {
        $cache     = $this->_bootstrap->bootstrap('cache')
                                      ->getResource('cache');
        $this->assertTrue($cache instanceof Zend_Cache_Core);
        $cache->save('testvalue', 'testkey');
        $this->assertEquals('testvalue', $cache->load('testkey'));
        $this->assertEquals(Zend_Db_Table_Abstract::getDefaultMetadataCache(), $cache);
        $this->assertEquals(Zend_Locale::getCache(), $cache);
    }

    public function testInitAdmin()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertContains('onAdminMenu', $wordPress->getActions());
    }

    public function testViewSettings()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $view = $this->_bootstrap->bootstrap('viewSettings')
                                 ->getResource('viewSettings');
        $this->assertEquals($this->_bootstrap->bootstrap('view')->getResource('view'), $view);
        $this->assertEquals($wordPress, $view->wordPress);
    }

    public function testOnPluginsLoaded()
    {
        // @todo
    }

    public function testOnWpTitle()
    {
        $view = $this->_bootstrap->bootstrap('view')->getResource('view');
        $view->headTitle('test<b>');
        $this->_frontController->setParam('isVulneroRoute', true);
        $this->assertEquals('test&lt;b&gt; - ', $this->_bootstrap->onWpTitle('ignore'));

        $this->_frontController->setParam('isVulneroRoute', false);
        $view->headTitle('test<b>');
        $this->assertEquals('ignore', $this->_bootstrap->onWpTitle('ignore'));
    }

    public function testOnWpFooter()
    {
        // @todo
    }

    public function testWpHead()
    {
        $view = $this->_bootstrap->bootstrap('view')->getResource('view');
        $view->headMeta()->appendName('test', 'value');
        $view->headStyle()->appendStyle('body', 'font-weight: bold');
        $view->headLink()->appendStylesheet('test.css');
        $view->headScript()->appendScript('test.js');

        ob_start();
        $this->_bootstrap->onWpHead();
        $contents = ob_get_contents();
        ob_end_clean();

        $expected = <<<STREND
<meta name="test" content="value" >
<style type="text/css" media="screen">
<!--
body
-->
</style>
<link href="/wp-content/plugins/vulnero/public/styles/main.css" media="screen" rel="stylesheet" type="text/css" >
<link href="test.css" media="screen" rel="stylesheet" type="text/css" >
<script type="text/javascript">
    //<!--
test.js    //-->
</script>
STREND;

        $this->assertEquals($expected, $contents);
    }

    public function testOnWidgetsInit()
    {
        $cache     = $this->_bootstrap->bootstrap('cache')
                                      ->getResource('cache');
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->_bootstrap->onWidgetsInit();
        $widgets = $cache->load('widgets');
        $this->assertTrue(is_array($widgets));
        $wordPress->registerWidget('Widget_Test');
        $widgets = $wordPress->getWidgets();
        $this->assertContains('Widget_Test', $widgets);
    }

    public function testOnPageTemplate()
    {
        $templates = $this->_bootstrap->onPageTemplate();
        $template  = $templates[0];
        $this->assertNotEquals(PLUGIN_BASE_PATH, substr($template, 0, strlen(PLUGIN_BASE_PATH)));
    }

    public function testOnSendHeaders()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $wp = new stdclass();
        $wp->request = 'badtestroute';
        $request = $this->_bootstrap->onSendHeaders($wp);
        $this->assertTrue($request instanceof Zend_Controller_Request_Http);
        $this->assertEquals('/badtestroute', $request->getRequestUri());
        $this->assertFalse($this->_frontController->getParam('isVulneroRoute'));

        $router = $this->_bootstrap->bootstrap('router')->getResource('router');
        $router->addRoute('unittest', new Zend_Controller_Router_Route(
            'unittest/:param',
            array(
                'module'        => 'default',
                'controller'    => 'default',
                'action'        => 'unittest',
            )
        ));
        $wp->request = 'unittest/first';
        $request = $this->_bootstrap->onSendHeaders($wp);
        $this->assertEquals('default', $request->getModuleName());
        $this->assertEquals('default', $request->getControllerName());
        $this->assertEquals('unittest', $request->getActionName());
        $this->assertTrue($this->_frontController->getParam('isVulneroRoute'));

        $this->assertContains('onCommentsOpen', $wordPress->getFilters());
        $this->assertContains('onPingsOpen', $wordPress->getFilters());
        $this->assertContains('onCommentsTemplate', $wordPress->getFilters());
        $this->assertContains('onWpLinkPagesArgs', $wordPress->getFilters());
        $this->assertContains('onPageTemplate', $wordPress->getFilters());

        $response = $this->_frontController->getParam('response');
        $this->assertTrue($response instanceof Zend_Controller_Response_Http);
        $this->assertContains('This is used during unit testing.', $response->getBody());

        $this->assertEquals(
            array(
                'request'           => '',
                'query_string'      => '',
                'matched_rule'      => '()(/.*)$',
                'matched_query'     => 'pagename=&page=1',
                'query_vars'        => array(
                                           'page' => 1,
                                           'pagename' => ''
                                       ),
                'extra_query_vars'  => array(),
            ),
            (array) $wp
        );
    }

    public function testOnTheContent()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $wp = new stdclass();
        $router = $this->_bootstrap->bootstrap('router')->getResource('router');
        $router->addRoute('unittest', new Zend_Controller_Router_Route(
            'unittest/:param',
            array(
                'module'        => 'default',
                'controller'    => 'default',
                'action'        => 'unittest',
            )
        ));
        $wp->request = 'unittest/first';
        $request = $this->_bootstrap->onSendHeaders($wp);

        $content = $this->_bootstrap->onTheContent('test-1-2-3');
        $this->assertNotContains('test-1-2-3', $content);
        $this->assertContains('This is used during unit testing.', $content);
        $this->_frontController->setParam('isVulneroRoute', false);
        $content = $this->_bootstrap->onTheContent('test-1-2-3');
        $this->assertContains('test-1-2-3', $content);
    }
}
