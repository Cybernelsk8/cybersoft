<?php

namespace App\Livewire\Admin;

use App\Traits\DataTable;
use App\Traits\Interact;
use App\Models\Admin\Page as ModelPage;
use App\Models\Admin\Permission;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Páginas')]
class Page extends Component
{

    use DataTable, Interact;

    public array $headers = [
        [ 'index' => 'id', 'label' => '#', 'align' => 'center' ],
        [ 'index' => 'label', 'label' => 'Página' ],
        [ 'index' => 'icon', 'label' => 'Icono' ],
        [ 'index' => 'view', 'label' => 'Vista', 'exclude' => true ],
        [ 'index' => 'route', 'label' => 'Ruta' ],
        [ 'index' => 'order', 'label' => 'Orden' ],
        [ 'index' => 'state', 'label' => 'Estado'],
        [ 'index' => 'parent.label', 'label' => 'Padre'],
        [ 'index' => 'type', 'label' => 'Tipo' ],
        [ 'index' => 'permission_name', 'label' => 'Permiso' ],
        [ 'index' => 'actions', 'label' => '', 'width' => '100px']
    ];
    public array $page = [];

    #[Computed]
    public function rows() {
        $query = ModelPage::with(['parent'])
        ->filterAdvance($this->headers,[
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortBy, 
                'direction' => $this->sortDirection
            ],
            'filters' => $this->processFilters(),
        ]);
        return $query->paginate($this->per_page ?? 10);
    }


    public function render() {
        $pages = ModelPage::where('state',true)->orderBy('label')->get(['id','label']);
        $permissions = Permission::where('module','menu')->orderBy('name')->get('name');
        return view('livewire.admin.page',compact('pages','permissions'));
    }

    public function store() {
        $this->validate([
            'page.label' => 'required|string|max:255',
            'page.icon' => 'nullable|string|max:255',
            'page.route' => 'nullable|string|max:255',
            'page.order' => 'nullable|integer',
            'page.type' => 'required|in:header,parent,page',
            'page.page_id' => 'required_if:page.type,page|nullable|exists:desarrollo-social.pages,id',
            'page.permission_name' => 'nullable|string|exists:desarrollo-social.permissions,name',
        ]);

        try {
            ModelPage::create($this->page);
    
            $this->toastSuccess('Página creada con éxito.');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al crear la página: ' . $th->getMessage());
        }

    }

    public function edit($id) {
        $page = ModelPage::findOrFail($id);
        $this->page = $page->toArray();
        Flux::modal('editPage')->show();
    }

    public function update() {
        $this->validate([
            'page.label' => 'required|string|max:255',
            'page.icon' => 'nullable|string|max:255',
            'page.route' => 'nullable|string|max:255',
            'page.order' => 'nullable|integer',
            'page.type' => 'required|in:header,parent,page',
            'page.page_id' => 'required_if:page.type,page|nullable|exists:desarrollo-social.pages,id',
            'page.permission_name' => 'nullable|string|exists:desarrollo-social.permissions,name',
            'page.state' => 'required|boolean',
        ]);

        try {
            
            $page = ModelPage::findOrFail($this->page['id']);
            $page->fill($this->page);
            if($page->isDirty()) {
                $page->update($this->page);
                $this->toastSuccess('Página actualizada con éxito.');
            }
    
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al actualizar la página');
        }

    }

    public function disableItem(int $id) {
        $this->page = ModelPage::findOrFail($id)->toArray();
        Flux::modal('disableItem')->show();
    }

    public function disabled() {

        try {
            $page = ModelPage::findOrFail($this->page['id']);
            $page->state = false;
            $page->save();

            $this->toastSuccess('Página desactivada con éxito.');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al desactivar la página');
        }
    }

    public function deleteItem(int $id) {
        $this->page = ModelPage::findOrFail($id)->toArray();
        Flux::modal('deleteItem')->show();
    }

    public function destroy() {
        try {
            $page = ModelPage::findOrFail($this->page['id']);
            $page->delete();
    
            $this->toastSuccess('Página eliminada con éxito.');
            $this->resetData();
        } catch (\Throwable $th) {
            $this->toastError('Error al eliminar la página');
        }
    }

    public function resetData () {
        $this->reset('page');
        $this->resetValidation();
        Flux::modals()->close();
    }
}