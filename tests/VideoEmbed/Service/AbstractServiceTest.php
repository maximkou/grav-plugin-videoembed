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
            '\\Grav\\Plugin\\VideoEmbed\\Service\\'.$this->getPluginName(),
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

    public function dpGetEmbedNode()
    {
        return [
            [ 'videoId', 'urlQuery'],
            ['videoId2']
        ];
    }
}
