<div class="grid grid-cols-1 px-4 pt-6 xl:grid-cols-3 xl:gap-4">
    
    <div class="col-span-full xl:col-auto">
        <div class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-zinc-600 sm:p-6 dark:bg-zinc-700">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white flex gap-2 items-center">
                <flux:icon.user-circle />
                Foto de usuario
            </h2>
            <div class="items-center sm:flex xl:block 2xl:flex sm:space-x-4 xl:space-x-0 2xl:space-x-4">                    
                <flux:avatar 
                    :name="$user->user_information->nombre_corto" 
                    size="xl"
                />
               
                    <label for="foto">
                        <div class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-md mt-4 sm:mt-0 xl:mt-4 2xl:mt-0 flex justify-center items-center">
                            <flux:icon.photo class="cursor-pointer" variant="solid" tooltip="Seleccionar foto" />
                        </div>
                        <input
                            id="foto"
                            wire:model="usuario.user_information.foto"
                            type="file" 
                            accept="image/*"
                            hidden
                        >
                    </label>
                    <flux:button
                        wire:click="uploadPicture"
                        icon="cloud-arrow-up"
                        variant="ghost"
                        tooltip="Subir foto"
                    />
                    <flux:button 
                        wire:click="deletePicture"
                        icon="trash"
                        variant="ghost"
                        tooltip="Eliminar foto"
                    />
            </div>
        </div>
        
        <div class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-zinc-600 sm:p-6 dark:bg-zinc-700">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white flex gap-2 items-center">
                <flux:icon.shield-check />
                Opciones de cuenta
            </h2>
            <div class="flex gap-4 justify-evenly">
                <flux:modal.trigger name="desactivar-usuario">
                    <flux:button
                        icon="power"
                        variant="filled">
                        Desactivar cuenta
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="reiniciar-password">
                    <flux:button
                        icon="key"
                        variant="filled">
                        Reiniciar contraseña
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>
    </div>

    <div class="col-span-2">
        <div class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-zinc-600 sm:p-6 dark:bg-zinc-700">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white flex gap-2 items-center">
                <flux:icon.identification />
                Información de usuario
            </h2>
            <form wire:submit.prevent="updateProfileInformation()" 
                class="my-6 w-full space-y-6" >
        
                <div class="grid grid-cols-6 gap-4">
                    <div class="col-span-6">
                        <flux:input 
                            wire:model="usuario.user_information.cui" 
                            label="Dpi *"
                            icon="identification"
                            required
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:input 
                            wire:model="usuario.user_information.nombres" 
                            label="Nombres *" 
                            type="text"
                            icon="pencil-square" 
                            required  
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:input 
                            wire:model="usuario.user_information.apellidos" 
                            label="Apellidos *" 
                            type="text"
                            icon="pencil-square" 
                            required
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:input 
                            wire:model="usuario.user_information.fecha_nacimiento" 
                            label="Fecha de nacimiento *" 
                            type="date" 
                            icon="cake"
                            required 
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:radio.group 
                            wire:model="usuario.user_information.sexo" 
                            label="Seleccione sexo *"
                            required >
                            <flux:radio 
                                value="F" 
                                label="Femenino"
                            />
                            <flux:radio 
                                value="M" 
                                label="Masculino" 
                            />
                        </flux:radio.group>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:input 
                            wire:model="usuario.user_information.telefono" 
                            label="Teléfono *" 
                            type="phone" 
                            placeholder="55555555" 
                            mask="99999999"
                            icon="phone"
                            required 
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:input 
                            wire:model="usuario.user_information.email" 
                            label="Correo *" 
                            type="email"
                            icon="envelope"
                            required 
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:select 
                            label="Departamentos" 
                            wire:model.live="departamento_id"
                            icon="building-2"
                            placeholder="Seleccione departamento" >
            
                            @forelse ($this->departamentos as $departamento)
                                <flux:select.option 
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
                            wire:model="usuario.user_information.municipio_id"
                            icon="landmark"
                            required >
                            <flux:select.option value="" > -- Seleccione municipio -- </flux:select.option>
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
                            label="Zonas" 
                            wire:model="usuario.user_information.zona" >
                            <flux:select.option value="" > -- Seleccione zona -- </flux:select.option>
                            @forelse (range(1,25) as $zona)
                                <flux:select.option 
                                    value="{{ $zona }}">
                                    {{ 'Zona '.$zona }}
                                </flux:select.option>                        
                            @empty                    
                                <flux:select.option>No hay data</flux:select.option>                        
                            @endforelse
                        </flux:select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:input 
                            wire:model="usuario.user_information.colonia" 
                            label="Colonia" 
                            icon="map-pin"
                            required 
                        />
                    </div>
                    <div class="col-span-6">
                        <flux:input 
                            wire:model="usuario.user_information.direccion" 
                            label="Dirección *"
                            icon="map" 
                            required 
                        />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <flux:select 
                            label="Roles" 
                            wire:model.live="usuario.role">
                            <flux:select.option value="" > -- Seleccione roles -- </flux:select.option>
                            @forelse ($this->roles as $role)
                                <flux:select.option 
                                    value="{{ $role->name }}">
                                    {{ $role->name }}
                                </flux:select.option>                        
                            @empty                    
                                <flux:select.option>No hay data</flux:select.option>                        
                            @endforelse
                        </flux:select>
                    </div>                    
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <flux:button
                            icon="check-badge"
                            variant="primary" 
                            type="submit" 
                            class="w-full">
                            Actualizar cambios
                        </flux:button>
                    </div>
                    
                </div>
            </form>
        </div>

        <div class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-zinc-600 sm:p-6 dark:bg-zinc-700">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white flex gap-2 items-center">
                <flux:icon.lock-closed />
                Asignar permisos directos
            </h2>       
            <x-data-table :headers="$this->headers" :rows="$this->rows" selectable>
                <x-slot:massActions>
                        <flux:modal.trigger name="asignar-permisos">
                        <flux:menu.item
                            icon="user-plus">
                            Asignar permisos
                        </flux:menu.item>
                    </flux:modal.trigger>
                </x-slot:massActions>
            </x-data-table>
        </div>
    </div>

    <flux:modal name="desactivar-usuario">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿ Desactivar cuenta ?</flux:heading>

                <flux:text class="mt-2">
                    Estás a punto de desactivar este usuario.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="disabledUser" variant="danger">Sí, desactivar</flux:button>
            </div>
        </div>
    </flux:modal>
    
    <flux:modal name="reiniciar-password">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Reiniciar password?</flux:heading>

                <flux:text class="mt-2">
                    Estás a punto de reiniciar la contraseña de este usuario.<br>
                    Esta acción no se puede revertir.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="resetPassword" variant="danger">Sí, reiniciar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="asignar-permisos">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Asignar permisos a este usuario?</flux:heading>

                <flux:text class="mt-2">
                    Estás a punto de asignar permisos directos a este usuario.<br>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="syncDirectPermissions" variant="danger">Sí, asignar</flux:button>
            </div>
        </div>
    </flux:modal>

</div>