<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = ['title', 'content', 'word_count', 'size', 'url'];

    /**
     * Определяет связь "многие ко многим" со словами.
     *
     * @return BelongsToMany
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class)
            ->withPivot('count'); // Включаем атрибут 'count' из промежуточной таблицы
    }
}
