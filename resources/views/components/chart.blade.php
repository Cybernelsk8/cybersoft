@props(['config', 'event' => null])

<div
    x-data="{
        chart: null,
        config: @js($config),

        init() {
            let options = JSON.parse(JSON.stringify(this.config));
            this.setupFunctions(options);

            this.chart = new ApexCharts(this.$refs.canvas, options);

            // Espera a que el render termine antes de aplicar el tema,
            // de lo contrario updateOptions llega antes de que ApexCharts
            // haya inicializado plotOptions.bar.dataLabels.total
            this.chart.render().then(() => {
                this.applyTheme(document.documentElement.classList.contains('dark'));
            });

            // Reactivo al cambio de tema en caliente (toggle Flux / cualquier clase en <html>)
            const observer = new MutationObserver(() => {
                if (this.chart) this.applyTheme(document.documentElement.classList.contains('dark'));
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });

            // Reactividad Livewire 4: actualiza series, labels y categorías
            this.$watch('config', (val) => {
                if (!val || !this.chart) return;
                this.chart.updateOptions({
                    series: val.series ?? [],
                    labels: val.labels ?? [],
                    xaxis: { categories: val.xaxis?.categories ?? [] },
                }, false, true);
            });
        },

        applyTheme(dark) {
            // Siempre parchea el total — ApexCharts lo ignora si no aplica al tipo de chart
            this.chart.updateOptions({
                theme: { mode: dark ? 'dark' : 'light' },
                plotOptions: {
                    bar: {
                        dataLabels: {
                            total: {
                                style: {
                                    color: dark ? '#ffffff' : '#1e293b',
                                },
                            },
                        },
                    },
                },
            });
        },

        setupFunctions(options) {
            if (options.__formatter) {
                const preset = this.resolveFormatter(options.__formatter);
                this.dotSet(options, 'tooltip.y.formatter', preset);
                this.dotSet(options, 'dataLabels.formatter', preset);
                this.dotSet(options, 'yaxis.labels.formatter', preset);
                delete options.__formatter;
            }

            if (options.__legend_formatter) {
                const fn = new Function('seriesName', 'opts', 'return (' + options.__legend_formatter + ')(seriesName, opts)');
                this.dotSet(options, 'legend.formatter', fn);
                delete options.__legend_formatter;
            }

            if (@js($event)) {
                this.dotSet(options, 'chart.events.dataPointSelection', (e, c, config) => {
                    $wire.dispatch(@js($event), {
                        value: config.w.config.series[config.seriesIndex]?.data?.[config.dataPointIndex]
                            ?? config.w.config.series[config.dataPointIndex],
                        label: config.w.config.labels?.[config.dataPointIndex] ?? null,
                    });
                });
            }
        },

        /**
         * Resuelve presets seguros o acepta una expresión JS personalizada.
         * Los presets evitan el uso de new Function() con strings arbitrarios.
         *
         * ADVERTENCIA: las expresiones JS personalizadas se evalúan con new Function().
         * Usar solo con valores controlados por el desarrollador, nunca con input del usuario.
         */
        resolveFormatter(value) {
            const presets = {
                currency: (val) => '$ ' + Number(val).toLocaleString('es-GT', { minimumFractionDigits: 2 }),
                percent:  (val) => Number(val).toFixed(1) + ' %',
                compact:  (val) => {
                    if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M';
                    if (val >= 1_000)     return (val / 1_000).toFixed(1) + 'K';
                    return val;
                },
            };

            if (presets[value]) return presets[value];

            // Expresión JS personalizada — solo para uso del desarrollador
            return new Function('val', 'return (' + value + ')(val)');
        },

        dotSet(obj, path, val) {
            path.split('.').reduce((acc, key, i, arr) =>
                acc[key] = (i === arr.length - 1) ? val : (acc[key] || {}), obj);
        },
    }"
    wire:ignore
    {{ $attributes->class(['relative w-full h-full rounded-xl']) }}
>
    <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-xs rounded-xl">
        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-ref="canvas"></div>
</div>