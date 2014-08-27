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
        // starts with scheme, www
        $r = '(http|https)?(:)?(\/\/)?(www\.)?';
        // youtube valid hosts
        $r .= '(player\.)?vimeo\.com\/';
        // video ID
        $r .= '(video\/)?(\d+)';
        // more params
        $r .= '([a-z0-9-._~:\/\?#\[\]@!$&\'()*\+,;\=]*)';

        return $r;
    }

    /**
     * Get embed node for replace link
     * @param $matches array
     * @return \DOMNode
     */
    public function getEmbedNode(array $matches)
    {
        $src = '//player.vimeo.com/video/'.$matches[7];

        $userOpts = [];
        if (!empty($matches[8])) {
            parse_str(trim($matches[8], '?&#'), $userOpts);
        }
        $userOpts = array_merge(
            (array)$this->config('embed_options', []),
            $userOpts
        );

        if (!empty($userOpts)) {
            $src .= '?'.http_build_query($userOpts);
        }

        $this->embed->setAttribute('src', $src);

        return $this->embed;
    }
}
