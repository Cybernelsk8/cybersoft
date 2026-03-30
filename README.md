# 📋 Cybersoft – Sistema Integral de Gestión

![Laravel](https://img.shields.io/badge/Laravel-12.0%2B-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php)
![Livewire](https://img.shields.io/badge/Livewire-4.0%2B-FB70A9?style=for-the-badge&logo=laravel)
![Flux](https://img.shields.io/badge/Flux-UI%20Components-2563EB?style=for-the-badge)

## 🎯 Descripción General

Cybersoft es una aplicación empresarial moderna construida con **Laravel 12**, **Livewire 4** y **Flux UI**, que proporciona un sistema robusto de gestión de usuarios, roles y permisos con autenticación avanzada. Incluye componentes interactivos, gráficos dinámicos y un sistema integral de búsqueda y filtros por autorización.

---

## 📚 Tabla de Contenidos

1. [Sistema de Roles y Permisos](#sistema-de-roles-y-permisos)
2. [Services (Servicios)](#services-servicios)
3. [Componentes de UI](#componentes-de-ui)
4. [DataTable Component](#datatable-component)
5. [Livewire Components](#livewire-components)
6. [Traits](#traits)
7. [Ejemplos Prácticos](#ejemplos-prácticos)

---

## 🔐 Sistema de Roles y Permisos

### 🏗️ Estructura Base

El proyecto utiliza **Spatie Permissions** para manejar un sistema flexible de roles y permisos con soporte para módulos.

#### Configuración

```php
// config/permission.php
return [
    'models' => [
        'permission' => App\Models\Admin\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],
];
```

### 📊 Estructura de Base de Datos

```
permissions
├── id
├── name              (ej: "admin.users.store")
├── alias             (ej: "Crear usuario")
├── module            (ej: "admin", "menu", "pages")
├── guard_name        (ej: "web")
└── timestamps

roles
├── id
├── name              (ej: "Sysadmin", "Admin", "Editor")
├── guard_name
└── timestamps

role_has_permissions
├── permission_id
└── role_id

model_has_roles (Usuario ↔ Rol)
├── role_id
├── model_id
└── model_type
```

### 🔑 Notación de Permisos

Los permisos siguen una convención de **notación de puntos** jerárquica:

```
formato: <módulo>.<acción>.<recurso>

Ejemplos:
✓ admin.users.store      → Crear usuarios admin
✓ admin.users.update     → Actualizar usuarios admin
✓ admin.users.delete     → Eliminar usuarios admin
✓ admin.users.restore    → Restaurar usuarios eliminados
✓ admin.roles.view       → Ver roles
✓ page.view.users        → Ver página de usuarios
✓ pages.edit             → Editar páginas
✓ pages.delete           → Eliminar páginas
```

### 🛠️ Configuración en Modelos

El modelo `User` implementa `HasRoles` de Spatie:

```php
// app/Models/Admin/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles,
        Searchable,
        TwoFactorAuthenticatable,
        SoftDeletes;

    // El usuario tiene relación con roles
    // Por defecto usa guard 'web'
}
```

**Métodos disponibles:**

```php
$user = User::find(1);

// ========== ASIGNACIÓN DE ROLES ==========
$user->assignRole('Admin');                       // Asignar rol
$user->syncRoles(['Admin', 'Editor']);            // Sincronizar roles (reemplaza)
$user->removeRole('Editor');                      // Remover rol
$user->hasRole('Admin');                          // true/false

// ========== PERMISOS ==========
$user->give('admin.users.store');                 // Dar permiso directo
$user->revokePermissionTo('admin.users.delete');  // Revocar permiso directo
$user->givePermissionTo(['perm1', 'perm2']);      // Múltiples permisos
$user->hasPermissionTo('admin.users.store');      // true/false
$user->can('admin.users.store');                  // true/false (alias)
$user->cannot('admin.users.delete');              // true/false (negación)

// ========== CONSULTAS ==========
$user->getAllPermissions();                       // Todos los permisos (rol + directos)
$user->getDirectPermissions();                    // Solo permisos directos
$user->roles;                                     // Relación a roles
```

### 🎫 Gate (Puerta de Control)

Se configura un **Gate::before** para permitir acceso automático a Sysadmin:

```php
// app/Providers/AppServiceProvider.php
Gate::before(function ($user, $ability) {
    return $user->hasRole('Sysadmin') ? true : null;
});
```

**Ventaja:** Los Sysadmin tienen acceso a todo sin necesidad de permisos específicos.

### ✔️ Directiva `@can` en Blade

La directiva `@can` verifica permisos antes de renderizar contenido.

#### Sintaxis

```blade
@can('permiso.name')
    <p>Contenido visible solo si tienes el permiso</p>
@endcan

@cannot('permiso.name')
    <p>Contenido visible si NO tienes el permiso</p>
@endcannot

@can('admin.users.delete')
    <flux:menu.item icon="trash" variant="danger">
        Eliminar
    </flux:menu.item>
@elsecan('admin.users.update')
    <flux:menu.item icon="pencil-square">
        Editar
    </flux:menu.item>
@endcan
```

#### Ejemplo Real: System Admin Verificación

```blade
{{-- resources/views/livewire/admin/user/index.blade.php --}}

@can('admin.users.store')
    <div class="flex justify-center mb-4">
        <flux:modal.trigger name="newUser">
            <flux:button 
                icon="plus" 
                variant="primary"
                title="Crear nuevo usuario">
                Crear nuevo usuario
            </flux:button>
        </flux:modal.trigger>
    </div>
@endcan

@can('page.view.users')
    <x-data-table :headers="$this->headers" :rows="$this->rows">
        @interact('actions', $row)
            <flux:dropdown>
                <flux:button icon="ellipsis-vertical" variant="ghost" />
                <flux:menu>
                    @can('admin.users.update')
                        <flux:menu.item 
                            icon="pencil-square"
                            :href="route('admin.users.show', $row->id)">
                            Editar
                        </flux:menu.item>
                    @endcan

                    @can('admin.users.restore')
                        @if ($row->user->deleted_at)
                            <flux:menu.item 
                                icon="check-circle"
                                wire:click="userRestore({{ $row->id }})">
                                Restaurar
                            </flux:menu.item>
                        @endif
                    @endcan

                    @can('admin.users.delete')
                        <flux:menu.item 
                            icon="trash"
                            variant="danger">
                            Eliminar
                        </flux:menu.item>
                    @endcan
                </flux:menu>
            </flux:dropdown>
        @endinteract
    </x-data-table>
@endcan
```

### 🛡️ Protección de Rutas con Middleware

Las rutas se protegen usando el middleware `can:permiso`:

```php
// routes/admin.php
Route::get('users', Index::class)
    ->middleware(['can:page.view.users'])
    ->name('admin.users.index');

Route::get('users/{user}', Show::class)
    ->middleware(['can:page.view.users'])
    ->name('admin.users.show');

Route::get('roles', Roles::class)
    ->middleware(['can:page.view.roles'])
    ->name('admin.roles');

Route::get('permissions', Permissions::class)
    ->middleware(['can:page.view.permissions'])
    ->name('admin.permissions');
```

**Flujo de ejecución:**

```
1. Usuario intenta acceder a /admin/users
   ↓
2. Laravel verifica middleware 'can:page.view.users'
   ↓
3. Llama a Gate::before($user, 'page.view.users')
   ↓
4. Si es Sysadmin → ✅ Permite acceso
   ↓
5. Si no → Verifica $user->hasPermissionTo('page.view.users')
   ↓
6. Si tiene permiso → ✅ Renderiza componente
   ↓
7. Si no tiene → ❌ Lanza 403 Forbidden
```

### 📋 Autorización en Componentes Livewire

Los componentes Livewire pueden verificar permisos:

```php
// app/Livewire/Admin/User/Index.php
public function store() {
    // Autoriza la acción
    $this->authorize('admin.users.store');

    // Continúa con la lógica...
    try {
        User::create([...]);
        $this->toastSuccess('Usuario creado');
    } catch (\Throwable $th) {
        $this->toastError('Error: ' . $th->getMessage());
    }
}

public function restore() {
    $this->authorize('admin.users.restore');
    
    $user = User::withTrashed()->findOrFail($this->user['id']);
    $user->restore();
    $this->toastSuccess('Usuario restaurado');
}
```

---

## 🎨 Services (Servicios)

### 🖼️ Service: Captcha

Genera imágenes CAPTCHA dinámicas para validación de formularios.

```php
// app/Services/Captcha.php
class Captcha
{
    public function generate(string $text)
```

**Características:**

- ✅ Genera imagen PNG con texto distorsionado
- ✅ Incluye ruido de líneas aleatorias
- ✅ Dimensiones: 150×50 píxeles
- ✅ Retorna respuesta HTTP con header image/png

**Ejemplo de uso:**

```php
// En un controlador o componente
use App\Services\Captcha;

$captcha = new Captcha();
return $captcha->generate('ABC123'); // Responde con imagen PNG
```

**En la vista:**

```blade
<img src="{{ route('captcha.generate', $code) }}" 
     alt="CAPTCHA"
     width="150" height="50" />
```

---

### 📊 Service: Chart

Componente para crear gráficos profesionales con **ApexCharts**.

```php
// app/Services/Chart.php
class Chart
{
    // Tipo: 'line', 'area', 'bar', 'pie', 'donut', 'scatter', 'bubble', 
    //       'heatmap', 'candlestick', 'treemap', 'radialBar'

    public static function make(string $type = 'line'): self
```

#### 🎯 Tipos de Gráficos Soportados

| Tipo | Descripción | Uso |
|------|------------|-----|
| `line` | Líneas | Tendencias en tiempo |
| `area` | Áreas rellenas | Distribución acumulada |
| `bar` | Barras horizontales | Comparativas |
| `pie` | Torta | Proporciones |
| `donut` | Dona | Proporciones con centro hueco |
| `scatter` | Dispersión | Correlaciones |
| `bubble` | Burbujas | Tres dimensiones |
| `heatmap` | Mapa de calor | Densidad de datos |
| `candlestick` | Velas financieras | OHLC (finanzas) |

#### 📝 Ejemplo Completo: Mixed Chart

```php
// app/Livewire/TestChart.php
use App\Traits\Charts;

class TestChart extends Component
{
    use Charts; // Proporciona métodos auxiliares

    public function render()
    {
        // ========== MIXED CHART ==========
        $chart1 = $this->mixedChart(
            [
                ['name' => 'Ventas', 'type' => 'column', 'data' => [10, 15, 8, 12, 20]],
                ['name' => 'Ingresos', 'type' => 'line', 'data' => [1000, 1500, 800, 1200, 2000]],
                ['name' => 'Costos', 'type' => 'area', 'data' => [700, 900, 600, 800, 1500]],
            ],
            ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
            'Reporte de Ventas'
        )
        ->set('stroke.width', [4, 0, 2])
        ->set('yaxis.show', false)
        ->colors('warm')                    // Esquema de colores
        ->build();

        // ========== BAR CHART ==========
        $chart2 = $this->barChart(
            [['name' => 'Usuarios', 'data' => [50, 70, 40, 90, 120]]],
            ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo']
        )
        ->set('plotOptions.bar.borderRadius', 6)
        ->set('yaxis.show', false)
        ->colors('cool')
        ->build();

        // ========== CANDLESTICK CHART (Finanzas) ==========
        $chart4 = $this->candlestickChart(
            [
                [
                    'name' => 'Precio Acción',
                    'data' => [
                        ['x' => '2024-01-01', 'y' => [100, 110, 90, 105]],
                        ['x' => '2024-01-02', 'y' => [105, 115, 95, 110]],
                        ['x' => '2024-01-03', 'y' => [110, 120, 100, 115]],
                    ],
                ],
            ]
        )
        ->set('plotOptions.candlestick.colors.upward', '#10b981')
        ->set('plotOptions.candlestick.colors.downward', '#ef4444')
        ->build();

        return view('livewire.test-chart', compact('chart1', 'chart2', 'chart4'));
    }
}
```

#### 🎨 Esquemas de Colores

```php
// Esquemas predefinidos
->colors('default')  // Azul, Verde, Naranja, Rojo, Púrpura
->colors('warm')     // Cálidos: Naranja, Rojo, Rojo oscuro
->colors('cool')     // Fríos: Azul, Cian, Púrpura
->colors('mono')     // Monocromático: Grises

// Personalizados
->colors(['#ff0000', '#00ff00', '#0000ff'])
```

#### 🔧 Métodos Encadenables

```php
$chart = Chart::make('line')
    ->series([
        ['name' => 'Serie 1', 'data' => [10, 20, 30]],
        ['name' => 'Serie 2', 'data' => [5, 15, 25]],
    ])
    ->labels(['Mes 1', 'Mes 2', 'Mes 3'])
    ->height(350)                          // Alto en px
    ->width('100%')                        // Ancho
    ->colors('warm')                       // Esquema
    ->set('theme.mode', 'dark')            // Tema
    ->formatter('currency')                // Formateador
    ->showToolbar(false)                   // Ocultar toolbar
    ->build();
```

#### 🎛️ Formateadores

```php
->formatter('currency')                    // $12,345.00
->formatter('percent')                     // 45.5%
->formatter('compact')                     // 1.2M

// Personalizado (JS puro)
->formatter('val => "$ " + val.toLocaleString()')
->formatter('val => val.toFixed(2) + "%"')
```

#### 🖼️ Vista del Gráfico

```blade
{{-- resources/views/livewire/test-chart.blade.php --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div class="relative aspect-video rounded-xl border p-4">
        <x-chart :config="$chart1" />
    </div>
    <div class="relative aspect-video rounded-xl border p-4">
        <x-chart :config="$chart2" />
    </div>
    <div class="relative aspect-video rounded-xl border p-4">
        <x-chart :config="$chart4" />
    </div>
</div>
```

---

### 🔲 Service: QR

Genera códigos QR en formatos SVG y PNG con caché inteligente.

```php
// app/Services/Qr.php
class Qr
{
    // Niveles de corrección de error
    const EC_LOW      = 'L';      // Recupera ~7% de datos
    const EC_MEDIUM   = 'M';      // Recupera ~15% de datos (default)
    const EC_QUARTILE = 'Q';      // Recupera ~25% de datos
    const EC_HIGH     = 'H';      // Recupera ~30% de datos
```

#### 📌 Métodos Disponibles

| Método | Retorna | Caché | Uso |
|--------|---------|-------|-----|
| `svg()` | String SVG | ✅ | Guardar en BD |
| `svgDataUri()` | Data URI | ✅ | Insertar en IMG |
| `png()` | Bytes binarios | ❌ | Respuesta HTTP |
| `pngDataUri()` | Data URI | ✅ | Insertar en IMG |

#### 💡 Ejemplo: Generar QR

```php
use App\Services\Qr;

// ========== SVG (Texto Escalable) ==========
$svg = Qr::svg(
    content: 'https://example.com/qr/abc123',
    size: 200,
    margin: 4,
    errorCorrection: Qr::EC_HIGH,
    foreground: [0, 0, 0],
    background: [255, 255, 255]
);

// ========== SVG como Data URI ==========
$dataUri = Qr::svgDataUri(
    'https://example.com',
    size: 200,
    ec: Qr::EC_MEDIUM
);
// → 'data:image/svg+xml;base64,...'

// ========== PNG como Data URI ==========
$pngUri = Qr::pngDataUri(
    'https://example.com',
    size: 200
);
// → 'data:image/png;base64,...'
```

#### 🖼️ Componente `<x-qr>`

```blade
{{-- Uso simple --}}
<x-qr data="https://example.com" size="200" />

{{-- Con descarga --}}
<x-qr 
    data="https://example.com" 
    size="300"
    format="svg"
    download
    filename="codigo-qr"
/>

{{-- Personalizado --}}
<x-qr 
    data="{{ $user->id }}"
    size="250"
    format="png"
    :foreground="[255, 0, 0]"
    :background="[255, 255, 255]"
    error-correction="Q"
/>
```

---

### 🔐 Service: Token (JWT)

Genera y verifica tokens JWT firmados criptográficamente para códigos QR y accesos seguros.

```php
// app/Services/Token.php
class Token
{
    // Estructura: v1.encodedSid.expirationTime.signature
```

#### 🔑 Métodos

```php
use App\Services\Token;

$tokenService = new Token();

// ========== CREAR TOKEN ==========
$hash = $tokenService->createShortHash(
    sid: 'user-12345',           // ID de sesión/usuario
    horasExpiracion: 24          // Horas (default: config)
);

// Ejemplo de retorno:
// "v1.dXNlci0xMjM0NQ.1711900800.base64Signature=="

// ========== VERIFICAR TOKEN ==========
try {
    $data = $tokenService->verifyShortHash($hash);
    
    // Retorna:
    // [
    //     'sid' => 'user-12345',
    //     'expires' => 1711900800
    // ]
    
} catch (\Exception $e) {
    // Errores posibles:
    // - "Token con formato inválido."
    // - "Versión de token no soportada."
    // - "El código QR ha expirado."
    // - "Firma de token inválida."
}
```

#### 💼 Caso de Uso: Código QR con Token JWT

```php
// En un controlador
use App\Services\Token;
use App\Services\Qr;

class QRController
{
    public function generate($userId)
    {
        $tokenService = new Token();
        
        // Crear token válido por 24 horas
        $token = $tokenService->createShortHash(
            sid: "user-{$userId}",
            horasExpiracion: 24
        );
        
        // Generar QR con el token
        $qrDataUri = Qr::svgDataUri(
            content: route('qr.verify', $token),
            size: 250,
            ec: Qr::EC_HIGH
        );
        
        return \view('qr.display', ['qrDataUri' => $qrDataUri]);
    }

    public function verify($token)
    {
        try {
            $data = (new Token())->verifyShortHash($token);
            
            return response()->json([
                'status' => 'verified',
                'sid' => $data['sid'],
                'expires' => $data['expires']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
```

---

## 🎨 Componentes de UI

### 📦 Componente: Select (Selelector Avanzado)

Componente select type-safe con búsqueda y múltiple selección.

```blade
{{-- resources/views/components/select.blade.php --}}
<x-select 
    wire:model.live="seleccion"
    :options="$users"
    option-value="id"
    option-label="nombre_completo"
    label="Selecciona un usuario"
    placeholder="Busca..."
    searchable
    multiple
/>
```

#### 📋 Props

```blade
<!-- Búsqueda -->
<x-select 
    :options="$municipios"
    option-value="id"
    option-label="nombre"
    searchable              {{-- Habilita búsqueda --}}
    label="Municipios"
/>

<!-- Múltiple selección -->
<x-select 
    :options="$roles"
    option-value="id"
    option-label="name"
    wire:model="userRoles"
    multiple                {{-- Permite múltiples --}}
    placeholder="Selecciona roles"
/>

<!-- Con validación -->
<x-select 
    :options="$departamentos"
    wire:model="dept_id"
    label="Departamento"
    :invalid="$errors->has('dept_id')"
/>

<!-- Tamaños -->
<x-select :options="$opts" size="xs" />      {{-- Extra pequeño --}}
<x-select :options="$opts" size="sm" />      {{-- Pequeño --}}
<x-select :options="$opts" />                {{-- Defecto (lg) --}}
```

#### 💡 Ejemplo Completo

```blade
<div class="p-4">
    <x-select 
        wire:model.live="selectedDepartment"
        :options="$departments"
        option-value="id"
        option-label="nombre"
        label="Selecciona Departamento"
        placeholder="Busca un departamento..."
        searchable
    />

    @if($selectedDepartment)
        <p>Has seleccionado: {{ $selectedDepartment }}</p>
        
        <x-select 
            wire:model.live="selectedMunicipality"
            :options="$municipalities"
            option-value="id"
            option-label="nombre"
            label="Municipios"
            placeholder="Selecciona municipio..."
            searchable
        />
    @endif
</div>
```

---

### 📊 Componente: Chart

Renderiza gráficos ApexCharts con soporte de temas y reactividad.

```blade
<x-chart :config="$chartConfig" event="chartClicked" />
```

#### 🎯 Props

```blade
<!-- Configuración -->
<x-chart :config="$myChartConfig" />

<!-- Con evento al hacer clic -->
<x-chart 
    :config="$chartConfig"
    event="dataPointClicked"
/>
```

#### 🔔 Escuchando Eventos

```php
// En Livewire
use Livewire\Attributes\On;

class Dashboard extends Component
{
    #[On('dataPointClicked')]
    public function handleChartClick($value, $label)
    {
        \Log::info("Clic en {$label}: {$value}");
    }
}
```

---

### 🔲 Componente: QR

Renderiza códigos QR con opciones de formato y descarga.

```blade
{{-- Básico --}}
<x-qr data="https://example.com/user/123" size="200" />

{{-- Con descarga --}}
<x-qr 
    data="https://example.com"
    size="300"
    format="svg"
    download
    filename="mi-qr"
/>

{{-- Colores personalizados --}}
<x-qr 
    data="ABC123"
    :foreground="[0, 0, 0]"
    :background="[255, 255, 255]"
    error-correction="H"
    format="png"
/>
```

#### 🔧 Props Detallados

```blade
<!-- data: string REQUERIDO -->
<x-qr data="Contenido del QR" />

<!-- size: int = 200 -->
<x-qr data="..." size="300" />

<!-- format: 'svg' | 'png' = 'svg' -->
<x-qr data="..." format="png" />

<!-- error-correction: 'L'|'M'|'Q'|'H' = 'M' -->
<x-qr data="..." error-correction="Q" />

<!-- foreground: RGB array = [0, 0, 0] -->
<x-qr data="..." :foreground="[10, 20, 30]" />

<!-- background: RGB array = [255, 255, 255] -->
<x-qr data="..." :background="[240, 240, 240]" />

<!-- download: bool = false -->
<x-qr data="..." download />

<!-- filename: string = 'qrcode' -->
<x-qr data="..." download filename="mi-codigo" />

<!-- alt: string = 'Código QR' -->
<x-qr data="..." alt="QR de acceso" />
```

---

## 📋 DataTable Component

Sistema completo de tablas dinámicas con búsqueda, filtros avanzados, acciones masivas y una **directiva Blade revolucionaria** para personalizar columnas.

### ⚡ El Poder de @interact

El DataTable alcanza su máximo potencial gracias a **`@interact`**, una directiva Blade personalizada que revoluciona la forma de personalizar columnas. Sin necesidad de crear componentes adicionales, puedes:

- ✅ Transformar datos con lógica condicional
- ✅ Renderizar componentes complejos por celda
- ✅ Acceder a propiedades de Livewire directamente (`$this->`)
- ✅ Usar variables de loop automáticamente (`$loop->`)
- ✅ Aplicar permisos con `@can` en tiempo real
- ✅ Ejecutar métodos del componente al interactuar
- ✅ Type-safe con autocompletar del IDE

Todo esto con una sintaxis limpia y elegante que mantiene tu código organizado y mantenible.

### 🏗️ Estructura Técnica

```
DataTable (Trait)
├── Propiedades de estado
├── Métodos de ordenamiento y filtros
├── Procesamiento de datos
└── Búsqueda inteligente

Searchable (Trait)
├── Búsqueda en campos locales
├── Búsqueda en relaciones (dot notation)
├── Soporte para accesores
└── getAccessorMap() para campos virtuales

Data-Table (Componente)
├── Headers dinámicos
├── Filas paginadas
├── Selección múltiple
├── Acciones masivas
└── Formularios de filtro avanzado

@interact (Directiva Blade - CORE)
├── Sistema personalizado de slots anónimos
├── Parsing dinámico de expresiones
├── Inyección automática de $loop
├── Acceso a propiedades de Livewire
├── Type-safe con autocompletar IDE
└── Sintaxis limpia y elegante
```

**La directiva `@interact` es lo que hace verdaderamente poderoso al DataTable**, permitiendo personalizar cada columna sin necesidad de crear componentes adicionales o pasar datos explícitamente. Es una técnica avanzada que combina:
- Directivas Blade personalizadas
- Slots anónimos de Blade
- Parsing inteligente con regex
- Inyección de contexto automática

### 📊 Componente Livewire Base

```php
// app/Livewire/Admin/Permissions.php
use App\Traits\DataTable;
use App\Traits\Interact;

class Permissions extends Component
{
    use DataTable, Interact;

    // ========== HEADERS ==========
    public array $headers = [
        ['index' => 'id', 'label' => '#', 'align' => 'center'],
        ['index' => 'name', 'label' => 'Permiso'],
        ['index' => 'alias', 'label' => 'Alias'],
        ['index' => 'module', 'label' => 'Módulo'],
        ['index' => 'actions', 'label' => '', 'width' => '100px']
    ];

    // ========== PROPIEDADES DATATABLES (heredadas) ==========
    // public string $search = '';
    // public string $sortBy = 'id';
    // public string $sortDirection = 'desc';
    // public int $per_page = 10;
    // public array $filters = [];
    // public array $selectedRows = [];

    public function mount() {
        $this->sortBy = 'module';  // Ordenamiento inicial
    }

    #[Computed]
    public function rows() {
        // filterAdvance: búsqueda + filtros + ordenamiento
        return Permission::filterAdvance($this->headers, [
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortBy,
                'direction' => $this->sortDirection
            ],
            'filters' => $this->processFilters(),
        ])->paginate($this->per_page);
    }

    public function render() {
        return view('livewire.admin.permissions');
    }
}
```

### 🖼️ Vista de DataTable

```blade
{{-- resources/views/livewire/admin/permissions.blade.php --}}
<x-data-table 
    :headers="$this->headers"
    :rows="$this->rows"
    selectable
    advance-filter
>
    <!-- Acciones masivas -->
    <x-slot:mass-actions>
        <flux:menu.item 
            wire:click="deleteSelected"
            variant="danger"
            icon="trash">
            Eliminar seleccionados
        </flux:menu.item>
    </x-slot:mass-actions>

    <!-- Personalizar columnas con @interact -->
    @interact('nombre_completo', $row)
        <div class="flex items-center gap-3">
            <flux:avatar 
                name="{{ $row->nombre_completo }}" 
                size="lg"
                :src="$row->url_photo"
            />
            <div>
                <p class="font-semibold">{{ $row->nombre_completo }}</p>
                <p class="text-xs text-gray-500">{{ $row->email }}</p>
            </div>
        </div>
    @endinteract

    @interact('module', $row)
        <flux:badge color="blue">{{ $row->module }}</flux:badge>
    @endinteract
</x-data-table>
```

### 🔍 Trait DataTable: Propiedades y Métodos

```php
// ========== PROPIEDADES ==========
public string $search = '';              // Búsqueda general
public string $sortBy = 'id';            // Campo de ordenamiento
public string $sortDirection = 'desc';   // Dirección (asc/desc)
public int $per_page = 10;               // Registros por página
public array $filters = [];              // Filtros avanzados
public array $selectedRows = [];         // IDs seleccionados

// ========== MÉTODOS PÚBLICOS ==========

// Ordenamiento
public function sort(string $column)
{
    if ($this->sortBy === $column) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }
    $this->resetPage();
}

// Filtros
public function addFilter()                        // Agregar filtro
public function deleteFilter(int $index)           // Eliminar filtro
public function clearFilters()                     // Limpiar todos
protected function processFilters(): array         // Procesar valores

// Acción masiva ejemplo
public function deleteSelected()
{
    $this->authorize('admin.items.delete');
    Item::whereIn('id', $this->selectedRows)->delete();
    $this->selectedRows = [];
    $this->toastSuccess('Elementos eliminados');
}
```

### 🔎 Trait Searchable: Búsqueda Inteligente

#### Búsqueda en Campos Locales

```php
// Automáticamente busca en campos especificados en headers
$headers = [
    ['index' => 'nombre', 'label' => 'Nombre'],
    ['index' => 'email', 'label' => 'Correo'],
];

// Si escribes "Juan" → busca en 'nombre' y 'email'
$searchTerm = 'juan';
```

#### Búsqueda en Relaciones (Dot Notation)

```php
$headers = [
    ['index' => 'nombre', 'label' => 'Nombre'],
    ['index' => 'user.email', 'label' => 'Email del Usuario'],
    ['index' => 'municipio.nombre', 'label' => 'Municipio'],
];

// Busca automáticamente en relaciones anidadas
```

#### getAccessorMap(): Búsqueda en Accesores

Define qué campos de la BD corresponden a cada accesor:

```php
// app/Models/Admin/UserInformation.php
class UserInformation extends Model
{
    use Searchable;

    protected $appends = ['nombre_completo'];

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    // ========== ACCESSOR MAP ==========
    public function getAccessorMap(): array
    {
        return [
            'nombre_completo' => ['nombres', 'apellidos'],
            // 'accesor' => ['campos', 'que', 'componen']
        ];
    }
}
```

**Resultado:** Al buscar "juan garcía", encuentra registros donde:
- `nombres` contiene "juan" O
- `apellidos` contiene "garcía" O
- El CONCAT de `nombres + apellidos` contiene la búsqueda

#### Ejemplo Completo: Búsqueda en Relaciones + Accesores

```php
// app/Models/Admin/User.php
class User extends Model
{
    // Relación
    public function user_information() {
        return $this->hasOne(UserInformation::class);
    }
}

// app/Livewire/Admin/User/Index.php
public array $headers = [
    ['index' => 'id', 'label' => '#'],
    ['index' => 'user_information.nombre_completo', 'label' => 'Usuario'],
    ['index' => 'user_information.email', 'label' => 'Email'],
    ['index' => 'user_information.municipio.nombre', 'label' => 'Municipio'],
    ['index' => 'actions', 'label' => '']
];

#[Computed]
public function rows()
{
    return User::filterAdvance($this->headers, [
        'search' => $this->search,  // Busca en todas las columnas
        'sort' => ['field' => 'id', 'direction' => 'asc'],
        'filters' => $this->processFilters(),
    ])->paginate($this->per_page);
}
```

**Búsquedas automáticas:**
- "Juan García" → encuentra en `user_information.nombre_completo`
- "Quetzaltenango" → encuentra en `municipio.nombre`
- "juan@mail.com" → encuentra en `user_information.email`

### 🎛️ Filtros Avanzados

```blade
<!-- El componente data-table incluye filtro dinámico -->
<x-data-table :headers="$this->headers" :rows="$this->rows" advance-filter>
    <!-- Aparece un botón "Filtros" en la parte superior -->
</x-data-table>
```

**Operadores soportados:**

```
Texto:
  - like          (contiene)
  - not like      (no contiene)

Números:
  - =             (igual)
  - !=            (no igual)
  - >             (mayor que)
  - <             (menor que)
  - >=            (mayor o igual)
  - <=            (menor o igual)

Arrays:
  - in            (está en lista)
  - not in        (no está en lista)
  - between       (entre valores)
  - not between   (fuera de rango)

Nulos:
  - null          (es nulo)
  - not null      (no es nulo)
```

### ✔️ Acciones Masivas (Bulk Actions)

```blade
<x-data-table 
    :headers="$this->headers"
    :rows="$this->rows"
    selectable
>
    <x-slot:mass-actions>
        <flux:menu.item
            wire:click="bulk_approve"
            variant="success"
            icon="check-circle">
            Aprobar seleccionados
        </flux:menu.item>

        <flux:menu.item
            wire:click="bulk_reject"
            variant="danger"
            icon="x-circle">
            Rechazar seleccionados
        </flux:menu.item>

        <flux:menu.item
            wire:click="bulk_export"
            variant="primary"
            icon="arrow-down-tray">
            Exportar seleccionados
        </flux:menu.item>
    </x-slot:mass-actions>
</x-data-table>
```

**En el Livewire:**

```php
public function bulk_approve()
{
    Request::authorize('items.bulk_approve');
    
    Item::whereIn('id', $this->selectedRows)->update([
        'status' => 'approved'
    ]);
    
    $this->selectedRows = [];
    $this->toastSuccess(
        count($this->selectedRows) . ' elementos aprobados'
    );
}
```

---

## 🎯 Livewire Components

### 🧪 Componente: Test

Ejemplo de DataTable con múltiple selección y acciones.

```php
// app/Livewire/Test.php
class Test extends Component
{
    use DataTable;

    public array $seleccion = [];

    public array $headers = [
        ['index' => 'id', 'label' => '#'],
        ['index' => 'full_name', 'label' => 'Usuario'],
        ['index' => 'email', 'label' => 'Correo'],
        ['index' => 'created_at', 'label' => 'Creado'],
        ['index' => 'actions', 'label' => '']
    ];

    #[Computed]
    public function rows()
    {
        return User::filterAdvance($this->headers, [
            'search' => $this->search,
            'sort' => ['field' => $this->sortBy, 'direction' => $this->sortDirection],
            'filters' => $this->processFilters(),
        ])->paginate($this->per_page);
    }

    public function deleteSelected()
    {
        User::whereIn('id', $this->selectedRows)->delete();
        $this->selectedRows = [];
    }
}
```

**Vista:**

```blade
{{-- resources/views/livewire/test.blade.php --}}
<div class="p-4">
    <x-select 
        wire:model.live="seleccion"
        :options="$this->searchResults"
        option-value="id"
        option-label="nombre_completo"
        label="Usuarios"
        multiple
        searchable
    />

    <x-data-table :headers="$this->headers" :rows="$this->rows" selectable>
        <x-slot:mass-actions>
            <flux:menu.item 
                wire:click="deleteSelected"
                variant="danger"
                icon="trash">
                Eliminar
            </flux:menu.item>
        </x-slot:mass-actions>

        @interact('actions', $row)
            <flux:avatar name="{{ $row->name }}" />
        @endinteract
    </x-data-table>
</div>
```

---

### 📊 Componente: TestChart

Ejemplo de múltiples gráficos con datos reales.

```php
// app/Livewire/TestChart.php
class TestChart extends Component
{
    use Charts;

    public function render()
    {
        $chart1 = $this->mixedChart(
            [
                ['name' => 'Ventas', 'type' => 'column', 'data' => [10, 15, 8]],
                ['name' => 'Ingresos', 'type' => 'line', 'data' => [1000, 1500, 800]],
            ],
            ['Ene', 'Feb', 'Mar'],
            'Reporte Mensual'
        )
        ->set('stroke.width', [4, 0])
        ->build();

        $chart2 = $this->barChart(
            [['name' => 'Usuarios', 'data' => [50, 70, 40]]],
            ['Ene', 'Feb', 'Mar']
        )
        ->colors('warm')
        ->build();

        return view('livewire.test-chart', compact('chart1', 'chart2'));
    }
}
```

---

### 🔔 Componente: Toast

Sistema de notificaciones interactivas.

```php
// app/Livewire/Toast.php
class Toast extends Component
{
    public $toasts = [];
    public $position = 'bottom-5 right-5';

    #[On('showToast')]
    public function add($data)
    {
        $id = uniqid();
        
        $this->toasts[$id] = [
            'id' => $id,
            'title' => $data['title'] ?? 'Notificación',
            'message' => $data['message'] ?? '',
            'type' => $data['type'] ?? 'secondary',
            'variant' => $this->getVariantFromType($data['type']),
            'icon' => $this->getIconFromType($data['type']),
            'duration' => $data['duration'] ?? 5000,
        ];
    }

    #[On('remove-toast')]
    public function remove($id)
    {
        $this->toasts[$id]['show'] = false;
    }
}
```

**Uso desde cualquier componente:**

```php
use App\Traits\Interact;

class MyComponent extends Component
{
    use Interact;

    public function save()
    {
        try {
            // ... guardar datos ...
            $this->toastSuccess('Guardado correctamente');
        } catch (\Exception $e) {
            $this->toastError('Error: ' . $e->getMessage());
        }
    }
}
```

---

## 🔧 Traits

### 📋 Trait: DataTable

Proporciona funcionalidad completa de tablas.

```php
trait DataTable
{
    use WithPagination;

    // Propiedades
    public string $search = '';
    public string $sortBy = 'id';
    public string $sortDirection = 'desc';
    public int $per_page = 10;
    public array $filters = [];
    public array $selectedRows = [];

    // Métodos públicos
    public function sort(string $column)
    public function addFilter()
    public function deleteFilter(int $index)
    public function clearFilters()
}
```

### 🔍 Trait: Searchable

Búsqueda inteligente con soporte para relaciones y accesores.

```php
trait Searchable
{
    // Scope principal
    public function scopeFilterAdvance(
        Builder $query,
        array $headers,
        array $params = []
    ): Builder

    // Helpers internos
    protected function applySmartFieldSearch()
    protected function applyRelationSearch()
    protected function applyAccessorSearch()
    protected function resolveRelatedModel()
    protected function applySorting()
}
```

### 💬 Trait: Interact

Proporciona métodos para notificaciones Toast y control de UI.

```php
trait Interact
{
    // Toast Notifications
    protected function toast(array $data): void
    protected function toastSuccess(string $message, string $title = 'Éxito')
    protected function toastError(string $message, string $title = 'Error')
    protected function toastInfo(string $message, string $title = 'Información')
    protected function toastWarning(string $message, string $title = 'Advertencia')

    // Stepper
    public int $step = 1;
    public function handleStep(int $step)
    public function nextStep()
    public function previousStep()

    // Navigation
    public ?int $nav_option = 1;
    public function navToggle(int $option)
}
```

### 📊 Trait: Charts

Proporciona métodos para crear gráficos.

```php
trait Charts
{
    public function mixedChart(array $series, array $labels, string $title = ''): Chart
    public function barChart(array $series, array $labels): Chart
    public function columnChart(array $series, array $labels): Chart
    public function lineChart(array $series, array $labels): Chart
    public function areaChart(array $series, array $labels): Chart
    public function pieChart(array $series, array $labels): Chart
    public function donutChart(array $series, array $labels): Chart
    public function scatterChart(array $series): Chart
    public function bubbleChart(array $series): Chart
    public function heatmapChart(array $series, array $labels): Chart
    public function candlestickChart(array $series, array $labels = []): Chart
}
```

### 🎯 Directiva Personalizada: @interact en AppServiceProvider

La directiva `@interact` es **registrada en el bootstrap** del aplicativo en lugar de ser un simple helper. La encontrarás en:

**Ubicación:** [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) → método `boot()`

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    // ... otras configuraciones ...

    // ========== DIRECTIVA @interact ==========
    Blade::directive('interact', function (mixed $expression): string {
        // Parsea la expresión dividiendo por comas (respetando paréntesis)
        $directive = array_map('trim', preg_split('/,(?![^(]*[)])/', $expression));
        $name      = array_shift($directive);                    // 'nombre_columna'
        $arguments = array_shift($directive) ?? '';              // '$row, $loop'

        // Crea identificador único para el slot
        $cleanName = 'column_' . str_replace('.', '_', trim($name, "'\""));

        // Transforma @interact en un slot anónimo Blade
        return "<?php \$__env->slot('{$cleanName}', function({$arguments}, \$loop = null) use (\$__env, \$__blaze) { ?>";
    });

    Blade::directive('endinteract', fn (): string => '<?php }); ?>');

    // Gate para autorización global
    Gate::before(function ($user, $ability) {
        return $user->hasRole('Sysadmin') ? true : null;
    });
}
```

**Esto garantiza que:**
- La directiva está registrada globalmente en cada request
- El parsing es consistente en toda la aplicación
- La sintaxis es validada en tiempo de compilación de Blade

### 🎯 Directiva Blade: @interact (Sistema Avanzado de Slots)

**@interact** es una directiva Blade personalizada extremadamente poderosa que permite pasar datos dinámicamente a slots anónimos con una sintaxis limpia y elegante. Transforma la forma de personalizar columnas en DataTables.

#### 🔧 Implementación Técnica

La directiva se define en [AppServiceProvider.php](app/Providers/AppServiceProvider.php):

```php
// app/Providers/AppServiceProvider.php - boot()
Blade::directive('interact', function (mixed $expression): string {
    // Parsea la expresión dividiendo por comas (sin dividir dentro de paréntesis)
    $directive = array_map('trim', preg_split('/,(?![^(]*[)])/', $expression));
    $name      = array_shift($directive);              // 'nombre_columna'
    $arguments = array_shift($directive) ?? '';        // '$row, $loop, etc'

    // Crea un identificador único para el slot
    $cleanName = 'column_' . str_replace('.', '_', trim($name, "'\""));

    // Transforma @interact en un slot anónimo de Blade
    return "<?php \$__env->slot('{$cleanName}', function({$arguments}, \$loop = null) use (\$__env, \$__blaze) { ?>";
});

Blade::directive('endinteract', fn (): string => '<?php }); ?>');
```

#### 🎯 Cómo Funciona Internamente

1. **Parsing**: Descompone la expresión usando regex inteligente que respeta paréntesis
2. **Slots Anónimos**: Convierte `@interact` en `$__env->slot()` de Blade
3. **Scope Dinámico**: Inyecta automáticamente `$loop` para acceso a metadatos de iteración
4. **Variables en Contexto**: Todas las variables disponibles en la vista están disponibles en el slot

#### ✨ Sintaxis y Uso

**Forma Básica:**
```blade
@interact('nombre_columna', $row)
    <!-- Contenido personalizado -->
    {{ $row->propiedad }}
@endinteract
```

**Con Variables Adicionales:**
```blade
@interact('usuario', $row, $loop)
    <div>
        <strong>{{ $row->nombre }}</strong>
        <span>Fila #{{ $loop->iteration }}</span>
    </div>
@endinteract
```

**Con Acceso a Variables de Livewire:**
```blade
{{-- Todas las propiedades públicas de Livewire están disponibles --}}
@interact('estado', $row)
    @if($row->status === $this->activeStatus)
        <flux:badge color="green">Activo</flux:badge>
    @else
        <flux:badge color="gray">Inactivo</flux:badge>
    @endif
@endinteract
```

#### 🚀 Casos de Uso Avanzados

##### 1️⃣ **Columna con Avatar y Datos Relacionados**

```blade
@interact('usuario', $row)
    <div class="flex items-center gap-3">
        <flux:avatar 
            name="{{ $row->nombre_completo }}" 
            size="lg"
            initials="{{ $row->user?->initials() }}"
            :src="$row->url_photo"
        />
        <div class="grid">
            <span class="font-semibold">{{ $row->nombre_completo }}</span>
            <div class="flex gap-2">
                <flux:icon.envelope class="size-4"/>
                <span class="text-xs text-gray-500">{{ $row->email }}</span>
            </div>
            <div class="flex gap-2">
                <flux:icon.phone class="size-4"/>
                <span class="text-xs text-gray-500">{{ $row->telefono }}</span>
            </div>
        </div>
    </div>
@endinteract
```

##### 2️⃣ **Lógica Condicional con Iconos**

```blade
@interact('estado_usuario', $row)
    @if ($row->user->deleted_at)
        <flux:icon.x-circle class="size-5 text-red-500 mx-auto" title="Usuario desactivado" />
    @else
        <flux:icon.check-circle class="size-5 text-green-500 mx-auto" title="Usuario activo" />
    @endif
@endinteract
```

##### 3️⃣ **Acciones Dinámicas con Permisos**

```blade
@interact('acciones', $row)
    <flux:dropdown>
        <flux:button 
            size="sm" 
            icon="ellipsis-vertical" 
            variant="ghost" 
        />
        <flux:menu>
            @can('admin.users.update')
                <flux:menu.item 
                    icon="pencil-square"
                    wire:click="edit({{ $row->id }})"
                    wire:navigate>
                    Editar
                </flux:menu.item>
            @endcan
            
            @can('admin.users.delete')
                @if ($row->user->deleted_at)
                    <flux:menu.item 
                        variant="danger" 
                        icon="check-circle"
                        wire:click="restore({{ $row->id }})">
                        Restaurar
                    </flux:menu.item>
                @else
                    <flux:menu.item 
                        variant="danger" 
                        icon="trash"
                        wire:click="delete({{ $row->id }})">
                        Eliminar
                    </flux:menu.item>
                @endif
            @endcan
        </flux:menu>
    </flux:dropdown>
@endinteract
```

##### 4️⃣ **Badges Dinámicos con Datos Relacionales**

```blade
@interact('modulo', $row)
    @switch($row->module)
        @case('admin')
            <flux:badge color="red">Administración</flux:badge>
            @break
        @case('users')
            <flux:badge color="blue">Usuarios</flux:badge>
            @break
        @case('menu')
            <flux:badge color="purple">Menú</flux:badge>
            @break
        @default
            <flux:badge color="gray">{{ ucfirst($row->module) }}</flux:badge>
    @endswitch
@endinteract
```

##### 5️⃣ **Acceso a Propiedades de Loop**

```blade
@interact('numero_fila', $row, $loop)
    <div class="flex justify-center">
        <span class="{{ 
            $loop->first ? 'font-bold text-blue-600' : 
            ($loop->last ? 'font-bold text-red-600' : 'text-gray-600')
        }}">
            {{ $loop->iteration }}
        </span>
    </div>
@endinteract
```

#### 📊 Variables Automáticas en Scope

Dentro de `@interact`, tienes acceso a:

```php
// Variables explícitas (pasadas en la directiva)
$row          // El objeto/array de la fila actual
$loop         // Objeto de información de iteración

// Variables heredadas del componente Livewire
$this->search
$this->sortBy
$this->filters
$this->selectedRows
// ... todas las propiedades públicas del componente

// Variables de la vista
// Todas las variables pasadas a view() están disponibles

// Métodos del componente
$this->authorize()
$this->toastSuccess()
$this->toastError()
// ... etc
```

#### 🔄 Comparativa: Antes vs Después

**❌ Opción 1: Método Tradicional (Verboso)**

```blade
<table>
    @foreach($rows as $row)
        <tr>
            <td>
                @if($row->status === 'active')
                    <span class="badge green">Activo</span>
                @else
                    <span class="badge gray">Inactivo</span>
                @endif
            </td>
            <td>
                <button @click="edit({{ $row->id }})">Editar</button>
            </td>
        </tr>
    @endforeach
</table>
```

**Problema:** Todo mezclado, difícil de reutilizar, manejo manual de loops.

---

**❌ Opción 2: Componentes Blade (Overhead)**

```blade
<x-users-table :rows="$rows">
    <x-slot name="status">
        {{-- Requiere pasar datos manualmente al componente --}}
        <x-status-badge :status="$row->status" />
    </x-slot>
</x-users-table>
```

**Problema:** Necesita crear componentes de UI intermedios, más boilerplate.

---

**✅ Opción 3: @interact (Óptimo) 🚀**

```blade
<x-data-table :headers="$headers" :rows="$rows">
    @interact('status', $row)
        @if($row->status === 'active')
            <flux:badge color="green">Activo</flux:badge>
        @else
            <flux:badge color="gray">Inactivo</flux:badge>
        @endif
    @endinteract

    @interact('actions', $row)
        <flux:dropdown>
            <flux:button icon="ellipsis-vertical" variant="ghost" />
            <flux:menu>
                @can('edit')
                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                        Editar
                    </flux:menu.item>
                @endcan
                @can('delete')
                    <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $row->id }})">
                        Eliminar
                    </flux:menu.item>
                @endcan
            </flux:menu>
        </flux:dropdown>
    @endinteract
</x-data-table>
```

**Ventajas:**
- ✨ Sintaxis limpia y declarativa
- 🎯 Lógica por columna separada
- 🔐 Acceso a `$this` (autorizaciones, métodos)
- 🔄 Reutilizable en múltiples tablas
- 📝 Type-safe con IDE autocompletar

---

**Tabla Comparativa Detallada:**

| Aspecto | Tradicional | Componentes | @interact |
|---------|-------------|-------------|-----------|
| **Sintaxis** | Verbose | Intermedia | Limpia |
| **Reutilización** | ❌ No | ✅ Parcial | ✅ Total |
| **Acceso a $this** | ❌ No | ⚠️ Indirecto | ✅ Directo |
| **Permisos (@can)** | ✅ Sí | ✅ Sí | ✅ Sí |
| **Variables de Loop** | ✅ Manual | ⚠️ Pasado | ✅ Automático |
| **Métodos Livewire** | ❌ No | ⚠️ Limitado | ✅ Completo |
| **Boilerplate** | Poco | Mucho | Mínimo |
| **Performance** | Bueno | Bueno | Excelente |
| **Mantenibilidad** | Baja | Media | Alta |

#### 💡 Ventajas Fundamentales

| Ventaja | Descripción |
|---------|-------------|
| **Sintaxis Limpia** | Separación clara entre headers y personalizaciones |
| **Reutilizable** | Define una vez, usa en múltiples tablas |
| **Type-Safe** | El IDE reconoce `$row` y autocompletar funciona |
| **Slots Anónimos** | Mejor que componentes tradicionales |
| **Acceso a Livewire** | `$this` disponible directamente |
| **Sin Boilerplate** | No necesitas pasar datos explícitamente |
| **Scopings Limpios** | Las variables de loop se inyectan automáticamente |

#### 🏗️ Cómo se Renderiza Internamente

La directiva se transforma en:

```blade
{{-- Entrada original --}}
@interact('nombre', $row)
    <span>{{ $row->nombre }}</span>
@endinteract

{{-- Se compila a --}}
<?php $__env->slot('column_nombre', function($row, $loop = null) use ($__env, $__blaze) { ?>
    <span>{{ $row->nombre }}</span>
<?php }); ?>
```

#### 🎨 Cómo el Componente data-table Captura los Slots

El componente `<x-data-table>` es inteligente sobre cómo maneja los slots personalizados:

**Flujo de Ejecución:**

```
1. Defines @interact('nombre', $row)
   ↓
2. AppServiceProvider compila a un slot anónimo 'column_nombre'
   ↓
3. El componente data-table itera sobre las filas
   ↓
4. Para cada fila, busca en $slots los slots disponibles
   ↓
5. Si existe 'column_nombre', lo renderiza en esa celda
   ↓
6. Si NO existe, renderiza el valor raw del campo
```

**Ejemplo en el Componente:**

```blade
{{-- resources/views/components/data-table.blade.php --}}
<table>
    <tbody>
        @foreach($rows as $index => $row)
            <tr>
                @foreach($headers as $header)
                    <td>
                        {{-- Busca un slot personalizado: 'column_' . $header['index'] --}}
                        @if(isset($slots['column_' . $header['index']]))
                            {{-- Renderiza el slot personalizado --}}
                            {{ $slots['column_' . $header['index']]($row, $loop) }}
                        @else
                            {{-- Fallback: renderiza el valor raw --}}
                            {{ data_get($row, $header['index']) }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
```

**Ventaja Clave:** El componente automáticamente busca `column_*` slots y los renderiza si existen. Si no existen, simplemente muestra el valor del campo del row. **Esto permite definir personalizaciones opcionales sin afectar las columnas sin personalizar.**

#### 🔄 Flujo Completo de Datos

```
User Define Headers
        ↓
    [
        ['index' => 'nombre', 'label' => 'Nombre'],
        ['index' => 'email', 'label' => 'Email'],
        ['index' => 'acciones', 'label' => 'Acciones']
    ]
        ↓
Componente Livewire Pasa Filas
        ↓
    Permission::filterAdvance(...)->paginate(...)
        ↓
Vista Renderiza x-data-table
        ↓
    <x-data-table :headers="$headers" :rows="$rows">
        @interact('nombre', $row)...
        @interact('acciones', $row)...
    </x-data-table>
        ↓
@interact se Compila a Slots Anónimos
        ↓
    $__env->slot('column_nombre', function($row, $loop)...)
    $__env->slot('column_acciones', function($row, $loop)...)
        ↓
Componente data-table Itera y Busca Slots
        ↓
    Para cada $row:
        Para cada $header:
            ¿Existe slot 'column_' . $header['index']?
            Si → renderiza slot con ($row, $loop)
            No → renderiza data_get($row, $header['index'])
        ↓
    Resultado: Tabla con celdas personalizadas
```

---

## 💡 Ejemplos Prácticos

### 📌 Ejemplo 1: CRUD Completo de Permisos

```php
// app/Livewire/Admin/Permissions.php
use App\Traits\DataTable;
use App\Traits\Interact;

class Permissions extends Component
{
    use DataTable, Interact;

    public array $permission = [];

    public array $headers = [
        ['index' => 'id', 'label' => '#'],
        ['index' => 'name', 'label' => 'Nombre'],
        ['index' => 'alias', 'label' => 'Alias'],
        ['index' => 'module', 'label' => 'Módulo'],
        ['index' => 'actions', 'label' => '']
    ];

    #[Computed]
    public function rows()
    {
        return Permission::filterAdvance($this->headers, [
            'search' => $this->search,
            'sort' => ['field' => $this->sortBy, 'direction' => $this->sortDirection],
            'filters' => $this->processFilters(),
        ])->paginate($this->per_page);
    }

    // ========== CREAR ==========
    public function store()
    {
        $this->validate([
            'permission.name' => 'required|string|max:255',
            'permission.alias' => 'required|string|max:255',
            'permission.module' => 'required|string|max:255',
        ]);

        try {
            Permission::create([
                'name' => mb_strtolower(trim($this->permission['name'])),
                'alias' => $this->permission['alias'],
                'module' => mb_strtolower($this->permission['module']),
                'guard_name' => 'web',
            ]);

            $this->toastSuccess('Permiso creado exitosamente');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error: ' . $th->getMessage());
        }
    }

    // ========== EDITAR ==========
    public function edit(int $id)
    {
        $this->permission = Permission::findOrFail($id)->toArray();
        Flux::modal('editPermission')->show();
    }

    public function update()
    {
        $this->validate([
            'permission.name' => 'required|string|min:3',
            'permission.alias' => 'required|string',
            'permission.module' => 'required|string',
        ]);

        try {
            $permission = Permission::findOrFail($this->permission['id']);
            $permission->update([
                'name' => mb_strtolower($this->permission['name']),
                'alias' => $this->permission['alias'],
                'module' => mb_strtolower($this->permission['module']),
            ]);

            $this->toastSuccess('Permiso actualizado');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error: ' . $th->getMessage());
        }
    }

    // ========== ELIMINAR ==========
    public function destroy(int $id)
    {
        $this->authorize('admin.permissions.delete');

        try {
            Permission::findOrFail($id)->delete();
            $this->toastSuccess('Permiso eliminado');
        } catch (\Throwable $th) {
            $this->toastError('Error: ' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.permissions');
    }
}
```

**Vista:**

```blade
{{-- resources/views/livewire/admin/permissions.blade.php --}}
<div>
    @can('admin.permissions.create')
        <flux:button 
            icon="plus"
            wire:click="createNew"
            variant="primary">
            Crear Permiso
        </flux:button>
    @endcan

    <x-data-table 
        :headers="$this->headers"
        :rows="$this->rows"
        advance-filter
    >
        @interact('actions', $row)
            <flux:dropdown>
                <flux:button icon="ellipsis-vertical" variant="ghost" />
                <flux:menu>
                    @can('admin.permissions.update')
                        <flux:menu.item
                            icon="pencil-square"
                            wire:click="edit({{ $row->id }})">
                            Editar
                        </flux:menu.item>
                    @endcan

                    @can('admin.permissions.delete')
                        <flux:menu.item
                            icon="trash"
                            variant="danger"
                            wire:click="delete({{ $row->id }})">
                            Eliminar
                        </flux:menu.item>
                    @endcan
                </flux:menu>
            </flux:dropdown>
        @endinteract
    </x-data-table>
</div>
```

---

### 📌 Ejemplo 2: Gráfico Dinámico con Datos Reales

```php
// app/Livewire/Dashboard.php
class Dashboard extends Component
{
    use Charts;

    public function render()
    {
        // Datos de la BD
        $users_por_mes = User::selectRaw('
            MONTH(created_at) as mes,
            COUNT(*) as total
        ')
        ->groupBy('mes')
        ->pluck('total', 'mes')
        ->toArray();

        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'];

        // Crear gráfico
        $chartDataUsers = $this->lineChart(
            [['name' => 'Usuarios Nuevos', 'data' => array_values($users_por_mes)]],
            $meses
        )
        ->colors('cool')
        ->set('stroke.width', 3)
        ->formatter('compact')
        ->build();

        // Gráfico de permisos por módulo
        $permissionsByModule = Permission::selectRaw('
            module,
            COUNT(*) as total
        ')
        ->groupBy('module')
        ->get();

        $chartPermissions = $this->donutChart(
            $permissionsByModule->pluck('total')->toArray(),
            $permissionsByModule->pluck('module')->toArray()
        )
        ->colors('warm')
        ->build();

        return view('dashboard', compact('chartDataUsers', 'chartPermissions'));
    }
}
```

---

### 📌 Ejemplo 3: Búsqueda Avanzada en Relaciones

```php
// app/Livewire/Admin/User/Index.php
class Index extends Component
{
    use DataTable, Interact;

    public array $headers = [
        ['index' => 'user_information.nombre_completo', 'label' => 'Nombre'],
        ['index' => 'user_information.email', 'label' => 'Email'],
        ['index' => 'user_information.municipio.nombre', 'label' => 'Municipio'],
        ['index' => 'user_information.fecha_nacimiento', 'label' => 'Nacimiento'],
        ['index' => 'actions', 'label' => '']
    ];

    #[Computed]
    public function rows()
    {
        // Búsqueda automática en:
        // - user_information.nombre_completo (accesor con getAccessorMap)
        // - user_information.email
        // - municipio.nombre (relación anidada)
        return User::with(['user_information', 'user_information.municipio'])
            ->filterAdvance($this->headers, [
                'search' => $this->search,  // Busca en todo
                'sort' => [
                    'field' => $this->sortBy,
                    'direction' => $this->sortDirection
                ],
                'filters' => $this->processFilters(),
            ])
            ->paginate($this->per_page);
    }
}
```

**Búsquedas posibles:**

```
"Juan García"        → Busca en nombre_completo (via getAccessorMap)
"quetzaltenango"     → Busca en municipio.nombre
"user@email.com"     → Busca en email
```

---

## 🚀 Instalación y Configuración

```bash
# Clonar repositorio
git clone <repo-url>
cd cybersoft

# Instalar dependencias
composer install
npm install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate --seed
php artisan storage:link

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

---

## 📞 Soporte y Documentación

- **Laravel**: https://laravel.com/docs
- **Livewire**: https://livewire.laravel.com
- **Flux UI**: https://fluxui.dev
- **ApexCharts**: https://apexcharts.com
- **Spatie Permissions**: https://spatie.be/docs/laravel-permission

---

**Última actualización:** Marzo 2026
**Versión:** 1.0.0
**License:** MIT
