<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Article extends Model {
    protected $fillable = ['title', 'content', 'word_count', 'size', 'url'];
    public function words()
    {
        return $this->belongsToMany(Word::class)
            ->withPivot('count'); // Указываем, что нужно получить данные из промежуточной таблицы
    }
}
