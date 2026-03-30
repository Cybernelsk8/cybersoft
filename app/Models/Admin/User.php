<?php

namespace App\Models\Admin;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Post;
use App\Traits\Searchable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    const DEFAULTPASS = 'password';
    /** @use HasFactory<UserFactory> */
    use HasFactory, 
        Notifiable, 
        TwoFactorAuthenticatable, 
        Searchable,
        HasRoles,
        SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected $appends = ['role'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string{
        return Str::of($this->user_information?->nombre_corto)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getMenuAttribute() {
        $allowedPermissions = $this->getAllPermissions()
            ->where('module','menu')
            ->pluck('name')
            ->toArray();

        if (empty($allowedPermissions)) {
            return [];
        }

        $pages = Page::with(['parent', 'children'])
            ->where('state',true)
            ->orderBy('order')
            ->get();

        $allowedPages = $pages->filter(function ($page) use ($allowedPermissions) {
            return in_array($page->permission_name, $allowedPermissions);
        });

        $menu = collect();

         foreach ($allowedPages as $page) {

            if ($page->parent) {
                $menu->push($page->parent);
            }

            if (!$page->parent) {
                $menu->push($page);
            }
        }

        $menu = $menu->unique('id')->sortBy('order')->values();

        $menu->each(function ($parent) use ($allowedPages) {

            $parent->childrens = $allowedPages
                ->where('page_id', $parent->id)
                ->sortBy('order')
                ->values();
        });

        return $menu->values()->all();
    }

    public function getRoleAttribute() {
        return $this->roles[0]?->name ?? null;
    }

    public function user_information() {
        return $this->hasOne(UserInformation::class);
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }
}
