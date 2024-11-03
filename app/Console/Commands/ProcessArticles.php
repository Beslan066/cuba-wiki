<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Word;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessArticles extends Command
{
    /**
     * Команда для обработки статей и добавления слов-атомов в связующую таблицу.
     *
     * @var string
     */
    protected $signature = 'articles:process {id?}';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Добавление слов-атомов в связующую таблицу для всех или одной статьи';

    /**
     * Выполнение команды.
     *
     * @return void
     */
    public function handle()
    {
        // Получаем ID статьи из аргумента, если он указан
        $id = $this->argument('id');

        // Получаем статьи: либо все, либо одну по ID
        $articles = $id ? Article::where('id', $id)->get() : Article::all();

        // Обрабатываем каждую статью
        foreach ($articles as $article) {
            // Извлекаем все слова из содержимого статьи
            preg_match_all('/[a-zA-Zа-яА-Я0-9]+/u', $article->content, $matches);
            $atoms = array_map('mb_strtolower', $matches[0]); // Приводим слова к нижнему регистру

            foreach ($atoms as $atom) {
                // Создаем или находим запись слова в таблице 'words'
                $word = Word::firstOrCreate(['word' => $atom]);

                // Проверяем существование записи в связующей таблице 'article_word'
                $existingPivot = DB::table('article_word')
                    ->where('article_id', $article->id)
                    ->where('word_id', $word->id)
                    ->first();

                if ($existingPivot) {
                    // Увеличиваем счетчик, если запись уже существует
                    DB::table('article_word')
                        ->where('article_id', $article->id)
                        ->where('word_id', $word->id)
                        ->increment('count');
                } else {
                    // Вставляем новую запись в связующую таблицу
                    DB::table('article_word')->insert([
                        'article_id' => $article->id,
                        'word_id' => $word->id,
                        'count' => 1
                    ]);
                }
            }

            // Выводим сообщение об успешной обработке статьи
            $this->info("Слова-атомы успешно добавлены для статьи с ID {$article->id}");
        }
    }
}

