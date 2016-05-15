<?php
namespace pocketmine\tile;

use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\tile\Spawnable;

class Music extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->note)){
			$nbt->note = new Byte("note", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getNote(){
		return $this->namedtag["note"];
	}

	public function setNote($note){
		$this->namedtag->note = new Byte("note", $note);
	}

	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", Tile::NOTEBLOCK),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			new Byte("note", $this->namedtag["note"])
		]);	
	}
}