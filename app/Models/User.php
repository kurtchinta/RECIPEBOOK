<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Define the relationship to the Role model.
     */
    public function role()
    {
        return $this->belongsTo(roles::class, 'role_id');
    }

    /**
     * Define the relationship to the Category model.
     */
    public function categories()
    {
        return $this->hasMany(Category::class, 'user_id');
    }

    /**
     * Define the relationship to the Recipe model through the Category model.
     */
    public function recipes()
    {
        return $this->hasManyThrough(
            Recipe::class,
            Category::class,
            'user_id', // Foreign key on categories table
            'category_id', // Foreign key on recipes table
            'id', // Local key on users table
            'id' // Local key on categories table
        );
    }
}
