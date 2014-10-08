<?php
namespace Grav\Plugin\VideoEmbed\Tests;

use Grav\Common\Data\Data;

/**
 * Class YoutubeTest
 * @package Maximkou\GravPluginYoutube\Tests
 * @covers \Grav\Plugin\VideoEmbedPlugin
 */
class VideoEmbedTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME = '\\Grav\\Plugin\\VideoEmbedPlugin';

    protected $config = [
        'responsive' => true,
        'test' => 'yes'
    ];

    /**
     * @dataProvider dpGetServiceByName
     */
    public function testGetServiceByName($serviceName, $exist = true)
    {
        $method = new \ReflectionMethod(self::CLASS_NAME, 'getServiceByName');
        $method->setAccessible(true);

        if (!$exist) {
            $this->setExpectedException('\Exception');
        }

        $plugin = $this->getMockBuilder(self::CLASS_NAME)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            '\\Grav\\Plugin\\VideoEmbed\\ServiceInterface',
            $method->invoke($plugin, $serviceName)
        );
    }

    public function testGetConfig()
    {
        $page = $this->getMockBuilder('\\Grav\\Common\\Page\\Page')
            ->disableOriginalConstructor()
            ->setMethods(['header'])
            ->getMock();

        $pluginConfig = [
            'responsive' => false
        ];

        $page->expects($this->once())
            ->method('header')
            ->willReturn(
                (object)[
                    'videoembed' => $pluginConfig
                ]
            );

        $plugin = $this->getPluginMockBld()->getMock();

        $mergeOptions = new \ReflectionMethod(self::CLASS_NAME, 'mergeOptions');
        $mergeOptions->setAccessible(true);
        $getConfig = new \ReflectionMethod(self::CLASS_NAME, 'getConfig');
        $getConfig->setAccessible(true);

        $this->assertEquals(
            $this->config,
            $getConfig->invoke($plugin)->toArray()
        );

        $this->assertEquals(
            $mergeOptions->invoke($plugin, $this->config, $pluginConfig),
            $getConfig->invoke($plugin, $page)->toArray()
        );
    }

    /**
     * @dataProvider dpProcessPage
     */
    public function testProcessPage(array $config)
    {
        $config = new Data($config);

        $plugin = $this
            ->getPluginMockBld()
            ->setMethods([
                'defineAssets',
                'enableResponsiveness',
                'getEmbedContainer',
                'getServiceByName'
            ])
            ->getMock();

        $plugin
            ->expects($config->get('responsive') ? $this->once() : $this->never())
            ->method('enableResponsiveness');

        $doc = new \DOMDocument();
        $container = $doc->createElement('test');

        $plugin
            ->expects($this->once())
            ->method('getEmbedContainer')
            ->willReturn($container);

        $services = array_filter(
            (array)$config->get('services', []),
            function ($s) {
                return !empty($s['enabled']);
            }
        );

        $service = $this->getMockBuilder('\\Grav\\Plugin\\VideoEmbed\\Tests\\Fake\\Service')
            ->setMethods(['processHtml'])
            ->getMock();
        $service
            ->expects($this->exactly(count($services)))
            ->method('processHtml');

        $plugin
            ->expects($this->exactly(count($services)))
            ->method('getServiceByName')
            ->willReturn($service);

        $plugin
            ->expects($this->never())
            ->method('defineAssets');

        $page = $this->getMockBuilder('\\Grav\\Common\\Page\\Page')
            ->setMethods(['content'])
            ->disableOriginalConstructor()
            ->getMock();

        $processPage = new \ReflectionMethod(self::CLASS_NAME, 'processPage');
        $processPage->setAccessible(true);
        $processPage->invoke($plugin, $page, $config);
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

    public function dpProcessPage()
    {
        return [
            [
                [
                    'responsive' => true,
                    'services' => [
                        'test' => [
                            'enabled' => true,
                        ]
                    ]
                ]
            ],
            [
                [
                    'responsive' => false,
                    'services' => [
                        'test' => [
                            'enabled' => true,
                        ],
                        'testDis' => [
                            'enabled' => false,
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getPluginMockBld()
    {
        $grav = $this->getMockBuilder('\\Grav\\Common\\Grav')
            ->disableOriginalConstructor()
            ->getMock();

        $config = $this->getMockBuilder('\\Grav\\Common\\Config')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $config->set('plugins.videoembed', $this->config);

        return $this->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$grav, $config]);
    }
}
