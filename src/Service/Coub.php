<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\ServiceAbstract;

/**
 * Class Coub
 * @package Grav\Plugin\VideoEmbed\Service
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
class Coub extends ServiceAbstract
{
    /**
     * Get regular expression, which take Coub video url
     * @return string
     * @codeCoverageIgnore
     */
    public function getRegExpression()
    {
        // coub valid hosts
        $r = self::REGEXP_HTTP_SCHEME.'coub\.com\/';
        // video ID
        $r .= '(view|embed)\/([^\/\?\#]+)';
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
            'http://coub.com/embed/'.$matches[6],
            !empty($matches[7]) ? $matches[7] : null
        );
    }
}
