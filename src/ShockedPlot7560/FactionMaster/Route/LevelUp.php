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

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionLevelUpEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class LevelUp implements Route {

    const SLUG = "levelUp";

    public $PermissionNeed = [
        PermissionIds::PERMISSION_LEVEL_UP,
    ];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var FactionEntity */
    private $Faction;
    /** @var RewardInterface */
    private $Reward;
    /** @var array */
    private $RewardData;
    /** @var bool */
    private $levelUpReady;

    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string {
        return self::SLUG;
    }

    public function __construct() {
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null) {
        $this->UserEntity = $User;
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->buttons = [];

        $XpLevel = Utils::getXpLevel($this->Faction->level);
        $Pourcent = floor((30 * $this->Faction->xp) / $XpLevel);
        $advanceChar = "§l";
        for ($i = 0; $i < $Pourcent; $i++) {
            $advanceChar .= "§a=";
        }
        for ($i = 0; $i < 30 - $Pourcent; $i++) {
            $advanceChar .= "§0=";
        }
        $this->Reward = MainAPI::getLevelReward($this->Faction->level + 1);
        $this->RewardData = MainAPI::getLevelRewardData($this->Faction->level + 1);

        $this->levelUpReady = false;
        if ($Pourcent >= 30) {
            $this->levelUpReady = true;
        }

        $message = "";
        if (isset($params[0])) {
            $message = $params[0];
        }

        $content = "";
        if ($this->Reward !== null) {
            $content = "";
            if ($message !== "") {
                $content .= $message . "\n";
            }

            $name = Utils::getText($player->getName(), $this->Reward->getName($player->getName()));
            $content .= Utils::getText($player->getName(), "LEVEL_UP_CONTENT_MAIN", ['name' => $name, 'value' => $this->Reward->getValue()]);
            if ($this->levelUpReady) {
                $this->buttons[] = Utils::getText($player->getName(), "BUTTON_LEVEL_UP_READY", ['level' => $this->Faction->level]);
            } else {
                $this->buttons[] = Utils::getText($player->getName(), "BUTTON_LEVEL_UP_ADVANCE", ['advance' => $advanceChar, 'level' => $this->Faction->level]);
            }
        } else {
            $content = "";
            if ($message !== "") {
                $content .= $message . "\n";
            }

            $content .= Utils::getText($player->getName(), "LEVEL_UP_CONTENT_MAIN_MAX");
            $this->buttons[] = Utils::getText($player->getName(), "BUTTON_LEVEL_UP_MAX");
        }
        $this->buttons[] = Utils::getText($player->getName(), "BUTTON_BACK");

        $menu = $this->levelUp($content);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        $levelReady = $this->levelUpReady;
        $Data = $this->RewardData;
        $faction = $this->Faction;
        return function (Player $player, $data) use ($backMenu, $Data, $levelReady, $faction) {
            if ($data === null) {
                return;
            }

            switch ($data) {
            case 0:
                $content = "";
                if (!\is_array($Data['cost'])) {
                    $Data['cost'] = [];
                }

                foreach ($Data['cost'] as $cost) {
                    $Reward = RewardFactory::get($cost['type']);
                    $content .= "\n §5>> §f" . Utils::getText($player->getName(), $Reward->getName($player->getName())) . " x" . $cost['value'];
                }
                if ($levelReady === true) {
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callLevelUp($faction->name),
                        Utils::getText($player->getName(), "CONFIRMATION_TITLE_LEVEL_UP"),
                        Utils::getText($player->getName(), "CONFIRMATION_CONTENT_LEVEL_UP", ['cost' => $content]),
                    ]);
                } else {
                    Utils::processMenu(RouterFactory::get(self::SLUG), $player);
                }
                break;
            case 1:
                Utils::processMenu($backMenu, $player);
                break;
            }
        };
    }

    private function levelUp(string $content = ""): SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "LEVEL_UP_TITLE_MAIN"));
        $menu->setContent($content);
        return $menu;
    }

    private function callLevelUp(string $factionName): callable {
        $rewardData = $this->RewardData;
        return function (Player $Player, $data) use ($factionName, $rewardData) {
            if ($data === null) {
                return;
            }

            if ($data) {
                $continue = true;
                if (!\is_array($rewardData['cost'])) {
                    $rewardData['cost'] = [];
                }

                $finish = false;
                foreach ($rewardData['cost'] as $Cost) {
                    if ($finish === true) {
                        continue;
                    }

                    $CostItem = RewardFactory::get($Cost['type']);
                    $result = $CostItem->executeCost($factionName, $Cost['value']);
                    if ($result !== true) {
                        $continue = Utils::getText($this->UserEntity->name, $result);
                        $finish = true;
                    }
                }
                if ($continue !== true) {
                    Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$continue]);
                } else {
                    $Faction = $this->Faction;
                    $Reward = $this->Reward;
                    $RewardData = $this->RewardData;
                    MainAPI::changeLevel($Faction->name, 1);
                    Utils::newMenuSendTask(new MenuSendTask(
                        function () use ($Faction) {
                            return MainAPI::getFaction($Faction->name)->level == $Faction->level + 1;
                        },
                        function () use ($Player, $Reward, $Faction, $RewardData, $factionName) {
                            $result = $Reward->executeGet($Faction->name, $RewardData['value']);
                            if ($result === true) {
                                (new FactionLevelUpEvent($Player, $factionName, $Faction->level, $Reward, $RewardData['value']))->call();
                                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "SUCCESS_LEVEL_UP")]);
                            } else {
                                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                            }
                        },
                        function () use ($Player) {
                            Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                        }
                    ));
                }
            } else {
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }
}