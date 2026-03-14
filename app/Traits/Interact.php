<?php

namespace App\Traits;

trait Interact
{
    // ========== Toast Notifications ==========

    protected function toast(array $data): void {
        $this->dispatch('showToast', $data);
    }

    protected function toastSuccess(string $message, string $title = 'Éxito', array $extra = []): void {
        $this->toast(array_merge([
            'type' => 'success',
            'title' => $title,
            'message' => $message,
        ], $extra));
    }

    protected function toastError(string $message, string $title = 'Error', array $extra = []): void {
        $this->toast(array_merge([
            'type' => 'danger',
            'title' => $title,
            'message' => $message,
        ], $extra));
    }

    protected function toastInfo(string $message, string $title = 'Información', array $extra = []): void {
        $this->toast(array_merge([
            'type' => 'secondary',
            'title' => $title,
            'message' => $message,
        ], $extra));
    }

    protected function toastWarning(string $message, string $title = 'Advertencia', array $extra = []): void {
        $this->toast(array_merge([
            'type' => 'warning',
            'title' => $title,
            'message' => $message,
        ], $extra));
    }

    //=== Stepper propertys and functions ========

    public int $step = 1;

    public function handleStep ($step) {

        if ($step >= 1 && $step <= count($this->steps)) {

            if ($step > $this->step) {
                $this->validateCurrentStep();
            }

            $this->step = $step;
        }
    }

    public function nextStep () {
        
        if($this->step < count($this->steps)) {
            $this->validateCurrentStep();
            $this->step++;
        }
    }

    public function previousStep () {
        if($this->step > 1) {
            $this->step--;
        }
    }

    protected function validateCurrentStep(){
        //
    }

    // Navbar and interactions

    public ?int $nav_option = 1;

    public function navToggle (int $option) {
        $this->nav_option = $option;
    }
}