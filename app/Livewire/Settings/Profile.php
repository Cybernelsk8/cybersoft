<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\Admin\User;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Traits\Interact;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules, Interact;

    public User $user;
    public array $usuario = [];
    public ?int $departamento_id = 7;
    public $departamentos, $roles;

    /**
     * Mount the component.
     */
    public function mount(): void {
        $this->user = Auth::user()->load(['roles','user_information']);
        $this->usuario = $this->user->toArray();
        $this->departamentos = Departamento::orderBy('nombre')->get();
        $this->roles = Role::orderBy('name')->get();
    }

    public function render() {
        $municipios = Municipio::where('departamento_id',$this->departamento_id)
        ->orderBy('nombre')
        ->get();

        return view('livewire.Settings.profile',compact('municipios'));
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
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

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
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
}
