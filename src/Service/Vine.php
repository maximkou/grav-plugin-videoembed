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
        $allowedTypes = ['simple', 'postcard'];
        $type = $matches[7];
        if (empty($type)) {
            $type = $this->config->get('embed_options.type');
        }

        if (!in_array($type, $allowedTypes)) {
            $type = $allowedTypes[0];
        }

        $nodes = [];
        $nodes[] = $iFrame = $this->prepareStandardEmbed(
            'https://vine.co/v/'.$matches[5].'/embed/'.$type,
            !empty($matches[8]) ? $matches[8] : null,
            ['type']
        );
        $nodes[] = $script = $iFrame->ownerDocument->createElement('script');
        $script->setAttribute('async', true);
        $script->setAttribute('src', '//platform.vine.co/static/scripts/embed.js');
        $script->setAttribute('charset', 'utf-8');

        return $nodes;
    }
}
