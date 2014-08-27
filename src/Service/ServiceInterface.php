<?php
namespace Grav\Plugin\VideoEmbed\Service;

interface ServiceInterface
{
    /**
     * Process html
     * @param string $html
     * @return string
     */
    public function processHtml($html);

    /**
     * Get regular expression, which take service url
     * @return string
     */
    public function getRegExpression();

    /**
     * Get embed node for replace link
     * @param $matches array
     * @return \DOMNode
     */
    public function getEmbedNode(array $matches);
} 