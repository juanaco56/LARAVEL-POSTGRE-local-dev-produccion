@extends('layouts.app')

@section('content')
<h2>{{ $player->name }}</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <td>{{ $player->id }}</td>
    </tr>
    <tr>
        <th>Ranking</th>
        <td>{{ $player->ranking }}</td>
    </tr>
    <tr>
        <th>Estado</th>
        <td>{{ $player->retired ? 'Retirado' : 'Activo' }}</td>
    </tr>
</table>

<p>
    <a href="{{ route('players.index') }}"><button>Volver</button></a>
    <a href="{{ route('players.edit', $player) }}"><button>Editar</button></a>
</p>
@endsection