<?php
namespace Grav\Plugin;

use Grav\Common\Assets;
use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Data\Data;

/**
 * Class VideoEmbedPlugin
 * This plugin replace links to youtube and replace it with embed element
 * @package Grav\Plugin
 * @author Maxim Hodyrev <maximkou@gmail.com>
 * @license MIT
 *
 * @property $grav \Grav\Common\Grav
 * @property $config \Grav\Common\Config
 */
class VideoEmbedPlugin extends Plugin
{
    /**
     * @var \Grav\Common\Data\Data
     */
    private $cfg;

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents()
    {
        return [
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onPageContentProcessed' => ['onPageContentProcessed', 0],
        ];
    }

    /**
     * Process links in page
     * @param Event $event
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function onPageContentProcessed(Event $event)
    {
        require_once __DIR__ . "/src/ServiceInterface.php";
        require_once __DIR__ . "/src/ServiceAbstract.php";

        /** @var \Grav\Common\Page\Page $page */
        $page = $event['page'];
        $config = $this->getConfig($page);

        if ( $this->active ) {
            $content = $this->processPage($page, $config);

            $isProcessMarkdown = $page->shouldProcess('markdown');
            $page->process(['markdown' => false]);
            $page->setRawContent($content);
            $page->process(['markdown' => $isProcessMarkdown]);
        }
    }

    /**
     * Add styles for video responsiveness
     * @see onPageInitialized
     * @return void
     * @codeCoverageIgnore
     */
    public function onTwigSiteVariables()
    {
        $this->addAssets($this->grav['page'], $this->grav['assets']);
    }

    /**
     * @param $serviceName
     * @param array $serviceConfig
     * @return \Grav\Plugin\VideoEmbed\ServiceInterface
     * @throws \Exception
     */
    protected function getServiceByName($serviceName, array $serviceConfig = [])
    {
        $serviceName = ucfirst($serviceName);
        $servicePath = __DIR__."/src/Service/$serviceName.php";
        $serviceClass = "\\Grav\\Plugin\\VideoEmbed\\Service\\$serviceName";

        if (!file_exists($servicePath)) {
            throw new \Exception("Service $serviceName is not supported.");
        }

        require_once $servicePath;

        return new $serviceClass($serviceConfig);
    }

    /**
     * @param Page $page
     * @return \Grav\Common\Data\Data
     * @codeCoverageIgnore
     */
    protected function getConfig(Page $page = null)
    {
        if ($this->cfg && empty($page)) {
            return $this->cfg;
        }

        if (!$this->cfg) {
            $this->cfg = new Data(
                (array)$this->config->get('plugins.videoembed', [])
            );
        }

        if (!$this->cfg->get('all_pages')) {
            $this->active = false;
        }

        if (!empty($page)) {
            $headers = $page->header();

            if (isset($headers->videoembed)) {

                if ($headers->videoembed == false) {
                    $this->active = false;
                    return $this->cfg;
                }

                $this->active = true;

                $this->cfg = new Data(
                    $this->mergeOptions(
                        $this->cfg->toArray(),
                        (array)$headers->videoembed
                    )
                );
            }
        }

        return $this->cfg;
    }

    /**
     * @param Page $page
     * @param Data $config
     * @return string
     * @throws \ErrorException
     * @throws \Exception
     */
    protected function processPage(Page $page, Data $config)
    {
        if ($config->get('responsive')) {
            $this->enableResponsiveness($config);
        }

        $container = $this->getEmbedContainer($config);
        $services = array_filter(
            (array)$config->get('services', []),
            function ($service) {
                return !empty($service['enabled']);
            }
        );

        $usedServices = [];
        $content = $page->getRawContent();
        foreach ($services as $serviceName => $serviceConfig) {
            $service = $this->getServiceByName($serviceName, $serviceConfig);
            $content = $service->processHtml($content, $container, $processedCnt);

            if ($processedCnt > 0) {
                $usedServices[] = $serviceName;
            }
        }

        if (!empty($usedServices)) {
            $this->defineAssets($page, $config, $usedServices);
        }

        return $content;
    }

    /**
     * @throws \ErrorException
     */
    protected function enableResponsiveness(Data $config)
    {
        /**
         * if you enable responsiveness, you need use some container for video
         * @see http://css-tricks.com/NetMag/FluidWidthVideo/Article-FluidWidthVideo.php
         */
        if (!$config->get('container.element')) {
            throw new \ErrorException(
                '"Responsive" option requires using some container for video.
                Please, set "container.element" option or disable responsiveness.'
            );
        }

        $containerClasses = explode(' ', $config->get('container.html_attr.class', ''));
        $containerClasses = array_map('trim', $containerClasses);

        $containerClasses[] = 'plugin-videoembed-container-fluid';
        $containerClasses = array_unique($containerClasses);

        $config->set(
            'container.html_attr.class',
            implode(' ', $containerClasses)
        );

        $assets = (array)$config->get('assets', []);
        $assets[] = 'plugin://videoembed/css/videombed-responsive.css';

        $config->set('assets', array_unique($assets));
    }

    /**
     * @param Page $page
     * @param Data $config
     * @param array $services
     */
    protected function defineAssets(Page $page, Data $config, array $services)
    {
        if (empty($services)) {
            return;
        }

        $assets = (array)$config->get('assets', []);
        foreach ($services as $name) {
            $assets = array_merge(
                $assets,
                $config->get("services.$name.assets", [])
            );
        }
        $assets = array_unique($assets);

        if (!empty($assets)) {
            $existHeaders = [];
            $headers = $page->header();

            if (isset($headers->videoembed)) {
                $existHeaders = $headers->videoembed;
            }

            $page->header()->videoembed = array_merge(
                $existHeaders,
                ['assets' => $assets]
            );
        }
    }

    /**
     * Add plugin specific assets
     * @param Page $page
     * @param Assets $assets
     */
    protected function addAssets(Page $page, Assets $assets)
    {
        $pluginAssets = [];
        if (!empty($page->header()->videoembed['assets'])) {
            $pluginAssets = (array)$page->header()->videoembed['assets'];
        }

        foreach ($pluginAssets as $asset) {
            $assets->add($asset);
        }
    }

    /**
     * @param Data $config
     * @return \DOMElement|null
     */
    protected function getEmbedContainer(Data $config)
    {
        $container = null;
        if ($cElem = $config->get('container.element')) {
            $document = new \DOMDocument();
            $container = $document->createElement($cElem);
            $containerAttr = (array)$config->get('container.html_attr', []);

            foreach ($containerAttr as $htmlAttr => $attrValue) {
                $container->setAttribute($htmlAttr, $attrValue);
            }
        }

        return $container;
    }

    /**
     * Merge options recursively
     *
     * @param  array $array1
     * @param  mixed $array2
     * @return array
     * @codeCoverageIgnore
     */
    protected function mergeOptions(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                        ? $this->mergeOptions($array1[$key], $array2[$key])
                        : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }

        return $array1;
    }
}
