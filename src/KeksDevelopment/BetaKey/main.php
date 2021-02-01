<?php
/**
 *  ______        _           _   __                      _____ 
 * | ___ \      | |         | | / /                     / __  \
 * | |_/ /  ___ | |_   __ _ | |/ /   ___  _   _  __   __`' / /'
 * | ___ \ / _ \| __| / _` ||    \  / _ \| | | | \ \ / /  / /  
 * | |_/ /|  __/| |_ | (_| || |\  \|  __/| |_| |  \ V / ./ /___
 * \____/  \___| \__| \__,_|\_| \_/ \___| \__, |   \_/  \_____/
 *                                        __/ |               
 *                                       |___/                
 * This BetaKey Plugin is made by KeksDev#9513 and LordArrow9#0717. 
 * Don't copy the Code or steal it. Copyright by KeksDevelopment.
 * Plugin created in 2021.
 * Our Discord: https://discord.gg/b4NMAJmPgn
 */

namespace KeksDevelopment\BetaKey;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\event\player\PlayerJoinEvent;

class main extends PluginBase implements Listener{

  public $keys;
  public $user;
  public $settings;

  public const PREFIX = "§e§lBetaKeyV2 §r> ";

  public function onEnable(){
    $this->keys = new Config($this->getDataFolder() . "keys.yml", Config::YAML);
    
    $this->user = new Config($this->getDataFolder() . "user.yml", Config::YAML);
    
    $this->saveResource("settings.yml");
    $this->settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
    
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $playername = $player->getName();
    
    if($this->user->get($playername) === null){
      $this->RedeemUI($player);
      return true;
    }
    if($this->user->get($playername) === false){
      $this->RedeemUI($player);
      return true;
    }
    $player->sendMessage(main::PREFIX. $this->settings->get("back-message"));
  }

  public function generateKey(Player $player, $largeness = 9){
    $token = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $generated_key = substr(str_shuffle($token), 0, $largeness);
    $this->keys->set($generated_key, true);
    $this->keys->save();
    $this->keys->reload();
    $player->sendMessage(main::PREFIX. "§6The key §c" . $generated_key . " §6has been generated");
    return $generated_key;
  }

  public function RedeemUI(Player $player){
    $form = new CustomForm(function(Player $player, $data = null){
      if($data === null){
        $player->kick($this->settings->get("kick-message"), false);
        return true;
      }
      if(empty($data[1])){
        $this->RedeemUI($player);
        return true;
      }
      if($this->keys->exists($data[0]) && $this->keys->get($data[0]) === true){
        
      $this->user->set($player->getName(), true);
      $this->user->save();
      $this->user->reload();
      $this->keys->set($data[0], false);
      $this->keys->save();
      $this->keys->reload();
      $player->sendMessage(main::PREFIX. $this->settings->get("used-key"));
      }else{
          $this->RedeemUI($player);
      }
    });
    $form->setTitle($this->settings->get("redeem-title"));
    $form->addLabel($this->settings->get("redeem-label"));
    $form->addInput($this->settings->get("redeem-input"));
    
    $form->sendToPlayer($player);
    return $form;
  }

  public function manageKeysUI(Player $player){
    $form = new SimpleForm(function(Player $player, int $data = null){
      if($data === null){
        return true;
      }
      switch($data){
        
        case 0:
        if(!$player->hasPermission("createkeys.ui")){
          $player->sendMessage(main::PREFIX. $this->settings->get("no-permission"));
          return true;
        }
        $this->createKeysUI($player);
        break;
        
        case 1:
        if(!$player->hasPermission("deletekeys.ui")){
          $player->sendMessage(main::PREFIX. $this->settings->get("no-permission"));
          return true;
        }
        $this->deleteKeysUI($player);
        break;
        
        case 2:
        if(!$player->hasPermission("listkeys.ui")){
          $player->sendMessage(main::PREFIX. $this->settings->get("no-permission"));
          return true;
        }
        $this->listKeysUI($player);
        break;
      }
    });
    $form->setTitle($this->settings->get("manage-title"));
    $form->setContent($this->settings->get("manage-content"));
    $form->addButton($this->settings->get("manage-button-create"));
    $form->addButton($this->settings->get("manage-button-delete"));
    $form->addButton($this->settings->get("manage-button-list"));
    
    $form->sendToPlayer($player);
    return $form;
  }

