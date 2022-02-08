<?php

namespace TungstenVn;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerJoinEvent;
class MineLevel extends PluginBase implements Listener
{


    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
           if(!isset($args[0])){
               $level = $this->getLevel($sender);
               $block = $this->getBlock($sender);
               $sender->sendMessage("§a§l【 §fLevel mine của bạn hiện tại là §6$level §a】");
               $sender->sendMessage("§a§l- §r§fBạn đã đào được §6[$block]§f block");
               $marker = $this->getConfig()->getNested("marker");
               if(!isset($marker[$level +1])){
                   $sender->sendMessage("§a§l- §r§6Bạn đã đạt cấp độ cao nhất của thẻ mùa, xin chúc mừng");
               }else{
                   $sender->sendMessage("§a§l- §r§fSố block cần đào để lên cấp mới là §6".$marker[$level +1]);
               }

           }else{
               a:
               $player = $this->getServer()->getPlayer($args[0]);
               if (null != $player) {
                   $level = $this->getLevel($player);
                   $block = $this->getBlock($player);
                   $name = $player->getName();
                   $sender->sendMessage("§a§l【 §fLevel mine của §6[$name] §flà §6$level §a】");
                   $sender->sendMessage("§a§l- §r§fBạn ấy đã đào được §6[$block]§f block");
               } else {
                   $sender->sendMessage("§a§l- §r§cKhông tìm thấy tên người chơi này");
               }
           }
        } else {
            if(isset($args[0])){
                goto a;
            }else{
                $sender->sendMessage("§a§l- §r§cThiếu tên, bảng điều khiển không thể sử dụng lệnh này một mình");
            }
        };
        return true;
    }
    public function getLevel(Player $player){
        $name = $player->getName();
        $level = $this->getConfig()->getNested("database")[$name]["level"];
        if (isset($level)) {
            return $level;
        }else{
            return "DataMissing";
        }
    }
    public function getBlock(Player $player){
        $name = $player->getName();
        $block = $this->getConfig()->getNested("database")[$name]["block"];
        if (isset($block)) {
            return $block;
        }else{
            return "data Missing";
        }
    }
    
    public function onBreak(BlockBreakEvent $e)
    {
        if($e->isCancelled()){
          return;
        }
        $name = $e->getPlayer()->getName();
        if (!isset($this->getConfig()->getNested("database")[$name]["block"]) || !isset($this->getConfig()->getNested("database")[$name]["level"])) {
            print("There is fatal problem at MineLevel,missing data of $name");
            return;
        }
        $block = $this->getConfig()->getNested("database")[$name]["block"];
        $level = $this->getConfig()->getNested("database")[$name]["level"];
        $this->getConfig()->setNested("database.$name.block", $block + 1);
        $this->getConfig()->save();

        $marker = $this->getConfig()->getNested("marker");
        if(!isset($marker[$level +1])){
            return;
        }
        if ($marker != null) {
            if ($block + 1 >= $marker[$level + 1]) {
                $this->getConfig()->setNested("database.$name.level", $level + 1);
                $this->getConfig()->save();
                $level = $level + 1;
                $block = $block + 1;
                if(!isset($marker[$level +1])){
                    $this->getServer()->broadcastMessage("§l§a【Ｃấp Ｍine】 §r§fChúc mừng §e[$name] §fđã §cđạt level cao nhất §fcủa §cMineLevel (thẻ mùa) §f. Level: §e[$level]§f, Tổng block đã đào:§e [$block]");
                }else{
                    $this->getServer()->broadcastMessage("§l§a【Ｃấp Ｍine】 §r§fChúc mừng §e[$name] §fđã lên MineLevel (thẻ mùa) là §e[$level]§f, Tổng block đã đào:§e [$block]");
                }
            }
        } else {
            print("There are nothing on 'marker' on MineLevel's config, pls check, dont leave it empty");
            return;
        }
    }

    public function onJoin(PlayerJoinEvent $e)
    {
        $name = $e->getPlayer()->getName();
        if (!isset($this->getConfig()->getNested("database")[$name])) {
            $this->getConfig()->setNested("database.$name.block", 0);
            $this->getConfig()->setNested("database.$name.level", 0);
            $this->getConfig()->save();
        }
    }
}