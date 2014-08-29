<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\ServiceAbstract;

/**
 * Class Vk
 * @package Grav\Plugin\VideoEmbed\Service
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
class Vk extends ServiceAbstract
{
    /**
     * Get regular expression, which take service url
     * @return string
     */
    public function getRegExpression()
    {
        // Vine valid hosts
        $r = self::REGEXP_HTTP_SCHEME.'vk\.com\/';
        // video ID
        $r .= 'video(\d+)\_(\d+)';
        // more params
        $r .= '('.self::REGEXP_ALLOWED_IN_URL.'*)';

        return $r;
    }

    /**
     * Get embed node for replace link
     * @param $matches array
     * @return \DOMNode|\DOMNode[]
     */
    public function getEmbedNodes(array $matches)
    {
        parse_str(!empty($matches[7]) ? $matches[7] : '', $urlQuery);
        $urlQuery = array_merge($urlQuery, [
           'oid' => $matches[5],
           'id' => $matches[6]
        ]);

        return $this->prepareStandardEmbed(
            'http://vk.com/video_ext.php',
            http_build_query($urlQuery)
        );
    }
}
