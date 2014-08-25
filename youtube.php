<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Component\EventDispatcher\Event;

/**
 * Class YoutubePlugin
 * @package Grav\Plugin
 * @property $grav \Grav\Common\Grav
 */
class YoutubePlugin extends Plugin
{
    const VALID_YOUTUBE_URL_REGEXP = '/(youtube\.com|youtu\.be|youtube-nocookie\.com)\/(watch\?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*)).*/i';

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onPageProcessed' => ['onPageProcessed', 0],
        ];
    }

    public function onPageProcessed(Event $event)
    {
        /** @var \Grav\Common\Page\Page $page */
        $page = $event->offsetGet('page');

        $doc = new \DOMDocument();
        $doc->loadHTML($page->content(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query("//a[contains(@href, 'youtu')]");

        if (!empty($nodes)) {
            /** @var \DOMNode $node */
            foreach ($nodes as $node) {
                $isYoutubeVideo = preg_match(
                    self::VALID_YOUTUBE_URL_REGEXP,
                    $node->attributes->getNamedItem('href')->nodeValue,
                    $matches
                );

                if ($isYoutubeVideo) {
                    $newNode = $doc->createElement('div');
                    $newNode->setAttribute('class', 'video-container');

                    $iframe = $doc->createElement('iframe');
                    $iframe->setAttribute(
                        'src',
                        '//youtube.com/embed/'.$matches[3]
                    );
                    $newNode->appendChild($iframe);
                    $node->parentNode->replaceChild($newNode, $node);
                }
            }
        }

        $page->content($doc->saveHTML());
    }
}
