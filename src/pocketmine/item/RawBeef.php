<?php
namespace pocketmine\item;

class RawBeef extends Food{
	public $saturation = 3;
	public $exp_smelt = 0.35;

	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::RAW_BEEF, $meta, $count, "Raw Beef");
	}
}