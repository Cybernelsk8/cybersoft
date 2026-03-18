{{-- resources/views/livewire/toast-notification.blade.php --}}
<div 
    class="fixed z-[100] space-y-3 w-full max-w-sm sm:max-w-md {{ $position }}"
    x-data
>
    @foreach($toasts as $toast)
        <div 
            wire:key="{{ $toast['id'] }}"
            x-data="{
                show: @js($toast['show']),
                init() {
                    // Auto-remover después de la duración
                    @if($toast['duration'] > 0)
                        setTimeout(() => {
                            if (this.show) {
                                @this.dispatch('remove-toast', {id: '{{ $toast['id'] }}'});
                            }
                        }, {{ $toast['duration'] }});
                    @endif
                }
            }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full"
            class="w-full"
        >
            <flux:callout
                variant="secondary" 
                :dismissible="$toast['dismissible']"
                class="border border-{{ $toast['variant'] }}-500"
            >
                <x-slot name="icon">
                    @if($toast['icon'])
                        <flux:icon :name="$toast['icon']" class="size-5" :color="$toast['variant']" variant="solid" />
                    @endif
                </x-slot>
                <flux:callout.heading>
                    {{ $toast['title'] }}
                </flux:callout.heading>
    
                <flux:callout.text>
                    {{ $toast['message'] }}
                </flux:callout.text>

                @if($toast['duration'] > 0)
                    <x-slot name="footer">
                        <div class="mt-2 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-current opacity-25 transition-all duration-linear"
                                x-data="{
                                    init() {
                                        // Animar la barra de progreso
                                        this.$el.style.transitionDuration = '{{ $toast['duration'] }}ms';
                                        this.$el.style.width = '0%';
                                    }
                                }"
                                style="width: 100%"
                            ></div>
                        </div>
                    </x-slot>
                @endif
    
                @if($toast['dismissible'])
                    <x-slot name="controls">
                        <flux:button
                            wire:click="actuallyRemove('{{ $toast['id'] }}')"
                            variant="ghost" 
                            size="sm" 
                            icon="x-mark" 
                        />
                    </x-slot>
                @endif
            </flux:callout>
        </div>
    @endforeach
    
    <script>
        // Manejar el evento de remove-toast-delayed
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('remove-toast-delayed', (event) => {
                eval(event.js);
            });
        });
    </script>
</div>