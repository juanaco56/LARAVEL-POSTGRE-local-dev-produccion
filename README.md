# GUÍA COMPLETA: CRUD Jugadores Laravel + PostgreSQL + Docker

**Versión simple y completa - Sin Breeze(Herramienta para hacer un login rápido o securizar endpoints)**

---

## COMANDOS ARTISAN PARA CREAR ARCHIVOS
```bash
php artisan make:model Player -m                    # Modelo + Migración
php artisan make:seeder PlayerSeeder                # Seeder
php artisan make:class Services/PlayerService       # Service
php artisan make:controller PlayerController        # Controlador Web
php artisan make:controller Api/PlayerApiController # Controlador API
php artisan make:view layouts.app                   # Layout
php artisan make:view players.index                 # Vista Index
php artisan make:view players.create                # Vista Create
php artisan make:view players.edit                  # Vista Edit
php artisan make:view players.show                  # Vista Show
```

---

## PASO 1: Docker

Crear `docker-compose.yml`:
```yaml
services:
  db:
    image: postgres:16
    container_name: postgres_example
    restart: always
    environment:
      POSTGRES_USER: user
      POSTGRES_PASSWORD: 1234
      POSTGRES_DB: laravel
    ports:
      - "5432:5432"
```
```bash
docker-compose up -d
```

---

## PASO 2: Crear Proyecto Laravel
```bash
composer create-project laravel/laravel players-crud
cd players-crud
```

Editar `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=user
DB_PASSWORD=1234
```

---

## PASO 3: Modelo y Migración
```bash
php artisan make:model Player -m
```

**Editar `database/migrations/xxxx_create_players_table.php`:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('ranking');
            $table->boolean('retired')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
```

**Editar `app/Models/Player.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['name', 'ranking', 'retired'];
    
    protected $casts = [
        'retired' => 'boolean',
    ];
}
```
```bash
php artisan migrate
```

---

## PASO 4: Seeder
```bash
php artisan make:seeder PlayerSeeder
```

**Editar `database/seeders/PlayerSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        Player::create(['name' => 'Novak Djokovic', 'ranking' => 1, 'retired' => false]);
        Player::create(['name' => 'Carlos Alcaraz', 'ranking' => 2, 'retired' => false]);
        Player::create(['name' => 'Jannik Sinner', 'ranking' => 3, 'retired' => false]);
        Player::create(['name' => 'Roger Federer', 'ranking' => 99, 'retired' => true]);
        Player::create(['name' => 'Rafael Nadal', 'ranking' => 98, 'retired' => true]);
    }
}
```
```bash
php artisan db:seed --class=PlayerSeeder
```

---

## PASO 5: Service
```bash
php artisan make:class Services/PlayerService
```

**Editar `app/Services/PlayerService.php`:**
```php
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
```

---

## PASO 6: Controlador Web
```bash
php artisan make:controller PlayerController
```

**Editar `app/Http/Controllers/PlayerController.php`:**
```php
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
```

---

## PASO 7: Rutas Web

**Editar `routes/web.php`:**
```php
<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('players.index');
});

// Forma corta: crea automáticamente 7 rutas
Route::resource('players', PlayerController::class);
```

### ¿Qué hace `Route::resource()`?

La línea:
```php
Route::resource('players', PlayerController::class);
```

**Crea automáticamente estas 7 rutas:**
```php
// GET /players - Listar todos
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');

// GET /players/create - Mostrar formulario de creación
Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create');

// POST /players - Guardar nuevo registro
Route::post('/players', [PlayerController::class, 'store'])->name('players.store');

// GET /players/{player} - Mostrar un registro específico
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

// GET /players/{player}/edit - Mostrar formulario de edición
Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');

// PUT/PATCH /players/{player} - Actualizar registro
Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');

// DELETE /players/{player} - Eliminar registro
Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');
```

### Tabla de Rutas Generadas

| Método HTTP | URI | Acción | Nombre de Ruta |
|-------------|-----|--------|----------------|
| GET | `/players` | index | players.index |
| GET | `/players/create` | create | players.create |
| POST | `/players` | store | players.store |
| GET | `/players/{player}` | show | players.show |
| GET | `/players/{player}/edit` | edit | players.edit |
| PUT/PATCH | `/players/{player}` | update | players.update |
| DELETE | `/players/{player}` | destroy | players.destroy |

### Ver todas las rutas creadas
```bash
php artisan route:list
```

O filtrar solo las de players:
```bash
php artisan route:list --path=players
```

---

## PASO 8: Vistas

### Layout
```bash
php artisan make:view layouts.app
```

**Editar `resources/views/layouts/app.blade.php`:**
```html
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
        <a href="{{ route('players.index') }}">Listado</a> | 
        <a href="{{ route('players.create') }}">Nuevo</a>
    </nav>
    <hr>

    @yield('content')
