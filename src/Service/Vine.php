<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\ServiceAbstract;

/**
 * Class Vine
 * @package Grav\Plugin\VideoEmbed\Service
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
class Vine extends ServiceAbstract
{
    /**
     * Get regular expression, which take Vine.co video url
     * @return string
     * @codeCoverageIgnore
     */
    public function getRegExpression()
    {
        // Vine valid hosts
        $r = self::REGEXP_HTTP_SCHEME.'vine\.co\/v\/';
        // video ID
        $r .= '([^\/\?\#]+)(\/embed\/(simple|postcard)?)?';
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
        return $this->prepareStandardEmbed(
            'https://vine.co/v/'.$matches[5].'/embed/'.$this->getEmbedType($matches),
            !empty($matches[8]) ? $matches[8] : null,
            ['type']
        );
    }

    /**
     * Get video embed type
     * @param array $matches
     * @return string
     */
    protected function getEmbedType(array $matches)
    {
        $allowedTypes = [
            'simple',
            'postcard'
        ];

        if (empty($matches[7])) {
            $type = $this->config->get('embed_options.type');
        } else {
            $type = $matches[7];
        }

        if (!in_array($type, $allowedTypes)) {
            $type = $allowedTypes[0];
        }

        return $type;
    }
}
