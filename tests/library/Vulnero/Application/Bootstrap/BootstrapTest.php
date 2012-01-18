<?php
class BootstrapTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testInitWordPress()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertTrue($wordPress instanceof Vulnero_WordPress);

        $this->assertContains('plugins_loaded', $wordPress->getActions());
        $this->assertContains('wp_footer', $wordPress->getActions());
        $this->assertContains('wp_head', $wordPress->getActions());
        $this->assertContains('widgets_init', $wordPress->getActions());
        $this->assertContains('send_headers', $wordPress->getActions());
        $this->assertContains('admin_menu', $wordPress->getActions());

        $this->assertContains('wp_title', $wordPress->getFilters());
        $this->assertContains('page_template', $wordPress->getFilters());
        $this->assertContains('home_template', $wordPress->getFilters());
        $this->assertContains('single_template', $wordPress->getFilters());

        $this->assertTrue($this->_frontController->getParam('bootstrap') instanceof
            Vulnero_Application_Bootstrap_Bootstrap
        );
    }

    public function testInitWidgets()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertContains('widgets_init', $wordPress->getActions());
    }

    public function testInitTemplates()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertContains('page_template', $wordPress->getFilters());
        $this->assertContains('home_template', $wordPress->getFilters());
        $this->assertContains('single_template', $wordPress->getFilters());
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
        $this->assertContains('send_headers', $wordPress->getActions());
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
        $this->assertTrue($db instanceof Zend_Db_Adapter_Pdo_Sqlite);
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
        $this->assertContains('admin_menu', $wordPress->getActions());
    }

    public function testViewSettings()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $layout = $this->_bootstrap->bootstrap('viewSettings')
                                   ->getResource('viewSettings');
        $this->assertEquals($this->_bootstrap->bootstrap('layout')->getResource('layout'), $layout);
        $this->assertEquals($this->_bootstrap->bootstrap('view')->getResource('view'), $layout->getView());
        $this->assertEquals($wordPress, $layout->wordPress);
        $this->assertEquals($wordPress, $layout->getView()->wordPress);
    }

    public function testOnPluginsLoaded()
    {
        // @todo
    }

    public function testOnWpTitle()
    {
        $view = $this->_bootstrap->bootstrap('view')->getResource('view');
        $view->headTitle('test<b>');
        $this->_frontController->setParam('isWordPressRoute', true);
        $this->assertEquals('test&lt;b&gt; - ', $this->_bootstrap->onWpTitle('ignore'));

        $this->_frontController->setParam('isWordPressRoute', false);
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
}
