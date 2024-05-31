<?php

declare(strict_types=1);

namespace Farmero\killmoney;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\player\Player;

use Farmero\moneysystem\MoneySystem;

class KillMoney extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerKill(EntityDeathEvent $event): void {
        $entity = $event->getEntity();
        $cause = $entity->getLastDamageCause();

        if ($entity instanceof Player && $cause !== null && $cause->getDamager() instanceof Player) {
            $victim = $entity;
            $killer = $cause->getDamager();

            $amountToTransfer = 100;

            
            $victimMoney = MoneySystem::getInstance()->getMoneyManager()->getMoney($victim);
            if ($victimMoney < $amountToTransfer) {
                $amountToTransfer = $victimMoney;
            }

            MoneySystem::getInstance()->getMoneyManager()->removeMoney($victim, $amountToTransfer);
            MoneySystem::getInstance()->getMoneyManager()->addMoney($killer, $amountToTransfer);

            $victim->sendMessage("You lost $amountToTransfer money for being killed!");
            $killer->sendMessage("You gained $amountToTransfer money for killing {$victim->getName()}!");
        }
    }
}