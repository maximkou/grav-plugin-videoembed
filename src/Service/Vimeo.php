<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\ServiceAbstract;

class Vimeo extends ServiceAbstract
{
    /**
     * @var \DOMNode
     */
    protected $embed;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->embed = $this->createDOMNode(
            new \DOMDocument(),
            'iframe',
            (array)$this->config('embed_html_attr', [])
        );
    }

    /**
     * Get regular expression, which take service url
     * @return string
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
    public function getEmbedNode(array $matches)
    {
        $embedUrl = $this->createStandardEmbedUrl(
            '//player.vimeo.com/video/'.$matches[7],
            !empty($matches[8]) ? $matches[8] : null
        );

        $this->embed->setAttribute('src', $embedUrl);

        return $this->embed;
    }
}
