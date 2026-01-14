<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\PlayerService;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $playerService;

    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
    }

    public function index()
    {
        $players = $this->playerService->getAllPlayers();
        return view('players.index', compact('players'));
    }

    public function create()
    {
        return view('players.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:players,name',
            'ranking' => 'required|integer|min:1',
        ], [
            'name.unique' => 'Ya existe un jugador con ese nombre',
        ]);

        $validated['retired'] = $request->has('retired');

        $this->playerService->createPlayer($validated);

        return redirect()->route('players.index')->with('success', 'Jugador creado');
    }

    public function show(Player $player)
    {
        return view('players.show', compact('player'));
    }

    public function edit(Player $player)
    {
        return view('players.edit', compact('player'));
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:players,name,' . $player->id,
            'ranking' => 'required|integer|min:1',
        ], [
            'name.unique' => 'Ya existe un jugador con ese nombre',
        ]);

        $validated['retired'] = $request->has('retired');

        $this->playerService->updatePlayer($player, $validated);

        return redirect()->route('players.index')->with('success', 'Jugador actualizado');
    }

    public function destroy(Player $player)
    {
        $this->playerService->deletePlayer($player);
        return redirect()->route('players.index')->with('success', 'Jugador eliminado');
    }
}