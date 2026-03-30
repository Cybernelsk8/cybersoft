<section class="w-full">
    
    <div class="flex justify-center mb-4">
        <flux:modal.trigger name="newPage">
            <flux:button 
                icon="plus" 
                title="Crear nueva página" 
                variant="primary" >
                Crear nueva página
            </flux:button>
        </flux:modal.trigger>
    </div>
    
    <x-data-table :headers="$this->headers" :rows="$this->rows">
        @interact('view', $row)
            @if($row->icon)
                <flux:icon :name="$row->icon" class="size-6"/>
            @endif
        @endinteract
        
        @interact('state',$row)
            <flux:icon 
                :name="$row->state ? 'check-circle' : 'x-circle'"
                class="size-5"
                :class="$row->state ? 'text-green-500' : 'text-red-500'" />
        @endinteract

        @interact('actions', $row)
            <flux:dropdown >
                <flux:button 
                    size="sm" 
                    icon="ellipsis-vertical" 
                    class="cursor-pointer" 
                    variant="ghost" 
                />
                <flux:menu>
                    @can('pages.edit')
                        <flux:menu.item 
                            icon="pencil-square" 
                            wire:click="edit({{ $row->id }})" >
                            Editar
                        </flux:menu.item>
                    @endcan
                    
                    @can('pages.disabled')
                        @if ($row->state)
                            <flux:menu.item 
                                variant="danger" 
                                icon="x-circle"
                                wire:click="disableItem({{ $row->id }})" >
                                Desactivar
                            </flux:menu.item>
                        @endif
                    @endcan
                    
                    @can('pages.delete')
                        <flux:menu.item 
                            icon="trash"
                            variant="danger"
                            wire:click="deleteItem({{ $row->id }})" >
                            Eliminar
                        </flux:menu.item>
                    @endcan
                </flux:menu>
            </flux:dropdown>
        @endinteract
    </x-data-table>
    
    <flux:modal name="newPage" flyout @close="resetData">
        <form wire:submit.prevent="store" class="space-y-6">
            <flux:heading size="lg">Nueva página</flux:heading>
            <div class="grid xl:grid-cols-2 gap-4">
                <flux:input 
                    label="Nombre *"
                    wire:model="page.label"
                    icon="pencil-square" 
                    placeholder="Nombre de la página"
                    autofocus
                    required
                />

                <flux:input 
                    label="Icono"
                    icon="rocket-launch"
                    wire:model="page.icon" 
                    placeholder="Icono de la página"
                />                

                <flux:input 
                    label="Ruta"
                    wire:model="page.route"
                    icon="link" 
                    placeholder="Nombre de la ruta"
                />

                <flux:input 
                    label="Orden"
                    wire:model="page.order" 
                    type="number"
                    icon="bars-arrow-up"
                    placeholder="Orden de la página"
                />

                <flux:select 
                    label="Tipo de página *" 
                    wire:model.live="page.type" >

                    <flux:select.option 
                        value="">
                        -- Seleccione tipo --
                    </flux:select.option>
                    <flux:select.option 
                        value="header">
                        Cabecera
                    </flux:select.option>
                    <flux:select.option 
                        value="parent">
                        Padre
                    </flux:select.option>
                    <flux:select.option 
                        value="page">
                        Página
                    </flux:select.option>
                </flux:select>

                @if(isset($this->page['type']) && $this->page['type'] == 'page')
                    <flux:select 
                        label="Padre" 
                        wire:model="page.page_id" >

                        <flux:select.option 
                            value="">
                            -- Seleccione padre --
                        </flux:select.option>
                        @forelse ($pages as $page)
                            <flux:select.option 
                                value="{{ $page->id }}">
                                {{ $page->id." - ".$page->label }}
                            </flux:select.option>
                            
                        @empty
                            <flux:select.option>
                                No hay datos
                            </flux:select.option>
                        @endforelse
                    </flux:select>
                @endif
                
                <div class="col-span-2">
                    <flux:select 
                        label="Permiso *" 
                        wire:model="page.permission_name" >
    
                        <flux:select.option 
                            value="">
                            -- Seleccione permiso --
                        </flux:select.option>
                        @forelse ($permissions as $permission)
                            <flux:select.option 
                                value="{{ $permission->name }}">
                                {{ $permission->name }}
                            </flux:select.option>
                            
                        @empty
                            <flux:select.option>
                                No hay datos
                            </flux:select.option>
                        @endforelse
                    </flux:select>
                </div>

            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button icon="x-mark" variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button type="submit" icon="arrow-up-on-square-stack" variant="danger">Crear</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="editPage" flyout @close="resetData">
        <form wire:submit.prevent="update" class="space-y-6">
            <flux:heading size="lg">Editar página</flux:heading>

            @if(isset($this->page['state']) && !$this->page['state'])
                <flux:switch 
                    wire:model="page.state"
                    label="Habilitar página"
                    align="left" 
                />
            @endif

            <div class="grid xl:grid-cols-2 gap-4">
                <flux:input
                    label="Nombre *"
                    icon="pencil-square"
                    wire:model="page.label" 
                    placeholder="Nombre de la página"
                    required
                />

                <flux:input 
                    label="Icono"
                    icon="rocket-launch"
                    wire:model="page.icon" 
                    placeholder="Icono de la página"
                />
                    
                <flux:input 
                    label="Ruta"
                    wire:model="page.route"
                    icon="link" 
                    placeholder="Nombre de la ruta"
                />

                <flux:input 
                    label="Orden"
                    icon="bars-arrow-up"
                    wire:model="page.order" 
                    type="number"
                    placeholder="Orden de la página"
                />

                <flux:select 
                    label="Tipo de página *" 
                    wire:model.live="page.type" >

                    <flux:select.option 
                        value="">
                        -- Seleccione tipo --
                    </flux:select.option>
                    <flux:select.option 
                        value="header">
                        Cabecera
                    </flux:select.option>
                    <flux:select.option 
                        value="parent">
                        Padre
                    </flux:select.option>
                    <flux:select.option 
                        value="page">
                        Página
                    </flux:select.option>
                </flux:select>

               @if(isset($this->page['type']) && $this->page['type'] == 'page')
                    <flux:select 
                        label="Padre" 
                        wire:model="page.page_id" >

                        <flux:select.option 
                            value="">
                            -- Seleccione padre --
                        </flux:select.option>
                        @forelse ($pages as $page)
                            <flux:select.option 
                                value="{{ $page->id }}">
                                {{ $page->id." - ".$page->label }}
                            </flux:select.option>
                            
                        @empty
                            <flux:select.option>
                                No hay datos
                            </flux:select.option>
                        @endforelse
                    </flux:select>
                @endif
                
                <div class="col-span-2">
                    <flux:select 
                        label="Permiso *" 
                        wire:model="page.permission_name" >
    
                        <flux:select.option 
                            value="">
                            -- Seleccione permiso --
                        </flux:select.option>
                        @forelse ($permissions as $permission)
                            <flux:select.option 
                                value="{{ $permission->name }}">
                                {{ $permission->name }}
                            </flux:select.option>
                            
                        @empty
                            <flux:select.option>
                                No hay datos
                            </flux:select.option>
                        @endforelse
                    </flux:select>
                </div>

            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button icon="x-mark" variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button type="submit" icon="arrow-path" variant="danger">Actualizar</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="deleteItem" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Eliminar página?</flux:heading>

                <flux:text class="mt-2">
                    Esta seguro de eliminar la pagina.<br>
                    <strong>{{ $this->page['label'] ?? null }}</strong>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button icon="x-mark" variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="destroy" icon="trash" variant="danger">Sí, eliminar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="disableItem" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Desactivar página?</flux:heading>

                <flux:text class="mt-2">
                    Esta seguro de desactivar la pagina.<br>
                    <strong>{{ $this->page['label'] ?? null }}</strong>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button icon="x-mark" variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="disabled" icon="x-circle" variant="danger">Sí, desactivar</flux:button>
            </div>
        </div>
    </flux:modal>

</section>