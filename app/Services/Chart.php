<?php

namespace App\Services;

class Chart
{
    private const VALID_TYPES = [
        'line', 'area', 'bar', 'pie', 'donut',
        'radialBar', 'scatter', 'bubble', 'heatmap',
        'candlestick', 'treemap', 'rangeBar',
    ];

    private const COLOR_SCHEMES = [
        'default' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        'warm'    => ['#f59e0b', '#ef4444', '#f97316', '#eab308', '#dc2626'],
        'cool'    => ['#3b82f6', '#06b6d4', '#8b5cf6', '#10b981', '#6366f1'],
        'mono'    => ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8'],
    ];

    protected array $options = [];
    protected ?string $formatter = null;

    public function __construct(string $type = 'line')
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new \InvalidArgumentException(
                "Tipo de chart inválido: '{$type}'. Válidos: " . implode(', ', self::VALID_TYPES)
            );
        }

        $this->options = [
            'chart' => [
                'type'       => $type,
                'height'     => '100%',
                'toolbar'    => ['show' => true],
                'fontFamily' => 'inherit',
                'animations' => [
                    'enabled'          => true,
                    'easing'           => 'easeinout',
                    'speed'            => 800,
                    'animateGradually' => ['enabled' => true, 'delay' => 150],
                    'dynamicAnimation' => ['enabled' => true, 'speed' => 350],
                ],
                'background' => 'transparent',
            ],
            'series'     => [],
            'colors'     => self::COLOR_SCHEMES['default'],
            'theme'      => ['mode' => 'dark'],
            'dataLabels' => ['enabled' => false],
            'stroke'     => ['width' => 2, 'curve' => 'smooth'],
            'grid'       => ['borderColor' => '#e2e8f0', 'strokeDashArray' => 4],
        ];
    }

    public static function make(string $type = 'line'): self {
        return new self($type);
    }

    public function series(array $series): self {
        $clone = clone $this;
        $clone->options['series'] = $series;
        return $clone;
    }

    public function labels(array $labels): self {
        $clone = clone $this;

        if (in_array($clone->options['chart']['type'], ['donut', 'pie', 'radialBar'])) {
            $clone->options['labels'] = $labels;
        } else {
            data_set($clone->options, 'xaxis.categories', $labels);
        }

        return $clone;
    }

    public function set(string $key, mixed $value): self {
        $clone = clone $this;
        data_set($clone->options, $key, $value);
        return $clone;
    }

    /**
     * Define un formatter JS para tooltip, dataLabels y eje Y.
     *
     * IMPORTANTE: Este valor se ejecuta con new Function() en el cliente.
     * Usar exclusivamente con expresiones controladas por el desarrollador,
     * nunca con input del usuario.
     *
     * Presets disponibles: 'currency', 'percent', 'compact'
     * O una expresión JS personalizada: 'val => "$ " + val.toLocaleString()'
     */
    public function formatter(string $jsExpressionOrPreset): self {
        $clone = clone $this;
        $clone->formatter = $jsExpressionOrPreset;
        return $clone;
    }

    public function legendFormatter(string $jsExpression): self {
        $clone = clone $this;
        $clone->options['__legend_formatter'] = $jsExpression;
        return $clone;
    }

    /**
     * Aplica un esquema de colores predefinido o un array de colores personalizados.
     *
     * Esquemas disponibles: 'default', 'warm', 'cool', 'mono'
     *
     * Ejemplos:
     *   ->colors('warm')
     *   ->colors(['#ff0000', '#00ff00', '#0000ff'])
     */
    public function colors(string|array $colors): self {
        if (is_string($colors)) {
            if (!array_key_exists($colors, self::COLOR_SCHEMES)) {
                throw new \InvalidArgumentException(
                    "Esquema de color inválido: '{$colors}'. Válidos: " . implode(', ', array_keys(self::COLOR_SCHEMES))
                );
            }
            $colors = self::COLOR_SCHEMES[$colors];
        }

        return $this->set('colors', $colors);
    }

    /**
     * Define el alto del chart.
     *
     * Ejemplos:
     *   ->height(350)
     *   ->height('100%')
     */
    public function height(int|string $value): self {
        return $this->set('chart.height', $value);
    }

    /**
     * Define el ancho del chart.
     *
     * Ejemplos:
     *   ->width(600)
     *   ->width('100%')
     */
    public function width(int|string $value): self {
        return $this->set('chart.width', $value);
    }

    /**
     * Muestra u oculta la toolbar del chart.
     */
    public function toolbar(bool $show = true): self {
        return $this->set('chart.toolbar.show', $show);
    }

    public function addGoal(int $value, string $label, string $color = '#ef4444'): self {
        $clone = clone $this;
        $clone->options['annotations']['yaxis'][] = [
            'y'           => $value,
            'borderColor' => $color,
            'label'       => [
                'text'  => $label,
                'style' => ['color' => '#fff', 'background' => $color],
            ],
        ];
        return $clone;
    }

    public function addGoals(array $goals): self {
        $clone = clone $this;
        foreach ($goals as $goal) {
            $clone->options['annotations']['yaxis'][] = [
                'y'           => $goal['value'],
                'borderColor' => $goal['color'] ?? '#ef4444',
                'label'       => [
                    'text'  => $goal['label'],
                    'style' => [
                        'color'      => '#fff',
                        'background' => $goal['color'] ?? '#ef4444',
                    ],
                ],
            ];
        }
        return $clone;
    }

    public function group(string $name): self {
        $clone = clone $this;
        $clone->options['chart']['group'] = $name;
        return $clone;
    }

    public function build(): array {
        $opts = $this->options;

        if ($this->formatter !== null) {
            $opts['__formatter'] = $this->formatter;
        }

        return $opts;
    }
}