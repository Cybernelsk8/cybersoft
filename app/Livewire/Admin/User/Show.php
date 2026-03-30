<?php

namespace App\Livewire\Admin\User;

use App\Traits\DataTable;
use App\Traits\Interact;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Admin\Permission;
use App\Models\Admin\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class Show extends Component
{
    use Interact, WithFileUploads, DataTable;

    public array $headers = [
        [ 'index' => 'id', 'label' => '#', 'align' => 'center' ],
        [ 'index' => 'alias', 'label' => 'Permiso' ],
        [ 'index' => 'module', 'label' => 'Pertenece a modulo' ],
    ];
    public User $user;
    public array $usuario = [];
    public $departamentos, $roles;
    public ?int $departamento_id = 7;
    public ?string $search_permissions = null;

    public function mount(User $user) {
        $this->user = $user->load(['roles','user_information']);
        $this->usuario = $this->user->toArray();
        $this->selectedRows = $user->getDirectPermissions()->pluck('id')->toArray();
        $this->departamentos = Departamento::orderBy('nombre')->get();
        $this->roles = Role::orderBy('name')->get();
    }

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

    public function render() {
        $municipios = Municipio::where('departamento_id',$this->departamento_id)
        ->orderBy('nombre')
        ->get();

        return view('livewire.admin.user.show',compact('municipios'));
    }

    public function updateProfileInformation() {
        
        $this->validate([
            'usuario.role' => 'nullable|exists:roles,name',

            'usuario.user_information.nombres' => 'required|string|max:255',
            'usuario.user_information.apellidos' => 'required|string|max:255',
            'usuario.user_information.cui' => 'required|digits:13|unique:user_information,cui,'.$this->usuario['user_information']['id'],
            'usuario.user_information.telefono' => 'required|digits:8',
            'usuario.user_information.fecha_nacimiento' => 'required|date|date_format:Y-m-d',
            'usuario.user_information.email' => 'required|string|email',
            'usuario.user_information.sexo' => 'required|in:M,F',
            'usuario.user_information.user_id' => 'required|exists:users,id',

            'usuario.user_information.municipio_id' => 'required|exists:municipios,id',
            'usuario.user_information.zona' => 'nullable|numeric',
        ]);

        try {
            
            $this->user->syncRoles([$this->usuario['role']]);
    
            $this->user->user_information->nombres = $this->usuario['user_information']['nombres'];
            $this->user->user_information->apellidos = $this->usuario['user_information']['apellidos'];
            $this->user->user_information->cui = $this->usuario['user_information']['cui'];
            $this->user->user_information->telefono = $this->usuario['user_information']['telefono'];
            $this->user->user_information->fecha_nacimiento = $this->usuario['user_information']['fecha_nacimiento'];
            $this->user->user_information->email = $this->usuario['user_information']['email'];
            $this->user->user_information->sexo = $this->usuario['user_information']['sexo'];
            $this->user->user_information->user_id = $this->usuario['user_information']['user_id'];
            $this->user->user_information->municipio_id = $this->usuario['user_information']['municipio_id'];
            $this->user->user_information->zona = $this->usuario['user_information']['zona'];

            if($this->user->user_information->isDirty()){
                $this->user->user_information->save();
                $this->toastSuccess('Informacion actualizada correctamente.');
            }

        } catch (\Throwable $th) {
            $this->toastError('Ocurrio un error al actualizar la informacion.');
        }
        
    }

    public function resetPassword() {
        try {
            
            $this->user->password = Hash::make($this->user::DEFAULTPASS);
            $this->user->save();
    
            $this->toastSuccess('Se ha restablecido la contraseña al usuario.');
    
            $this->resetData();

        } catch (\Throwable $th) {
            $this->toastError('Ocurrio un error al restablecer la contraseña.');
        }
    }

    public function disabledUser() {

        try {
            $this->user->delete();

            $this->toastSuccess('Se ha desactivado al usuario.');

            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Ocurrio un error al desactivar al usuario.');
        }
    }

    public function uploadPicture() {
        $this->validate([
            'usuario.information.foto' => 'nullable|image|max:2048', // 2MB Max
        ]);

        try {
            if ($this->usuario['information']['foto']) {
                $path = $this->usuario['information']['foto']->store('user-photos');
                $this->user->information->foto = $path;
                $this->user->information->save();
    
                $this->toastSuccess('Foto de perfil actualizada correctamente.');
            }
        } catch (\Throwable $th) {
            $this->toastError('Ocurrio un error al subir la foto de perfil.');
        }

    }

    public function deletePicture() {
        try {
            Storage::delete($this->user->information->foto);
            $this->user->information->foto = null;
            $this->user->information->save();
    
            $this->toastSuccess('Foto de perfil eliminada correctamente.');
        } catch (\Throwable $th) {
            $this->toastError('Ocurrio un error al eliminar la foto de perfil.');
        }
    }

    public function syncDirectPermissions() {
        try {
            $this->user->permissions()->sync($this->selectedRows);
            $this->toastSuccess('Permisos asignados correctamente.');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Ocurrio un error al asignar los permisos.');
        }
    }

    public function resetData() {
        Flux::modals()->close();
    }
}