<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Component\EventDispatcher\Event;

/**
 * Class YoutubePlugin
 * This plugin replace links to youtube and replace it with embed element
 * @package Grav\Plugin
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @property $grav \Grav\Common\Grav
 */
class YoutubePlugin extends Plugin
{
    const VALID_YOUTUBE_URL_REGEXP = '/(youtube\.com|youtu\.be|youtube-nocookie\.com)\/(watch\?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*)).*/i';

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPageProcessed' => ['onPageProcessed', 0],
        ];
    }

    /**
     *
     * @param Event $event
     */
    public function onPageProcessed(Event $event)
    {
        /** @var \Grav\Common\Page\Page $page */
        $page = $event->offsetGet('page');
        $content = $this->replaceYoutubeLinks($page->content());

        $page->content($content);
    }

    /**
     * Replace youtube links with embed
     * @param $html
     * @return string
     */
    public function replaceYoutubeLinks($html)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query("//a[contains(@href, 'youtu')]");

        if (empty($nodes)) {
            return $html;
        }

        /** @var \DOMNode $node */
        foreach ($nodes as $node) {
            if ( !($href = $node->attributes->getNamedItem('href')) ) {
                continue;
            }

            if ( !($src = $this->prepareEmbedSrc($href->nodeValue)) ) {
                continue;
            }

            $newNode = $this->createDOMNode(
                $doc, 'div', (array)$this->config('container_html_attr', [])
            );

            $frameHtmlAttributes = array_merge(
                ['src' => $src],
                (array)$this->config('embed_html_attr', [])
            );

            $newNode->appendChild(
                $this->createDOMNode($doc, 'iframe', $frameHtmlAttributes)
            );

            $node->parentNode->replaceChild($newNode, $node);
        }

        return $doc->saveHTML();
    }

    /**
     * Check validity and add custom params for embed,
     * which defined in `embed_options` section of `youtube.yaml`
     * @param $sourceUrl string raw youtube url
     * @return null|string
     */
    protected function prepareEmbedSrc($sourceUrl)
    {
        $isYoutubeVideo = preg_match(
            self::VALID_YOUTUBE_URL_REGEXP,
            $sourceUrl,
            $matches
        );

        if (!$isYoutubeVideo) {
            return null;
        }

        $embedParams = '';
        if ($this->config('embed_options')) {
            $embedParams = '?'.http_build_query($this->config('embed_options'));
        }

        return '//youtube.com/embed/'.$matches[3].$embedParams;
    }

    /**
     * Create node with passed attributes
     * @param $doc \DOMDocument
     * @param $name string dom node name
     * @param array $attributes
     * @return \DOMElement
     */
    protected function createDOMNode(\DOMDocument $doc, $name, array $attributes = [])
    {
        $node = $doc->createElement($name);
        $this->batchSetAttributes(
            [$node, 'setAttribute'],
            $attributes
        );

        return $node;
    }

    /**
     * Set many attributes for node, e.g. [src => google.com, class => video]
     * @param $setter \Callable
     * @param array $attributes
     * @throws \InvalidArgumentException
     */
    protected function batchSetAttributes($setter, array $attributes)
    {
        if (!is_callable($setter)) {
            throw new \InvalidArgumentException('Func must be callable');
        }

        foreach ($attributes as $attr => $value) {
            call_user_func($setter, $attr, $value);
        }
    }

    /**
     * Get plugin specific config
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return $this->config->get('plugins.youtube.'.$key, $default);
    }
}
