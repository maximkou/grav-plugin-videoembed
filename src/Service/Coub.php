<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Plugin\VideoEmbed\ServiceAbstract;

class Coub extends ServiceAbstract
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
    public function getEmbedNode(array $matches)
    {
        $embedUrl = $this->createStandardEmbedUrl(
            'http://coub.com/embed/'.$matches[6],
            !empty($matches[7]) ? $matches[7] : null
        );

        $this->embed->setAttribute('src', $embedUrl);

        return $this->embed;
    }
}
