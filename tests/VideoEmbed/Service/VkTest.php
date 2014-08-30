<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;

class VkTest extends AbstractServiceTest
{
    /**
     * @dataProvider dpGetEmbedNode
     */
    public function testGetEmbedNodes($vId, $urlQuery = null)
    {
        $service = $this->getMock(
            '\\Grav\\Plugin\\VideoEmbed\\Service\\'.$this->getPluginName(),
            ['prepareStandardEmbed']
        );

        $matches = $this->createMatchesArray($vId, $urlQuery);
        parse_str(!empty($matches[7]) ? $matches[7] : '', $urlQuery);
        $urlQuery = array_merge($urlQuery, [
            'oid' => $matches[5],
            'id' => $matches[6]
        ]);

        $service->expects($this->once())
            ->method('prepareStandardEmbed')
            ->with(
                $this->equalTo($this->getVideoUrl($vId)),
                $this->equalTo(http_build_query($urlQuery))
            );

        $service->getEmbedNodes($matches);
    }

    public function getPluginName()
    {
        return 'Vk';
    }

    public function getVideoUrl($videoId, $urlQuery = null)
    {
        return 'http://vk.com/video_ext.php';
    }

    public function createMatchesArray($videoId, $urlQuery = null)
    {
        return [
            5 => $videoId,
            6 => '123',
            7 => $urlQuery
        ];
    }
}
