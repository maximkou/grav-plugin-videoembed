<?php
namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Component\EventDispatcher\Event;
use \Grav\Common\Data\Data;

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
    protected $cfg;

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents()
    {
        return [
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onPageProcessed' => ['onPageProcessed', 0],
        ];
    }

    /**
     * Add styles for video responsiveness
     * @see onPageInitialized
     * @return void
     * @codeCoverageIgnore
     */
    public function onTwigSiteVariables()
    {
        /** @var \Grav\Common\Page\Page $page */
        $page = $this->grav['page'];

        $assets = [];
        if (!empty($page->header()->videoembed['assets'])) {
            $assets = (array)$page->header()->videoembed['assets'];
        }

        foreach ($assets as $asset) {
            $this->grav['assets']->add($asset);
        }
    }

    /**
     * Process links in page
     * @param Event $event
     * @throws \Exception
     */
    public function onPageProcessed(Event $event)
    {
        require_once __DIR__ . "/src/ServiceInterface.php";
        require_once __DIR__ . "/src/ServiceAbstract.php";

        /** @var \Grav\Common\Page\Page $page */
        $page = $event->offsetGet('page');

        $content = $page->content();
        $services = $this->getEnabledServicesSettings();
        $container = $this->getEmbedContainer();

        $usedServices = [];
        foreach ($services as $serviceName => $serviceConfig) {
            $service = $this->getServiceByName($serviceName, $serviceConfig);
            $content = $service->processHtml($content, $container, $processedCnt);

            if ($processedCnt > 0) {
                $usedServices[] = $serviceName;
            }
        }

        if (!empty($usedServices)) {
            $this->defineAssets($page, $usedServices);
        }

        $isProcessMarkdown = $page->shouldProcess('markdown');
        $page->process(['markdown' => false]);
        $page->content($content);
        $page->process(['markdown' => $isProcessMarkdown]);
    }

    /**
     * Enabled services settings
     * @return array
     */
    public function getEnabledServicesSettings()
    {
        $services = (array)$this->getConfig()->get('services');

        return array_filter($services, function ($config) {
            return (!empty($config['enabled']) && $config['enabled']);
        });
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

    /**
     * @return \Grav\Common\Data\Data
     * @codeCoverageIgnore
     */
    protected function getConfig()
    {
        if (!$this->cfg) {
            return $this->initConfig();
        }

        return $this->cfg;
    }

    /**
     * Init config and merge this with user params
     * @param array $userConfig
     * @return \Grav\Common\Data\Data
     * @throws \ErrorException
     */
    protected function initConfig($userConfig = [])
    {
        $config = (array)$this->config->get('plugins.videoembed', []);

        if (isset($this->grav['page'])) {
            $headers = $this->grav['page']->header();

            if (isset($headers->videoembed)) {
                $config = $this->mergeOptions($config, (array)$headers->videoembed);
            }
        }

        if (!empty($userConfig)) {
            $config = $this->mergeOptions($config, $userConfig);
        }

        $this->cfg = new Data($config);

        if ($this->cfg->get('responsive')) {
            $this->enableResponsiveness();
        }

        return $this->cfg;
    }

    /**
     * @throws \ErrorException
     */
    protected function enableResponsiveness()
    {
        $config = $this->getConfig();

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
     * @param array $services
     */
    protected function defineAssets(Page $page, array $services)
    {
        if (empty($services)) {
            return;
        }

        $assets = (array)$this->getConfig()->get('assets', []);

        foreach ($services as $name) {
            $assets = array_merge(
                $assets,
                $this->getConfig()->get("services.$name.assets", [])
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
     * @return \DOMElement|null
     */
    protected function getEmbedContainer()
    {
        $container = null;
        if ($cElem = $this->getConfig()->get('container.element')) {
            $document = new \DOMDocument();
            $container = $document->createElement($cElem);
            $containerAttr = (array)$this->getConfig()->get('container.html_attr', []);
            foreach ($containerAttr as $htmlAttr => $attrValue) {
                $container->setAttribute($htmlAttr, $attrValue);
            }
        }

        return $container;
    }
}
