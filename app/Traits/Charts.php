<?php

namespace App\Traits;

use App\Services\Chart;

trait Charts
{
    /**
     * Mixed Chart: Permite series con type por elemento.
     * Ejemplo: ['name' => 'Ventas', 'type' => 'column', 'data' => [...]]
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo:
     *   $this->mixedChart($series, $labels, 'Reporte')
     *        ->set('stroke.width', [4, 0, 2])
     *        ->build();
     */
    public function mixedChart(array $series, array $labels, string $title = ''): Chart
    {
        return Chart::make('line')
            ->series($series)
            ->labels($labels)
            ->set('title.text', $title)
            ->set('stroke.width', [4, 0, 2]);
    }

    /**
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo:
     *   $this->barChart($series, $labels)
     *        ->set('plotOptions.bar.horizontal', true)
     *        ->build();
     */
    public function barChart(array $series, array $labels): Chart
    {
        return Chart::make('bar')
            ->series($series)
            ->labels($labels)
            ->set('plotOptions.bar.borderRadius', 6);
    }

    /**
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo:
     *   $this->columnChart($series, $labels)
     *        ->colors('warm')
     *        ->build();
     */
    public function columnChart(array $series, array $labels): Chart
    {
        return Chart::make('bar')
            ->series($series)
            ->labels($labels)
            ->set('plotOptions.bar.horizontal', false)
            ->set('plotOptions.bar.borderRadius', 3);
    }

    /**
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo:
     *   $this->lineChart($series, $labels)
     *        ->set('stroke.width', 3)
     *        ->build();
     */
    public function lineChart(array $series, array $labels): Chart
    {
        return Chart::make('line')
            ->series($series)
            ->labels($labels)
            ->set('stroke.curve', 'smooth');
    }

    /**
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo:
     *   $this->donutChart($series, $labels)
     *        ->set('plotOptions.pie.donut.size', '70%')
     *        ->build();
     */
    public function donutChart(array $series, array $labels): Chart
    {
        return Chart::make('donut')
            ->series($series)
            ->labels($labels);
    }

    public function pieChart(array $series, array $labels): Chart
    {
        return Chart::make('pie')
            ->series($series)
            ->labels($labels);
    }

    /**
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo:
     *   $this->areaChart($series, $labels)
     *        ->set('fill.type', 'gradient')
     *        ->build();
     */
    public function areaChart(array $series, array $labels): Chart
    {
        return Chart::make('area')
            ->series($series)
            ->labels($labels);
    }

    /**
     * Scatter Chart: Para visualizar correlación entre dos variables.
     * Cada punto requiere coordenadas [x, y].
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo de series:
     *   [['name' => 'Grupo A', 'data' => [[10, 20], [15, 35], [40, 60]]]]
     *
     * Ejemplo:
     *   $this->scatterChart($series)
     *        ->set('xaxis.tickAmount', 10)
     *        ->build();
     */
    public function scatterChart(array $series): Chart
    {
        return Chart::make('scatter')
            ->series($series)
            ->set('xaxis.tickAmount', 10)
            ->set('yaxis.tickAmount', 7);
    }

    /**
     * Bubble Chart: Como scatter pero con una tercera dimensión (tamaño de burbuja).
     * Cada punto requiere coordenadas [x, y, z] donde z define el tamaño.
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo de series:
     *   [['name' => 'Producto A', 'data' => [[10, 20, 5], [15, 35, 10], [40, 60, 3]]]]
     *
     * Ejemplo:
     *   $this->bubbleChart($series)
     *        ->set('plotOptions.bubble.minBubbleRadius', 4)
     *        ->build();
     */
    public function bubbleChart(array $series): Chart
    {
        return Chart::make('bubble')
            ->series($series)
            ->set('dataLabels.enabled', false)
            ->set('plotOptions.bubble.minBubbleRadius', 4)
            ->set('plotOptions.bubble.maxBubbleRadius', 40);
    }

