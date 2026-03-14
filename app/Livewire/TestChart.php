<?php

namespace App\Livewire;

use App\Traits\Charts;
use Livewire\Component;

class TestChart extends Component
{
    use Charts;

    public function render() {

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
        ->build(); 

        $chart2 = $this->barChart(
            [
                ['name' => 'Usuarios', 'data' => [50, 70, 40, 90, 120]],
            ],
            ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo']
        )
        ->set('plotOptions.bar.borderRadius', 6)
        ->set('yaxis.show', false)
        ->build();

        $chart3 = $this->areaChart(
            [
                ['name' => 'Visitas', 'data' => [200, 300, 150, 400, 500]],
            ],
            ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo']
        )
        ->set('fill.opacity', 0.5)
        ->set('yaxis.show', false)
        ->build();

        $chart4 = $this->candlestickChart(
            [
                [
                    'name' => 'Precio',
                    'data' => [
                        ['x' => '2024-01-01', 'y' => [100, 110, 90, 105]],
                        ['x' => '2024-01-02', 'y' => [105, 115, 95, 110]],
                        ['x' => '2024-01-03', 'y' => [110, 120, 100, 115]],
                        ['x' => '2024-01-04', 'y' => [115, 125, 105, 120]],
                        ['x' => '2024-01-05', 'y' => [120, 130, 110, 125]],
                    ],
                ],
            ],
            [] // Las etiquetas se toman de los valores x
        )
        ->set('plotOptions.candlestick.colors.upward', '#10b981')
        ->set('plotOptions.candlestick.colors.downward', '#ef4444')
        ->set('plotOptions.candlestick.wick.useFillColor', true)
        ->build();

        return view('livewire.test-chart', compact('chart1', 'chart2', 'chart3', 'chart4'));
    }
}
