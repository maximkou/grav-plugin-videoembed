<?php
/**
 * Created by PhpStorm.
 * User: maximkou
 * Date: 30.08.14
 * Time: 15:15
 */

namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;


abstract class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{
    abstract public function getVideoUrl($videoId, $urlQuery = null);

    abstract public function createMatchesArray($videoId, $urlQuery = null);

    abstract public function getPluginName();

    /**
     * @dataProvider dpGetEmbedNode
     */
    public function testGetEmbedNodes($vId, $urlQuery = null)
    {
        $service = $this->getMock(
            $this->getPluginClass(),
            ['prepareStandardEmbed']
        );

        $service->expects($this->once())
            ->method('prepareStandardEmbed')
            ->with(
                $this->equalTo($this->getVideoUrl($vId)),
                $this->equalTo($urlQuery)
            );

        $service->getEmbedNodes(
            $this->createMatchesArray($vId, $urlQuery)
        );
    }

    /**
     * @dataProvider dpProcessHtml
     */
    public function testProcessHtml($config, $inputHtml, $expectedHtml, $container = null)
    {
        $pluginClass = $this->getPluginClass();

        $plugin = new $pluginClass($config);
        $this->assertEquals(
            preg_replace('/\n/', '', $expectedHtml),
            preg_replace('/\n/', '', $plugin->processHtml($inputHtml, $container))
        );
    }

    public function dpProcessHtml()
    {
        $htmlDir = __DIR__.'/../../_data/VideoEmbed/Service/'.ucfirst($this->getPluginName());

        $doc = new \DOMDocument();
        $data = [];
        foreach (glob($htmlDir.'/*', GLOB_ONLYDIR) as $case) {
            $elem = null;

            if (file_exists($case.'/container.json')) {
                $c = json_decode(file_get_contents($case.'/container.json'), true);
                $elem = $doc->createElement($c['name']);
                if (!empty($c['attr'])) {
                    foreach ($c['attr'] as $attr => $attrVal) {
                        $elem->setAttribute($attr, $attrVal);
                    }
                }
            }

            $data[] = [
                json_decode(file_get_contents($case.'/options.json'), true),
                file_get_contents($case.'/input.html'),
                file_get_contents($case.'/output.html'),
                $elem
            ];
        }

        return $data;
    }

    public function dpGetEmbedNode()
    {
        return [
            [ 'videoId', 'urlQuery'],
            ['videoId2']
        ];
    }

    protected function getPluginClass()
    {
        return '\\Grav\\Plugin\\VideoEmbed\\Service\\'.$this->getPluginName();
    }
}
