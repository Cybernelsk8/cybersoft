<section class="w-full">

    <div class="flex justify-center mb-4">
        <flux:modal.trigger name="newRole">
            <flux:button icon="plus" title="Crear nuevo role" variant="primary">
                Crear nuevo role
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

    <flux:modal name="newRole" flyout @close="resetData">
        <form wire:submit.prevent="store" class="space-y-6">
            <flux:heading size="lg">Nuevo role</flux:heading>
            <div>

                <flux:input label="Nombre *" icon="pencil-square" wire:model="role.name" placeholder="Nombre del role"
                    autofocus required />

                <div class="flex justify-between my-4 items-center">
                    <h3 class="font-semibold mb-2">Todos los permisos</h3>
                    <flux:input wire:model.live.debounce="search_permissions" label="Buscar permisos" type="search"
                        icon="magnifying-glass" placeholder="Buscar ..." />
                </div>
                <div class="grid gap-4">
                    @foreach ($all_permissions as $module => $permissions)
                        <details wire:key="details-{{ $module }}" @if ($loop->first) open @endif
                            class="border border-gray-300 p-4 rounded-lg">
                            <summary class="cursor-pointer mb-2 font-semibold flex items-center gap-2">
                                Permisos de {{ $module }}
                            </summary>
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 rounded p-4 gap-4">
                                @foreach ($permissions as $permission)
                                    <flux:checkbox wire:model="role.permissions" :value="$permission->id"
                                        :label="$permission->name" class="text-xs"
                                        id="new-permission-{{ $permission->id }}" />
                                @endforeach
                            </div>
                        </details>
                    @endforeach
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

    <flux:modal name="editRole" flyout @close="resetData">
        <form wire:submit.prevent="update" class="space-y-6">
            <flux:heading size="lg">Editar role</flux:heading>
            <div>

                <flux:input label="Nombre *" icon="pencil-square" wire:model="role.name" placeholder="Nombre del role"
                    autofocus required />

                <div class="flex justify-between my-4 items-center">
                    <h3 class="font-semibold mb-2">Todos los permisos</h3>
                    <flux:input wire:model.live.debounce="search_permissions" label="Buscar permisos" type="search"
                        icon="magnifying-glass" placeholder="Buscar ..." />
                </div>
                <div class="grid gap-4">
                    @foreach ($all_permissions as $module => $permissions)
                        <details wire:key="details-edit-{{ $module }}"
                            @if ($loop->first) open @endif class="border border-gray-300 p-4 rounded-lg">
                            <summary class="cursor-pointer mb-2 font-semibold flex items-center gap-2">
                                Permisos de {{ $module }}
                            </summary>
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 rounded p-4 gap-4">
                                @foreach ($permissions as $permission)
                                    <flux:checkbox wire:model="role.permissions" :value="$permission->id"
                                        :label="$permission->name" class="text-xs"
                                        id="new-permission-{{ $permission->id }}" />
                                @endforeach
                            </div>
                        </details>
                    @endforeach
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

    <flux:modal name="deleteRole" @close="resetData">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Eliminar role?</flux:heading>

                <flux:text class="mt-2">
                    Esta seguro de eliminar el role.<br>
                    <strong>{{ $this->role['name'] ?? null }}</strong>
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
