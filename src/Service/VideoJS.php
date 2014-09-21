<?php
namespace Grav\Plugin\VideoEmbed\Service;

use Grav\Common\Data\Data;
use Grav\Plugin\VideoEmbed\ServiceAbstract;

/**
 * Class VideoJS
 * @see http://www.videojs.com/
 * @package Grav\Plugin\VideoEmbed\Service
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
class VideoJS extends ServiceAbstract
{
    protected $notSupportedMessage = <<<EOT
        <p class="vjs-no-js">
            To view this video please enable JavaScript,
            and consider upgrading to a web browser that
            <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
        </p>
EOT;

    /**
     * Supported video extensions
     * @link http://www.videojs.com/projects/mimes.html
     * @var array
     */
    protected $supportedExt = [
        'webm'  => 'video/webm',
        'ogg'   => 'video/ogg',
        'mp4'   => 'video/mp4',
        'm4v'   => 'video/mp4',
        'mp2t'  => 'video/mp2t',
        'avi'   => 'video/avi',
        '3gp'   => 'video/3gp',
        '3gpp'  => 'video/3gpp',
        '3gpp2' => 'video/3gpp2',
        'm3u8'  => 'application/x-mpegURL',
        'mov'   => 'video/quicktime',
    ];

    public function __construct(array $config = [])
    {
        $this->config = new Data($config);

        $this->embed = $this->createDOMNode(
            new \DOMDocument(),
            'video',
            (array)$this->config->get('embed_html_attr', [])
        );
    }

    /**
     * Get regular expression, which take VideoJS video url
     * @return string
     * @codeCoverageIgnore
     */
    public function getRegExpression()
    {
        $r = '('.self::REGEXP_HTTP_SCHEME.'('.self::REGEXP_ALLOWED_IN_URL.'+)';
        $r .= '\.('.implode('|', array_keys($this->getSupportedExt())).')';
        $r .= '('.self::REGEXP_ALLOWED_IN_URL.'*))';

        return $r;
    }

    /**
     * Get embed node for replace link
     * @param $matches array
     * @return \DOMNode
     */
    public function getEmbedNodes(array $matches)
    {
        $query = $matches[8];
        $dataSetup = [];
        if (!empty($query)) {
            parse_str(trim($query, '?&'), $dataSetup);
        }
        $dataSetup = array_merge(
            (array)$this->config->get('data_setup', []),
            $dataSetup
        );

        $this->embed->setAttribute(
            'data-setup',
            json_encode($dataSetup)
        );

        $doc = $this->embed->ownerDocument;

        $source = $doc->createElement('source');
        $source->setAttribute('src', $matches[1]);
        $source->setAttribute(
            'type',
            $this->getSupportedExt()[$matches[7]]
        );

        $isNotSupportedMsg = $doc->createDocumentFragment();
        $isNotSupportedMsg->appendXML($this->notSupportedMessage);

        $this->embed->appendChild($source);
        $this->embed->appendChild($isNotSupportedMsg);

        return $this->embed;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function getSupportedExt()
    {
        return $this->supportedExt;
    }
}
