<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Searchable
{
    /**
     * Aplica filtro avanzado con headers dinámicos
     *
     * USO: User::filterAdvance($headers, ['search' => 'texto', 'sort' => ['field' => 'id', 'direction' => 'asc']])
     */
    public function scopeFilterAdvance(Builder $query, array $headers, array $params = []): Builder
    {
        $search  = $params['search'] ?? $params['q'] ?? '';
        $filters = $params['filters'] ?? [];
        $sort    = $params['sort'] ?? [];

        $searchableFields = $this->extractSearchableFieldsFromHeaders($headers);

        if (!empty($search) && !empty($searchableFields)) {
            $query = $this->applyHeaderBasedSearch($query, $search, $searchableFields);
        }

        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        if (!empty($sort)) {
            $field     = $sort['field'] ?? $sort['column'] ?? null;
            $direction = $sort['direction'] ?? $sort['dir'] ?? 'asc';

            if ($field && in_array($field, $this->getIndexesFromHeaders($headers))) {
                $query = $this->applySorting($query, [
                    'field'     => $field,
                    'direction' => $direction,
                ]);
            }
        }

        $eagerLoads = $this->getEagerLoadsFromHeaders($headers);
        if (!empty($eagerLoads)) {
            $query->with($eagerLoads);
        }

        $withCounts = $this->getWithCountFromHeaders($headers);
        if (!empty($withCounts)) {
            $query->withCount($withCounts);
        }

        return $query;
    }

    /**
     * Extrae campos buscables de los headers
     * Ignora: actions, checkbox, action, options, selection, active y campos _count
     */
    protected function extractSearchableFieldsFromHeaders(array $headers): array
    {
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

    /**
     * Extrae relaciones para withCount desde headers con convención _count
     */
    protected function getWithCountFromHeaders(array $headers): array
    {
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
    protected function applyHeaderBasedSearch(Builder $query, string $search, array $searchableFields): Builder
    {
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
    protected function applySmartFieldSearch(Builder $query, string $field, string $term): void
    {
        if (Str::contains($field, '.')) {
            $this->applyRelationSearch($query, $field, $term);
        } elseif ($this->isAccessorField($field)) {
            $this->applyAccessorSearch($query, $field, $term);
        } else {
            $fieldWithAlias = $this->resolveFieldWithAlias($query, $field);
            $query->orWhere(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', "%{$term}%");
        }
    }

    /**
     * Búsqueda en relaciones — soporta anidamiento y accesores en modelos relacionados
     */
    protected function applyRelationSearch(Builder $query, string $fieldPath, string $term): void
    {
        $parts        = explode('.', $fieldPath);
        $column       = array_pop($parts);
        $relationPath = implode('.', $parts);

        $relatedModel   = $this->resolveRelatedModel($parts);
        $accessorMethod = 'get' . Str::studly($column) . 'Attribute';
        $isAccessor     = $relatedModel && (
                            method_exists($relatedModel, $accessorMethod) ||
                            in_array($column, $relatedModel->appends ?? [])
                          );

        $accessorMap  = ($relatedModel && method_exists($relatedModel, 'getAccessorMap'))
                        ? $relatedModel->getAccessorMap()
                        : [];

        $relatedTable = $relatedModel ? $relatedModel->getTable() : null;

        $query->orWhereHas($relationPath, function ($q) use ($column, $term, $isAccessor, $accessorMap, $relatedTable) {
            if ($isAccessor && isset($accessorMap[$column]) && $relatedTable) {
                $fields = $accessorMap[$column];

                $q->where(function ($subQ) use ($fields, $term, $relatedTable) {
                    foreach ($fields as $realField) {
                        if (!Str::contains($realField, '.')) {
                            $subQ->orWhere(DB::raw("LOWER({$relatedTable}.{$realField})"), 'LIKE', "%{$term}%");
                        }
                    }

                    $localFields = collect($fields)
                        ->filter(fn($f) => !Str::contains($f, '.'))
                        ->values();

                    if ($localFields->count() >= 2) {
                        $concatParts = $localFields
                            ->map(fn($f) => "{$relatedTable}.{$f}")
                            ->implode(", ' ', ");

                        $subQ->orWhere(DB::raw("LOWER(CONCAT({$concatParts}))"), 'LIKE', "%{$term}%");
                    }
                });
            } else {
                $q->where(DB::raw("LOWER({$relatedTable}.{$column})"), 'LIKE', "%{$term}%");
            }
        });
    }

    /**
     * Resuelve el modelo relacionado navegando la cadena de relaciones
     */
    protected function resolveRelatedModel(array $relationParts): ?object
    {
        try {
            $model = $this;

            foreach ($relationParts as $relationName) {
                $relation = $model->$relationName();
                $model    = $relation->getRelated();
            }

            return $model;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Búsqueda en accesores del modelo base
     */
    protected function applyAccessorSearch(Builder $query, string $field, string $term): void
    {
        $accessorMap = method_exists($this, 'getAccessorMap') ? $this->getAccessorMap() : [];

        if (!isset($accessorMap[$field])) {
            return;
        }

        $fields = $accessorMap[$field];
        $table  = $this->getTable();

        $query->orWhere(function ($q) use ($fields, $term, $table) {
            foreach ($fields as $realField) {
                if (Str::contains($realField, '.')) {
                    $this->applyRelationSearch($q, $realField, $term);
                } else {
                    $fieldWithAlias = $this->resolveFieldWithAlias($q, $realField);
                    $q->orWhere(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', "%{$term}%");
                }
            }

            $localFields = collect($fields)
                ->filter(fn($f) => !Str::contains($f, '.'))
                ->values();

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
    protected function isAccessorField(string $field): bool
    {
        $accessorMethod = 'get' . Str::studly($field) . 'Attribute';

        if (method_exists($this, $accessorMethod)) {
            return true;
        }

        return in_array($field, $this->appends ?? []);
    }

    /**
     * Obtiene relaciones para eager loading desde headers con notación de punto
     */
    protected function getEagerLoadsFromHeaders(array $headers): array
    {
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
    protected function getIndexesFromHeaders(array $headers): array
    {
        return collect($headers)->pluck('index')->toArray();
    }

    /**
     * Normaliza término de búsqueda a minúsculas sin espacios extra
     */
    protected function normalizeTerm($term)
    {
        if (is_array($term)) {
            return array_map(fn($t) => mb_strtolower(trim($t), 'UTF-8'), $term);
        }

        return is_string($term) ? mb_strtolower(trim($term), 'UTF-8') : $term;
    }

    /**
     * Resuelve el alias de tabla para un campo
     */
    protected function resolveFieldWithAlias(Builder $query, string $field): string
    {
        if (Str::contains($field, '.')) {
            return $field;
        }

        $table = $query->getModel()->getTable();

        return "{$table}.{$field}";
    }

    /**
     * Aplica ordenamiento — soporta campos locales, relaciones y accesores
     */
    protected function applySorting(Builder $query, array $sort): Builder
    {
        $field     = $sort['field'] ?? null;
        $direction = strtolower($sort['direction'] ?? 'asc');

        if (!$field) return $query;

        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        if ($this->isAccessorField($field)) {
            $accessorMap = method_exists($this, 'getAccessorMap') ? $this->getAccessorMap() : [];

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

            return $query;
        }

        return Str::contains($field, '.')
            ? $this->applyRelationSort($query, $field, $direction)
            : $query->orderBy($field, $direction);
    }

    /**
     * Aplica ordenamiento por campo de relación usando LEFT JOIN
     */
    protected function applyRelationSort(Builder $query, string $fieldPath, string $direction): Builder
    {
        $parts         = explode('.', $fieldPath);
        $field         = array_pop($parts);
        $model         = $query->getModel();
        $baseTable     = $model->getTable();
        $select        = ["{$baseTable}.*"];
        $previousAlias = $baseTable;

        foreach ($parts as $index => $relationName) {
            $relation = $model->$relationName();

            if (!$relation) {
                throw new \RuntimeException("Relación {$relationName} no existe.");
            }

            $related      = $relation->getRelated();
            $relatedTable = $related->getTable();
            $alias        = "{$relatedTable}_rel_{$index}";

            if ($relation instanceof BelongsTo) {
                $query->leftJoin(
                    "{$relatedTable} as {$alias}",
                    "{$previousAlias}.{$relation->getForeignKeyName()}",
                    '=',
                    "{$alias}.{$relation->getOwnerKeyName()}"
                );
            } else {
                $query->leftJoin(
                    "{$relatedTable} as {$alias}",
                    "{$alias}.{$relation->getForeignKeyName()}",
                    '=',
                    "{$previousAlias}.{$relation->getLocalKeyName()}"
                );
            }

            $model         = $related;
            $previousAlias = $alias;
        }

        return $query->select($select)->orderBy("{$previousAlias}.{$field}", $direction);
    }

    /**
     * Aplica todos los filtros avanzados
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->where(function ($q) use ($filters) {
            foreach ($filters as $filter) {
                $this->applySingleFilter($q, $filter);
            }
        });
    }

    /**
     * Aplica un filtro individual — detecta _count, accesores y relaciones
     */
    protected function applySingleFilter(Builder $query, array $filter): void
    {
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

        // Campos accesores del modelo base
        if ($this->isAccessorField($field)) {
            $accessorMap = method_exists($this, 'getAccessorMap') ? $this->getAccessorMap() : [];

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

                    if (in_array($operator, ['like', 'not like', '='])) {
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

    /**
     * Aplica un filtro estándar sobre un campo local
     */
    protected function applyStandardFilter(Builder $query, string $field, string $operator, $value, string $boolean): void
    {
        $value          = $this->normalizeTerm($value);
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
            'in'          => $query->whereIn($field, (array) $value, $boolean),
            'not in'      => $query->whereNotIn($field, (array) $value, $boolean),
            'like'        => $query->where(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', $value, $boolean),
            'not like'    => $query->where(DB::raw("LOWER({$fieldWithAlias})"), 'NOT LIKE', $value, $boolean),
            default       => ($this->isDateValue($value) || is_numeric($value))
                                ? $query->where($fieldWithAlias, $operator, $value, $boolean)
                                : $query->where(DB::raw("LOWER({$fieldWithAlias})"), $operator, $value, $boolean),
        };
    }

    /**
     * Detecta si un valor es una fecha en formato estándar
     */
    protected function isDateValue(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/', $value);
    }

    /**
     * Aplica un filtro sobre un campo de relación — soporta accesores en modelos relacionados
     */
    protected function applyRelationFilter(Builder $query, string $fieldPath, string $operator, $value, string $boolean): void
    {
        $parts    = explode('.', $fieldPath);
        $field    = array_pop($parts);
        $relation = implode('.', $parts);

        $relatedModel   = $this->resolveRelatedModel($parts);
        $accessorMethod = 'get' . Str::studly($field) . 'Attribute';
        $isAccessor     = $relatedModel && (
                            method_exists($relatedModel, $accessorMethod) ||
                            in_array($field, $relatedModel->appends ?? [])
                          );

        $accessorMap  = ($relatedModel && method_exists($relatedModel, 'getAccessorMap'))
                        ? $relatedModel->getAccessorMap()
                        : [];

        $relatedTable = $relatedModel ? $relatedModel->getTable() : null;

        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->$method($relation, function ($q) use ($field, $operator, $value, $isAccessor, $accessorMap, $relatedTable) {
            if ($isAccessor && isset($accessorMap[$field]) && $relatedTable) {
                $fields = $accessorMap[$field];

                $q->where(function ($subQ) use ($fields, $operator, $value, $relatedTable) {
                    foreach ($fields as $realField) {
                        if (!Str::contains($realField, '.')) {
                            $this->applyStandardFilter($subQ, "{$relatedTable}.{$realField}", $operator, $value, 'or');
                        }
                    }

                    if (in_array($operator, ['like', 'not like', '='])) {
                        $localFields = collect($fields)
                            ->filter(fn($f) => !Str::contains($f, '.'))
                            ->values();

                        if ($localFields->count() >= 2) {
                            $concatParts = $localFields
                                ->map(fn($f) => "{$relatedTable}.{$f}")
                                ->implode(", ' ', ");

                            $concatValue = $this->normalizeTerm($value);

                            $subQ->orWhere(
                                DB::raw("LOWER(CONCAT({$concatParts}))"),
                                $operator === '=' ? 'LIKE' : $operator,
                                $operator === '=' ? "%{$concatValue}%" : $concatValue
                            );
                        }
                    }
                });
            } else {
                $fieldWithAlias = $this->resolveFieldWithAlias($q, $field);
                $this->applyStandardFilter($q, $fieldWithAlias, $operator, $value, 'and');
            }
        });
    }

    /**
     * Valida y normaliza parámetros de filtro
     */
    protected function validateFilterParams(array $params): array
    {
        $validated = [];

        if (!empty($params['search']['q']) || !empty($params['search']['fields'])) {
            $validated['search'] = [
                'q'      => $params['search']['q'] ?? '',
                'fields' => array_filter($params['search']['fields'] ?? []),
            ];
        }

        if (!empty($params['filters']) && is_array($params['filters'])) {
            $validated['filters'] = array_values(array_filter(array_map(function ($filter) {
                $field    = $filter['field'] ?? null;
                $operator = strtolower($filter['operator'] ?? '');
                $value    = $filter['value'] ?? null;
                $boolean  = $filter['boolean'] ?? 'and';

                if (!$field || !$operator) return null;

                $validOperators = [
                    '=', '!=', '>', '<', '>=', '<=',
                    'between', 'not between', 'in', 'not in',
                    'null', 'not null', 'like', 'not like',
                ];

                return in_array($operator, $validOperators)
                    ? compact('field', 'operator', 'value', 'boolean')
                    : null;
            }, $params['filters'])));
        }

        if (!empty($params['sort']) && is_array($params['sort'])) {
            $validated['sort'] = [
                'field'       => $params['sort']['field'] ?? null,
                'direction'   => $params['sort']['direction'] ?? 'asc',
                'field_first' => $params['sort']['field_first'] ?? 'id',
            ];
        }

        return $validated;
    }
}