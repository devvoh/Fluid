<?php

namespace Parable\Tests\Components\Framework;

class SessionMessageTest extends \Parable\Tests\Base
{
    /** @var \Parable\Framework\SessionMessage */
    protected $sessionMessage;

    protected function setUp()
    {
        parent::setUp();

        $session = new \Parable\GetSet\Session();
        $session->set(
            \Parable\Framework\SessionMessage::SESSION_KEY,
            [
                'notice' => [
                    'This is message 1.',
                    'This is message 2.'
                ],
            ]
        );
        $this->sessionMessage = new \Parable\Framework\SessionMessage($session);
    }

    public function testGet()
    {
        $messages = $this->sessionMessage->get();

        $this->assertSame(
            [
                'notice' => ['This is message 1.', 'This is message 2.'],
            ],
            $messages
        );

        $notices = $this->sessionMessage->get('notice');

        $this->assertSame(
            ['This is message 1.', 'This is message 2.'],
            $notices
        );
    }

    public function testAddAndGetClear()
    {
        $this->sessionMessage->add('Test message to be cleared.', 'test');

        $messages = $this->sessionMessage->getClear('test');

        $this->assertSame(
            ['Test message to be cleared.'],
            $messages
        );

        // Should now be empty
        $messages = $this->sessionMessage->get('test');
        $this->assertCount(0, $messages);
    }

    public function testClear()
    {
        $this->sessionMessage->add('to be cleared', 'notice');
        $this->sessionMessage->add('also cleared', 'alert');

        $this->sessionMessage->clear();

        // Should now be empty
        $messages = $this->sessionMessage->get();
        $this->assertCount(0, $messages);
    }

    public function testCountAllAndType()
    {
        $this->sessionMessage->add('Test message to be cleared.', 'test');

        $this->assertSame(3, $this->sessionMessage->count());
        $this->assertSame(2, $this->sessionMessage->count('notice'));
        $this->assertSame(1, $this->sessionMessage->count('test'));
    }
}
