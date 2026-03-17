<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Searchable
{
    /**
     * Aplica filtro avanzado con headers dinámicos
     * 
     * USO: User::filterAdvance($headers, ['search' => 'texto', 'sort' => ['field' => 'id', 'direction' => 'asc']])
     */
    public function scopeFilterAdvance(Builder $query, array $headers, array $params = []): Builder {
        // 1. Preparar parámetros
        $search = $params['search'] ?? $params['q'] ?? '';
        $filters = $params['filters'] ?? [];
        $sort = $params['sort'] ?? [];
        
        // 2. Extraer campos buscables de headers
        $searchableFields = $this->extractSearchableFieldsFromHeaders($headers);
        
        // 3. Aplicar búsqueda si existe término
        if (!empty($search) && !empty($searchableFields)) {
            $query = $this->applyHeaderBasedSearch($query, $search, $searchableFields);
        }
        
        // 4. Aplicar filtros adicionales
        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }
        
        // 5. Aplicar ordenamiento
        if (!empty($sort)) {
            $field = $sort['field'] ?? $sort['column'] ?? null;
            $direction = $sort['direction'] ?? $sort['dir'] ?? 'asc';
            
            if ($field && in_array($field, $this->getIndexesFromHeaders($headers))) {
                $query = $this->applySorting($query, [
                    'field' => $field,
                    'direction' => $direction
                ]);
            }
        }
        
        // 6. Aplicar eager loading automático para relaciones usadas
        $eagerLoads = $this->getEagerLoadsFromHeaders($headers);
        if (!empty($eagerLoads)) {
            $query->with($eagerLoads);
        }

        // 7. Aplicar withCount automático para columnas _count
        $withCounts = $this->getWithCountFromHeaders($headers);
        if (!empty($withCounts)) {
            $query->withCount($withCounts);
        }
        
        return $query;
    }
    
    /**
     * Extrae campos buscables de los headers
     * Ignora: actions, checkbox, action, options
     */
    protected function extractSearchableFieldsFromHeaders(array $headers): array {
        $excludedIndexes = ['actions', 'checkbox', 'action', 'options', 'selection', 'active'];

        return collect($headers)
            ->filter(function ($header) use ($excludedIndexes) {
                if (!empty($header['exclude'])) return false;
                if (str_ends_with($header['index'] ?? '', '_count')) return false;
                return isset($header['index']) && !in_array($header['index'], $excludedIndexes);
            })
            ->pluck('index')
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getWithCountFromHeaders(array $headers): array {
        return collect($headers)
            ->pluck('index')
            ->filter(fn($index) => str_ends_with($index, '_count'))
            ->map(fn($index) => Str::beforeLast($index, '_count'))
            ->unique()
            ->values()
            ->toArray();
    }

    
    /**
     * Búsqueda basada en headers
     */
    protected function applyHeaderBasedSearch(Builder $query, string $search, array $searchableFields): Builder {
        $normalizedSearch = $this->normalizeTerm($search);
        
        return $query->where(function ($q) use ($searchableFields, $normalizedSearch) {
            foreach ($searchableFields as $field) {
                $this->applySmartFieldSearch($q, $field, $normalizedSearch);
            }
        });
    }
    
    /**
     * Aplica búsqueda inteligente según tipo de campo
     */
    protected function applySmartFieldSearch(Builder $query, string $field, string $term): void {
        // 1. Si es una relación (contiene punto)
        if (Str::contains($field, '.')) {
            $this->applyRelationSearch($query, $field, $term);
        }
        // 2. Si es un accesor (no existe en BD pero sí en modelo)
        elseif ($this->isAccessorField($field)) {
            $this->applyAccessorSearch($query, $field, $term);
        }
        // 3. Campo local normal
        else {
            $fieldWithAlias = $this->resolveFieldWithAlias($query, $field);
            $query->orWhere(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', "%{$term}%");
        }
    }
    
    /**
     * Búsqueda en relaciones (supporta anidamiento: 'relation.field' o 'relation.subrelation.field')
     */
    protected function applyRelationSearch(Builder $query, string $fieldPath, string $term): void {
        $parts = explode('.', $fieldPath);
        $column = array_pop($parts); // último elemento es la columna
        $relationPath = implode('.', $parts); // resto es la relación
        
        $query->orWhereHas($relationPath, function ($q) use ($column, $term) {
            $q->where(DB::raw("LOWER({$column})"), 'LIKE', "%{$term}%");
        });
    }
    
    /**
     * Búsqueda en accesores (ej: 'full_name' busca en 'first_name' y 'last_name')
     */
    protected function applyAccessorSearch(Builder $query, string $field, string $term): void {
        $accessorMap = $this->accessorMap ?? [];

        if (!isset($accessorMap[$field])) {
            return;
        }

        $fields = $accessorMap[$field];
        $table = $this->getTable();

        $query->orWhere(function ($q) use ($fields, $term, $table) {
            // Búsqueda individual por cada campo
            foreach ($fields as $realField) {
                if (Str::contains($realField, '.')) {
                    $this->applyRelationSearch($q, $realField, $term);
                } else {
                    $fieldWithAlias = $this->resolveFieldWithAlias($q, $realField);
                    $q->orWhere(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', "%{$term}%");
                }
            }

            // Búsqueda concatenada cuando todos los campos son locales (sin relaciones)
            $localFields = collect($fields)->filter(fn($f) => !Str::contains($f, '.'))->values();

            if ($localFields->count() >= 2) {
                $concatParts = $localFields
                    ->map(fn($f) => "{$table}.{$f}")
                    ->implode(", ' ', ");

                $q->orWhere(DB::raw("LOWER(CONCAT({$concatParts}))"), 'LIKE', "%{$term}%");
            }
        });
    }
    
    /**
     * Verifica si un campo es un accesor del modelo
     */
    protected function isAccessorField(string $field): bool {
        // Método 1: Verificar si existe método get{Field}Attribute
        $accessorMethod = 'get' . Str::studly($field) . 'Attribute';
        if (method_exists($this, $accessorMethod)) {
            return true;
        }
        
        // Método 2: Verificar si está en $appends
        return in_array($field, $this->appends ?? []);
    }
    
    /**
     * Obtiene relaciones para eager loading desde headers
     */
    protected function getEagerLoadsFromHeaders(array $headers): array {
        return collect($headers)
            ->pluck('index')
            ->filter(fn($index) => Str::contains($index, '.'))
            ->map(fn($field) => explode('.', $field)[0])
            ->unique()
            ->values()
            ->toArray();
    }
    
    /**
     * Obtiene todos los índices de los headers
     */
    protected function getIndexesFromHeaders(array $headers): array {
        return collect($headers)->pluck('index')->toArray();
    }
    
    /**
     * Normaliza término de búsqueda
     */
    protected function normalizeTerm($term) {
        if (is_array($term)) {
            return array_map(fn($t) => mb_strtolower(trim($t), 'UTF-8'), $term);
        }
        return is_string($term) ? mb_strtolower(trim($term), 'UTF-8') : $term;
    }
    
    /**
     * Resuelve alias de campo con tabla
     */
    protected function resolveFieldWithAlias(Builder $query, string $field): string {
        if (Str::contains($field, '.')) {
            return $field;
        }
        $table = $query->getModel()->getTable();
        return "{$table}.{$field}";
    }
    
    // ==================== MÉTODOS EXISTENTES DEL TRAIT ORIGINAL ====================
    
    /**
     * Apply advanced filtering to the query.
     */

    protected function applySorting(Builder $query, array $sort): Builder {
        $field     = $sort['field'] ?? null;
        $direction = strtolower($sort['direction'] ?? 'asc');

        if (!$field) return $query;

        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        // Si es un accesor, ordenar por el primer campo real del accessorMap
        if ($this->isAccessorField($field)) {
            $accessorMap = $this->accessorMap ?? [];

            if (isset($accessorMap[$field])) {
                $localFields = collect($accessorMap[$field])
                    ->filter(fn($f) => !Str::contains($f, '.'))
                    ->values();

                if ($localFields->isNotEmpty()) {
                    foreach ($localFields as $realField) {
                        $query->orderBy("{$this->getTable()}.{$realField}", $direction);
                    }
                    return $query;
                }
            }

            // Si es accesor pero no tiene mapa definido, ignorar el ordenamiento
            return $query;
        }

        return Str::contains($field, '.')
            ? $this->applyRelationSort($query, $field, $direction)
            : $query->orderBy($field, $direction);
    }
    
    protected function applyRelationSort(Builder $query, string $fieldPath, string $direction): Builder {
        $parts = explode('.', $fieldPath);
        $field = array_pop($parts);
        $model = $query->getModel();
        $baseTable = $model->getTable();
        $select = ["{$baseTable}.*"];
        $previousAlias = $baseTable;
        
        foreach ($parts as $index => $relationName) {
            $relation = $model->$relationName();
            
            if (!$relation) {
                throw new \RuntimeException("Relación $relationName no existe.");
            }
            
            $related = $relation->getRelated();
            $relatedTable = $related->getTable();
            $alias = "{$relatedTable}_rel_{$index}";
            
            if ($relation instanceof BelongsTo) {
                $query->leftJoin("{$relatedTable} as {$alias}", "{$previousAlias}.{$relation->getForeignKeyName()}", '=', "{$alias}.{$relation->getOwnerKeyName()}");
            } else {
                $query->leftJoin("{$relatedTable} as {$alias}", "{$alias}.{$relation->getForeignKeyName()}", '=', "{$previousAlias}.{$relation->getLocalKeyName()}");
            }
            
            $model = $related;
            $previousAlias = $alias;
        }
        
        return $query->select($select)->orderBy("{$previousAlias}.{$field}", $direction);
    }
    
    protected function applyFilters(Builder $query, array $filters): Builder {
        return $query->where(function ($q) use ($filters) {
            foreach ($filters as $filter) {
                $this->applySingleFilter($q, $filter);
            }
        });
    }
    
    protected function applySingleFilter(Builder $query, array $filter): void {
        $field = $filter['field'] ?? null;

        if (!$field) return;

        $operator = strtolower($filter['operator'] ?? '=');
        $value    = $filter['value'] ?? null;
        $boolean  = $filter['boolean'] ?? 'and';

        // Campos _count usan HAVING en lugar de WHERE
        if (str_ends_with($field, '_count')) {
            $method = $boolean === 'or' ? 'orHavingRaw' : 'havingRaw';
            $query->$method("{$field} {$operator} ?", [$value]);
            return;
        }

        // Campos accesores usan los campos reales del accessorMap
        if ($this->isAccessorField($field)) {
            $accessorMap = $this->accessorMap ?? [];

            if (isset($accessorMap[$field])) {
                $fields = $accessorMap[$field];
                $table  = $this->getTable();
                $method = $boolean === 'or' ? 'orWhere' : 'where';

                $query->$method(function ($q) use ($fields, $operator, $value, $table) {
                    foreach ($fields as $realField) {
                        if (Str::contains($realField, '.')) {
                            $this->applyRelationFilter($q, $realField, $operator, $value, 'or');
                        } else {
                            $this->applyStandardFilter($q, "{$table}.{$realField}", $operator, $value, 'or');
                        }
                    }

                    // Si el operador es like o = también buscar en la concatenación
                    if (in_array($operator, ['like', 'not like', '=']) ) {
                        $localFields = collect($fields)
                            ->filter(fn($f) => !Str::contains($f, '.'))
                            ->values();

                        if ($localFields->count() >= 2) {
                            $concatParts = $localFields
                                ->map(fn($f) => "{$table}.{$f}")
                                ->implode(", ' ', ");

                            $concatValue = $this->normalizeTerm($value);

                            $q->orWhere(
                                DB::raw("LOWER(CONCAT({$concatParts}))"),
                                $operator === '=' ? 'LIKE' : $operator,
                                $operator === '=' ? "%{$concatValue}%" : $concatValue
                            );
                        }
                    }
                });
            }
            return;
        }

        Str::contains($field, '.')
            ? $this->applyRelationFilter($query, $field, $operator, $value, $boolean)
            : $this->applyStandardFilter($query, $field, $operator, $value, $boolean);
    }
    
    protected function applyStandardFilter(Builder $query, string $field, string $operator, $value, string $boolean): void {
        $value = $this->normalizeTerm($value);
        $fieldWithAlias = $this->resolveFieldWithAlias($query, $field);

        match ($operator) {
            'null'        => $query->whereNull($field, $boolean),
            'not null'    => $query->whereNotNull($field, $boolean),
            'between'     => is_array($value) && count($value) === 2
                                ? $query->whereBetween($field, $value, $boolean)
                                : null,
            'not between' => is_array($value) && count($value) === 2
                                ? $query->whereNotBetween($field, $value, $boolean)
                                : null,
            'in'          => $query->whereIn($field, (array)$value, $boolean),
            'not in'      => $query->whereNotIn($field, (array)$value, $boolean),
            'like'        => $query->where(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', $value, $boolean),
            'not like'    => $query->where(DB::raw("LOWER({$fieldWithAlias})"), 'NOT LIKE', $value, $boolean),
            default       => ($this->isDateValue($value) || is_numeric($value))
                                ? $query->where($fieldWithAlias, $operator, $value, $boolean)
                                : $query->where(DB::raw("LOWER({$fieldWithAlias})"), $operator, $value, $boolean),
        };
    }

    protected function isDateValue(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/', $value);
    }
    
    protected function applyRelationFilter(Builder $query, string $fieldPath, string $operator, $value, string $boolean): void {
        $parts = explode('.', $fieldPath);
        $field = array_pop($parts);
        $relation = implode('.', $parts);

        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->$method($relation, function ($q) use ($field, $operator, $value) {
            $fieldWithAlias = $this->resolveFieldWithAlias($q, $field);
            $this->applyStandardFilter($q, $fieldWithAlias, $operator, $value, 'and');
        });
    }
    
    protected function validateFilterParams(array $params): array{
        $validated = [];
        
        if (!empty($params['search']['q']) || !empty($params['search']['fields'])) {
            $validated['search'] = [
                'q' => $params['search']['q'] ?? '',
                'fields' => array_filter($params['search']['fields'] ?? [])
            ];
        }
        
        if (!empty($params['filters']) && is_array($params['filters'])) {
            $validated['filters'] = array_values(array_filter(array_map(function ($filter) {
                $field = $filter['field'] ?? null;
                $operator = strtolower($filter['operator'] ?? '');
                $value = $filter['value'] ?? null;
                $boolean = $filter['boolean'] ?? 'and';
                
                if (!$field || !$operator) return null;
                
                $validOperators = [
                    '=', '!=', '>', '<', '>=', '<=',
                    'between', 'not between', 'in', 'not in',
                    'null', 'not null', 'like', 'not like'
                ];
                
                return in_array($operator, $validOperators)
                    ? compact('field', 'operator', 'value', 'boolean')
                    : null;
            }, $params['filters'])));
        }
        
        if (!empty($params['sort']) && is_array($params['sort'])) {
            $validated['sort'] = [
                'field' => $params['sort']['field'] ?? null,
                'direction' => $params['sort']['direction'] ?? 'asc',
                'field_first' => $params['sort']['field_first'] ?? 'id',
            ];
        }
        
        return $validated;
    }
}