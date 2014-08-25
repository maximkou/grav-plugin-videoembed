<?php
namespace Maximkou\GravPluginYoutube\Tests;

/**
 * Class YoutubeTest
 * @package Maximkou\GravPluginYoutube\Tests
 * @covers \Grav\Plugin\YoutubePlugin
 */
class YoutubeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dpReplaceYoutubeLinks
     */
    public function testReplaceYoutubeLinks($config, $before, $expectedAfter)
    {
        $grav = $this->getMock('\Grav\Common\Grav');
        $storage = new Fake\Config($config);

        $plugin = new \Grav\Plugin\YoutubePlugin($grav, $storage);

        $result = $plugin->replaceYoutubeLinks($before);
        $this->assertEquals($expectedAfter, $result);
    }

    public function dpReplaceYoutubeLinks()
    {
        $data = [];

        foreach (glob(__DIR__.'/_data/onPageProcessed/*', GLOB_ONLYDIR) as $caseDir) {
            $data[] = [
                json_decode(file_get_contents($caseDir.'/youtube.json'), true),
                file_get_contents($caseDir.'/page.html'),
                file_get_contents($caseDir.'/page_expected.html')
            ];
        }

        return $data;
    }
}
