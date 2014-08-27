<?php
namespace Grav\Plugin\VideoEmbed\Tests;

/**
 * Class YoutubeTest
 * @package Maximkou\GravPluginYoutube\Tests
 * @covers \Grav\Plugin\VideoEmbedPlugin
 */
class VideoEmbedTest extends \PHPUnit_Framework_TestCase
{
    public function testOnPageProcessed()
    {
        $event = $this->getMock(
            '\\Grav\\Component\\EventDispatcher\\Event',
            ['offsetGet']
        );

        $page = $this->getMock(
            '\\Grav\\Common\\Page\\Page',
            ['content'],
            [],
            '',
            false
        );
        $page->expects($this->exactly(2))
            ->method('content')
            ->willReturn(
                $this->equalTo('testContent')
            );

        $event->expects($this->any())
            ->method('offsetGet')
            ->willReturn($page);

        $fakeService = $this->getMock(
            '\\Grav\\Plugin\\VideoEmbed\\Tests\\Fake\\Service',
            ['processHtml']
        );
        $fakeService->expects($this->any())
            ->method('processHtml')
            ->willReturn('testContent');

        $grav = $this->getMock('\\Grav\Common\\Grav', [], [], '', false);
        $config = $this->getMock('\\Grav\Common\\Config', ['get'], [], '', false);
        $config->expects($this->at(0))
            ->method('get')
            ->with(
                $this->equalTo('plugins.videoembed.container.element'),
                $this->anything(),
                $this->anything()
            )
            ->willReturn('div');

        $config->expects($this->at(1))
            ->method('get')
            ->with(
                $this->equalTo('plugins.videoembed.container.html_attr'),
                $this->anything(),
                $this->anything()
            )
            ->willReturn(['class' => 'container']);

        $plugin = $this->getMock(
            '\\Grav\\Plugin\\VideoEmbedPlugin',
            ['getEnabledServicesSettings', 'getServiceByName'],
            [$grav, $config]
        );
        $plugin->expects($this->any())
            ->method('getEnabledServicesSettings')
            ->willReturn(
                ['test' => []]
            );
        $plugin->expects($this->any())
            ->method('getServiceByName')
            ->willReturn($fakeService);

        $plugin->onPageProcessed($event);
    }

    public function testGetEnabledServicesSettings()
    {
        $res = $this->getPluginMock()->getEnabledServicesSettings();
        $this->assertArrayHasKey('testService', $res);
        $this->assertArrayNotHasKey('testServiceDisabled', $res);
    }

    /**
     * @dataProvider dpGetServiceByName
     */
    public function testGetServiceByName($serviceName, $exist = true)
    {
        $method = new \ReflectionMethod('\\Grav\\Plugin\\VideoEmbedPlugin', 'getServiceByName');
        $method->setAccessible(true);

        if (!$exist) {
            $this->setExpectedException('\Exception');
        }

        $this->assertInstanceOf(
            '\\Grav\\Plugin\\VideoEmbed\\ServiceInterface',
            $method->invoke($this->getPluginMock(), $serviceName)
        );
    }

    public function dpGetServiceByName()
    {
        $dir = __DIR__.'/../src/Service';

        $data = [];
        /** @var $serviceFile \SplFileInfo */
        foreach (new \DirectoryIterator($dir) as $serviceFile) {
            if ($serviceFile->isDot() || $serviceFile->isDir()) continue;

            $data[] = [preg_replace('/\.php$/', '', $serviceFile->getBasename()), true];
        }

        $data[] = ['unexistService', false];

        return $data;
    }

    protected function getPluginMock()
    {
        $grav = $this->getMock('\\Grav\Common\\Grav', [], [], '', false);
        $config = $this->getMock('\\Grav\Common\\Config', ['get'], [], '', false);
        $config->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    'testService' => ['enabled' => true],
                    'testServiceDisabled' => ['enabled' => false]
                ]
            );

        return $this->getMock(
            '\\Grav\\Plugin\\VideoEmbedPlugin',
            null,
            [$grav, $config]
        );
    }
}
