<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRUD Jugadores ATP')</title>
</head>
<body>
    <h1>Jugadores ATP</h1>
    <nav>
        <a href="{{ route('players.index') }}">Listado</a>  | 
        <a href="{{ route('players.create') }}">Nuevo</a>
    </nav>
    <hr>

    @yield('content')
</body>
</html>