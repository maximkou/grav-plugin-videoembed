<?php
namespace Grav\Plugin\VideoEmbed\Service;

class Youtube extends ServiceAbstract
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
        $r .= '(youtube\.com|youtu\.be|youtube-nocookie\.com)\/';
        // video ID
        $r .= '(watch\?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*))';
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
        $embedUrl = '//youtube.com/embed/'.$matches[7];
        $userOpts = [];
        if (!empty($matches[11])) {
            parse_str(trim($matches[11], '?&'), $userOpts);
        }
        $userOpts = array_merge(
            (array)$this->config('embed_options', []),
            $userOpts
        );

        if (!empty($userOpts)) {
            $embedUrl .= '?'.http_build_query($userOpts);
        }

        $this->embed->setAttribute('src', $embedUrl);

        return $this->embed;
    }
}