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
        $urlRegExpr = $this->getRegExpr();
        $linkRegexp = "/<a href=\"$urlRegExpr\">$urlRegExpr<\/a>/i";

        $document = new \DOMDocument();
        $containerNode = $this->createDOMNode(
            $document,
            'div',
            (array)$this->config('container_html_attr', [])
        );
        $frameNode = $this->createDOMNode(
            $document,
            'iframe',
            (array)$this->config('embed_html_attr', [])
        );
        $containerNode->appendChild($frameNode);

        $embedOptions = $this->config('embed_options', []);

        return preg_replace_callback($linkRegexp, function ($matches) use ($document, $frameNode, $embedOptions) {
            $embedUrl = '//youtube.com/embed/'.$matches[7];
            $userOpts = [];
            if (!empty($matches[11])) {
                parse_str(trim($matches[11], '?&'), $userOpts);
            }
            $userOpts = array_merge($embedOptions, $userOpts);

            if (!empty($userOpts)) {
                $embedUrl .= '?'.http_build_query($userOpts);
            }

            $frameNode->setAttribute('src', $embedUrl);

            return html_entity_decode($document->saveHTML($frameNode->parentNode));
        }, $html);
    }

    /**
     * Get youtube video reg expression
     * @return string
     */
    protected function getRegExpr()
    {
        // starts with scheme, www
        $r = '(http|https)?(:)?(\/\/)?(www\.)?';
        // youtube valid hosts
        $r .= '(youtube\.com|youtu\.be|youtube-nocookie\.com)\/';
        // video ID
        $r .= '(watch\?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*))';
        // more params
        $r .= '([a-z0-9-._~:\/\?#\[\]@!$&\'()*\+,;\=]*)';

        return $r;
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