  public function createKeysUI(Player $player){
    $form = new CustomForm(function(Player $player, $data = null){
      if($data === null){
        $this->manageKeysUI($player);
        return true;
      }
      if(empty($data[0])){
        $player->sendMessage(main::PREFIX. $this->settings->get("input-empty"));
        return true;
      }
      if(!is_numeric($data[0])){
        $player->sendMessage(main::PREFIX. $this->settings->get("input-number"));
        return true;
      }
      if($data[0] == 0){
        $player->sendMessage(main::PREFIX. $this->settings->get("input-0"));
        return true;
      }
      if(empty($data[1])){
          for($i = 0; $i < $data[0]; $i++){
        $this->generateKey($player);
          }
      }else{
          
      if(!is_numeric($data[1])){
        $player->sendMessage(main::PREFIX. $this->settings->get("input-number"));
        return true;
      }
      if($data[1] < 6){
        $player->sendMessage(main::PREFIX. $this->settings->get("min-input"));
        return true;
      }
      if($data[1] > 14){
        $player->sendMessage(main::PREFIX. $this->settings->get("max-input"));
        return true;
      }
      for($i = 0; $i < $data[0]; $i++){
        $this->generateKey($player, $data[1]);
      }
      }
    });
    $form->setTitle($this->settings->get("create-title"));
    $form->addInput($this->settings->get("create-input1"));
    $form->addInput($this->settings->get("create-input2"));
    
    $form->sendToPlayer($player);
    return $form;
  }

  public function deleteKeysUI(Player $player){
    $form = new CustomForm(function(Player $player, $data = null){
      if($data === null){
        $this->manageKeysUI($player);
        return true;
      }
      if(empty($data[0])){
        $player->sendMessage("Message");
        return true;
      }
      if(!$this->keys->exists($data[0])){
        $player->sendMessage(main::PREFIX. $this->settings->get("key-dont-exists"));
        return true;
      }
      if($this->keys->get($data[0]) === false){
        $player->sendMessage(main::PREFIX. $this->settings->get("key-dont-exists"));
        return true;
      }
      $this->keys->set($data[0], false);
      $this->keys->save();
      $this->keys->reload();
      $player->sendMessage(main::PREFIX. $this->settings->get("key-deleted"));
    });
    $form->setTitle($this->settings->get("delete-title"));
    $form->addInput($this->settings->get("delete-input"));
    
    $form->sendToPlayer($player);
    return $form;
  }

  public function listKeysUI(Player $player){
    $form = new CustomForm(function(Player $player, $data = null){
      if($data === null){
        $this->manageKeysUI($player);
        return true;
      }
    });
    $form->setTitle($this->settings->get("list-title"));
    $UseableKeys = array("§aAll unused Beta-Keys:");
    foreach (array_keys($this->keys->getall()) as $key){
      if($this->keys->get($key) === true){
        array_push($UseableKeys, $key);
      }
    }
    $allkeys = implode("\n", $UseableKeys);
      $form->addLabel($allkeys);
    $form->sendToPlayer($player);
    return $form;
  }

  public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
    
    switch($cmd->getName()){
    
    case "betakeys":
      if(!$sender instanceof Player){
          $sender->sendMessage(main::PREFIX. $this->settings->get("only-ingame"));
          return true;
       }
       if(!$sender->hasPermission("betakeys.ui")){
         $sender->sendMessage(main::PREFIX. $this->settings->get("no-permission"));
         return true;
       }
       $this->manageKeysUI($sender);
       return true;
    }
    return true;
  }
}
