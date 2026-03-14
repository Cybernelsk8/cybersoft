<?php

namespace App\Livewire;

use App\Models\User;
use App\Traits\DataTable;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Test extends Component
{
    use DataTable;

    public array $seleccion = [];

    public array $headers = [
        ['index' => 'id', 'label' => '#', 'align' => 'center'],
        ['index' => 'full_name', 'label' => 'Usuario'],
        ['index' => 'email', 'label' => 'Correo Electrónico'],
        ['index' => 'created_at', 'label' => 'Fecha de Creación'],
        ['index' => 'posts_count', 'label' => 'Número de Publicaciones'],
        ['index' => 'actions', 'label' => '', 'align' => 'end'],
    ];

    #[Computed]
    public function rows() {
        return User::filterAdvance($this->headers,[
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortBy, 
                'direction' => $this->sortDirection
            ],
            'filters' => $this->processFilters(),
        ])->paginate($this->per_page);
    }

    #[Computed]
    public function searchResults() {
        return User::all();
    }

    public function render() {

        return view('livewire.test');
    }

    public function deleteSelected() {
        User::whereIn('id', $this->selectedRows)->delete();
        $this->selectedRows = [];
    }

    public function qrcreate($id) {
        
    }
}
