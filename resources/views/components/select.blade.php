{{-- resources/views/components/select.blade.php --}}
@props([
    'label'                => null,
    'placeholder'          => 'Selecciona...',
    'searchable'           => false,
    'multiple'             => false,
    'size'                 => 'default',
    'invalid'              => null,
    'options'              => [],
    'optionValue'          => 'id',
    'optionLabel'          => 'nombre',
    'optionLabelSeparator' => ' ',
])

@php
    $name    = $attributes->whereStartsWith('wire:model')->first();
    $invalid ??= ($name && $errors->has($name));

    $normalized = collect($options)->map(function ($option) use ($optionValue, $optionLabel, $optionLabelSeparator) {
        if (is_array($option) && array_key_exists('value', $option) && array_key_exists('label', $option)) {
            return ['value' => $option['value'], 'label' => (string) $option['label']];
        }

        $item = is_object($option) ? $option : (object) $option;

        $valueFields = array_map('trim', explode(',', $optionValue));
        $value = count($valueFields) === 1
            ? data_get($item, $valueFields[0])
            : implode($optionLabelSeparator, array_map(fn($f) => data_get($item, $f), $valueFields));

        $labelFields = array_map('trim', explode(',', $optionLabel));
        $label = implode($optionLabelSeparator, array_filter(
            array_map(fn($f) => data_get($item, $f), $labelFields)
        ));

        return ['value' => $value, 'label' => $label];
    })->values()->toArray();

    $classes = Flux::classes()
        ->add('relative flex items-center group w-full transition-all cursor-default')
        ->add(match($size) {
            'sm'    => 'h-8 py-1 px-2 text-sm rounded-md',
            'xs'    => 'h-6 py-0.5 px-2 text-xs rounded-md',
            default => 'h-10 py-1.5 px-3 text-base sm:text-sm rounded-lg',
        })
        ->add('bg-white dark:bg-zinc-700 border shadow-xs')
        ->add($invalid
            ? 'border-red-500 ring-1 ring-red-500/20'
            : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
        )
        ->add('focus-within:ring-2 focus-within:ring-zinc-200 focus-within:border-zinc-500 dark:focus-within:border-white/10');

    $labelAttributes = Flux::attributesAfter('label:', $attributes);
@endphp

