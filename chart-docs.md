# 📊 Chart Service — Documentación

Sistema de generación de gráficas para Laravel + Livewire 4 + Alpine.js usando [ApexCharts](https://apexcharts.com).

---

## 📁 Estructura

```
app/
├── Services/
│   └── Chart.php          # Builder principal
├── Traits/
│   └── Charts.php         # Presets reutilizables
resources/views/components/
└── chart.blade.php        # Componente anónimo Blade
```

---

## ⚡ Inicio rápido

### 1. Agrega el trait a tu componente Livewire

```php
use App\Traits\Charts;

class DashboardComponent extends Component
{
    use Charts;

    public array $config = [];

    public function mount(): void
    {
        $this->config = $this->barChart(
            series: [['name' => 'Ventas', 'data' => [10, 25, 18, 30]]],
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril']
        )->build();
    }
}
```

### 2. Usa el componente en tu vista

```blade
<x-chart :config="$config" class="h-64" />
```

---

## 🏗️ Chart Service

El servicio principal sigue el **patrón builder con inmutabilidad** — cada método devuelve un clon, así que puedes reutilizar una base sin efectos secundarios.

```php
use App\Services\Chart;

$config = Chart::make('line')   // Crea una instancia
    ->series([...])             // Define los datos
    ->labels([...])             // Define etiquetas
    ->height(350)               // Personaliza
    ->build();                  // ✅ Devuelve el array final
```

> **Importante:** `.build()` siempre debe ser la última llamada de la cadena. Devuelve el array PHP que el componente Blade convierte a JS via `@js()`.

---

## 🔧 Métodos del builder

### `series(array $series)`

Define los datos del chart. El formato varía según el tipo.

```php
// Serie simple (line, bar, area)
->series([
    ['name' => 'Ingresos', 'data' => [100, 200, 150, 300]],
    ['name' => 'Gastos',   'data' => [80,  120, 90,  200]],
])

// Serie para mixed chart (columna + línea)
->series([
    ['name' => 'Ventas',   'type' => 'column', 'data' => [10, 25, 18]],
    ['name' => 'Promedio', 'type' => 'line',   'data' => [15, 20, 16]],
])
```

---

### `labels(array $labels)`

Define las etiquetas del eje X (charts cartesianos) o las etiquetas de cada segmento (pie, donut).

```php
// Charts cartesianos → xaxis.categories
->labels(['Ene', 'Feb', 'Mar', 'Abr'])

// Pie / Donut → labels
->labels(['Ventas', 'Marketing', 'Soporte'])
```

> El método detecta automáticamente el tipo de chart y coloca los datos en la clave correcta.

---

### `set(string $key, mixed $value)`

Acceso directo a cualquier opción de ApexCharts usando notación de puntos.

```php
->set('stroke.curve', 'stepline')
->set('plotOptions.bar.horizontal', true)
->set('xaxis.title.text', 'Mes')
->set('yaxis.min', 0)
->set('chart.zoom.enabled', false)
```

> Consulta la [documentación de ApexCharts](https://apexcharts.com/docs/options/) para todas las opciones disponibles.

---

### `colors(string|array $colors)`

Aplica un esquema predefinido o colores personalizados.

```php
// Esquema predefinido
->colors('default')  // 🔵 Azul, verde, amarillo, rojo, violeta
->colors('warm')     // 🔴 Tonos cálidos: naranja, rojo, amarillo
->colors('cool')     // 🔷 Tonos fríos: azul, cian, violeta
->colors('mono')     // ⬛ Escala de grises oscuros

// Colores personalizados
->colors(['#FF6384', '#36A2EB', '#FFCE56'])
```

---

### `height(int|string $value)` y `width(int|string $value)`

```php
->height(350)       // Píxeles fijos
->height('100%')    // Relativo al contenedor (default)
->width(600)
->width('100%')
```

---

### `toolbar(bool $show = true)`

Muestra u oculta los controles de zoom, descarga y selección.

```php
->toolbar()         // Muestra (default)
->toolbar(false)    // Oculta — recomendado en dashboards compactos
```

---

### `formatter(string $preset)`

Define el formato de los valores en tooltip, dataLabels y eje Y.

```php
->formatter('currency')   // $ 1,250.00
->formatter('percent')    // 85.3 %
->formatter('compact')    // 1.2K / 3.5M

// Expresión JS personalizada (solo uso del desarrollador)
->formatter('val => "Q " + val.toLocaleString()')
```

> ⚠️ Las expresiones JS personalizadas se evalúan con `new Function()`. Nunca usar con valores provenientes del usuario.

---

### `addGoal(int $value, string $label, string $color = '#ef4444')`

Agrega una línea de meta horizontal al chart.

```php
->addGoal(5000, 'Meta mensual')
->addGoal(8000, 'Meta anual', '#10b981')
```

---

### `addGoals(array $goals)`

Agrega múltiples metas en una sola llamada.

```php
->addGoals([
    ['value' => 3000, 'label' => 'Meta mínima', 'color' => '#f59e0b'],
    ['value' => 5000, 'label' => 'Meta base',   'color' => '#10b981'],
    ['value' => 8000, 'label' => 'Meta ideal',  'color' => '#3b82f6'],
])
```

---

### `group(string $name)`

Sincroniza el tooltip y zoom entre múltiples charts del mismo grupo.

```php
// Chart 1
->group('ventas-dashboard')

// Chart 2 (se sincronizan al hacer hover)
->group('ventas-dashboard')
```

---

### `responsive(array $breakpoints)`

Agrega breakpoints responsivos **adicionales** a los defaults globales (640px y 1024px). Se fusionan, no se reemplazan.

```php
->responsive([
    [
        'breakpoint' => 480,
        'options'    => [
            'chart'  => ['height' => 200],
            'legend' => ['show' => false],
        ],
    ],
])
```

---

### `responsiveOverride(array $breakpoints)`

Reemplaza **completamente** los defaults responsivos globales. Usar solo cuando los defaults no aplican.

```php
->responsiveOverride([
    [
        'breakpoint' => 768,
        'options'    => ['chart' => ['height' => 400]],
    ],
])
```

---

### `build()`

Cierra la cadena y devuelve el array de opciones listo para pasar al componente.

```php
$config = Chart::make('bar')
    ->series([...])
    ->labels([...])
    ->build(); // ← siempre al final
```

---

## 🎨 Presets del Trait

El trait `Charts` ofrece estructuras base para los tipos más comunes. Se usa dentro de un componente Livewire y cada método devuelve el builder para que puedas seguir personalizando antes de llamar `.build()`.

### 📊 `barChart` — Barras horizontales

```php
$this->barChart(
    series: [['name' => 'Departamentos', 'data' => [44, 55, 41, 67, 22]]],
    labels: ['RRHH', 'Ventas', 'TI', 'Ops', 'Legal']
)->build();
```

---

### 📈 `columnChart` — Barras verticales

```php
$this->columnChart(
    series: [
        ['name' => 'Q1', 'data' => [10, 25, 18, 30]],
        ['name' => 'Q2', 'data' => [20, 15, 28, 22]],
    ],
    labels: ['Enero', 'Febrero', 'Marzo', 'Abril']
)->colors('cool')->build();
```

---

### 📉 `lineChart` — Líneas

```php
$this->lineChart(
    series: [['name' => 'Temperatura', 'data' => [28, 31, 26, 33, 29]]],
    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie']
)->set('stroke.width', 3)->build();
```

---

### 🏔️ `areaChart` — Áreas

```php
$this->areaChart(
    series: [['name' => 'Usuarios activos', 'data' => [120, 180, 150, 220, 200]]],
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May']
)->set('fill.type', 'gradient')->build();
```

---

### 🍩 `donutChart` — Dona

```php
$this->donutChart(
    series: [44, 55, 13, 33],
    labels: ['Ventas', 'Marketing', 'Soporte', 'Desarrollo']
)->set('plotOptions.pie.donut.size', '70%')->build();
```

---

### 🔀 `mixedChart` — Mixto (columna + línea + área)

```php
$this->mixedChart(
    series: [
        ['name' => 'Ingresos', 'type' => 'column', 'data' => [440, 505, 414, 671]],
        ['name' => 'Flujo',    'type' => 'line',   'data' => [230, 420, 350, 500]],
        ['name' => 'Gastos',   'type' => 'area',   'data' => [180, 310, 290, 400]],
    ],
    labels: ['Q1', 'Q2', 'Q3', 'Q4'],
    title:  'Resumen financiero'
)->build();
```

---

### 🔵 `scatterChart` — Dispersión

Los datos son pares `[x, y]`.

```php
$this->scatterChart(
    series: [
        ['name' => 'Grupo A', 'data' => [[10, 20], [15, 35], [40, 60], [25, 45]]],
        ['name' => 'Grupo B', 'data' => [[30, 10], [20, 50], [50, 30], [35, 65]]],
    ]
)->build();
```

---

### 🫧 `bubbleChart` — Burbujas

Los datos son triples `[x, y, z]` donde `z` define el tamaño de la burbuja.

```php
$this->bubbleChart(
    series: [
        ['name' => 'Producto A', 'data' => [[10, 20, 15], [15, 35, 8],  [40, 60, 25]]],
        ['name' => 'Producto B', 'data' => [[30, 10, 5],  [20, 50, 20], [50, 30, 12]]],
    ]
)->build();
```

---

### 🌡️ `heatmapChart` — Mapa de calor

Cada serie es una **fila** de la matriz.

```php
$this->heatmapChart(
    series: [
        ['name' => 'Lunes',    'data' => [10, 45, 30, 20, 55]],
        ['name' => 'Martes',   'data' => [25, 15, 50, 35, 40]],
        ['name' => 'Miércoles','data' => [40, 60, 10, 45, 20]],
        ['name' => 'Jueves',   'data' => [15, 30, 55, 25, 35]],
        ['name' => 'Viernes',  'data' => [50, 20, 40, 60, 10]],
    ],
    labels: ['8am', '10am', '12pm', '2pm', '4pm']
)->build();
```

---

### 🕯️ `candlestickChart` — Velas financieras (OHLC)

Cada punto tiene `x` (etiqueta) y `y` con `[apertura, máximo, mínimo, cierre]`.

```php
$this->candlestickChart(
    series: [
        ['data' => [
            ['x' => 'Ene', 'y' => [154, 168, 149, 162]],
            ['x' => 'Feb', 'y' => [162, 172, 155, 169]],
            ['x' => 'Mar', 'y' => [169, 175, 158, 161]],
            ['x' => 'Abr', 'y' => [161, 180, 157, 178]],
        ]],
    ],
    labels: ['Ene', 'Feb', 'Mar', 'Abr']
)->build();
```

> 🟢 Verde = cierre mayor que apertura (alcista) | 🔴 Rojo = cierre menor que apertura (bajista)

---

### 🗺️ `treemapChart` — Mapa de árbol

Cada punto tiene `x` (nombre del bloque) y `y` (valor que determina el tamaño).

```php
$this->treemapChart(
    series: [
        ['data' => [
            ['x' => 'Ventas',      'y' => 218],
            ['x' => 'Marketing',   'y' => 149],
            ['x' => 'Desarrollo',  'y' => 310],
            ['x' => 'Soporte',     'y' => 184],
            ['x' => 'Legal',       'y' => 95],
        ]],
    ]
)->build();
```

---

### 📅 `rangeBarChart` — Barras de rango / Gantt

Cada punto tiene `x` (tarea) y `y` con `[inicio, fin]`.

```php
$this->rangeBarChart(
    series: [
        ['name' => 'Fase 1 — Diseño', 'data' => [
            ['x' => 'Wireframes', 'y' => [0,  15]],
            ['x' => 'UI/UX',      'y' => [10, 30]],
        ]],
        ['name' => 'Fase 2 — Desarrollo', 'data' => [
            ['x' => 'Frontend',   'y' => [25, 60]],
            ['x' => 'Backend',    'y' => [20, 55]],
        ]],
        ['name' => 'Fase 3 — QA', 'data' => [
            ['x' => 'Testing',    'y' => [50, 75]],
            ['x' => 'Deploy',     'y' => [70, 80]],
        ]],
    ]
)->build();
```

---

## 🖼️ Componente Blade

### Props

| Prop | Tipo | Requerido | Descripción |
|------|------|-----------|-------------|
| `config` | `array` | ✅ | Array de opciones generado por `.build()` |
| `event` | `string\|null` | ❌ | Nombre del evento Livewire al hacer clic en un punto |

### Uso básico

```blade
<x-chart :config="$config" class="h-64" />
```

### Con altura personalizada

```blade
<x-chart :config="$config" class="h-96 w-full" />
```

### Con evento al hacer clic en un punto

```blade
<x-chart :config="$config" event="chartPointSelected" class="h-64" />
```

En tu componente Livewire escuchas el evento:

```php
#[On('chartPointSelected')]
public function onPointSelected(mixed $value, ?string $label): void
{
    // $value → valor del punto seleccionado
    // $label → etiqueta del punto (eje X o labels del pie/donut)
    $this->selected = $label . ': ' . $value;
}
```

---

## 🔄 Reactividad con Livewire

El componente reacciona automáticamente cuando cambias `$config` desde Livewire. Actualiza series, labels y categorías sin re-renderizar el DOM.

```php
public function filterByYear(int $year): void
{
    $data = $this->getDataByYear($year);

    $this->config = $this->lineChart(
        series: $data['series'],
        labels: $data['labels']
    )->build();

    // Alpine detecta el cambio y llama updateOptions() internamente
}
```

---

## 🌙 Dark mode

El componente detecta el tema al inicializarse y reacciona automáticamente cuando el usuario cambia el tema en caliente (compatible con el toggle de Flux).

No necesitas hacer nada adicional.

---

## 📱 Responsividad

Todos los charts incluyen **breakpoints globales automáticos**:

| Breakpoint | Comportamiento |
|------------|----------------|
| `≤ 1024px` | Toolbar oculta, leyenda abajo, fuente 11px, max 6 ticks en X |
| `≤ 640px`  | Alto fijo 280px, toolbar oculta, leyenda abajo, fuente 10px, max 4 ticks en X, stroke 1.5px |

Algunos presets tienen ajustes adicionales propios (donut, heatmap, rangeBar, etc.).

Para agregar tus propios breakpoints encima de los defaults:

```php
$this->areaChart($series, $labels)
    ->responsive([
        [
            'breakpoint' => 480,
            'options'    => [
                'chart'  => ['height' => 200],
                'legend' => ['show' => false],
            ],
        ],
    ])
    ->build();
```

Para reemplazar completamente los defaults (casos excepcionales):

```php
->responsiveOverride([
    ['breakpoint' => 768, 'options' => ['chart' => ['height' => 400]]],
])
```

---

## 💡 Ejemplos de uso avanzado

### Chart con múltiples metas y formatter

```php
$this->config = $this->columnChart(
    series: [['name' => 'Ventas mensuales', 'data' => [3200, 4100, 2800, 5000, 4600]]],
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May']
)
->formatter('currency')
->addGoals([
    ['value' => 3000, 'label' => 'Mínimo',  'color' => '#f59e0b'],
    ['value' => 4500, 'label' => 'Objetivo', 'color' => '#10b981'],
])
->colors('cool')
->toolbar(false)
->build();
```

---

### Dos charts sincronizados

```php
$this->configVentas = $this->lineChart($ventasSeries, $labels)
    ->group('dashboard-principal')
    ->height(200)
    ->toolbar(false)
    ->build();

$this->configCostos = $this->areaChart($costosSeries, $labels)
    ->group('dashboard-principal')   // ← mismo grupo
    ->height(200)
    ->toolbar(false)
    ->build();
```

```blade
<x-chart :config="$configVentas" class="h-52" />
<x-chart :config="$configCostos" class="h-52" />
```

> Al hacer hover sobre un punto en un chart, el otro se sincroniza automáticamente.

---

### Chart sin usar el trait (builder directo)

```php
use App\Services\Chart;

$this->config = Chart::make('radialBar')
    ->series([76, 67, 61, 90])
    ->set('labels', ['Ventas', 'Soporte', 'Desarrollo', 'Marketing'])
    ->set('plotOptions.radialBar.hollow.size', '30%')
    ->set('plotOptions.radialBar.dataLabels.total.show', true)
    ->set('plotOptions.radialBar.dataLabels.total.label', 'Total')
    ->colors('warm')
    ->height(380)
    ->build();
```

---

### Reutilizar una base para variantes

Gracias a la inmutabilidad del builder, puedes crear una base y derivar variantes sin que se afecten entre sí:

```php
$base = Chart::make('bar')
    ->series($series)
    ->labels($labels)
    ->toolbar(false);

// Cada variante es independiente
$this->configDark  = $base->colors('mono')->build();
$this->configWarm  = $base->colors('warm')->build();
$this->configGoals = $base->addGoal(500, 'Meta')->build();
```

---

## ⚠️ Errores comunes

### Tipo de chart inválido

```php
// ❌ Lanza InvalidArgumentException
Chart::make('gauge');

// ✅ Tipos válidos
// line, area, bar, pie, donut, radialBar,
// scatter, bubble, heatmap, candlestick, treemap, rangeBar
```

---

### Olvidar llamar `.build()`

```php
// ❌ $config es una instancia de Chart, no un array
$this->config = $this->barChart($series, $labels);

// ✅ Siempre cierra con .build()
$this->config = $this->barChart($series, $labels)->build();
```

---

### Esquema de color inválido

```php
// ❌ Lanza InvalidArgumentException
->colors('ocean');

// ✅ Esquemas válidos: 'default', 'warm', 'cool', 'mono'
->colors('cool');
```

---

### Pasar config sin los dos puntos en Blade

```blade
{{-- ❌ Pasa el string literal "$config" --}}
<x-chart config="$config" />

{{-- ✅ Los dos puntos evalúan la expresión PHP --}}
<x-chart :config="$config" />
```
