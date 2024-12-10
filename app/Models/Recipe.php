<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;

class Recipe extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipe_name',
        'description',
        'ingredients',
        'procedure',
        'prep_time',
        'servings',
        'url_image',
        'category_id',
        'user_id',
    ];

    /**
     * Get the user that owns the recipe.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that the recipe belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Delete the recipe's image from storage.
     */
    public function deleteImage()
    {
        if ($this->url_image) {
            Storage::disk('public')->delete($this->url_image);
        }
    }

    /**
     * Get the recipe's image URL.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        return $this->url_image
            ? Storage::disk('public')->url($this->url_image)
            : asset('views/default/lawlaw.jpg');
    }

    /**
     * Scope a query to only include recipes of a given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include recipes of the authenticated user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Get the recipe's preparation time in a human-readable format.
     *
     * @return string
     */
    public function getFormattedPrepTimeAttribute()
    {
        // Convert prep_time to integer in case it's stored as a string
        $totalMinutes = (int) $this->prep_time;

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        $timeString = '';
        if ($hours > 0) {
            $timeString .= $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ';
        }
        if ($minutes > 0) {
            $timeString .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }

        return trim($timeString) ?: 'N/A';
    }
}