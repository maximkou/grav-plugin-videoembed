<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;

class VimeoTest extends AbstractServiceTest
{
    public function getPluginName()
    {
        return 'Vimeo';
    }

    public function getVideoUrl($videoId, $urlQuery = null)
    {
        return '//player.vimeo.com/video/'.$videoId;
    }

    public function createMatchesArray($videoId, $urlQuery = null)
    {
        return [
            7 => $videoId,
            8 => $urlQuery
        ];
    }
}
