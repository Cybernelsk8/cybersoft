<?php

namespace App\Models\Admin;

use App\Models\Municipio;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    use Searchable;

    protected $table = 'user_information';

    protected $fillable = [
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'cui',
        'telefono',
        'email',
        'municipio_id',
        'zona',
        'colonia',
        'direccion',
        'user_id',
    ];

    
    protected $appends = ['nombre_completo', 'nombre_corto'];
    
    public function getAccessorMap() {
        return [
            'nombre_completo' => ['nombres', 'apellidos'],
        ];
    }

    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function municipio() {
        return $this->belongsTo(Municipio::class);
    }

    public function getNombreCortoAttribute() {

        $prepositions = ['de', 'del', 'la', 'los', 'las'];

        $nombres = explode(" ", $this->nombres)[0];
        $apellidos = str_replace($prepositions,"", $this->apellidos);
        $apellidos = explode(" ",$apellidos)[0];

        return $nombres.' '.$apellidos;
    }

    public function getNombreCompletoAttribute(): string {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
