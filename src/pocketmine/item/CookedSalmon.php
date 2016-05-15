<?php
namespace pocketmine\item;

class CookedSalmon extends Food{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::COOKED_SALMON, $meta, $count, "Cooked Salmon");
      	}

	public function getSaturation(){
		return 6;
	}
}
