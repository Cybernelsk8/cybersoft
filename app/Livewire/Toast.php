<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Toast extends Component
{
    public $toasts = [];
    public $position = 'bottom-5 right-5';
    public $timers = []; // Para manejar los timers

    #[On('showToast')]
    public function add($data) {
        $id = uniqid();
        
        $type = $data['type'] ?? 'secondary';
        
        $this->toasts[$id] = [
            'id' => $id,
            'title' => $data['title'] ?? $this->getDefaultTitle($type),
            'message' => $data['message'] ?? '',
            'type' => $type,
            'variant' => $data['variant'] ?? $this->getVariantFromType($type),
            'icon' => $data['icon'] ?? $this->getIconFromType($type),
            'duration' => $data['duration'] ?? 5000,
            'actions' => $data['actions'] ?? [],
            'dismissible' => $data['dismissible'] ?? true,
            'show' => true,
        ];

        // Programar auto-remoción si tiene duración
        if ($this->toasts[$id]['duration'] > 0) {
            $this->dispatch('remove-toast', id: $id);
        }
    }

    #[On('remove-toast')]
    public function remove($id) {
        if (isset($this->toasts[$id])) {
            $this->toasts[$id]['show'] = false;
            
            // Usar JavaScript para el delay de eliminación
            $this->dispatch(
                'remove-toast-delayed',
                js: "setTimeout(() => { Livewire.dispatch('actually-remove-toast', {id: '{$id}'}) }, 5000)"
            );
        }
    }
    
    #[On('actually-remove-toast')]
    public function actuallyRemove($id){
        unset($this->toasts[$id]);
    }
    
    #[On('toast-action')]
    public function handleAction($toastId, $actionIndex){
        if (!isset($this->toasts[$toastId])) {
            return;
        }
        
        $toast = $this->toasts[$toastId];
        
        if (isset($toast['actions'][$actionIndex])) {
            $action = $toast['actions'][$actionIndex];
            
            if (isset($action['event'])) {
                $this->dispatch($action['event'], ...($action['payload'] ?? []));
            }
            
            if (!isset($action['keep_open']) || !$action['keep_open']) {
                $this->remove($toastId);
            }
        }
    }
    
    private function getDefaultTitle($type){
        return match($type) {
            'success' => 'Éxito',
            'error' => 'Error',
            'warning' => 'Advertencia',
            'info' => 'Información',
            default => 'Aviso'
        };
    }
    
    private function getVariantFromType($type){
        return match($type) {
            'success' => 'green',
            'danger' => 'red',
            'warning' => 'yellow',
            default => 'secondary',
        };
    }
    
    private function getIconFromType($type){
        return match($type) {
            'success' => 'check-circle',
            'danger' => 'x-circle',
            'warning' => 'exclamation-triangle',
            default => 'information-circle',
        };
    }

    public function render()
    {
        return view('livewire.toast');
    }
}