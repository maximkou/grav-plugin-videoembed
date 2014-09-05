<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed\Service;

class CoubTest extends AbstractServiceTest
{
    public function getPluginName()
    {
        return 'Coub';
    }

    public function getVideoUrl($videoId, $urlQuery = null)
    {
        return 'http://coub.com/embed/'.$videoId;
    }

    public function createMatchesArray($videoId, $urlQuery = null)
    {
        return [
            6 => $videoId,
            7 => $urlQuery
        ];
    }
}
