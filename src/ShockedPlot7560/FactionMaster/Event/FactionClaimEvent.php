<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\Event;
use pocketmine\level\format\Chunk;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;

class FactionClaimEvent extends Event {

    private $Player;
    private $Faction;
    private $Chunk;
    private $CostType;
    private $CostValue;

    public function __construct(Player $Player, FactionEntity $Faction, Chunk $Chunk, string $CostType, int $CostValue) {
        $this->Player = $Player;
        $this->Faction = $Faction;
        $this->Chunk = $Chunk;
        $this->CostType = $CostType;
        $this->CostValue = $CostValue;
    }

    public function getPlayer(): Player {
        return $this->Player;
    }

    public function getFaction(): FactionEntity {
        return $this->Faction;
    }

    public function getChunk(): Chunk {
        return $this->Chunk;
    }

    public function getCostValue(): int {
        return $this->CostValue;
    }

    public function getCostType(): string {
        return $this->CostType;
    }

}