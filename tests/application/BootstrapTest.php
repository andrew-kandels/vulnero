<?php
class BootstrapTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testInitWordPress()
    {
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $this->assertTrue($wordPress instanceof Vulnero_WordPress);
        $this->assertEquals(
            array(
                 'plugins_loaded',
                 'wp_footer',
                 'wp_head',
                 'widgets_init',
                 'send_headers',
                 'admin_menu',
            ),
            $wordPress->getActions()
        );
        $this->assertEquals(
            array(
                'wp_title',
                'page_template',
                'home_template',
                'single_template',
            ),
            $wordPress->getFilters()
        );
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
        $wordPress = $this->_bootstrap->bootstrap('wordPress')
                                      ->getResource('wordPress');
        $cache     = $this->_bootstrap->bootstrap('cache')
                                      ->getResource('cache');
        $routes    = $this->_bootstrap->bootstrap('routes')
                                      ->getResource('routes');
        $this->assertTrue($cache instanceof Zend_Cache_Core);
        // @todo: enable restartable cache for unit tests
        // $this->assertEquals($routes, $cache->load('routes'));
    }
}
