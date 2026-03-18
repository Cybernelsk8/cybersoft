<section class="w-full">
     
    <div class="flex justify-center mb-4">
        <flux:modal.trigger name="newUser">
            <flux:button 
                icon="plus" 
                title="Crear nuevo usuario"
                variant="primary" >
                Crear nuevo usuario
            </flux:button>
        </flux:modal.trigger>
    </div>
    
    <x-data-table :headers="$this->headers" :rows="$this->rows">
        @interact('nombre_completo', $row)
            <div class="flex items-center gap-3">
                <flux:avatar 
                    circle 
                    name="{{ $row->nombre_completo }}" 
                    size="lg" 
                    initials="{{ $row->user?->initials() ?? null }}"
                    :src="$row->url_photo"
                />
                <div class="grid">
                    <span class="font-medium text-nowrap">
                        {{ $row->nombre_completo ?? null }}
                    </span>
                    <div class="flex gap-2 items-center">
                        <flux:icon.envelope class="size-4"/>
                        <span class="text-xs opacity-60">
                            {{ $row->email }}
                        </span>
                    </div>
                    <div class="flex gap-2 items-center">
                        <flux:icon.phone class="size-4"/>
                        <span class="text-xs opacity-60">
                            {{ $row->telefono ?? null }}
                        </span>
                    </div>
                </div>
            </div>
        @endinteract

        @interact('user.deleted_at',$row)
            @if ($row->user->deleted_at)
                <flux:icon.x-circle class="size-5 text-red-500 mx-auto" />
            @else
                <flux:icon.check-circle class="size-5 text-green-500 mx-auto" />
            @endif
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
                   
                    <flux:menu.item 
                        icon="pencil-square"
                        :href="route('admin.users.show', $row->id)" 
                        wire:navigate >
                        Editar
                    </flux:menu.item>
                
                    @if ($row->user->deleted_at)
                        <flux:menu.item 
                            variant="danger" 
                            icon="check-circle"
                            wire:click="userRestore({{ $row->id }})" >
                            Restaurar
                        </flux:menu.item>
                    @endif

                    @if (!$row->user->deleted_at)
                        <flux:menu.item 
                            variant="danger" 
                            icon="trash"
                            wire:click="delete({{ $row->id }})" >
                            Eliminar
                        </flux:menu.item>
                    @endif
                    
                </flux:menu>
            </flux:dropdown>
        @endinteract
    </x-data-table>
    

    <flux:modal name="newUser" flyout @close="resetData">
        <form wire:submit.prevent="store" class="space-y-6">
            <flux:heading size="lg">Nuevo usuario</flux:heading>

            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.nombres" 
                        label="Nombres *" 
                        type="text"
                        icon="pencil-square" 
                        required  
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.apellidos" 
                        label="Apellidos *"
                        icon="pencil-square" 
                        type="text" 
                        required
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.cui" 
                        label="Cui *" 
                        type="text"
                        maxlength="13"
                        icon="identification" 
                        required 
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.fecha_nacimiento" 
                        label="Fecha de nacimiento *" 
                        type="date"
                        icon="calendar" 
                        required 
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.telefono" 
                        label="Teléfono *" 
                        type="tel"
                        mask="9999-9999"
                        maxlength="9"
                        icon="phone" 
                        required 
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.email" 
                        label="Correo *" 
                        type="email"
                        icon="envelope" 
                        required 
                    />
                </div>
                 <div class="col-span-6 sm:col-span-3">
                    <flux:select 
                        label="Departamentos *" 
                        wire:model.live="departamento_id"
                        placeholder="Seleccione departamento" >
                        @forelse ($departamentos as $departamento)
                            <flux:select.option 
                                :selected="$departamento->id == 7"
                                value="{{ $departamento->id }}">
                                {{ $departamento->nombre }}
                            </flux:select.option>                        
                        @empty                    
                            <flux:select.option>No hay data</flux:select.option>                        
                        @endforelse
                    </flux:select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:select 
                        label="Municipios *" 
                        wire:model="user.municipio_id">
                        <flux:select.option> --Seleccione municipio --</flux:select.option>
                        @forelse ($municipios as $municipio)
                            <flux:select.option 
                                value="{{ $municipio->id }}">
                                {{ $municipio->nombre }}
                            </flux:select.option>                        
                        @empty                    
                            <flux:select.option>No hay data</flux:select.option>                        
                        @endforelse
                    </flux:select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:select 
                        label="Zona" 
                        wire:model="user.zona">
                        <flux:select.option> --Seleccione zona --</flux:select.option>
                        @forelse (range(1,25) as $zona)
                            <flux:select.option 
                                value="{{ $zona }}">
                                Zona {{ $zona }}
                            </flux:select.option>                        
                        @empty                    
                            <flux:select.option>No hay data</flux:select.option>                        
                        @endforelse
                    </flux:select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.colonia" 
                        label="Colonia *"
                        icon="map-pin" 
                        type="text" 
                        required
                    />
                </div>
                <div class="col-span-6 ">
                    <flux:input 
                        wire:model="user.direccion" 
                        label="Dirección *"
                        icon="map" 
                        type="text" 
                        required
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:select 
                        label="Roles" 
                        wire:model.live="user.role"
                        placeholder="Selecciona un rol">
                        @forelse ($roles as $role)
                            <flux:select.option>
                                {{ $role->name }}
                            </flux:select.option>                        
                        @empty                    
                            <flux:select.option>No hay data</flux:select.option>                        
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

    <flux:modal name="restartUser" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Restaurar usuario?</flux:heading>

                <flux:text class="mt-2">
                    Estás a punto de restaurar al usuario:<br>
                    <strong>{{ $this->user['nombre_completo'] ?? null }}</strong>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="restore" variant="danger">Sí, restaurar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="deleteUser" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Eliminar usuario?</flux:heading>

                <flux:text class="mt-2">
                    Estás a punto de eliminar al usuario:<br>
                    <strong>{{ $this->user['nombre_completo'] ?? null }}</strong>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="destroy" variant="danger">Sí, eliminar</flux:button>
            </div>
        </div>
    </flux:modal>
</section>