</body>
</html>
```

---

### Vista Index
```bash
php artisan make:view players.index
```

**Editar `resources/views/players/index.blade.php`:**
```html
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
```

---

### Vista Create
```bash
php artisan make:view players.create
```

**Editar `resources/views/players/create.blade.php`:**
```html
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
```

---

### Vista Edit
```bash
php artisan make:view players.edit
```

**Editar `resources/views/players/edit.blade.php`:**
```html
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
```

---

### Vista Show
```bash
php artisan make:view players.show
```

**Editar `resources/views/players/show.blade.php`:**
```html
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
```

---

## PASO 9: API REST (Opcional)
```bash
php artisan install:api
php artisan make:controller Api/PlayerApiController
```

**Editar `app/Http/Controllers/Api/PlayerApiController.php`:**
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Services\PlayerService;
use Illuminate\Http\Request;

class PlayerApiController extends Controller
{
    protected $playerService;

    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
    }

    public function index()
    {
        return response()->json($this->playerService->getAllPlayers());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:players,name',
            'ranking' => 'required|integer|min:1',
        ]);

        $player = $this->playerService->createPlayer($request->all());
        return response()->json($player, 201);
    }

    public function show(Player $player)
    {
        return response()->json($player);
    }

    public function update(Request $request, Player $player)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:players,name,' . $player->id,
            'ranking' => 'sometimes|required|integer|min:1',
        ]);

        $player = $this->playerService->updatePlayer($player, $request->all());
        return response()->json($player);
    }

    public function destroy(Player $player)
    {
        $this->playerService->deletePlayer($player);
        return response()->json(['message' => 'Eliminado']);
    }
}
```

**Editar `routes/api.php`:**
```php
<?php

use App\Http\Controllers\Api\PlayerApiController;
use Illuminate\Support\Facades\Route;

// Forma corta: crea automáticamente 5 rutas API (sin create y edit)
Route::apiResource('players', PlayerApiController::class);
```

### ¿Qué hace `Route::apiResource()`?

La línea:
```php
Route::apiResource('players', PlayerApiController::class);
```

**Crea automáticamente estas 5 rutas:**
```php
// GET /api/players - Listar todos
Route::get('/players', [PlayerApiController::class, 'index'])->name('players.index');

// POST /api/players - Guardar nuevo registro
Route::post('/players', [PlayerApiController::class, 'store'])->name('players.store');

// GET /api/players/{player} - Mostrar un registro específico
Route::get('/players/{player}', [PlayerApiController::class, 'show'])->name('players.show');

// PUT/PATCH /api/players/{player} - Actualizar registro
Route::put('/players/{player}', [PlayerApiController::class, 'update'])->name('players.update');

// DELETE /api/players/{player} - Eliminar registro
Route::delete('/players/{player}', [PlayerApiController::class, 'destroy'])->name('players.destroy');
```

**NO incluye:**
- `create` - Porque las APIs no necesitan formularios HTML
- `edit` - Porque las APIs no necesitan formularios HTML

### Diferencia entre Route::resource() y Route::apiResource()

| Tipo | Rutas | Uso |
|------|-------|-----|
| `Route::resource()` | 7 rutas (con create y edit) | Web con formularios HTML |
| `Route::apiResource()` | 5 rutas (sin create y edit) | API REST con JSON |

---

## PROBAR
```bash
php artisan serve
```

**Web:** `http://localhost:8000`

**API:**
```bash
# Listar todos
curl http://localhost:8000/api/players

# Crear nuevo
curl -X POST http://localhost:8000/api/players \
  -H "Content-Type: application/json" \
  -d '{"name":"Alexander Zverev","ranking":6,"retired":false}'

# Ver uno
curl http://localhost:8000/api/players/1

# Actualizar
curl -X PUT http://localhost:8000/api/players/1 \
  -H "Content-Type: application/json" \
  -d '{"ranking":2}'

# Eliminar
curl -X DELETE http://localhost:8000/api/players/1
```

---

## RESUMEN DE COMANDOS
```bash
# 1. Docker
docker-compose up -d

# 2. Proyecto Laravel
composer create-project laravel/laravel players-crud
cd players-crud
# Editar .env

# 3. Base de datos
php artisan make:model Player -m
php artisan migrate
php artisan make:seeder PlayerSeeder
php artisan db:seed --class=PlayerSeeder

# 4. Lógica de negocio
php artisan make:class Services/PlayerService
php artisan make:controller PlayerController

# 5. Vistas
php artisan make:view layouts.app
php artisan make:view players.index
php artisan make:view players.create
php artisan make:view players.edit
php artisan make:view players.show

# 6. API (opcional)
php artisan install:api
php artisan make:controller Api/PlayerApiController

# 7. Ver rutas creadas
php artisan route:list
php artisan route:list --path=players

# 8. Iniciar servidor
php artisan serve
```

---

## ARQUITECTURA DEL PROYECTO
```
players-crud/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── PlayerController.php        # Controlador Web (7 métodos)
│   │       └── Api/
│   │           └── PlayerApiController.php # Controlador API (5 métodos)
│   ├── Models/
│   │   └── Player.php                      # Modelo Eloquent
│   └── Services/
│       └── PlayerService.php               # Lógica de negocio
├── database/
│   ├── migrations/
│   │   └── xxxx_create_players_table.php  # Migración
│   └── seeders/
│       └── PlayerSeeder.php                # Datos de prueba
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php               # Layout base
│       └── players/
│           ├── index.blade.php             # Listado
│           ├── create.blade.php            # Crear
│           ├── edit.blade.php              # Editar
│           └── show.blade.php              # Ver detalle
├── routes/
│   ├── web.php                             # Rutas web (Route::resource)
│   └── api.php                             # Rutas API (Route::apiResource)
└── docker-compose.yml                      # PostgreSQL
```

---

## FLUJO DE DATOS
```
┌─────────────┐
│   Request   │
│  (Usuario)  │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│   Controller    │ ← Maneja peticiones HTTP
│                 │ ← Valida datos
│                 │ ← Coordina flujo
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│    Service      │ ← Lógica de negocio
│                 │ ← Operaciones complejas
│                 │ ← Reutilizable
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│     Model       │ ← Interactúa con BD
│   (Eloquent)    │ ← Define estructura
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   PostgreSQL    │
│    (Docker)     │
└─────────────────┘
```

---
