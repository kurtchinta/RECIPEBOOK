<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipe_name',
        'ingredients',
        'procedures',
        'category_id',
    ];

    /**
     * Define the relationship to the Category model.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Define the relationship to the User model through Category.
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Category::class,
            'id', // Foreign key on categories table
            'id', // Foreign key on users table
            'category_id', // Local key on recipes table
            'user_id' // Local key on categories table
        );
    }
}
