<?php
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class JungleWoodStairs extends Stair{

	protected $id = self::JUNGLE_WOOD_STAIRS;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Jungle Wood Stairs";
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getDrops(Item $item){
		return [
			[$this->id, 0, 1],
		];
	}
}