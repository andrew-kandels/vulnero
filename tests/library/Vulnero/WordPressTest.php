<?php
class Vulnero_WordPressTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testRegisterActivationHook()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->registerActivationHook('test', 'func');
        $this->assertTrue($w->hasActivationHook());

        $w->setIsMock(false);
        try {
            $w->registerActivationHook('test', 'func');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testAddAction()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->addAction('test', 3);
        $this->assertEquals(array('onTest'), $w->getActions());

        $w->addAction('test', $this->_frontController);
        $this->assertContains('onTest', $w->getActions());
        $this->assertEquals($this->_bootstrap, $w->getDelegate());

        $w->setIsMock(false);
        try {
            $w->addAction('test', 3);
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testAddFilter()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->addFilter('test');
        $this->assertEquals(array('onTest'), $w->getFilters());

        $w->setIsMock(false);
        try {
            $w->addFilter('test');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetSidebar()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals($w, $w->getSidebar());

        $w->setIsMock(false);
        try {
            $w->getSidebar();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testRegisterWidget()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->registerWidget('test');
        $this->assertEquals(array('test'), $w->getWidgets());

        $w->setIsMock(false);
        try {
            $w->registerWidget('test');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetActivationHooks()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(false, $w->hasActivationHook());
    }

    public function testGetFilters()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getFilters());
    }

    public function testGetWidgets()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getWidgets());
    }

    public function testGetActions()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getActions());
    }

    public function testGetSidebars()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getSidebars());
    }

    public function testGetBlogInfo()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals('Test', $w->getBlogInfo('name'));
        $this->assertEquals(null, $w->getBlogInfo('bad key'));

        $w->setIsMock(false);
        try {
            $w->getBlogInfo('name');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetThemeRoot()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(PLUGIN_BASE_PATH, $w->getThemeRoot());

        $w->setIsMock(false);
        try {
            $w->getThemeRoot();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetTemplate()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getTemplate());

        $w->setIsMock(false);
        try {
            $w->getTemplate();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetTags()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getTags());

        $w->setIsMock(false);
        try {
            $w->getTags();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetCategory()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertTrue($w->getCategory('test') instanceof stdclass);

        $w->setIsMock(false);
        try {
            $w->getCategory('test');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetPostCateories()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getPostCategories());

        $w->setIsMock(false);
        try {
            $w->getPostCategories();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetDatabase()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertTrue($w->getDatabase() instanceof Zend_Db_Adapter_Pdo_Mysql);
    }

    public function testLocateTemplate()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(
            array(realpath(PLUGIN_BASE_PATH . '/../../themes') . '/page.php'),
            $w->locateTemplate('test')
        );

        $w->setIsMock(false);
        try {
            $w->locateTemplate('test');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testApplyFilters()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->applyFilters('test', 'text');
        $this->assertEquals(array('test'), $w->getFilters());

        $w->setIsMock(false);
        try {
            $w->applyFilters('test', 'text');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testAddMenuPage()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->addMenuPage(
            'page title',
            'menu title',
            'manage_options',
            'slug',
            array($this, 'testAddMenuPage'),
            '/test.png',
            3
        );
        $this->assertContains('slug', $w->getAdminPages());

        $w->setIsMock(false);
        try {
            $w->addMenuPage(
                'page title',
                'menu title',
                'manage_options',
                'slug',
                array($this, 'testAddMenuPage'),
                '/test.png',
                3
            );
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testAddOptionsPage()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->addOptionsPage(
            'page title',
            'menu title',
            'manage_options',
            'slug',
            array($this, 'testAddMenuPage'),
            '/test.png',
            3
        );
        $this->assertContains('slug', $w->getAdminPages());

        $w->setIsMock(false);
        try {
            $w->addOptionsPage(
                'page title',
                'menu title',
                'manage_options',
                'slug',
                array($this, 'testAddMenuPage'),
                '/test.png',
                3
            );
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGettingAndSettingCustomOptions()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->setCustomOption('scalar', $scalarValue = 'test');
        $w->setCustomOption('nonscalar', $nonScalarValue = array('color' => 'red'));
        $this->assertEquals($scalarValue, $w->getCustomOption('scalar'));
        $this->assertEquals($nonScalarValue, $w->getCustomOption('nonscalar'));

        $this->assertFalse($w->getCustomOption('non-existent'));

        $w->setIsMock(false);
        try {
            $w->setCustomOption('scalar', $scalarValue = 'test');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));

        $w->setIsMock(false);
        try {
            $w->getCustomOption('nonscalar');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));

        try {
            $w->setCustomOption(str_repeat('x', 70), 'test');
        } catch (UnexpectedValueException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetOption()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertTrue(is_null($w->getOption('non-existent')));

        $w->setIsMock(false);
        try {
            $w->getOption('non-existent');
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetCurrentUser()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->setIsMock(false);
        try {
            $w->getCurrentUser();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }

    public function testGetPluginData()
    {
        $lines = file(PLUGIN_BASE_PATH . '/wordpress-plugin.php');
        $version = 'unknown';
        foreach ($lines as $line) {
            if (preg_match('/^Version: (.*)/', $line, $matches)) {
                $version = trim($matches[1]);
            }
        }

        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(
            array(
                'Name' => 'vulnero',
                'PluginURI' => 'http://www.vulnero.com/',
                'Version' => $version,
                'Description' => 'WordPress Plugin',
                'Author' => 'Andrew P. Kandels',
                'AuthorURI' => 'http://andrewkandels.com/',
                'TextDomain' => 'Text Domain',
                'DomainPath' => 'Domain Path',
                'Network' => 'Network',
                '_siteWide' => 'Site Wide Only',
            ),
            $w->getPluginData()
        );

        $w->setIsMock(false);
        try {
            $w->getCurrentUser();
        } catch (RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue(isset($thrown));
    }
}
