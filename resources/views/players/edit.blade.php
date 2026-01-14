@extends('layouts.app')

@section('content')
<h2>Editar Jugador</h2>

@if($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ route('players.update', $player) }}" method="POST">
    @csrf
    @method('PUT')
    
    <p>
        <label>Nombre:</label><br>
        <input type="text" name="name" value="{{ old('name', $player->name) }}" required size="50">
    </p>

    <p>
        <label>Ranking:</label><br>
        <input type="number" name="ranking" value="{{ old('ranking', $player->ranking) }}" min="1" required>
    </p>

    <p>
        <label>
            <input type="checkbox" name="retired" value="1" {{ $player->retired ? 'checked' : '' }}>
            Retirado
        </label>
    </p>

    <button type="submit">Actualizar</button>
    <a href="{{ route('players.index') }}"><button type="button">Cancelar</button></a>
</form>
@endsection