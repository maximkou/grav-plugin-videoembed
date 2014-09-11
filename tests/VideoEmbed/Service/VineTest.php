<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;

class VineTest extends AbstractServiceTest
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

        $doc = new \DOMDocument();
        $doc->appendChild(
            $node = $doc->createElement('div')
        );

        $service->expects($this->once())
            ->method('prepareStandardEmbed')
            ->with(
                $this->equalTo($this->getVideoUrl($vId)),
                $this->equalTo($urlQuery),
                $this->equalTo(['type'])
            )
            ->willReturn($node);

        $nodes = $service->getEmbedNodes(
            $this->createMatchesArray($vId, $urlQuery)
        );

        $this->assertEquals($node, $nodes[0]);
        $this->assertEquals($nodes[1]->nodeName, 'script');
        $this->assertTrue($nodes[1]->hasAttribute('src'));
    }

    /**
     * @dataProvider dpGetEmbedType
     */
    public function testGetEmbedType($matches, $expectedType)
    {
        $service = $this->getMock(
            '\\Grav\\Plugin\\VideoEmbed\\Service\\'.$this->getPluginName(),
            null,
            [[
                'embed_options' => [
                    'type' => 'postcard'
                ]
            ]]
        );

        $method = new \ReflectionMethod($service, 'getEmbedType');
        $method->setAccessible(true);

        $this->assertEquals(
            $expectedType,
            $method->invoke($service, $matches)
        );
    }

    public function getPluginName()
    {
        return 'Vine';
    }

    public function getVideoUrl($videoId, $urlQuery = null)
    {
        return 'https://vine.co/v/'.$videoId.'/embed/simple';
    }

    public function createMatchesArray($videoId, $urlQuery = null)
    {
        return [
            5 => $videoId,
            7 => 'simple',
            8 => $urlQuery
        ];
    }

    public function dpGetEmbedType()
    {
        return [
            [
                [7 => 'simple'], 'simple'
            ],
            [
                [7 => 'postcard'], 'postcard'
            ],
            [
                [7 => 'unexist'], 'simple'
            ],
            [
                [], 'postcard'
            ]
        ];
    }
}
