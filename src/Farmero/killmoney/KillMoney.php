<?php

declare(strict_types=1);

namespace Farmero\killmoney;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\player\Player;

use Farmero\moneysystem\MoneySystem;

class KillMoney extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (Server::getInstance()->getPluginManager()->getPlugin("MoneySystem") === null) {
            $this->getLogger()->info("Disabling KillMoney, MoneySystem not found... Please make sure to have it installed before trying again!");
            Server::getInstance()->getPluginManager()->disablePlugin($this);
            return;
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if ($entity instanceof Player && $damager instanceof Player) {
            if ($entity->getHealth() - $event->getFinalDamage() <= 0) {
                $this->handlePlayerKill($entity, $damager);
            }
        }
    }

    private function handlePlayerKill(Player $victim, Player $killer): void {
        $amountToTransfer = 100;

        $victimMoney = MoneySystem::getInstance()->getMoneyManager()->getMoney($victim);
        if ($victimMoney < $amountToTransfer) {
            $amountToTransfer = $victimMoney;
        }
        MoneySystem::getInstance()->getMoneyManager()->removeMoney($victim, $amountToTransfer);
        MoneySystem::getInstance()->getMoneyManager()->addMoney($killer, $amountToTransfer);
        $victim->sendMessage("You lost $amountToTransfer money for being killed.");
        $killer->sendMessage("You gained $amountToTransfer money for killing {$victim->getName()}.");
    }
}
