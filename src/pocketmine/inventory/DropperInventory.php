<?php
namespace pocketmine\inventory;

use pocketmine\Player;
use pocketmine\inventory\ContainerInventory;
use pocketmine\inventory\InventoryType;
use pocketmine\tile\Dropper;

class DropperInventory extends ContainerInventory{
	public function __construct(Dropper $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::DROPPER));
	}

	/**
	 * @return Dropper
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function onClose(Player $who){
		parent::onClose($who);
	}
}