<div class="grid gap-2.5">
    @isset($label)
        <flux:label :attributes="$labelAttributes">{{ $label }}</flux:label>
    @endisset

    <div
        x-data="{
            open: false,
            search: '',
            selected: @entangle($attributes->wire('model')),
            multiple: @js($multiple),
            allOptions: @js($normalized),
            focusedIndex: -1,

            get filteredOptions() {
                if (this.search === '') return this.allOptions;
                const q = this.search.toLowerCase();
                return this.allOptions.filter(o => o.label.toLowerCase().includes(q));
            },

            get displayLabel() {
                if (this.multiple) return null;
                return this.allOptions.find(o => String(o.value) === String(this.selected))?.label ?? null;
            },

            get selectedItems() {
                if (!this.multiple) return [];
                const sel = Array.isArray(this.selected) ? this.selected : [];
                return sel.map(v => ({
                    value: v,
                    label: this.allOptions.find(o => String(o.value) === String(v))?.label ?? String(v)
                }));
            },

            get summaryLabel() {
                const count = Array.isArray(this.selected) ? this.selected.length : 0;
                if (count === 0) return null;
                if (count === 1) return this.allOptions.find(o => String(o.value) === String(this.selected[0]))?.label ?? '1 seleccionado';
                return count + ' seleccionados';
            },

            toggle(value) {
                if (this.multiple) {
                    const sel = Array.isArray(this.selected) ? [...this.selected] : [];
                    const idx = sel.findIndex(v => String(v) === String(value));
                    this.selected = idx >= 0 ? sel.filter((_, i) => i !== idx) : [...sel, value];
                } else {
                    this.selected = value;
                    this.open = false;
                    this.search = '';
                    this.focusedIndex = -1;
                }
            },

            isSelected(value) {
                if (this.multiple) {
                    return Array.isArray(this.selected) && this.selected.some(v => String(v) === String(value));
                }
                return String(this.selected) === String(value);
            },

            scrollToFocused() {
                this.$nextTick(() => {
                    const list = this.$refs.optionsList;
                    if (!list) return;
                    const item = list.children[this.focusedIndex];
                    if (item) item.scrollIntoView({ block: 'nearest' });
                });
            },

            handleKeydown(e) {
                e.stopPropagation();

                if (!this.open) {
                    if (['Enter', 'ArrowDown', ' '].includes(e.key)) {
                        e.preventDefault();
                        this.open = true;
                        this.focusedIndex = 0;
                        this.scrollToFocused();
                    }
                    return;
                }

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        this.focusedIndex = Math.min(this.focusedIndex + 1, this.filteredOptions.length - 1);
                        this.scrollToFocused();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        this.focusedIndex = Math.max(this.focusedIndex - 1, 0);
                        this.scrollToFocused();
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (this.focusedIndex >= 0 && this.filteredOptions[this.focusedIndex]) {
                            this.toggle(this.filteredOptions[this.focusedIndex].value);
                        }
                        break;
                    case 'Escape':
                        this.open = false;
                        this.search = '';
                        this.focusedIndex = -1;
                        this.$refs.searchInput?.blur();
                        break;
                    case 'Tab':
                        this.open = false;
                        this.search = '';
                        break;
                }
            }
        }"
        @click.away="open = false; search = ''; focusedIndex = -1"
        @keydown="handleKeydown($event)"
        tabindex="0"
        role="combobox"
        :aria-expanded="open"
        aria-haspopup="listbox"
        @if($invalid) aria-invalid="true" data-invalid @endif
        {{ $attributes->except(['wire:model'])->class($classes) }}
        data-flux-control
    >
        {{-- Trigger --}}
        <div @click="open = !open" class="flex justify-between w-full items-center min-w-0 gap-2">

            {{-- Single --}}
            <template x-if="!multiple">
                <span
                    class="truncate"
                    :class="displayLabel ? 'text-zinc-800 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500'"
                    x-text="displayLabel ?? '{{ $placeholder }}'"
                ></span>
            </template>

            {{-- Multiple vacío --}}
            <template x-if="multiple && selectedItems.length === 0">
                <span class="text-zinc-400 dark:text-zinc-500 truncate">{{ $placeholder }}</span>
            </template>

            {{-- Multiple con selección --}}
            <template x-if="multiple && selectedItems.length > 0">
                <div class="flex items-center gap-2 min-w-0 flex-1">

                    {{-- Móvil: solo contador compacto --}}
                    <span class="sm:hidden inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-white/10 text-xs text-zinc-600 dark:text-zinc-200 border border-zinc-200 dark:border-white/5 font-medium">
                        <span x-text="summaryLabel"></span>
                        <span
                            @click.stop="selected = []; focusedIndex = -1"
                            class="cursor-pointer hover:text-red-500 transition-colors leading-none"
                        >
                            <flux:icon.x-mark variant="micro" class="size-3" />
                        </span>
                    </span>

                    {{-- Desktop: tags individuales --}}
                    <div class="hidden sm:flex flex-wrap gap-1 min-w-0">
                        <template x-for="item in selectedItems" :key="item.value">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-white/10 text-xs text-zinc-600 dark:text-zinc-200 border border-zinc-200 dark:border-white/5 max-w-[160px]">
                                <span class="truncate" x-text="item.label"></span>
                                <flux:icon.x-mark
                                    @click.stop="toggle(item.value)"
                                    variant="micro"
                                    class="size-3 shrink-0 cursor-pointer hover:text-red-500 transition-colors"
                                />
                            </span>
                        </template>
                    </div>

                </div>
            </template>

            <flux:icon.chevron-up-down
                class="size-5 text-zinc-400 shrink-0 transition-transform duration-200"
                :class="open ? 'rotate-180 text-zinc-600 dark:text-zinc-200' : ''"
            />
        </div>

        {{-- Dropdown --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            role="listbox"
            :aria-multiselectable="multiple"
            class="absolute left-0 top-full mt-1.5 w-full z-[100] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-xl overflow-hidden p-1" >
            
            {{-- Resumen de seleccionados en móvil (dentro del dropdown) --}}
            <template x-if="multiple && selectedItems.length > 0">
                <div class="sm:hidden px-1 pt-1 pb-1.5 border-b border-zinc-100 dark:border-white/5 mb-1">
                    <div class="flex flex-wrap gap-1">
                        <template x-for="item in selectedItems" :key="item.value">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-white/10 text-xs text-zinc-600 dark:text-zinc-200 border border-zinc-200 dark:border-white/5">
                                <span class="truncate max-w-[140px]" x-text="item.label"></span>
                                <flux:icon.x-mark
                                    @click.stop="toggle(item.value)"
                                    variant="micro"
                                    class="size-3 shrink-0 cursor-pointer hover:text-red-500 transition-colors"
                                />
                            </span>
                        </template>
                    </div>
                </div>
            </template>

            @if($searchable)
                <div class="px-1 pt-1 pb-1.5">
                    <div class="relative">
                        <flux:icon.magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 size-3.5 text-zinc-400 pointer-events-none" />
                        <input
                            x-model.debounce.120ms="search"
                            x-ref="searchInput"
                            x-effect="if(open) $nextTick(() => $refs.searchInput?.focus())"
                            type="text"
                            placeholder="Buscar..."
                            class="w-full pl-8 pr-3 py-1.5 bg-zinc-100 dark:bg-zinc-700 border border-transparent text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-zinc-300 dark:focus:ring-white/10 dark:text-white dark:placeholder-zinc-500 transition"
                            @click.stop
                            @keydown="handleKeydown($event)"
                        >
                    </div>
                </div>
            @endif

            <div class="max-h-60 overflow-y-auto" role="listbox" x-ref="optionsList">
                <template x-for="(option, index) in filteredOptions" :key="option.value">
                    <div
                        @click="toggle(option.value)"
                        :class="{
                            'bg-zinc-100 dark:bg-zinc-600 text-zinc-900 dark:text-white font-medium': isSelected(option.value),
                            'ring-1 ring-inset ring-zinc-300 dark:ring-white/20 bg-zinc-50 dark:bg-zinc-700/60': focusedIndex === index && !isSelected(option.value),
                        }"
                        class="flex items-center justify-between px-3 py-2 text-sm rounded-lg cursor-pointer select-none
                               text-zinc-700 dark:text-zinc-300
                               hover:bg-zinc-100 dark:hover:bg-zinc-700
                               hover:text-zinc-900 dark:hover:text-white
                               transition-colors"
                        role="option"
                        :aria-selected="isSelected(option.value)"
                    >
                        <span class="flex-1 truncate" x-text="option.label"></span>
                        <template x-if="isSelected(option.value)">
                            <flux:icon.check variant="micro" class="size-4 shrink-0 ml-2 text-zinc-500 dark:text-zinc-300" />
                        </template>
                    </div>
                </template>

                {{-- Sin resultados --}}
                <template x-if="filteredOptions.length === 0">
                    <div class="px-3 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
                        <template x-if="search !== ''">
                            <span>Sin resultados para "<span x-text="search" class="font-medium"></span>"</span>
                        </template>
                        <template x-if="search === ''">
                            <span>Sin opciones disponibles</span>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @if($invalid && $name)
        <flux:error name="{{ $name }}" />
    @endif
</div>