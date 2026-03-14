<div class="p-4">
    <x-select 
        wire:model.live="seleccion"
        :options="$this->searchResults"
        option-value="id"
        option-label="name"
        label="Selecciona una opción"
        placeholder="Selecciona una opción"
        searchable
        multiple>
    </x-select>

    

    <x-data-table :headers="$this->headers" :rows="$this->rows" selectable >

        <x-slot:mass-actions>
            <flux:menu.item
                wire:click="deleteSelected" 
                variant="danger" 
                icon="trash">
                Eliminar seleccionados
            </flux:menu.item>
        </x-slot:mass-actions>

        @interact('actions',$row)
            {{-- @if($loop->first) --}}
            <flux:avatar :name="$row->name" />
            {{-- @else
            {{ $row->name }}
            @endif --}}
        @endinteract

    </x-data-table>


    

</div>
