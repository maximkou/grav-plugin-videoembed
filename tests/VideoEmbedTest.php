<?php
namespace Grav\Plugin\VideoEmbed\Tests;

/**
 * Class YoutubeTest
 * @package Maximkou\GravPluginYoutube\Tests
 * @covers \Grav\Plugin\VideoEmbedPlugin
 */
class VideoEmbedTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME = '\\Grav\\Plugin\\VideoEmbedPlugin';

    /**
     * @dataProvider dpOnPageInitialized
     */
    public function testOnPageInitialized($isResponsiveConfig, $isResponsiveHeader)
    {
        $pageMock = $this->getMockBuilder('\\Grav\\Common\\Page\\Page')
            ->setMethods(['value'])
            ->disableOriginalConstructor()
            ->getMock();

        $pageMock->expects($this->any())
            ->method('value')
            ->with($this->equalTo('header.videoembed.responsive'))
            ->willReturn($isResponsiveHeader);

        $config = $this->getMock('\\Grav\Common\\Config', ['get'], [], '', false);
        $config->expects($this->any())
            ->method('get')
            ->with($this->equalTo('plugins.videoembed.responsive'))
            ->willReturn($isResponsiveConfig);

        $grav = $this->getMock('\\Grav\Common\\Grav', null, [], '', false);
        $grav->offsetSet('page', $pageMock);

        $plugin = $this->getMock(
            '\\Grav\\Plugin\\VideoEmbedPlugin',
            ['enable'],
            [$grav, $config]
        );

        if (
            ($isResponsiveConfig === true && $isResponsiveHeader === null)
            || $isResponsiveHeader === true
        ) {
            $plugin->expects($this->once())
                ->method('enable')
                ->with(
                    $this->arrayHasKey('onTwigSiteVariables')
                );
        }

        $plugin->onPageInitialized();
    }

    public function testOnPageProcessed()
    {
        $event = $this->getMock(
            '\\Grav\\Component\\EventDispatcher\\Event',
            ['offsetGet']
        );

        $page = $this->getMock(
            '\\Grav\\Common\\Page\\Page',
            ['content', 'header'],
            [],
            '',
            false
        );
        $page->expects($this->exactly(2))
            ->method('content')
            ->willReturn(
                $this->equalTo('testContent')
            );
        $page->expects($this->any())
            ->method('header')
            ->willReturn(
                (object)[]
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
                $this->equalTo('plugins.videoembed'),
                $this->anything(),
                $this->anything()
            )
            ->willReturn([
                'container' => [
                    'element' => 'div',
                    'html_attr' => ['class' => 'container']
                ]
            ]);

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

    /**
     * @dataProvider dpInitConfig
     */
    public function testInitConfig(array $globalConfig, array $userConfig)
    {
        $optionsMerge = new \ReflectionMethod(self::CLASS_NAME, 'mergeOptions');
        $optionsMerge->setAccessible(true);

        $config = $this->getMock('\\Grav\Common\\Config', ['get'], [], '', false);
        $config->expects($this->any())
            ->method('get')
            ->with($this->equalTo('plugins.videoembed'))
            ->willReturn($globalConfig);


        $grav = $this->getMock('\\Grav\Common\\Grav', null, [], '', false);

        $plugin = $this->getMock(
            '\\Grav\\Plugin\\VideoEmbedPlugin',
            ['enable'],
            [$grav, $config]
        );
        $methodReflection = new \ReflectionMethod(self::CLASS_NAME, 'initConfig');
        $methodReflection->setAccessible(true);

        $opts = $optionsMerge->invoke($plugin, $globalConfig, $userConfig);
        $opts = new \Grav\Common\Data\Data($opts);

        if ($isErrorExpected = (!$opts->get('container.element') && $opts->get('responsive', false))) {
            $this->setExpectedException('\ErrorException');
        }

        $resultCfg = $methodReflection->invoke($plugin, $userConfig);
        if (!$isErrorExpected && $opts->get('responsive', false)) {
            $this->assertContains(
                'plugin-videoembed-container-fluid',
                $resultCfg->get('container.html_attr.class')
            );
        }
    }

    public function dpOnPageInitialized()
    {
        return [
            [false, true],
            [false, null],
            [false, false],
            [true, true],
            [true, null],
            [true, false]
        ];
    }

    public function dpGetServiceByName()
    {
        $dir = __DIR__.'/../src/Service';

        $data = [];
        /** @var $serviceFile \SplFileInfo */
        foreach (new \DirectoryIterator($dir) as $serviceFile) {
            if ($serviceFile->isDot() || $serviceFile->isDir()) {
                continue;
            }

            $data[] = [preg_replace('/\.php$/', '', $serviceFile->getBasename()), true];
        }

        $data[] = ['unexistService', false];

        return $data;
    }

    public function dpInitConfig()
    {
        return [
            [
                [
                    'container' => [
                        'element' => false
                    ],
                    'responsive' => false
                ],
                [
                    'responsive' => true
                ]
            ],
            [
                [
                    'container' => [
                        'element' => false
                    ],
                    'responsive' => false
                ],
                []
            ],
            [
                [
                    'container' => [
                        'element' => 'div'
                    ],
                    'responsive' => true
                ],
                []
            ]
        ];
    }

    protected function getPluginMock()
    {
        $grav = $this->getMock('\\Grav\Common\\Grav', [], [], '', false);
        $config = $this->getMock('\\Grav\Common\\Config', ['get'], [], '', false);
        $config->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    'services' => [
                        'testService' => ['enabled' => true],
                        'testServiceDisabled' => ['enabled' => false]
                    ]
                ]
            );

        return $this->getMock(
            '\\Grav\\Plugin\\VideoEmbedPlugin',
            null,
            [$grav, $config]
        );
    }
}
