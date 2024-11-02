<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Word;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessArticles extends Command
{
    protected $signature = 'articles:process {id?}';
    protected $description = 'Добавление слов-атомов в связующую таблицу для всех или одной статьи';

    public function handle()
    {
        $id = $this->argument('id');

        $articles = $id ? Article::where('id', $id)->get() : Article::all();

        foreach ($articles as $article) {
            preg_match_all('/[a-zA-Zа-яА-Я0-9]+/u', $article->content, $matches);
            $atoms = array_map('mb_strtolower', $matches[0]);

            foreach ($atoms as $atom) {
                $word = Word::firstOrCreate(['word' => $atom]);

                $existingPivot = DB::table('article_word')
                    ->where('article_id', $article->id)
                    ->where('word_id', $word->id)
                    ->first();

                if ($existingPivot) {
                    DB::table('article_word')
                        ->where('article_id', $article->id)
                        ->where('word_id', $word->id)
                        ->increment('count');
                } else {
                    DB::table('article_word')->insert([
                        'article_id' => $article->id,
                        'word_id' => $word->id,
                        'count' => 1
                    ]);
                }
            }

            $this->info("Слова-атомы успешно добавлены для статьи с ID {$article->id}");
        }
    }
}
