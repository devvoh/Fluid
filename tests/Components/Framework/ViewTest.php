<?php

namespace Parable\Tests\Components\Framework;

class ViewTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\View */
    protected $view;

    protected function setUp()
    {
        parent::setUp();

        $this->view = \Parable\DI\Container::create(\Parable\Framework\View::class);
    }

    public function testGettingNonExistingMagicPropertyThrowsException()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Could not find property 'stuff'. Make sure it was registered with the View.");

        $this->assertNull($this->view->stuff);
    }

    public function testLoadingNonExistingTemplatePathIsSilent()
    {
        $this->view->setTemplatePath("stuff");
        $this->view->render();

        $this->assertEmpty($this->getActualOutput());
    }

    public function testAccessingExistingMagicPropertyWorks()
    {
        $this->assertInstanceOf(
            \Parable\Framework\Toolkit::class,
            $this->view->toolkit
        );
    }

    public function testRegisterCustomClassesWorks()
    {
        $this->view->registerClass("basic_test_class", \Parable\Tests\TestClasses\Basic::class);

        $this->assertInstanceOf(
            \Parable\Tests\TestClasses\Basic::class,
            $this->view->basic_test_class
        );
    }
}
