<?php

declare(strict_types=1);

namespace xenialdan\MWEEMyPlot;

use pocketmine\plugin\PluginBase;

class Loader extends PluginBase
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
}