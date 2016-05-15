<?php

namespace pocketmine\block;

use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\event\block\BlockUpdateEvent;

class Sponge extends Solid{
	protected $id = self::SPONGE;

	public function __construct(){}

	public function getHardness(){
		return 0.6;
	}

	public function getName(){
		return "Sponge";
	}

	public function dryArea(){
		for($ix = ($this->getX() - 2); $ix <= ($this->getX() + 2); $ix++){
			for($iy = ($this->getY() - 2); $iy <= ($this->getY() + 2); $iy++){
				for($iz = ($this->getZ() - 2); $iz <= ($this->getZ() + 2); $iz++){
					$b = $this->getLevel()->getBlock(new Vector3($ix, $iy, $iz));
					if($b instanceof Water){
						$this->getLevel()->setBlock($b, new Air(), null, false);
					}
				}
			}
		}
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->getLevel()->setBlock($block, $this);
		$this->dryArea();
		return true;
	}

	public function onWaterFlow(BlockUpdateEvent $event){
		if($event->getBlock() instanceof Water){
			$event->setCancelled();
			$this->dryArea();
		}
	}
}