@extends('layouts.app')

@section('content')
<h2>Nuevo Jugador</h2>

@if($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ route('players.store') }}" method="POST">
    @csrf
    
    <p>
        <label>Nombre:</label><br>
        <input type="text" name="name" value="{{ old('name') }}" required size="50">
    </p>

    <p>
        <label>Ranking:</label><br>
        <input type="number" name="ranking" value="{{ old('ranking') }}" min="1" required>
    </p>

    <p>
        <label>
            <input type="checkbox" name="retired" value="1">
            Retirado
        </label>
    </p>

    <button type="submit">Guardar</button>
    <a href="{{ route('players.index') }}"><button type="button">Cancelar</button></a>
</form>
@endsection