    /**
     * Heatmap Chart: Para visualizar densidad o intensidad en una matriz.
     * Cada serie representa una fila, cada punto un valor de calor.
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo de series:
     *   [
     *     ['name' => 'Lunes',   'data' => [10, 20, 5, 30, 15]],
     *     ['name' => 'Martes',  'data' => [8,  25, 12, 18, 22]],
     *   ]
     *
     * Ejemplo:
     *   $this->heatmapChart($series, $labels)
     *        ->set('plotOptions.heatmap.colorScale.ranges', [...])
     *        ->build();
     */
    public function heatmapChart(array $series, array $labels): Chart
    {
        return Chart::make('heatmap')
            ->series($series)
            ->labels($labels)
            ->set('dataLabels.enabled', false)
            ->set('plotOptions.heatmap.shadeIntensity', 0.5)
            ->set('plotOptions.heatmap.radius', 0)
            ->set('plotOptions.heatmap.useFillColorAsStroke', true);
    }

    /**
     * Candlestick Chart: Para datos financieros OHLC (apertura, máximo, mínimo, cierre).
     * Cada punto requiere [x (timestamp o label), { o, h, l, c }].
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo de series:
     *   [['data' => [
     *     ['x' => 'Jan', 'y' => [154, 168, 149, 162]],
     *     ['x' => 'Feb', 'y' => [162, 172, 155, 169]],
     *   ]]]
     *
     * Ejemplo:
     *   $this->candlestickChart($series, $labels)
     *        ->set('plotOptions.candlestick.colors.upward', '#10b981')
     *        ->build();
     */
    public function candlestickChart(array $series, array $labels): Chart
    {
        return Chart::make('candlestick')
            ->series($series)
            ->labels($labels)
            ->set('plotOptions.candlestick.colors.upward', '#10b981')
            ->set('plotOptions.candlestick.colors.downward', '#ef4444')
            ->set('plotOptions.candlestick.wick.useFillColor', true);
    }

    /**
     * Treemap Chart: Para visualizar datos jerárquicos por tamaño proporcional.
     * Útil para mostrar distribución de categorías o tamaños relativos.
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo de series:
     *   [['data' => [
     *     ['x' => 'Ventas',    'y' => 218],
     *     ['x' => 'Marketing', 'y' => 149],
     *     ['x' => 'Soporte',   'y' => 184],
     *   ]]]
     *
     * Ejemplo:
     *   $this->treemapChart($series)
     *        ->set('plotOptions.treemap.enableShades', false)
     *        ->build();
     */
    public function treemapChart(array $series): Chart
    {
        return Chart::make('treemap')
            ->series($series)
            ->set('legend.show', false)
            ->set('plotOptions.treemap.enableShades', true)
            ->set('plotOptions.treemap.shadeIntensity', 0.5)
            ->set('plotOptions.treemap.distributed', true);
    }

    /**
     * RangeBar Chart: Para visualizar rangos entre dos valores (tipo Gantt o franjas).
     * Cada punto requiere [x (label), { y: [inicio, fin] }].
     *
     * @return Chart Builder preconfigurado. Llama ->build() para obtener el array de opciones.
     *
     * Ejemplo de series:
     *   [['name' => 'Fase 1', 'data' => [
     *     ['x' => 'Diseño',      'y' => [0, 30]],
     *     ['x' => 'Desarrollo',  'y' => [25, 70]],
     *     ['x' => 'QA',          'y' => [65, 90]],
     *   ]]]
     *
     * Ejemplo:
     *   $this->rangeBarChart($series)
     *        ->set('plotOptions.bar.horizontal', false)
     *        ->build();
     */
    public function rangeBarChart(array $series): Chart
    {
        return Chart::make('rangeBar')
            ->series($series)
            ->set('plotOptions.bar.horizontal', true)
            ->set('plotOptions.bar.borderRadius', 4)
            ->set('plotOptions.bar.isDumbbell', false)
            ->set('dataLabels.enabled', false);
    }
}