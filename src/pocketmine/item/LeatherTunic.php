<?php
namespace pocketmine\item;


class LeatherTunic extends Armor{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::LEATHER_TUNIC, $meta, $count, "Leather Tunic");
	}
	
	public function isArmor(){
		return true;
	}

	public function getMaxDurability(){
		return 81;
	}
}