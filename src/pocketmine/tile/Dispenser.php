<?php
namespace pocketmine\tile;

use pocketmine\entity\Entity;
use pocketmine\inventory\DispenserInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Short;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Int;
use pocketmine\item\Launchable;
use pocketmine\item\Dye;
use pocketmine\block\Crops;

class Dispenser extends Spawnable implements InventoryHolder, Container, Nameable{

	/** @var DispenserInventory */
	protected $inventory;

	public function __construct(FullChunk $chunk, Compound $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new DispenserInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof Enum)){
			$this->namedtag->Items = new Enum("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}
		
		$this->scheduleUpdate();
	}

	public function close(){
		if($this->closed === false){
			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}
			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items = new Enum("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 9;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if((int) $slot["Slot"] === (int) $index){
				return (int) $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return NBT::getItemHelper($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = NBT::putItemHelper($item, $index);

		if($item->getId() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}

		return true;
	}

	/**
	 * @return DispenserInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function getName(){
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Dispenser";
	}

	public function hasName(){
		return isset($this->namedtag->CustomName);
	}

	public function setName($str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new String("CustomName", $str);
	}

	public function getMotion(){
		$meta = $this->getBlock()->getDamage();
		switch($meta){
			case Vector3::SIDE_DOWN:
				return [0, -1, 0];
			case Vector3::SIDE_UP:
				return [0, 1, 0];
			case Vector3::SIDE_NORTH:
				return [0, 0, -1];
			case Vector3::SIDE_SOUTH:
				return [0, 0, 1];
			case Vector3::SIDE_WEST:
				return [-1, 0, 0];
			case Vector3::SIDE_EAST:
				return [1, 0, 0];
			default:
				return [0, 0, 0];
		}
	}

	public function activate(){
		$itemIndex = [];
		for($i = 0; $i < $this->getSize(); $i++){
			$item = $this->getInventory()->getItem($i);
			if($item->getId() != Item::AIR){
				$itemIndex[] = [$i, $item];
			}
		}
		$max = count($itemIndex) - 1;
		if($max < 0) $itemArr = null;
		elseif($max == 0) $itemArr = $itemIndex[0];
		else $itemArr = $itemIndex[mt_rand(0, $max)];

		if(is_array($itemArr)){
			/** @var Item $item */
			$item = $itemArr[1];
			$item->setCount($item->getCount() - 1);
			$this->getInventory()->setItem($itemArr[0], $item->getCount() > 0 ? $item : Item::get(Item::AIR));
			$motion = $this->getMotion();
			$needItem = Item::get($item->getId(), $item->getDamage());
			$f = 1.5;
			if($needItem instanceof Launchable){
				$nbt = new Compound("", [
					"Pos" => new Enum("Pos", [
						new Double("", $this->x + $motion[0] * 2 + 0.5),
						new Double("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
						new Double("", $this->z + $motion[2] * 2 + 0.5)
					]),
					"Motion" => new Enum("Motion", [
						new Double("", $motion[0]),
						new Double("", $motion[1]),
						new Double("", $motion[2])
					]),
					"Rotation" => new Enum("Rotation", [
						new Float("", lcg_value() * 360),
						new Float("", 0)
					])
				]);
				$thrownEntity = Entity::createEntity($needItem->entityname, $this->chunk, $nbt);
				$thrownEntity->setMotion($thrownExpBottle->getMotion()->multiply($f));
				$thrownEntity->spawnToAll();
			}elseif($needItem->getId() === Item::DYE && $needItem->getDamage() === Dye::BONEMEAL){// Add instanceof Dispenseable + switch function
				$dispenseto = $this->getLevel()->getBlock($this->add($motion[0],$motion[1],$motion[2]));
				if($dispenseto instanceof Crops){
					$needItem->useOn($dispenseto);
				}
			}
			else{
				$item = NBT::putItemHelper($needItem);
				$item->setName("Item");
					$nbt = new Compound("", [
					"Pos" => new Enum("Pos", [
						new Double("", $this->x + $motion[0] * 2 + 0.5),
						new Double("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
						new Double("", $this->z + $motion[2] * 2 + 0.5)
					]),
					"Motion" => new Enum("Motion", [
						new Double("", $motion[0]),
						new Double("", $motion[1]),
						new Double("", $motion[2])
					]),
					"Rotation" => new Enum("Rotation", [
						new Float("", lcg_value() * 360),
						new Float("", 0)
					]),
					"Health" => new Short("Health", 5),
					"Item" => $item,
					"PickupDelay" => new Short("PickupDelay", 10)
				]);
				$f = 0.3;
				$itemEntity = new ItemEntity($this->chunk, $nbt, $this);
				$itemEntity->setMotion($itemEntity->getMotion()->multiply($f));
				$itemEntity->spawnToAll();
			}

			for($i = 1; $i < 10; $i++){
				$this->getLevel()->addParticle(new SmokeParticle($this->add($motion[0] * $i * 0.3 + 0.5, $motion[1] == 0 ? 0.5 : $motion[1] * $i * 0.3, $motion[2] * $i * 0.3 + 0.5)));
			}
		}
	}

	public function getSpawnCompound(){
		$c = new Compound("", [
			new String("id", Tile::DISPENSER),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z)
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}

		return $c;
	}
}
