<?php
namespace pocketmine\item;

class RawSalmon extends Food{
	public $exp_smelt = 0.35; 
	
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::RAW_SALMON, $meta, $count, "Raw Salmon");
	}


	public function getSaturation(){
		return 2;
	}
}
