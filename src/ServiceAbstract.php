<?php
namespace Grav\Plugin\VideoEmbed;

use Grav\Common\Data\Data;

/**
 * Class ServiceAbstract
 * @package Grav\Plugin\VideoEmbed
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 */
abstract class ServiceAbstract implements ServiceInterface
{
    const REGEXP_HTTP_SCHEME = '(http|https)?(:)?(\/\/)?(www\.)?';
    const REGEXP_ALLOWED_IN_URL = '[a-z0-9-._~:\/\?#\[\]@!$&\'()*\+,;\=]';

    /**
     * @var \Grav\Common\Data\Data
     */
    protected $config;

    /**
     * @var \DOMNode
     */
    protected $embed;

    public function __construct(array $config = [])
    {
        $this->config = new Data($config);
        $defaultHtmlAttributes = (array)$this->config->get('embed_html_attr', []);

        if ($this->config->get('embed_html_attr.allowfullscreen', false)) {
            // automatic add browser-specified allowfullscreen attributes
            $defaultHtmlAttributes = array_merge(
                $defaultHtmlAttributes,
                ['webkitallowfullscreen' => true, 'mozallowfullscreen' => true]
            );
        }

        $this->embed = $this->createDOMNode(
            new \DOMDocument(),
            'iframe',
            $defaultHtmlAttributes
        );
    }

    /**
     * Process html
     * @param string $html
     * @param \DOMNode $container
     * @return string
     */
    public function processHtml($html, \DOMNode $container = null)
    {
        $urlRegExpr = $this->getRegExpression();
        $linkRegexp = "/<a href=\"$urlRegExpr\">$urlRegExpr<\/a>/i";

        $document = new \DOMDocument();

        if (!$container) {
            $container = $document;
        } else {
            $container = $document->importNode($container);
        }

        return preg_replace_callback($linkRegexp, function ($matches) use ($document, $container) {
            $matches = array_map('html_entity_decode', $matches);

            $frameNode = $document->importNode(
                $this->getEmbedNode($matches),
                true
            );
            if ($container->hasChildNodes()) {
                $container->replaceChild($frameNode, $container->firstChild);
            } else {
                $container->appendChild($frameNode);
            }

            return $document->saveHTML($frameNode->parentNode);
        }, $html);
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

    protected function prepareStandardEmbed($embedUrl, $urlQuery)
    {
        $userOpts = [];
        if (!empty($urlQuery)) {
            parse_str(trim($urlQuery, '?&'), $userOpts);
        }
        $userOpts = array_merge(
            (array)$this->config->get('embed_options', []),
            $userOpts
        );

        if (!empty($userOpts)) {
            $embedUrl .= '?'.http_build_query($userOpts);
        }

        $this->embed->setAttribute('src', $embedUrl);

        return $this->embed;
    }
}
