<?php

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\tile\Hopper as HopperTile;
use pocketmine\tile\Tile;
use pocketmine\inventory\HopperInventory;

class Hopper extends Solid{

	protected $id = self::HOPPER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Hopper";
	}

	public function canBeActivated(){
		return true;
	}

	public function getHardness(){
		return 3.5;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		];
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];
		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new Compound("", [
			new Enum("Items", []),
			new String("id", Tile::HOPPER),
			new Int("x", $this->x),
			new Int("y", $this->y),
			new Int("z", $this->z)
		]);
		$nbt->Items->setTagType(NBT::TAG_Compound);

		if($item->hasCustomName()){
			$nbt->CustomName = new String("CustomName", $item->getCustomName());
		}

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile("Hopper", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			$t = $this->getLevel()->getTile($this);
			$furnace = false;
			if($t instanceof HopperTile){
				$furnace = $t;
			}else{
				$nbt = new Compound("", [
					new Enum("Items", []),
					new String("id", Tile::HOPPER),
					new Int("x", $this->x),
					new Int("y", $this->y),
					new Int("z", $this->z)
				]);
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$furnace = Tile::createTile("Hopper", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			}

			if(isset($furnace->namedtag->Lock) and $furnace->namedtag->Lock instanceof String){
				if($furnace->namedtag->Lock->getValue() !== $item->getCustomName()){
					return true;
				}
			}

			$player->addWindow($furnace->getInventory());
		}

		return true;
	}

	public function getDrops(Item $item){
		$drops = [];
		if($item->isPickaxe() >= 1){
			$drops[] = [Item::HOPPER, 0, 1];
		}

		return $drops;
	}
}