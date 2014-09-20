<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\Service\VideoJS;

class VideoJSTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VideoJS
     */
    protected $service;

    protected function setUp()
    {
        $this->service = new VideoJS([
            'embed_html_attr' => [
                'id' => 'test'
            ]
        ]);
    }

    /**
     * @param $url string
     * @param $isMatch bool
     * @param $format string
     * @dataProvider dpGetRegExpression
     */
    public function testGetRegExpression($url, $isMatch, $format = null)
    {
        $this->assertEquals(
            $isMatch,
            preg_match('/'.$this->service->getRegExpression().'/', $url, $matches)
        );


        if ($isMatch) {
            $this->assertEquals($format, $matches[7]);
        }
    }


    /**
     * @param array $matches
     * @dataProvider dpGetEmbedNodes
     */
    public function testGetEmbedNodes(array $matches)
    {
        $embed = $this->service->getEmbedNodes($matches);

        $this->assertEquals('video', $embed->nodeName);
        $this->assertEquals(
            'test',
            $embed->attributes->getNamedItem('id')->nodeValue
        );

        $dSetup = $embed->attributes->getNamedItem('data-setup');
        $dSetup = json_decode($dSetup->nodeValue, true);

        parse_str(trim($matches[8], '?& '), $expectedSettings);
        foreach ($expectedSettings as $setting => $settingVal) {
            $this->assertEquals(
                $settingVal,
                $dSetup[$setting]
            );
        }

        $source = $embed->childNodes->item(0);
        $this->assertEquals('source', $source->nodeName);
        $this->assertEquals(
            $this->service->getSupportedExt()[$matches[7]],
            $source->attributes->getNamedItem('type')->nodeValue
        );
        $this->assertEquals(
            $matches[1],
            $source->attributes->getNamedItem('src')->nodeValue
        );
    }

    public function dpGetEmbedNodes()
    {
        return [
            [[
                1 => 'http://test/vid1.mp4',
                7 => 'mp4',
                8 => ''
            ]],
            [[
                1 => 'http://test2/vid2.mov',
                7 => 'mp4',
                8 => '?poster=test.jpg&test=ok'
            ]]
        ];
    }

    public function dpGetRegExpression()
    {
        return [
            [
                'http://localhost/vid1.mp4',
                true,
                'mp4'
            ],
            [
                '.mp4',
                false
            ],
            [
                'https://google.com/video/vid1.mp3',
                false
            ],
            [
                'https://google.com/video/vid1.mov',
                true,
                'mov'
            ]
        ];
    }
}
