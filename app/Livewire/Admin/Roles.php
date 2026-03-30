<?php

namespace App\Livewire\Admin;

use App\Traits\DataTable;
use App\Traits\Interact;
use App\Models\Admin\Permission;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Roles')]
class Roles extends Component
{
    use DataTable, Interact;

    public array $headers = [
        [ 'index' => 'id', 'label' => '#', 'align' => 'center' ],
        [ 'index' => 'name', 'label' => 'Role' ],
        [ 'index' => 'actions', 'label' => '', 'width' => '100px' ]
    ];
    public array $role = [
        'name' => null,
        'permissions' => []
    ];
    
    public ?string $search_permissions = null;

    #[Computed]
    public function rows() {
        $query = Role::when($this->search,function($query){
            $query->where('name','like','%'.$this->search.'%')
                ->orWhere('id',$this->search);
        })->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->per_page ?? 10);
    }

    public function render() {
    
        $all_permissions = Permission::where('name','like','%'.$this->search_permissions.'%')
        ->get()
        ->groupBy('module');

        return view('livewire.admin.roles', compact('all_permissions'));
    }

    public function store() {

        $this->validate([
            'role.name' => 'required|string|max:255',
        ]);

        try {
            
            $role = Role::create([
                'name' => $this->role['name'],
            ]);
    
            if(!empty($this->role['permissions'])) {
                $role->permissions()->sync($this->role['permissions'] ?? []);
            }
    
            $this->toastSuccess('Role creado exitosamente.');
    
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al crear el role');
        }


    }

    public function edit(int $id) {
        $role = Role::findOrFail($id);
        $this->role = $role->toArray();
        $this->role['permissions'] = $role->permissions->pluck('id')->toArray();
        Flux::modal('editRole')->show();
    }

    public function update() {

        $this->validate([
            'role.name' => 'required|string|max:255',
        ]);

        try {
            $role = Role::findOrFail($this->role['id']);
    
            $role->name = $this->role['name'];
    
            if($role->isDirty('name') || hasChanged($this->role['permissions'], $role->permissions->pluck('id')->toArray())) {
                $role->save();
                $role->permissions()->sync($this->role['permissions'] ?? []);
                $this->toastSuccess('Role actualizado exitosamente.');
            }

            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al actualizar el role');
        }


    }

    public function delete(int $id) {
        $this->role = Role::findOrFail($id)->toArray();
        Flux::modal('deleteRole')->show();
    }

    public function destroy () {
        try {
            
            $role = Role::findOrFail($this->role['id']);
            $role->delete();
    
            $this->toastSuccess('Role eliminado exitosamente.');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al eliminar el role');
        }
    }

    public function resetData() {
        $this->reset('role');
        $this->resetValidation();
        Flux::modals()->close();
    }
}