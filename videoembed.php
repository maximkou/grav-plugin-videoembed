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
        /** @var \Grav\Common\Page\Page $page */
        $page = $event->offsetGet('page');

        $content = $page->content();
        $services = $this->getEnabledServicesSettings();

        foreach ($services as $serviceName => $serviceConfig) {
            $service = $this->getServiceByName($serviceName, $serviceConfig);
            $content = $service->processHtml($content);
        }

        $page->process(['twig' => true]);

        $page->content($content);
    }

    /**
     * Enabled services settings
     * @return array
     */
    protected function getEnabledServicesSettings()
    {
        $services = (array)$this->config->get('plugins.videoembed.services');

        return array_filter($services, function($config) {
            return (!empty($config['enabled']) && $config['enabled']);
        });
    }

    /**
     * @param $serviceName
     * @param array $serviceConfig
     * @return \Grav\Plugin\VideoEmbed\Service\ServiceInterface
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
}
