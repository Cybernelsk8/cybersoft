<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\HtmlString;

class DataTable extends Component
{
   private $capturedSlots = [];
    
    public function __construct(
        public array $headers,
        public iterable $rows,
        public bool $selectable = false,
        public bool $advanceFilter = true,
        public bool $massActions = false,

    ) {}
    
    public function render(): View|Closure|string {
        return function (array $data) {

            $this->captureSlots($data);
            
            return view('components.data-table', [
                'headers' => $this->headers,
                'rows' => $this->rows,
                'capturedSlots' => $this->capturedSlots,
            ]);
        };
    }
    
    private function captureSlots(array $data): void {
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'column_') && $value instanceof \Closure) {
                $this->capturedSlots[$key] = $value;
            }
        }
    }
}
