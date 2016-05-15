<?php
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;

class MonsterSpawner extends Solid{

	protected $exp_min = 15;
	protected $exp_max = 43;

	protected $id = self::MONSTER_SPAWNER;
	
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	
	public function getHardness(){
		return 5;
	}
	
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	
	public function getName(){
		return "Monster Spawner";
	}
	
	public function canBeActivated(){
		return true;
	}
	
	public function onActivate(Item $item, Player $player = null){
		if($this->getDamage() == 0){
			if($item->getId() == Item::SPAWN_EGG){
				$tile = $this->getLevel()->getTile($this);
				if($tile instanceof MobSpawner){
					$this->meta = $item->getDamage();
					//$this->getLevel()->setBlock($this, $this, true, false);
					$tile->setEntityId($this->meta);
				}
				return true;
			}
		}
		return false;
	}
	
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new Compound("", [
			new String("id", Tile::MOB_SPAWNER),
			new Int("x", $block->x),
			new Int("y", $block->y),
			new Int("z", $block->z),
			new Int("EntityId", 0),
		]);
		
		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}
		
		Tile::createTile(Tile::MOB_SPAWNER, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		return true;
	}
	
	public function getDrops(Item $item){
		return [];
	}
}