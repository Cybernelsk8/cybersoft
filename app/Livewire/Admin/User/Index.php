<?php

namespace App\Livewire\Admin\User;

use App\Models\Admin\User;
use App\Models\Admin\UserInformation;
use App\Models\Municipio;
use App\Traits\DataTable;
use App\Traits\Interact;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Usuarios')]

class Index extends Component
{
    use DataTable, Interact;

    public array $headers = [
        [ 'index' => 'id', 'label' => '#', 'align' => 'center' ],
        [ 'index' => 'nombre_completo', 'label' => 'User', ],
        [ 'index' => 'cui', 'label' => 'Cui', ],
        [ 'index' => 'user.role', 'label' => 'Role', 'exclude' => true ],
        [ 'index' => 'user.deleted_at', 'label' => 'Active', 'align' => 'center' ],
        [ 'index' => 'actions', 'label' => '']
    ];

    public array $user = [];
    public array $departamentos = [];
    public int $departamento_id = 7;

    public function mount() {
        $this->departamentos = DB::table('departamentos')->orderBy('nombre')->get()->toArray();
    }

    public function render() {
        $municipios = Municipio::where('departamento_id',$this->departamento_id)
        ->orderBy('nombre')
        ->get();
        $all_roles = Role::orderBy('name')->get();
        return view('livewire.admin.user.index',compact('municipios','all_roles'));
    }

    #[Computed]
    public function rows() {
        return UserInformation::with(['municipio', 'user'])
        ->filterAdvance($this->headers, [
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortBy, 
                'direction' => $this->sortDirection
            ],
            'filters' => $this->processFilters(),
        ])->paginate($this->per_page ?? 10);
    }

    public function store() {

        $this->authorize('admin.users.store');

        $this->validate([
            'user.nombres' => 'required|string|max:255',
            'user.apellidos' => 'required|string|max:255',
            'user.fecha_nacimiento' => 'required|date|before:today|after:'.(date('Y') - 100).'-12-31',
            'user.cui' => 'required|string|max:13|unique:user_information,cui',
            'user.telefono' => 'required|regex:/^[0-9]{4}-[0-9]{4}$/',
            'user.email' => 'required|email|unique:users,email',
            'user.municipio_id' => 'required|exists:municipios,id',
            'user.zona' => 'nullable|numeric|between:1,25',
            'user.colonia' => 'nullable|string|max:100',
            'user.direccion' => 'nullable|string|max:255',
            'user.user_id' => 'nullable|exists:users,id',
            'user.role' => 'nullable|exists:roles,name',
        ]);

        try {

            DB::transaction(function () {
            
                $user = User::create([
                    'email' => mb_strtolower($this->user['email']),
                    'password' => Hash::make(User::DEFAULTPASS),
                ]);

                UserInformation::create([
                    'nombres' => ucwords(mb_strtolower(trim($this->user['nombres']))),
                    'apellidos' => ucwords(mb_strtolower(trim($this->user['apellidos']))),
                    'fecha_nacimiento' => $this->user['fecha_nacimiento'],
                    'cui' => trim($this->user['cui']),
                    'telefono' => str_replace("-","",$this->user['telefono']),
                    'email' => mb_strtolower($this->user['email']),
                    'municipio_id' => $this->user['municipio_id'],
                    'zona' => $this->user['zona'],
                    'colonia' => ucwords(mb_strtolower($this->user['colonia'])),
                    'direccion' => mb_strtolower($this->user['direccion']),
                    'user_id' => $user->id,
                ]);

    
    
                if(isset($this->user['role'])) {
                    $user->syncRoles($this->user['role']);
                }

                $this->toastSuccess('Usuario creado correctamente.');

                $this->resetData();

            });
            

        } catch (\Throwable $th) {
            $this->toastError('Error al crear al usuario.'.$th->getMessage());
        }
    }

    public function userRestore(int $id) {
        $user = UserInformation::where('user_id', $id)->firstOrFail();
        $this->user = $user->append('nombre_completo')->toArray() ;
        Flux::modal('restartUser')->show();
    }

    public function restore() {
        $this->authorize('admin.users.restore');
        $user = User::withTrashed()->findOrFail($this->user['id']);
        $user->restore();
        $this->toastSuccess('Usuario restaurado correctamente.');
        $this->resetData();
    }

    public function delete(int $id) {
        $user = UserInformation::where('user_id', $id)->firstOrFail();
        $this->user = $user->append('nombre_completo')->toArray() ;
        Flux::modal('deleteUser')->show();
    }

    public function destroy() {
        $this->authorize('admin.users.delete');
        $user = User::withTrashed()->findOrFail($this->user['user_id']);
        $user->delete();      
        $this->toastSuccess('Usuario eliminado correctamente.');
        $this->resetData();
    }

    public function resetData() {
        $this->reset(['user']);
        Flux::modals()->close();
    }
}
