<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Component\EventDispatcher\Event;

/**
 * Class VideoEmbedPlugin
 * This plugin replace links to youtube and replace it with embed element
 * @package Grav\Plugin
 * @author Maxim Hodyrev <maximkou@gmail.com>
 *
 * @property $grav \Grav\Common\Grav
 * @property $config \Grav\Common\Config
 */
class VideoEmbedPlugin extends Plugin
{
    /** @var \Grav\Common\Data\Data */
    protected $cfg;

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
     * @throws \Exception
     */
    public function onPageProcessed(Event $event)
    {
        require_once __DIR__ . "/src/ServiceInterface.php";
        require_once __DIR__ . "/src/ServiceAbstract.php";

        /** @var \Grav\Common\Page\Page $page */
        $page = $event->offsetGet('page');
        $headers = $page->header();
        $this->initConfig(isset($headers->videoembed) ? $headers->videoembed : []);

        $content = $page->content();
        $services = $this->getEnabledServicesSettings();

        $container = null;
        if ($cElem = $this->cfg->get('container.element')) {
            $document = new \DOMDocument();
            $container = $document->createElement($cElem);
            $containerAttr = (array)$this->cfg->get('container.html_attr', []);
            foreach ($containerAttr as $htmlAttr => $attrValue) {
                $container->setAttribute($htmlAttr, $attrValue);
            }
        }

        foreach ($services as $serviceName => $serviceConfig) {
            $service = $this->getServiceByName($serviceName, $serviceConfig);
            $content = $service->processHtml($content, $container);
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
     */
    protected function getConfig()
    {
        if (!$this->cfg) {
            return $this->initConfig();
        }
        return $this->cfg;
    }

    /**
     * Init config anf merge this with user params
     * @param array $userConfig
     * @return \Grav\Common\Data\Data
     */
    protected function initConfig($userConfig = [])
    {
        $config = $this->mergeOptions(
            (array)$this->config->get('plugins.videoembed', []),
            $userConfig
        );

        return $this->cfg = new \Grav\Common\Data\Data($config);
    }
}
