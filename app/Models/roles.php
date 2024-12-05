<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id'; // Aligns with your migration

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_user', // Column for role name (e.g., Admin, Chef, User)
    ];

    /**
     * Define the relationship to the User model.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
