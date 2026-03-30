<?php

namespace App\Livewire\Admin;

use App\Traits\DataTable;
use App\Traits\Interact;
use App\Models\Admin\Permission;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Permisos')]
class Permissions extends Component
{
    use DataTable, Interact;

    public array $headers = [
        [ 'index' => 'id', 'label' => '#', 'align' => 'center' ],
        [ 'index' => 'name', 'label' => 'Permiso' ],
        [ 'index' => 'alias', 'label' => 'Alias' ],
        [ 'index' => 'module', 'label' => 'Módulo' ],
        [ 'index' => 'actions', 'label' => '', 'width' => '100px' ]
    ];

    public array $permission = [];

    #[Computed]
    public function rows() {
        $query = Permission::filterAdvance($this->headers,[
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortBy, 
                'direction' => $this->sortDirection
            ],
            'filters' => $this->processFilters(),
        ]);
        return $query->paginate($this->per_page);
    }

    public function mount() {
        $this->sortBy = 'module';
    }

    public function render() {
        return view('livewire.admin.permissions');
    }

    public function store() {
        $this->validate([
            'permission.name' => 'required|string|max:255',
            'permission.alias' => 'required|string|max:255',
            'permission.module' => 'required|string|max:255',
        ]);

        try {
            Permission::create([
                'name' => mb_strtolower(trim($this->permission['name'])),
                'alias' => mb_strtolower(trim($this->permission['alias'])),
                'module' => mb_strtolower(trim($this->permission['module'])),
                'guard_name' => 'web',
            ]);
    
            $this->toastSuccess('Permiso creado exitosamente.');
    
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al crear el permiso');
        }

    }

    public function edit(int $id) {
        $this->permission = Permission::findOrFail($id)->toArray();
        Flux::modal('editPermission')->show();
    }

    public function update() {
        $this->validate([
            'permission.name' => 'required|string|min:3|unique:desarrollo-social.permissions,name,' . $this->permission['id'],
            'permission.name' => 'required|string|min:3',
            'permission.module' => 'required|string|max:255',
        ]);

        try {
            
            $permission = Permission::find($this->permission['id']);
            $permission->name = mb_strtolower(trim($this->permission['name']));
            $permission->alias = mb_strtolower(trim($this->permission['alias']));
            $permission->module = mb_strtolower(trim($this->permission['module']));

            if($permission->isDirty()) {
                $permission->save();
                $this->toastSuccess('Permiso actualizado exitosamente.');
            }
    
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al actualizar el permiso');
        }

    }

    public function delete(int $id) {
       $this->permission = Permission::findOrFail($id)->toArray();
        Flux::modal('deletePermission')->show();
    }

    public function destroy() {
        try {
            $permission = Permission::find($this->permission['id']);
            $permission->delete();
    
            $this->toastSuccess('Permiso eliminado exitosamente.');
    
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al eliminar el permiso');
        }
    }

    public function resetData() {
        $this->reset('permission');
        $this->resetValidation();
        Flux::modals()->close();
    }
}