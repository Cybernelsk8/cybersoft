<?php

namespace App\Livewire\Admin\User;

use App\Models\Admin\User;
use App\Traits\DataTable;
use App\Traits\Interact;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use DataTable, Interact;

    public array $user = [];

    public function render() {
        
        $headers = [
            [ 'index' => 'id', 'label' => '#', 'align' => 'center' ],
            [ 'index' => 'full_name', 'label' => 'User', ],
            [ 'index' => 'email', 'label' => 'Email', ],
            [ 'index' => 'role_name', 'label' => 'Role', 'exclude' => true ],
            [ 'index' => 'deleted_at', 'label' => 'Active', 'align' => 'center' ],
            [ 'index' => 'actions', 'label' => '']
        ];

        $rows = User::withTrashed()->filterAdvance($headers, [
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortBy, 
                'direction' => $this->sortDirection
            ],
            'filters' => $this->processFilters(),
        ])->paginate($this->per_page ?? 10);

        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.user.index',compact('headers','rows','roles'));
    }

    public function store() {
        $this->validate([
            'user.name' => 'required|string|max:255',
            'user.lastname' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.role' => 'nullable|exists:roles,name',
        ]);

        try {

            DB::transaction(function () {
                
                $user = User::create([
                    'name' => ucwords(mb_strtolower($this->user['name'])),
                    'lastname' => ucwords(mb_strtolower($this->user['lastname'])),
                    'email' => mb_strtolower($this->user['email']),
                    'password' => Hash::make(User::DEFAULTPASS),
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
        $user = User::withTrashed()->findOrFail($id);
        $this->user = $user->toArray() ;
        Flux::modal('restartUser')->show();
    }

    public function restore() {
        $user = User::withTrashed()->findOrFail($this->user['id']);
        $user->restore();

        
        $this->toastSuccess(
            'Usuario restaurado correctamente.',
        );

        Flux::modals()->close();
    }

    public function resetData() {
        $this->reset(['user']);
        Flux::modals()->close();
    }
}
