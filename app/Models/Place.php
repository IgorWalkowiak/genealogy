<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Place extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'postal_code',
        'latitude',
        'longitude',
    ];

    /**
     * Get the team that owns the place.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all people born in this place.
     */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class, 'birthplace_id');
    }

    /**
     * Get the full name of the place (name + postal code).
     */
    public function getFullNameAttribute(): string
    {
        if ($this->postal_code) {
            return "{$this->name}, {$this->postal_code}";
        }

        return $this->name;
    }

    /**
     * Scope to search places by name.
     */
    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('postal_code', 'like', "%{$search}%");
    }

    /**
     * Scope to filter places by team.
     */
    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}
