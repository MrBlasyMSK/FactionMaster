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

use jojoe77777\FormAPI\SimpleForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageFaction extends Button {

    public function __construct() {
        parent::__construct(
            "manageFaction",
            function ($Player) {
                return Utils::getText($Player, "BUTTON_MANAGE_FACTION");
            },
            function ($Player) {
                Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $Player);
            },
            [
                PermissionIds::PERMISSION_BREAK_ALLIANCE,
                PermissionIds::PERMISSION_SEND_ALLIANCE_INVITATION,
                PermissionIds::PERMISSION_ACCEPT_ALLIANCE_DEMAND,
                PermissionIds::PERMISSION_REFUSE_ALLIANCE_DEMAND,
                PermissionIds::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION,
                PermissionIds::PERMISSION_CHANGE_FACTION_DESCRIPTION,
                PermissionIds::PERMISSION_CHANGE_FACTION_MESSAGE,
                PermissionIds::PERMISSION_CHANGE_FACTION_VISIBILITY,
                PermissionIds::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
            ],
            "textures/img/option_faction",
            SimpleForm::IMAGE_TYPE_PATH
        );
    }

}