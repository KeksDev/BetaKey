<?php

namespace BetaKey;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\event\Listener;

use jojoe77777\FormAPI\SimpleForm;

use jojoe77777\FormAPI\CustomForm;

use pocketmine\utils\Config;

use pocketmine\utils\TextFormat;

use pocketmine\Player;

class main extends PluginBase implements Listener {

	public $cfg;	public $usr;

	

	public function onEnable() {

		$this->cfg = new Config($this->getDataFolder() . "keys.yml", Config::YAML);

		$this->usr = new Config($this->getDataFolder() . "user.yml", Config::YAML);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getLogger()->info("BetaKey loaded.");

	}

	

	public function onJoin(PlayerJoinEvent $ev) {

		

		if($this->usr->get($ev->getPlayer()->getName()) === null) {

			$this->usr->set($ev->getPlayer()->getName(), false);

			$this->usr->save();

			$this->usr->reload();

			$this->sendRedeem($ev->getPlayer());

		}

		if($this->usr->get($ev->getPlayer()->getName()) === false) {

			$this->sendRedeem($ev->getPlayer());

		} else {

			$ev->getPlayer()->sendMessage("§bWelcome back");

		}

	}

	

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool {

		if($command->getName() === "genkey") {

			if(!isset($args[0])) {

				$sender->sendMessage("§cPlease provide a number of keys");

				return true;

			}

			

			if(!is_numeric($args[0])) {

				$sender->sendMessage("§cPlease provide a number");

				return true;

			}

			

			if($args[0] === 0) {

				$sender->sendMessage("§cPlease provide a number over 0");

				return true;

			}

			

			for ($i = 0; $i < $args[0]; $i++) {

				$key = $this->genKey();

				$sender->sendMessage("§e".$key);

				$this->cfg->set($key, true);

				$this->cfg->save();

				$this->cfg->reload();

			}

			return true;

		}

	}

	 

	public function genKey($length = 7) {

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $charactersLength = strlen($characters);

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {

        $randomString .= $characters[rand(0, $charactersLength - 1)];

    }

    return $randomString;

}

	public function checkKey(Player $u, string $key) {

		$keys = $this->cfg;

		if($this->getKey($key) === true) {

			$this->usr->set($u->getName(), true);

			$this->usr->save();

			$this->usr->reload();

			$this->delKey($key);

		} else {

			$this->sendRedeem($u);

		}

	}

	

	public function getKey(string $key) {

		$keys = $this->cfg;

		if($keys->get($key) !== true) {

			return false;

		} else {

			return true;

		}

	}

	

	public function delKey(string $key) {

		$keys = $this->cfg;

		$keys->set($key, false);

		$keys->save();

		$keys->reload();

	}

	

	

	/*

		TODO: Fix and implement it

	*/

	

	public function openMenu(Player $player) {

		$form = new SimpleForm(function (Player $player, $data = null){

            $result = $data;

            if($result === null){

                return true;

            }

			if($result === 1) {

				$this->sendRedeem($player);

				return true;

			}       

		

        });

        $form->setTitle("§l§cBetaKey");

        $form->setContent("§o§ePlease choose an option");

        $form->addButton("§l§6Redeem Key", 1);

		$form->sendToPlayer($player);

	}

	

	public function sendRedeem(Player $player) {

		$form = new CustomForm(function(Player $player, $data = null){

			if($data === null){

				$player->kick("§cYou're not verified.", false);

				return true;

			}

			if(!empty($data[0])) {

				$this->checkKey($player, $data[0]);

				return true;

			}

			if(empty($data[0])){

				$this->sendRedeem($player);

				return true;

			}

			

		

			

		});

		$form->setTitle("§l§cBetaKey");

		$form->addInput("§eBut in your Beta Key here", "Fill in your Key here");

		$form->addLabel("§cMade by KeksDev#9513");

		$form->sendToPlayer($player);

	} 

}
