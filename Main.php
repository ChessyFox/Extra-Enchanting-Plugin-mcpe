<?php

declare(strict_types=1);

namespace EnchantAdvance;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener {

    private Config $config;

    public function onEnable(): void {
        $this->saveDefaultConfig(); // Creates config.yml if missing
        $this->config = $this->getConfig(); // Loads config.yml
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("CustomEnchant Plugin Loaded!");
    }

    public function onDisable(): void {
        $this->getLogger()->info("CustomEnchant Plugin Disabled!");
    }

    public function applyCustomEnchantments(Player $player, Item $item): void {
        $enchants = $this->config->get("enchantments", []);

        foreach ($enchants as $enchantName => $settings) {
            $enchant = Enchantment::getEnchantmentByName($enchantName);
            if ($enchant !== null) {
                $maxLevel = $settings["max_level"] ?? 5;
                $xpRequired = $settings["xp_costs"] ?? [30, 40, 50];

                $playerXp = $player->getXpLevel();
                for ($level = count($xpRequired); $level > 0; $level--) {
                    if ($playerXp >= $xpRequired[$level - 1]) {
                        $item->addEnchantment(new EnchantmentInstance($enchant, $level));
                        $player->sendMessage("§aApplied {$enchantName} level {$level} to your item!");
                        return;
                    }
                }
                $player->sendMessage("§cNot enough XP to enchant with {$enchantName}.");
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->isEnchantable()) {
            $this->applyCustomEnchantments($player, $item);
        }
    }
}
