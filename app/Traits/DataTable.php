<?php
namespace App\Traits;

use Illuminate\Support\Str;
use Livewire\WithPagination;

trait DataTable
{
    use WithPagination;
    
    // ========== PROPIEDADES PÚBLICAS DEL TRAIT ==========
    public string $search = '';
    public string $sortBy = 'id';
    public string $sortDirection = 'desc';

    public int $per_page = 10;
    public array $filters = [];
    public array $selectedRows = [];
    
    // ========== CONSTANTES DE OPERADORES ==========
    protected array $arrayOperators = ['between', 'not between', 'in', 'not in'];
    protected array $numericOperators = ['=', '!=', '>', '<', '>=', '<='];
    protected array $stringOperators = ['like', 'not like'];
    protected array $nullOperators = ['null', 'not null'];

    protected array $queryString = [
        'search'        => ['except' => ''],
        'sortBy'        => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'per_page'      => ['except' => 10],
    ];
    
    // ========== INICIALIZACIÓN ==========
    public function bootDataTable(): void {
        $this->initializeFilters();
    }
    
    // ========== MÉTODOS PÚBLICOS PARA VISTAS ==========
    
    /**
     * Ordena por columna específica
     */
    public function sort(string $column)  {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        
        $this->resetPage();
    }
    
    /**
     * Agrega un nuevo filtro vacío
     */
    public function addFilter(): void {
        $this->filters[] = ['field' => '', 'operator' => '', 'value' => ''];
    }
    
    /**
     * Elimina un filtro específico
     */
    public function deleteFilter(int $index): void {
        if ($index === 0 && count($this->filters) === 1) {
            $this->filters[0] = ['field' => '', 'operator' => '', 'value' => ''];
        } else {
            unset($this->filters[$index]);
            $this->filters = array_values($this->filters);
        }
        
        $this->resetPage();
    }
    
    /**
     * Limpia todos los filtros
     */
    public function clearFilters(): void {
        $this->resetPage();
        $this->filters = [['field' => '', 'operator' => '', 'value' => '']];
    }
    
  
    // ========== MÉTODOS DE PROCESAMIENTO ==========
    
    /**
     * Procesa los filtros convirtiendo valores según el operador
     */
    protected function processFilters(): array {
        return collect($this->filters)
            ->filter(function ($filter) {
                return !empty($filter['field']) && !empty($filter['operator']);
            })
            ->map(function ($filter) {
                $operator = strtolower($filter['operator']);
                $value = $filter['value'] ?? '';
                
                if (in_array($operator, $this->arrayOperators) && is_string($value) && !empty($value)) {
                    $filter['value'] = $this->convertToArray($value);
                    
                    if (in_array($operator, ['between', 'not between'])) {
                        if (is_array($filter['value']) && count($filter['value']) !== 2) {
                            $filter['value'] = [];
                        }
                    }
                }
                
                if (in_array($operator, $this->nullOperators)) {
                    $filter['value'] = null;
                }
                
                if (in_array($operator, ['like', 'not like']) && is_string($value) && !empty($value)) {
                    if (!Str::contains($value, '%')) {
                        $filter['value'] = "%{$value}%";
                    }
                }
                
                return $filter;
            })
            ->values()
            ->toArray();
    }
    
    /**
     * Convierte string separado por comas en array
     */
    protected function convertToArray(string $value): array {
        return collect(explode(',', $value))
            ->map(fn($item) => trim($item))
            ->filter(fn($item) => $item !== '')
            ->map(function ($item) {
                // Convertir números
                if (is_numeric($item)) {
                    return str_contains($item, '.') ? (float) $item : (int) $item;
                }
                
                // Detectar fechas
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $item)) {
                    return $item;
                }
                
                // Detectar booleanos
                $lowerItem = strtolower($item);
                if ($lowerItem === 'true') return true;
                if ($lowerItem === 'false') return false;
                if ($lowerItem === 'null') return null;
                
                return $item;
            })
            ->toArray();
    }
    
    // ========== MÉTODOS DE VALIDACIÓN ==========
    
    
    // ========== MÉTODOS DE CONSULTA PARA VISTAS ==========
    
    /**
     * Obtiene campos disponibles para filtros
     */
    public function getAvailableFields(): array {
        return collect($this->headers)
            ->pluck('index')
            ->reject(fn($index) => in_array($index, ['actions', 'checkbox', 'selection']))
            ->values()
            ->toArray();
    }

    protected function getAllOperators(): array {
        return array_merge(
            $this->numericOperators,
            $this->stringOperators,
            $this->arrayOperators,
            $this->nullOperators
        );
    }
    
    /**
     * Obtiene operadores disponibles
     */
    
    public function getAvailableOperators(): array {
        return $this->getAllOperators();
    }


    
    /**
     * Obtiene operadores agrupados por tipo
     */
    public function getGroupedOperators(): array {
        return [
            'Comparación' => $this->numericOperators,
            'Texto' => $this->stringOperators,
            'Array' => $this->arrayOperators,
            'Nulos' => $this->nullOperators,
        ];
    }
    
    /**
     * Obtiene el número de filtros activos
     */
    public function getActiveFiltersCount(): int {
        return count(array_filter($this->filters, fn($f) => !empty($f['field'])));
    }
    
    // ========== MÉTODOS DE RESET ==========
    
    /**
     * Resetea la página cuando se actualiza la búsqueda
     */
    public function updatingSearch(): void {
        $this->resetPage();
    }
    
    /**
     * Resetea la página cuando se actualizan los filtros
     */
    public function updatingFilters(): void {
        $this->resetPage();
    }
    
    /**
     * Resetea la página cuando se cambia items por página
     */
    public function updatingPerPage(): void {
        $this->resetPage();
    }
    
    // ========== MÉTODOS PROTEGIDOS ==========
    
    /**
     * Inicializa el primer filtro vacío
     */
    protected function initializeFilters(): void {
        if (empty($this->filters)) {
            $this->filters = [['field' => '', 'operator' => '', 'value' => '']];
        }
    }

    public function selectedAllCurrentPage(array $currentPageIds): void {
        if (count($this->selectedRows) === count($currentPageIds) &&
            empty(array_diff($currentPageIds, $this->selectedRows))) {
            $this->selectedRows = [];
        } else {
            $this->selectedRows = $currentPageIds;
        }
    }
}