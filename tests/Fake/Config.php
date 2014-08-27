<?php
namespace Grav\Plugin\VideoEmbed\Tests\Fake;

class Config extends \Grav\Common\Config
{
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}
