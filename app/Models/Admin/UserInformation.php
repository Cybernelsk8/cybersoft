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
        'cui',
        'telefono',
        'email',
        'municipio_id',
        'zona',
        'colonia',
        'direccion',
        'user_id',
    ];

    protected array $accessorMap = [
        'nombre_completo' => ['nombres', 'apellidos'],
    ];

    protected $appens = ['nombre_completo'];

    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function municipio() {
        return $this->belongsTo(Municipio::class);
    }

    public function getNombreCompletoAttribute(): string {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
