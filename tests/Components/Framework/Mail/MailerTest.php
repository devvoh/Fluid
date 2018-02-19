<?php

namespace Parable\Tests\Components\Framework\Mail;

class MailerTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Mail\Mailer */
    protected $mailer;

    protected function setUp()
    {
        parent::setUp();

        $this->mailer = new \Parable\Framework\Mail\Mailer(
            \Parable\DI\Container::get(\Parable\Framework\Config::class),
            \Parable\DI\Container::get(\Parable\Framework\View::class),
            \Parable\DI\Container::get(\Parable\Framework\Mail\TemplateVariables::class),
            $this->testPath
        );
    }

    public function testMailerPicksUpMailSenderFromConfig()
    {
        // By default it should be the PhpMail sender
        $this->assertInstanceOf(\Parable\Mail\Sender\PhpMail::class, $this->mailer->getMailSender());

        $config = new \Parable\Framework\Config($this->testPath);
        $config->set("parable.mail.sender", \Parable\Mail\Sender\NullMailer::class);

        $mailer = new \Parable\Framework\Mail\Mailer(
            $config,
            \Parable\DI\Container::get(\Parable\Framework\View::class),
            \Parable\DI\Container::get(\Parable\Framework\Mail\TemplateVariables::class),
            $this->testPath
        );

        $this->assertInstanceOf(\Parable\Mail\Sender\NullMailer::class, $mailer->getMailSender());
    }

    public function testMailerThrowsExceptionOnInvalidSender()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Invalid mail sender set in config.");

        $config = new \Parable\Framework\Config($this->testPath);
        $config->set("parable.mail.sender", "what am dis");

        new \Parable\Framework\Mail\Mailer(
            $config,
            \Parable\DI\Container::get(\Parable\Framework\View::class),
            \Parable\DI\Container::get(\Parable\Framework\Mail\TemplateVariables::class),
            $this->testPath
        );
    }

    public function testMailerPicksUpFromEmailAndNameFromConfig()
    {
        $config = new \Parable\Framework\Config($this->testPath);
        $config->set("parable.mail.from.email", "me@thatplace.gov");
        $config->set("parable.mail.from.name", "yo that's me!");

        $mailer = new \Parable\Framework\Mail\Mailer(
            $config,
            \Parable\DI\Container::get(\Parable\Framework\View::class),
            \Parable\DI\Container::get(\Parable\Framework\Mail\TemplateVariables::class),
            $this->testPath
        );

        $this->assertSame("yo that's me! <me@thatplace.gov>", $mailer->getAddressesForType("from"));
    }

    public function testSetGetTemplateVariable()
    {
        $this->mailer->setTemplateVariable('3', 'three');
        $this->assertSame('three', $this->mailer->getTemplateVariable('3'));
    }

    public function testSetGetTemplateVariablesAsArray()
    {
        $this->mailer->setTemplateVariables([
            '1' => 'one',
            '2' => 'two',
        ]);
        $this->assertSame(
            [
                '1' => 'one',
                '2' => 'two',
            ],
            $this->mailer->getTemplateVariables()
        );
    }

    public function testResetAndResetMailDataRemovesTemplateVariables()
    {
        $this->mailer->setTemplateVariable('3', 'three');
        $this->assertSame('three', $this->mailer->getTemplateVariable('3'));

        $this->mailer->resetMailData();
        $this->assertNull($this->mailer->getTemplateVariable('3'));

        $this->mailer->setTemplateVariable('3', 'three');
        $this->assertSame('three', $this->mailer->getTemplateVariable('3'));

        $this->mailer->reset();
        $this->assertNull($this->mailer->getTemplateVariable('3'));
    }

    public function testLoadTemplate()
    {
        $this->mailer->loadTemplate("tests/TestTemplates/mailer_template.phtml");
        $this->assertSame("Mailer template is here!\n\nTemplateVariable:", $this->mailer->getBody());
    }

    public function testLoadTemplateThrowsExceptionWhenInvalidTemplatePassed()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Email template '");

        $this->mailer->loadTemplate("tests/TestTemplates/mailer_template_noperz.phtml");
    }

    public function testLoadTemplateWithTemplateVariable()
    {
        $this->mailer->setTemplateVariable('key', 'set!');
        $this->mailer->loadTemplate("tests/TestTemplates/mailer_template.phtml");
        $this->assertSame("Mailer template is here!\n\nTemplateVariable: set!", $this->mailer->getBody());
    }
}
