<?php
/**
 * @author Fabian Fuelling <fabian@fabfuel.de>
 * @created: 14.11.14 12:13
 */

namespace Fabfuel\Prophiler\Plugin\Phalcon\Mvc;

use Fabfuel\Prophiler\Profiler;
use Phalcon\DI;
use Phalcon\Mvc\DispatcherInterface;

class DispatcherPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DispatcherPlugin
     */
    protected $dispatcherPlugin;

    /**
     * @var Profiler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $profiler;

    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    public function setUp()
    {
        $this->profiler = $this->getMockBuilder('Fabfuel\Prophiler\Profiler')->getMock();
        $this->dispatcher = $this->getMockBuilder('Phalcon\Mvc\Dispatcher')->getMock();
        $this->dispatcherPlugin = new DispatcherPlugin($this->profiler, $this->dispatcher);
    }

    public function testDispatchLoop()
    {
        $token = 'token';

        $this->profiler->expects($this->once())
            ->method('start')
            ->with('Phalcon\Mvc\Dispatcher::dispatchLoop', [], 'Dispatcher')
            ->willReturn($token);

        $this->profiler->expects($this->once())
            ->method('stop')
            ->with($token);

        $event = $this->getMockBuilder('Phalcon\Events\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->exactly(1))
            ->method('getSource')
            ->willReturn(new \Phalcon\Mvc\Dispatcher);

        $this->dispatcherPlugin->beforeDispatchLoop($event);
        $this->dispatcherPlugin->afterDispatchLoop($event);
    }

    public function testExecuteRoute()
    {
        $token = 'token';
        $metadata = [
            'class' => 'stdClass',
            'controller' => 'test-controller',
            'action' => 'test-action',
            'params' => ['test-params' => 'foobar'],
        ];

        $this->dispatcher->expects($this->once())
            ->method('getControllerName')
            ->willReturn('test-controller');

        $this->dispatcher->expects($this->once())
            ->method('getActionName')
            ->willReturn('test-action');

        $this->dispatcher->expects($this->once())
            ->method('getParams')
            ->willReturn(['test-params' => 'foobar']);

        $this->dispatcher->expects($this->once())
            ->method('getActiveController')
            ->willReturn(new \stdClass);

        $this->profiler->expects($this->once())
            ->method('start')
            ->with('Phalcon\Mvc\Dispatcher::executeRoute', $metadata, 'Dispatcher')
            ->willReturn($token);

        $this->profiler->expects($this->once())
            ->method('stop')
            ->with($token);

        $event = $this->getMockBuilder('Phalcon\Events\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->exactly(1))
            ->method('getSource')
            ->willReturn(new \Phalcon\Mvc\Dispatcher);

        $this->dispatcherPlugin->beforeExecuteRoute($event);
        $this->dispatcherPlugin->afterExecuteRoute($event);
    }
}