<?php

namespace App\Services;

use App\Models\Player;

class PlayerService
{
    public function getAllPlayers()
    {
        return Player::orderBy('ranking')->get();
    }

    public function createPlayer(array $data)
    {
        return Player::create($data);
    }

    public function updatePlayer(Player $player, array $data)
    {
        $player->update($data);
        return $player;
    }

    public function deletePlayer(Player $player)
    {
        return $player->delete();
    }
}