<?php

namespace App\Http\Controllers;

use App\Models\Player;

class PlayerApiController extends Controller
{
    //

    public function list(){
        $players = Player::all();
        return response()->json([
            'status' => true,
            'message' => 'Prueba',
            'data' => $players
        ], 200);
    }
}