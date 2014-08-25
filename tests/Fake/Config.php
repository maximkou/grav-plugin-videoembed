<?php
/**
 * Created by PhpStorm.
 * User: maximkou
 * Date: 26.08.14
 * Time: 0:56
 */

namespace Maximkou\GravPluginYoutube\Tests\Fake;


class Config extends \Grav\Common\Config
{
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}
