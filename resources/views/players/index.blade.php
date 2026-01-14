@extends('layouts.app')

@section('content')
<h2>Ranking ATP</h2>

@if(session('success'))
    <p><strong>{{ session('success') }}</strong></p>
@endif

<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>Ranking</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($players as $player)
        <tr>
            <td>{{ $player->ranking }}</td>
            <td>{{ $player->name }}</td>
            <td>{{ $player->retired ? 'Retirado' : 'Activo' }}</td>
            <td>
                <a href="{{ route('players.show', $player) }}">Ver</a> |
                <a href="{{ route('players.edit', $player) }}">Editar</a> |
                <form action="{{ route('players.destroy', $player) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('¿Seguro?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection