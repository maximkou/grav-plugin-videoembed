<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\Service\Youtube;

class YoutubeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dpGetEmbedNode
     */
    public function testGetEmbedNode($matches)
    {
        $options = [
            'enabled' => true,
            'container_html_attr' => [
                'class' => 'video-container'
            ],
            'embed_html_attr' => [
                'frameborder'=> 0,
                'width'      => 560,
                'height'     => 315,
            ],
            'embed_options' => [
                'autoplay'   => 1,
                'autohide'   => 1,
                'fs'         => 1,
                'rel'        => 0,
                'hd'         => 1,
                'vq'         => 'hd1080',
                'wmode'      => 'opaque',
                'enablejsapi'=> 1,
            ]
        ];
        $y = new Youtube($options);

        $node = $y->getEmbedNode($matches);

        $uOpts = [];
        if (!empty($matches[11])) {
            parse_str(trim($matches[11], '&?'), $uOpts);
        }
        $uOpts = array_merge($options['embed_options'], $uOpts);
        if (!empty($uOpts)) {
            $uOpts = '?'.http_build_query($uOpts);
        }

        $this->assertEquals(
            '//youtube.com/embed/'.$matches[7].$uOpts,
            $node->attributes->getNamedItem('src')->nodeValue
        );

        $this->assertEquals(0, $node->attributes->getNamedItem('frameborder')->nodeValue);
        $this->assertEquals(560, $node->attributes->getNamedItem('width')->nodeValue);
        $this->assertEquals(315, $node->attributes->getNamedItem('height')->nodeValue);
    }

    public function dpGetEmbedNode()
    {
        return [
            [ [7 => 'gdfgDJSJSJSs', 11 => '&page=1'] ],
            [ [7 => 'gdfgwFSdsaS'] ]
        ];
    }
} 