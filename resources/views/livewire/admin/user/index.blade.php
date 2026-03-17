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
    
    <x-data-table :$headers :$rows>
        @interact('full_name', $row)
            <div class="flex items-center gap-3">
                <flux:avatar 
                    circle 
                    name="{{ $row->full_name }}" 
                    size="lg" 
                    initials="{{ $row->initials() }}"
                    :src="$row->url_photo"
                />
                <div class="grid">
                    <span class="font-medium text-nowrap">
                        {{ $row->full_name }}
                    </span>
                    <div class="flex gap-2 items-center">
                        <flux:icon.envelope class="size-4"/>
                        <span class="text-xs opacity-60">
                            {{ $row->email }}
                        </span>
                    </div>
                </div>
            </div>
        @endinteract

        @interact('deleted_at',$row)
            @if ($row->deleted_at)
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
                
                    @if ($row->deleted_at)
                        <flux:menu.item 
                            variant="danger" 
                            icon="check-circle"
                            wire:click="userRestore({{ $row->id }})" >
                            Restaurar
                        </flux:menu.item>
                    @endif
                    
                </flux:menu>
            </flux:dropdown>
        @endinteract
    </x-data-table>
    

    <flux:modal name="newUser" class="min-w-[22rem]" flyout @close="resetData">
        <form wire:submit.prevent="store" class="space-y-6">
            <flux:heading size="lg">Nuevo usuario</flux:heading>

            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.name" 
                        label="Nombres *" 
                        type="text"
                        icon="pencil-square" 
                        required  
                    />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <flux:input 
                        wire:model="user.lastname" 
                        label="Apellidos *"
                        icon="pencil-square" 
                        type="text" 
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
                        label="Roles" 
                        wire:model.live="user.role"
                        placeholder="Selecciona un rol">
                        @forelse ($roles as $role)
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

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button icon="x-mark" variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button type="submit" icon="arrow-up-on-square-stack" variant="danger">Crear</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="restartUser" class="min-w-[22rem]" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Restaurar usuario?</flux:heading>

                <flux:text class="mt-2">
                    Estás a punto de restaurar al usuario:<br>
                    <strong>{{ $this->user['full_name'] ?? null }}</strong>
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
</section>