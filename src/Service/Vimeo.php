<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\ServiceAbstract;

/**
 * Class Vimeo
 * @package Grav\Plugin\VideoEmbed\Service
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
class Vimeo extends ServiceAbstract
{
    /**
     * Get regular expression, which take Vimeo video url
     * @return string
     * @codeCoverageIgnore
     */
    public function getRegExpression()
    {
        // vimeo valid hosts
        $r = self::REGEXP_HTTP_SCHEME.'(player\.)?vimeo\.com\/';
        // video ID
        $r .= '(video\/)?(\d+)';
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
            '//player.vimeo.com/video/'.$matches[7],
            !empty($matches[8]) ? $matches[8] : null
        );
    }
}
