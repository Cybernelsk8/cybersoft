<x-layouts::app.sidebar :title="$title ?? null">
     @php
        $currentRoute = Route::currentRouteName();
        $menu = Auth::user()->menu ?? [];
        $breadcrumbs = [];

        // 1. Buscamos la coincidencia en el menú dinámico
        foreach ($menu as $page) {
            // Verificar si la ruta actual es el padre directo
            if (isset($page['route']) && $page['route'] === $currentRoute) {
                $breadcrumbs[] = ['label' => $page['label'], 'route' => $page['route']];
                break;
            }

            // Verificar en los hijos
            if (!empty($page['childrens'])) {
                foreach ($page['childrens'] as $child) {
                    // Coincidencia exacta (ej: admin.users.index) o coincidencia de recurso (ej: admin.users.show pertenece a admin.users)
                    $isResourceChild = !empty($child['route']) && str_starts_with($currentRoute, str_replace('.index', '', $child['route']));

                    if ($child['route'] === $currentRoute || $isResourceChild) {
                        $breadcrumbs[] = ['label' => $page['label'], 'route' => null];
                        $breadcrumbs[] = ['label' => $child['label'], 'route' => $child['route']];
                        
                        // Si es una ruta de detalle/edición que no está en el menú, agregamos el "Detalle"
                        if ($currentRoute !== $child['route']) {
                            $labelExtra = str_contains($currentRoute, 'show') ? 'Ver' : (str_contains($currentRoute, 'edit') ? 'Editar' : 'Detalle');
                            $breadcrumbs[] = ['label' => $labelExtra, 'route' => null];
                        }
                        break 2;
                    }
                }
            }
        }
    @endphp
    <nav class="absolute left-14 lg:left-72 top-4 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <flux:breadcrumbs>
            {{-- Siempre mostramos Dashboard como inicio --}}
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />

            @foreach ($breadcrumbs as $item)
                <flux:breadcrumbs.item 
                    :href="$item['route'] && Route::has($item['route']) ? route($item['route']) : null"
                >
                    {{ $item['label'] }}
                </flux:breadcrumbs.item>
            @endforeach
        </flux:breadcrumbs>
    </nav>
    <br>
    <livewire:toast />
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
