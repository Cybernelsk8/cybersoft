# 📊 DataTable Component

Componente reutilizable para **Laravel 12 + Livewire 4 + Flux 2 + TailwindCSS 4** que provee una tabla con búsqueda, filtros avanzados, ordenamiento, paginación, selección de filas y acciones masivas.

---

## 📋 Tabla de Contenidos

- [🗂️ Archivos del sistema](#️-archivos-del-sistema)
- [⚙️ Instalación y configuración](#️-instalación-y-configuración)
- [🚀 Uso básico](#-uso-básico)
- [🏷️ Headers (columnas)](#️-headers-columnas)
- [🎰 Slots personalizados con @interact](#-slots-personalizados-con-interact)
- [🔄 Loop en @interact](#-loop-en-interact)
- [🔍 Búsqueda global](#-búsqueda-global)
- [🔎 Filtros avanzados](#-filtros-avanzados)
- [↕️ Ordenamiento](#️-ordenamiento)
- [📄 Paginación](#-paginación)
- [☑️ Selección de filas](#️-selección-de-filas)
- [⚡ Acciones masivas](#-acciones-masivas)
- [🔗 Trait Searchable en modelos](#-trait-searchable-en-modelos)
- [📊 withCount automático](#-withcount-automático)
- [🌐 Persistencia de estado en URL](#-persistencia-de-estado-en-url)
- [⚡ Performance](#-performance)
- [📖 Referencia de props](#-referencia-de-props)
- [📖 Referencia de operadores](#-referencia-de-operadores)

---

## 🗂️ Archivos del sistema

```
app/
├── View/
│   └── Components/
│       └── DataTable.php           # Clase del componente Blade
├── Traits/
│   └── DataTable.php               # Trait Livewire (búsqueda, filtros, ordenamiento)
    └── Searchable.php          # Trait de modelo (queries avanzados)

resources/
└── views/
    └── components/
        └── data-table.blade.php    # Vista del componente

app/Providers/
└── AppServiceProvider.php          # Registro de directivas @interact / @endinteract
```

---

## ⚙️ Instalación y configuración

### 1️⃣ Registrar la directiva `@interact` en `AppServiceProvider`

```php
use Illuminate\Support\Facades\Blade;

public function boot(): void
{
    Blade::directive('interact', function (mixed $expression): string {
        $directive = array_map('trim', preg_split('/,(?![^(]*[)])/', $expression));
        $name      = array_shift($directive);
        $arguments = array_shift($directive) ?? '';

        $cleanName = 'column_' . str_replace('.', '_', trim($name, "'\""));

        return "<?php \$__env->slot('{$cleanName}', function({$arguments}, \$__loop = null) use (\$__env, \$__blaze) { ?>";
    });

    Blade::directive('endinteract', fn (): string => '<?php }); ?>');
}
```

> **💡 Nota:** `$__blaze` es requerido por Flux 2 para que los componentes de Flux funcionen dentro de slots. `$__loop` permite acceder a la iteración de filas dentro del slot.

### 2️⃣ Agregar el trait `Searchable` a cada modelo

```php
use App\Models\Traits\Searchable;

class User extends Authenticatable
{
    use Searchable;
}
```

### 3️⃣ Usar el trait `DataTable` en cada componente Livewire

```php
use App\Traits\DataTable;

class Users extends Component
{
    use DataTable;
}
```

> **💡 Nota:** El trait se inicializa automáticamente gracias a `bootDataTable()` — no es necesario llamar ningún método en `mount()`.

---

## 🚀 Uso básico

### Componente Livewire

```php
<?php

namespace App\Livewire;

use App\Models\User;
use App\Traits\DataTable;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Users extends Component
{
    use DataTable;

    public array $headers = [
        ['index' => 'id',         'label' => '#',                 'align' => 'start'],
        ['index' => 'name',       'label' => 'Nombre'],
        ['index' => 'email',      'label' => 'Correo Electrónico'],
        ['index' => 'created_at', 'label' => 'Fecha de Creación'],
        ['index' => 'actions',    'label' => '',                  'align' => 'end'],
    ];

    #[Computed]
    public function rows()
    {
        return User::filterAdvance($this->headers, [
            'search'  => $this->search,
            'filters' => $this->processFilters(),
            'sort'    => [
                'field'     => $this->sortBy,
                'direction' => $this->sortDirection,
            ],
        ])->paginate($this->per_page);
    }

    public function render()
    {
        return view('livewire.users');
    }
}
```

### Vista Livewire

```blade
<div class="p-4">
    <x-data-table :headers="$this->headers" :rows="$this->rows" />
</div>
```

---

## 🏷️ Headers (columnas)

Cada header es un array con las siguientes claves:

| Clave | Tipo | Requerido | Descripción |
|---|---|---|---|
| `index` | `string` | ✅ | Nombre del campo, relación o accesor |
| `label` | `string` | ✅ | Texto que aparece en el encabezado |
| `align` | `string` | ❌ | Alineación: `start`, `center`, `end`. Default: `start` |
| `width` | `string` | ❌ | Ancho de la columna (ej: `100px`, `10%`) |
| `class` | `string` | ❌ | Clases CSS adicionales para el encabezado |
| `exclude` | `bool` | ❌ | Si es `true`, excluye el campo de la búsqueda global |

### 📌 Columna de acciones

La columna `actions` es especial — no es sorteable ni buscable:

```php
public array $headers = [
    ['index' => 'id',      'label' => '#'],
    ['index' => 'name',    'label' => 'Nombre'],
    ['index' => 'actions', 'label' => '', 'align' => 'end'], // ← columna especial
];
```

### 🔗 Columnas de relaciones

Usa notación de punto — el eager loading se aplica automáticamente:

```php
public array $headers = [
    ['index' => 'id',                'label' => '#'],
    ['index' => 'name',              'label' => 'Nombre'],
    ['index' => 'role.name',         'label' => 'Rol'],         // ← relación simple
    ['index' => 'company.city.name', 'label' => 'Ciudad'],      // ← relación anidada
];
```

### 🚫 Excluir un campo de la búsqueda global

```php
public array $headers = [
    ['index' => 'id',   'label' => '#'],
    ['index' => 'uuid', 'label' => 'UUID', 'exclude' => true], // ← no se busca aquí
    ['index' => 'name', 'label' => 'Nombre'],
];
```

### 📊 Columnas con conteo de relaciones

Usa la convención `relacion_count` — el `withCount()` se aplica automáticamente:

```php
public array $headers = [
    ['index' => 'id',           'label' => '#'],
    ['index' => 'name',         'label' => 'Nombre'],
    ['index' => 'posts_count',  'label' => 'Posts',   'align' => 'center'],
    ['index' => 'orders_count', 'label' => 'Órdenes', 'align' => 'center'],
];
```

---

## 🎰 Slots personalizados con @interact

La directiva `@interact` permite personalizar el contenido de cualquier columna con HTML y componentes de Flux.

### Sintaxis

```blade
@interact('nombre_columna', $variable)
    {{-- contenido personalizado --}}
@endinteract
```

### 📝 Ejemplo básico — personalizar una celda

```blade
<x-data-table :headers="$this->headers" :rows="$this->rows">

    @interact('name', $row)
        <div class="flex items-center gap-2">
            <flux:avatar :name="$row->name" size="sm" />
            <span>{{ $row->name }}</span>
        </div>
    @endinteract

</x-data-table>
```

### 🎨 Ejemplo con badge de estado

```blade
@interact('status', $row)
    @if($row->status === 'active')
        <flux:badge color="green">Activo</flux:badge>
    @elseif($row->status === 'inactive')
        <flux:badge color="red">Inactivo</flux:badge>
    @else
        <flux:badge color="yellow">Pendiente</flux:badge>
    @endif
@endinteract
```

### ⚡ Ejemplo con botones de acción

```blade
@interact('actions', $row)
    <div class="flex gap-2">
        <flux:button size="sm" wire:click="edit({{ $row->id }})">
            Editar
        </flux:button>
        <flux:button size="sm" variant="danger" wire:click="delete({{ $row->id }})">
            Eliminar
        </flux:button>
    </div>
@endinteract
```

### 🔗 Ejemplo con columna de relación

```blade
{{-- En $headers --}}
['index' => 'role.name', 'label' => 'Rol'],

{{-- En la vista --}}
@interact('role.name', $row)
    <flux:badge color="blue">{{ $row->role->name }}</flux:badge>
@endinteract
```

> **💡 Nota:** Cuando el `index` contiene puntos (`.`), en el `@interact` se usa el mismo valor con puntos — la directiva los convierte internamente a guiones bajos.

---

## 🔄 Loop en @interact

La variable `$__loop` está siempre disponible dentro de cualquier `@interact` — contiene los datos de la iteración de **filas**, no de columnas.

### Propiedades disponibles

| Propiedad | Descripción |
|---|---|
| `$__loop->index` | Índice actual comenzando en `0` |
| `$__loop->iteration` | Iteración actual comenzando en `1` |
| `$__loop->count` | Total de registros en la página |
| `$__loop->first` | `true` si es la primera fila |
| `$__loop->last` | `true` si es la última fila |
| `$__loop->even` | `true` si la iteración es par |
| `$__loop->odd` | `true` si la iteración es impar |
| `$__loop->remaining` | Cuántas filas faltan |

### 📝 Ejemplo con número de fila

```blade
@interact('name', $row)
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-400">{{ $__loop->iteration }}</span>
        {{ $row->name }}
    </div>
@endinteract
```

### 🎨 Ejemplo con estilos condicionales

```blade
@interact('name', $row)
    <span class="{{ $__loop->even ? 'text-blue-600' : 'text-gray-800' }}">
        {{ $row->name }}
    </span>

    @if($__loop->first)
        <flux:badge color="green" size="sm">Primero</flux:badge>
    @endif

    @if($__loop->last)
        <flux:badge color="red" size="sm">Último</flux:badge>
    @endif
@endinteract
```

> **⚠️ Importante:** Usa `$__loop?->first` con el operador nullsafe `?->` para protegerte de contextos donde `$__loop` pudiera ser `null`.

---

## 🔍 Búsqueda global

La búsqueda global se activa automáticamente con el input en la barra superior. Busca en **todos los campos** definidos en `$headers` excepto los marcados con `exclude => true` y los campos reservados.

### Comportamiento por tipo de campo

| Tipo | Ejemplo de index | Comportamiento |
|---|---|---|
| Campo local | `name` | `WHERE LOWER(users.name) LIKE '%term%'` |
| Relación | `role.name` | `WHERE EXISTS (subquery LIKE '%term%')` |
| Accesor | `full_name` | Busca en campos reales del `$accessorMap` |
| `_count` | `posts_count` | Excluido de la búsqueda global |

---

## 🔎 Filtros avanzados

El panel de filtros avanzados permite al usuario construir condiciones complejas. Un **badge azul** en el botón del funnel indica cuántos filtros están activos.

### ✅ Activar / desactivar filtros avanzados

```blade
{{-- Activos por defecto --}}
<x-data-table :headers="$this->headers" :rows="$this->rows" />

{{-- Desactivados --}}
<x-data-table :headers="$this->headers" :rows="$this->rows" :advance-filter="false" />
```

### 🔧 Usar filtros en la consulta

```php
#[Computed]
public function rows()
{
    return User::filterAdvance($this->headers, [
        'search'  => $this->search,
        'filters' => $this->processFilters(), // ← procesa y convierte los filtros
        'sort'    => [
            'field'     => $this->sortBy,
            'direction' => $this->sortDirection,
        ],
    ])->paginate($this->per_page);
}
```

### 📋 Operadores disponibles

#### Comparación (numéricos, texto y fechas)
| Operador | Descripción | Ejemplo de valor |
|---|---|---|
| `=` | Igual a | `john`, `5`, `2024-01-01` |
| `!=` | Diferente de | `john` |
| `>` | Mayor que | `100`, `2024-01-01` |
| `<` | Menor que | `100`, `2024-12-31` |
| `>=` | Mayor o igual que | `18` |
| `<=` | Menor o igual que | `65` |

#### Texto
| Operador | Descripción | Ejemplo de valor |
|---|---|---|
| `like` | Contiene | `john` → busca `%john%` |
| `not like` | No contiene | `spam` |

#### Array (valores separados por coma)
| Operador | Descripción | Ejemplo de valor |
|---|---|---|
| `in` | Está en la lista | `1, 2, 3` |
| `not in` | No está en la lista | `4, 5, 6` |
| `between` | Entre dos valores | `10, 100` |
| `not between` | Fuera del rango | `10, 100` |

#### Nulos
| Operador | Descripción |
|---|---|
| `null` | El campo es nulo |
| `not null` | El campo no es nulo |

### 📅 Filtros con fechas

El sistema detecta automáticamente si un valor es una fecha:

| Operador | Valor | Resultado SQL |
|---|---|---|
| `=` | `2024-01-01` | `WHERE fecha = '2024-01-01'` |
| `>` | `2024-01-01` | `WHERE fecha > '2024-01-01'` |
| `between` | `2024-01-01, 2024-12-31` | `WHERE fecha BETWEEN '2024-01-01' AND '2024-12-31'` |
| `in` | `2024-01-01, 2024-06-01` | `WHERE fecha IN ('2024-01-01', '2024-06-01')` |
| `like` | `2024` | `WHERE LOWER(fecha) LIKE '%2024%'` |

Formatos de fecha soportados:
- `2024-01-01`
- `2024-01-01 10:30`
- `2024-01-01 10:30:00`
- `2024-01-01T10:30:00`

### 📊 Filtros para columnas `_count`

Las columnas con conteo de relaciones usan `HAVING` automáticamente:

| Operador | Valor | Resultado SQL |
|---|---|---|
| `>` | `5` | `HAVING posts_count > 5` |
| `=` | `0` | `HAVING posts_count = 0` |
| `between` | `1, 10` | `HAVING posts_count BETWEEN 1 AND 10` |

---

## ↕️ Ordenamiento

El ordenamiento se activa haciendo click en el encabezado de cualquier columna. La columna `actions` no es sorteable.

### 🔧 Valores por defecto

```php
public string $sortBy        = 'id';
public string $sortDirection = 'desc';
```

Para cambiar los valores por defecto en un componente específico:

```php
class Users extends Component
{
    use DataTable;

    public string $sortBy        = 'name'; // ← nuevo default
    public string $sortDirection = 'asc';  // ← nuevo default
}
```

### 🔗 Ordenamiento por relaciones

Automático cuando el `index` usa notación de punto:

```php
['index' => 'role.name', 'label' => 'Rol'],
// genera: ORDER BY roles_rel_0.name ASC
```

### 🎭 Ordenamiento por accesores

Si el campo es un accesor, ordena por los campos reales del `$accessorMap`:

```php
// Con este accessorMap en el modelo:
protected array $accessorMap = [
    'full_name' => ['first_name', 'last_name'],
];

// Al ordenar por full_name genera:
// ORDER BY users.first_name ASC, users.last_name ASC
```

---

## 📄 Paginación

### 🔢 Opciones de registros por página

El selector está disponible en la barra superior con opciones: `5`, `10`, `20`, `50`, `100`, `500`, `1000`.

### 🔧 Valor por defecto

```php
public int $per_page = 10;
```

Para cambiar el valor por defecto:

```php
class Users extends Component
{
    use DataTable;

    public int $per_page = 25; // ← nuevo default
}
```

---

## ☑️ Selección de filas

### ✅ Activar selección

```blade
<x-data-table :headers="$this->headers" :rows="$this->rows" :selectable="true" />
```

### 🖱️ Seleccionar todos en la página actual

El checkbox en el encabezado hace toggle — selecciona todos los visibles si no están todos seleccionados, y los deselecciona si ya lo están.

### 🎨 Feedback visual

Las filas seleccionadas se resaltan automáticamente con un fondo más oscuro. Al desmarcar el checkbox la fila vuelve a su color normal.

### 💻 Acceder a los IDs seleccionados

```php
public function deleteSelected(): void
{
    User::whereIn('id', $this->selectedRows)->delete();
    $this->selectedRows = [];
}

public function exportSelected(): void
{
    $users = User::whereIn('id', $this->selectedRows)->get();
    // lógica de exportación...
}
```

---

## ⚡ Acciones masivas

Las acciones masivas permiten ejecutar operaciones sobre los registros seleccionados. Requiere `selectable` activo.

### ✅ Activar el dropdown de acciones masivas

```blade
<x-data-table
    :headers="$this->headers"
    :rows="$this->rows"
    :selectable="true"
    :mass-actions="true">

    <x-slot:massActions>
        <flux:menu.item variant="danger" icon="trash" wire:click="deleteSelected">
            Eliminar seleccionados
        </flux:menu.item>
        <flux:menu.item icon="arrow-down-tray" wire:click="exportSelected">
            Exportar seleccionados
        </flux:menu.item>
    </x-slot:massActions>

</x-data-table>
```

### 🔐 Controlar acceso con permisos

```blade
<x-data-table
    :mass-actions="auth()->user()->can('delete-users')"
    :selectable="true"
    :headers="$this->headers"
    :rows="$this->rows">

    <x-slot:massActions>
        <flux:menu.item variant="danger" icon="trash" wire:click="deleteSelected">
            Eliminar seleccionados
        </flux:menu.item>
    </x-slot:massActions>

</x-data-table>
```

### 💻 Métodos en el componente Livewire

```php
public function deleteSelected(): void
{
    User::whereIn('id', $this->selectedRows)->delete();
    $this->selectedRows = [];
}

public function exportSelected(): void
{
    $records = User::whereIn('id', $this->selectedRows)->get();
    // lógica de exportación...
}
```

---

## 🔗 Trait Searchable en modelos

### ⚙️ Instalación básica

```php
use App\Models\Traits\Searchable;

class User extends Authenticatable
{
    use Searchable;
}
```

### 🔗 Búsqueda en relaciones

Solo define el header con notación de punto — el eager loading y la búsqueda son automáticos:

```php
// En $headers
['index' => 'role.name',         'label' => 'Rol'],
['index' => 'company.city.name', 'label' => 'Ciudad'],
```

### 🎭 Búsqueda en accesores

Para buscar en campos calculados necesitas tres cosas en el modelo:

```php
use App\Models\Traits\Searchable;

class User extends Authenticatable
{
    use Searchable;

    // 1. Agregar a $appends
    protected $appends = ['full_name'];

    // 2. Definir el $accessorMap
    protected array $accessorMap = [
        'full_name' => ['first_name', 'last_name'],
    ];

    // 3. Crear el accesor
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
```

Con esto la búsqueda funciona de dos formas:
- Buscar `"john"` → encuentra por `first_name` OR `last_name`
- Buscar `"john doe"` → encuentra por `CONCAT(first_name, ' ', last_name)`

### 🔭 Uso del scope `filterAdvance`

```php
User::filterAdvance($this->headers, [
    'search'  => $this->search,
    'filters' => $this->processFilters(),
    'sort'    => [
        'field'     => $this->sortBy,
        'direction' => $this->sortDirection,
    ],
])->paginate($this->per_page);
```

---

## 📊 withCount automático

Cualquier columna cuyo `index` termine en `_count` aplica automáticamente `withCount()` en el query.

### 📝 Ejemplo

```php
// En $headers
public array $headers = [
    ['index' => 'id',           'label' => '#'],
    ['index' => 'name',         'label' => 'Nombre'],
    ['index' => 'posts_count',  'label' => 'Posts',   'align' => 'center'],
    ['index' => 'orders_count', 'label' => 'Órdenes', 'align' => 'center'],
];

// Genera automáticamente:
// User::withCount(['posts', 'orders'])->...
```

### 🔎 Filtrar por columnas `_count`

```
Campo:    posts_count
Operador: >
Valor:    5
// Genera: HAVING posts_count > 5
```

### ⚠️ Requisito en el modelo

El modelo debe tener la relación definida:

```php
class User extends Model
{
    use Searchable;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

---

## 🌐 Persistencia de estado en URL

Las propiedades `search`, `sortBy`, `sortDirection` y `per_page` se sincronizan automáticamente con la URL.

### 🔧 Configuración en el componente

```php
class Users extends Component
{
    use DataTable;

    protected array $queryString = [
        'search'        => ['except' => ''],
        'sortBy'        => ['except' => 'id'],   // debe coincidir con el default
        'sortDirection' => ['except' => 'desc'],  // debe coincidir con el default
        'per_page'      => ['except' => 10],
    ];
}
```

### 🌐 Ejemplo de URL generada

```
/users?search=john&sortBy=name&sortDirection=asc&per_page=25
```

> **💡 Nota:** Los parámetros solo aparecen en la URL cuando su valor difiere del `except` — si todos son los valores por defecto la URL queda limpia.

### ✅ Beneficios

- **Compartir enlaces** con búsqueda y orden ya aplicados
- **Navegar con el botón atrás** manteniendo el estado
- **Recargar la página** sin perder búsqueda, orden o paginación

---

## ⚡ Performance

### 📌 `$headers` como propiedad pública

Mover `$headers` fuera del `render()` evita que se recalculen en cada acción de Livewire:

```php
// ❌ Mal — se recalcula en cada render
public function render()
{
    $headers = [...];
    $rows = User::...->paginate();
    return view('livewire.users', compact('headers', 'rows'));
}

// ✅ Bien — se serializa una sola vez
public array $headers = [
    ['index' => 'id',      'label' => '#'],
    ['index' => 'name',    'label' => 'Nombre'],
    ['index' => 'actions', 'label' => ''],
];

public function render()
{
    return view('livewire.users');
}
```

### ⚡ `#[Computed]` para `$rows`

Con `#[Computed]` el resultado del query se cachea durante el ciclo de vida del request:

```php
use Livewire\Attributes\Computed;

// ❌ Mal — query en render()
public function render()
{
    $rows = User::...->paginate();
    return view('livewire.users', compact('rows'));
}

// ✅ Bien — query cacheado con #[Computed]
#[Computed]
public function rows()
{
    return User::filterAdvance($this->headers, [
        'search'  => $this->search,
        'filters' => $this->processFilters(),
        'sort'    => [
            'field'     => $this->sortBy,
            'direction' => $this->sortDirection,
        ],
    ])->paginate($this->per_page);
}

public function render()
{
    return view('livewire.users');
}
```

### 📊 Acceso en la vista

```blade
<x-data-table :headers="$this->headers" :rows="$this->rows" />
```

---

## 📖 Referencia de props

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `headers` | `array` | requerido | Definición de columnas |
| `rows` | `iterable` | requerido | Registros paginados |
| `selectable` | `bool` | `false` | Activa checkboxes por fila |
| `advance-filter` | `bool` | `true` | Muestra panel de filtros avanzados |
| `mass-actions` | `bool` | `false` | Muestra dropdown de acciones masivas |

---

## 📖 Referencia de operadores

| Operador | Grupo | Tipo de valor | Ejemplo |
|---|---|---|---|
| `=` | Comparación | Simple, numérico o fecha | `john`, `5`, `2024-01-01` |
| `!=` | Comparación | Simple, numérico o fecha | `john` |
| `>` | Comparación | Numérico o fecha | `100`, `2024-01-01` |
| `<` | Comparación | Numérico o fecha | `100`, `2024-12-31` |
| `>=` | Comparación | Numérico o fecha | `18` |
| `<=` | Comparación | Numérico o fecha | `65` |
| `like` | Texto | Simple | `john` → `%john%` |
| `not like` | Texto | Simple | `spam` |
| `in` | Array | Separado por comas | `1, 2, 3` |
| `not in` | Array | Separado por comas | `4, 5, 6` |
| `between` | Array | Exactamente 2 valores | `10, 100` |
| `not between` | Array | Exactamente 2 valores | `10, 100` |
| `null` | Nulos | Sin valor | — |
| `not null` | Nulos | Sin valor | — |
