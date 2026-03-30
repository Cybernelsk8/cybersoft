<section class="w-full">

    <div class="flex justify-center mb-4">
        <flux:modal.trigger name="newPermission">
            <flux:button icon="plus" title="Crear nuevo permiso" variant="primary">
                Crear nuevo permiso
            </flux:button>
        </flux:modal.trigger>
    </div>

    <x-data-table :headers="$this->headers" :rows="$this->rows">
        @interact('actions', $row)
            <flux:dropdown>
                <flux:button size="sm" icon="ellipsis-vertical" class="cursor-pointer" variant="ghost" />
                <flux:menu>

                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                        Editar
                    </flux:menu.item>



                    <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $row->id }})">
                        Eliminar
                    </flux:menu.item>

                </flux:menu>
            </flux:dropdown>
        @endinteract
    </x-data-table>


    <flux:modal name="newPermission" flyout @close="resetData">
        <form wire:submit.prevent="store" class="space-y-6">
            <flux:heading size="lg">Nuevo permiso</flux:heading>
            <div class="grid gap-4">

                <flux:input 
                    label="Nombre *" 
                    icon="pencil-square" 
                    wire:model="permission.name"
                    placeholder="Nombre del permiso" 
                    autofocus 
                    required 
                />

                <flux:input 
                    label="Alias *" 
                    icon="pencil-square" 
                    wire:model="permission.alias"
                    placeholder="Alias del permiso" 
                    required 
                />

                <flux:input 
                    label="Módulo *" 
                    icon="rectangle-group" 
                    wire:model="permission.module"
                    placeholder="Nombre del módulo" 
                    required 
                />
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

    <flux:modal name="editPermission" flyout @close="resetData">
        <form wire:submit.prevent="update" class="space-y-6">
            <flux:heading size="lg">Editar permiso</flux:heading>
            <div class="grid gap-4">

                <flux:input 
                    label="Nombre *" 
                    icon="pencil-square" 
                    wire:model="permission.name"
                    placeholder="Nombre del permiso" 
                    autofocus 
                    required 
                />

                <flux:input 
                    label="Alias *" 
                    icon="pencil-square" 
                    wire:model="permission.alias"
                    placeholder="Alias del permiso" 
                    required 
                />

                <flux:input 
                    label="Módulo *" 
                    icon="rectangle-group" 
                    wire:model="permission.module"
                    placeholder="Nombre del módulo" 
                    required 
                />
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

    <flux:modal name="deletePermission" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Eliminar permiso?</flux:heading>

                <flux:text class="mt-2">
                    Esta seguro de eliminar el permiso.<br>
                    <strong>{{ $this->permission['name'] ?? null }}</strong>
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
</section>
