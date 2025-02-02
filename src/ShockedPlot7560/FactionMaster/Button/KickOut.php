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

namespace ShockedPlot7560\FactionMaster\Button;

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\MemberKickOutEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\ManageMember as MembersManageMember;
use ShockedPlot7560\FactionMaster\Route\ManageMembersList;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class KickOut extends Button {

    public function __construct(UserEntity $Member) {
        parent::__construct(
            "kickOut",
            function (string $Player) {
                return Utils::getText($Player, "BUTTON_KICK_OUT");
            },
            function (Player $Player) use ($Member) {
                Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                    function (Player $Player, $data) use ($Member) {
                        $Faction = MainAPI::getFactionOfPlayer($Player->getName());
                        if ($data === null) {
                            return;
                        }

                        if ($data) {
                            $message = Utils::getText($Player->getName(), "SUCCESS_KICK_OUT", ['playerName' => $Member->name]);
                            MainAPI::removeMember($Faction->name, $Member->name);
                            Utils::newMenuSendTask(new MenuSendTask(
                                function () use ($Player, $Faction) {
                                    $user = MainAPI::getUser($Player->getName());
                                    return $user instanceof UserEntity && $user->faction !== $Faction->name;
                                },
                                function () use ($Player, $Faction, $message) {
                                    $Member = MainAPI::getUser($Player->getName());
                                    (new MemberKickOutEvent($Player, $Faction, $Member))->call();
                                    Utils::processMenu(RouterFactory::get(ManageMembersList::SLUG), $Player, [$message]);
                                },
                                function () use ($Player) {
                                    Utils::processMenu(RouterFactory::get(ManageMembersList::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                }
                            ));
                        } else {
                            Utils::processMenu(RouterFactory::get(MembersManageMember::SLUG), $Player, [$Member]);
                        }
                    },
                    Utils::getText($Player->getName(), "CONFIRMATION_TITLE_KICK_OUT"),
                    Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_KICK_OUT"),
                ]);
            },
            [PermissionIds::PERMISSION_KICK_MEMBER]
        );
    }

}