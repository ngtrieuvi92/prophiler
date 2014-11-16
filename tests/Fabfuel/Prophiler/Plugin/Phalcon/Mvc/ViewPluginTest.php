<?php
/**
 * @author Fabian Fuelling <fabian@fabfuel.de>
 * @created: 14.11.14 12:13
 */

namespace Fabfuel\Prophiler\Plugin\Phalcon\Mvc;

use Fabfuel\Prophiler\Profiler;
use Phalcon\DI;

class ViewPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ViewPlugin
     */
    protected $viewPlugin;

    /**
     * @var Profiler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $profiler;

    public function setUp()
    {
        $this->profiler = $this->getMockBuilder('Fabfuel\Prophiler\Profiler')->getMock();
        $this->viewPlugin = new ViewPlugin($this->profiler);
    }

    public function testRenderView()
    {
        $token = 'token';

        $view = $this->getMockBuilder('Phalcon\Mvc\View')
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder('Phalcon\Events\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getSource')
            ->willReturn($view);

        $view->expects($this->once())
            ->method('getCurrentRenderLevel')
            ->willReturn(1);

        $view->expects($this->once())
            ->method('getActiveRenderPath')
            ->willReturn('main');

        $metadata = [
            'view' => 'main',
            'level' => 'action',
        ];

        $this->profiler->expects($this->once())
            ->method('start')
            ->with(get_class($view) . '::render', $metadata, 'View')
            ->willReturn($token);

        $this->profiler->expects($this->once())
            ->method('stop')
            ->with($token);

        $this->viewPlugin->beforeRenderView($event, $view);
        $this->viewPlugin->afterRenderView($event, $view);
    }

    /**
     * @param $renderLevelInt
     * @param $renderLevelString
     * @dataProvider getRenderLevels
     */
    public function testGetRenderLevel($renderLevelInt, $renderLevelString)
    {
        $this->assertSame($renderLevelString, $this->viewPlugin->getRenderLevel($renderLevelInt));
    }

    /**
     * @return array
     */
    public function getRenderLevels()
    {
        return [
            [\Phalcon\Mvc\View::LEVEL_ACTION_VIEW, 'action'],
            [\Phalcon\Mvc\View::LEVEL_AFTER_TEMPLATE, 'afterTemplate'],
            [\Phalcon\Mvc\View::LEVEL_BEFORE_TEMPLATE, 'beforeTemplate'],
            [\Phalcon\Mvc\View::LEVEL_LAYOUT, 'controller'],
            [\Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT, 'main'],
            ['foobar', ''],
            ['lorem', ''],
            ['', ''],
        ];
    }
}