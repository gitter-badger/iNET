<?php
namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\entity\Wolf;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;

class SummonCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.give.description",
			"%pocketmine.command.give.usage"
		);
		$this->setPermission("pocketmine.command.give");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return true;
		}

		$player = $sender->getServer()->getPlayer($sender->getName());
		$entitytype = $args[0];
		
		if(!isset($args[1], $args[2], $args[3])){
			$position = $player->getPosition();
		}else{
			$position = new Position($args[1], $args[2], $args[3], $player->getLevel());
		}

		$chunk = $position->getLevel()->getChunk($position->getX() >> 4, $position->getZ() >> 4, true);

		if(!($chunk instanceof FullChunk)){
			return false;
		}
		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new Double("", $position->getX() + 0.5),
				new Double("", $position->getY()),
				new Double("", $position->getZ() + 0.5)
			]),
			"Motion" => new Enum("Motion", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
			]),
			"Rotation" => new Enum("Rotation", [
				new Float("", lcg_value() * 360),
				new Float("", 0)
			]),
		]);
		$entity = Entity::createEntity($entitytype, $chunk, $nbt);
		
		if(isset($args[4])){
			$tags = $exception = null;
			$data = implode(" ", array_slice($args, 4));
			try{
				$tags = NBT::parseJSON($data);
			}catch (\Exception $ex){
				$exception = $ex;
			}

			if(!($tags instanceof Compound) or $exception !== null){
				$sender->sendMessage(new TranslationContainer("commands.give.tagError", [$exception !== null ? $exception->getMessage() : "Invalid tag conversion"]));
				return true;
			}
			$entity->setNameTag($tags);
		}

		if($player instanceof Player){
			if($entity === null){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.give.item.notFound", [$entitytype]));

				return true;
			}

			//TODO: overflow
			$position->getLevel()->addEntity(clone $entity);
			$entity->spawnToAll();
		}else{
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

			return true;
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.give.success", [
			$entity->getName() . " (" . $entity->getId() . ":" . $entity->getName() . ")",
			$entity->getNameTag(),
			$player->getName()
		]));
		return true;
	}
}