<?php
/*
 * THIS IS COPIED FROM THE PLUGIN FlowerPot MADE BY @beito123!!
 * https://github.com/beito123/PocketMine-MP-Plugins/blob/master/test%2FFlowerPot%2Fsrc%2Fbeito%2FFlowerPot%2Fomake%2FSkull.php
 * 
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\Byte;
use pocketmine\tile\Skull;

class SkullBlock extends Transparent{

	protected $id = self::SKULL_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	protected function recalculateBoundingBox(){
		if($this->meta === 0 || $this->meta === 1){
			return new AxisAlignedBB(
				$this->x + 0.25,
				$this->y,
				$this->z + 0.25,
				$this->x + 0.75,
				$this->y + 0.5,
				$this->z + 0.75
			);
		}
		elseif($this->meta === 2){
			$x1 = 0.25;
			$x2 = 0.75;
			$z1 = 0;
			$z2 = 0.5;
		}
		elseif($this->meta === 3){
			$x1 = 0.5;
			$x2 = 1;
			$z1 = 0.25;
			$z2 = 0.75;
		}
		elseif($this->meta === 4){
			$x1 = 0.25;
			$x2 = 0.75;
			$z1 = 0.5;
			$z2 = 1;
		}
		elseif($this->meta === 5){
			$x1 = 0;
			$x2 = 0.5;
			$z1 = 0.25;
			$z2 = 0.75;
		}
		return new AxisAlignedBB(
			$this->x + $x1,
			$this->y + 0.25,
			$this->z + $z1,
			$this->x + $x2,
			$this->y + 0.75,
			$this->z + $z2
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($face !== 0 && $fy > 0.5 && $target->getId() !== self::SKULL_BLOCK && !$down instanceof SkullBlock){
			$this->getLevel()->setBlock($block, Block::get(Block::SKULL_BLOCK, 0), true, true);
			if($face === 1){
				$rot = new Byte("Rot", floor(($player->yaw * 16 / 360) + 0.5) & 0x0F);
			}
			else{
				$rot = new Byte("Rot", 0);
			}
			$nbt = new Compound("", [
				new String("id", Tile::SKULL),
				new Int("x", $block->x),
				new Int("y", $block->y),
				new Int("z", $block->z),
				new Byte("SkullType", $item->getDamage()),
				$rot
			]);

			$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
			$pot = Tile::createTile("Skull", $chunk, $nbt);
			$this->getLevel()->setBlock($block, Block::get(Block::SKULL_BLOCK, $face), true, true);
			return true;
		}
		return false;
	}

	public function getResistance(){
		return 5;
	}

	public function getName(){
		static $names = [
			0 => "Skeleton Skull",
			1 => "Wither Skeleton Skull",
			2 => "Zombie Head",
			3 => "Head",
			4 => "Creeper Head"
		];
		return $names[$this->meta & 0x04];
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);
		return true;
	}

	public function getDrops(Item $item){
		if(($tile = $this->getLevel()->getTile($this)) instanceof Skull){
			return [[Item::SKULL,$tile->getSkullType(),1]];
		}
		else
			return [[Item::SKULL,0,1]];
	}
}