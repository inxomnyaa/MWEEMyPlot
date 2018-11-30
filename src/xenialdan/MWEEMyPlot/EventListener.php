<?php

namespace xenialdan\MWEEMyPlot;

use MyPlot\MyPlot;
use MyPlot\Plot;
use MyPlot\PlotLevelSettings;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\MagicWE2\event\MWEEditEvent;
use xenialdan\MagicWE2\Selection;
use xenialdan\MagicWE2\Session;

class EventListener implements Listener
{
    public $owner;

    public function __construct(Plugin $plugin)
    {
        $this->owner = $plugin;
    }

    //TODO cleanup and optimisation
    public function onMWEEdit(MWEEditEvent $event)
    {
        if ($event->isCancelled()) return;
        if (($session = $event->getSession()) instanceof Session) {
            //return if no selection
            if (!($selection = $session->getLatestSelection()) instanceof Selection) return;
            if (!$selection->isValid()) return;
            if (($player = $session->getPlayer()) instanceof Player) {
                //check if it is a MyPlot level
                if (!($settings = MyPlot::getInstance()->getLevelSettings($player->getLevel()->getFolderName())) instanceof PlotLevelSettings) return;
                if ($settings->plotSize < $selection->getSizeX() || $settings->plotSize < $selection->getSizeZ() && !$player->hasPermission("mwee.myplot.extempt")) {
                    $player->sendMessage(TextFormat::RED . "Your selection is bigger than a plot, aborting edit!");
                    $event->setCancelled();
                    return;
                }
                //make sure you edit on the same plot
                $plot1 = MyPlot::getInstance()->getPlotByPosition($selection->getPos1());
                $plot2 = MyPlot::getInstance()->getPlotByPosition($selection->getPos2());
                //allow admin permission to do everything
                if ($player->hasPermission("myplot.admin.build")) return;
                //allow plot edit for plot helpers/owners and admins that may build on plots - when same plot
                if ($plot1 instanceof Plot && $plot2 instanceof Plot && $plot1 === $plot2 && ($player->hasPermission("myplot.admin.build.plot") || $plot1->owner === $player->getName() || $plot1->isHelper($player->getName()))) return;
                //if not same plot, only allow edits on plots the player owns - or admins that can do it
                else {
                    //TODO performance improvement
                    foreach (($blocks = $event->getNewBlocks()) as $i => $block) {
                        $plot = MyPlot::getInstance()->getPlotByPosition($block);
                        if ($plot instanceof Plot) {
                            if (!($player->hasPermission("myplot.admin.build.plot") || $plot1->owner === $player->getName() || $plot1->isHelper($player->getName()))) {
                                unset($blocks[$i]);
                            }
                        } elseif (!$player->hasPermission("myplot.admin.build.road")) {
                            unset($blocks[$i]);
                        }
                    }
                }
                $event->setNewBlocks($blocks);
            }
        }
    }
}