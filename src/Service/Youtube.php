<?php
namespace Grav\Plugin\VideoEmbed\Service;

use \Grav\Plugin\VideoEmbed\ServiceAbstract;

/**
 * Class Youtube
 * @package Grav\Plugin\VideoEmbed\Service
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
class Youtube extends ServiceAbstract
{
    /**
     * Get regular expression, which take Youtube video url
     * @return string
     * @codeCoverageIgnore
     */
    public function getRegExpression()
    {
        // youtube valid hosts
        $r = self::REGEXP_HTTP_SCHEME.'(youtube\.com|youtu\.be|youtube-nocookie\.com)\/';
        // video ID
        $r .= '(watch\?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*))';
        // more params
        $r .= '('.self::REGEXP_ALLOWED_IN_URL.'*)';

        return $r;
    }

    /**
     * Get embed node for replace link
     * @param $matches array
     * @return \DOMNode
     */
    public function getEmbedNodes(array $matches)
    {
        return $this->prepareStandardEmbed(
            '//youtube.com/embed/'.$matches[7],
            !empty($matches[11]) ? $matches[11] : null
        );
    }
}
