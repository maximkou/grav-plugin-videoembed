<?php
namespace Grav\Plugin\VideoEmbed;

/**
 * Interface ServiceInterface
 * @package Grav\Plugin\VideoEmbed
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
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
     * @return \DOMNode|\DOMNode[]
     */
    public function getEmbedNodes(array $matches);
}
