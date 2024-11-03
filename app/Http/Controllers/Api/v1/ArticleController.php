<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{


    /**
     * Вывод статей на главную.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        // Получаем все статьи из БД
        $articles = Article::query()->orderBy('id', 'desc')->get();
        return response()->json($articles);
    }

    /**
     * Импорт статьи с Википедии.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        // Получаем ключевое слово из запроса
        $keyword = $request->input('keyword');

        // Проверяем, существует ли уже статья с таким названием
        $existingArticle = Article::where('title', $keyword)->first();
        if ($existingArticle) {
            return response()->json(['message' => 'Статья уже импортирована'], 409);
        }

        // Формируем URL для запроса к API Википедии
        $wikiApiUrl = 'https://ru.wikipedia.org/w/api.php';
        $response = file_get_contents($wikiApiUrl . '?action=query&format=json&prop=extracts&titles=' . urlencode($keyword) . '&explaintext=1');
        $data = json_decode($response, true);

        // Проверяем, содержит ли ответ данные статьи
        if (isset($data['query']['pages'])) {
            $pages = $data['query']['pages'];
            foreach ($pages as $page) {
                if (isset($page['extract']) && !empty($page['extract'])) {
                    // Извлекаем контент статьи
                    $content = $page['extract'];
                    $sizeInBytes = strlen($content);
                    $wordCount = preg_match_all('/\p{L}+/u', $content);

                    // Сохраняем статью в базе данных
                    Article::create([
                        'title' => $page['title'],
                        'content' => $content,
                        'size' => $sizeInBytes,
                        'url' => 'https://ru.wikipedia.org/wiki/' . urlencode($page['title']),
                        'word_count' => $wordCount,
                    ]);

                    return response()->json(['message' => 'Статья успешно импортирована'], 200);
                }
            }
        }

        // Возвращаем сообщение об ошибке, если статья не найдена
        return response()->json(['message' => 'Статья не найдена'], 404);
    }

    /**
     * Поиск статей по ключевому слову.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Получаем ключевое слово из запроса
        $keyword = $request->input('keyword');

        // Проверяем, было ли передано ключевое слово
        if (!$keyword) {
            return response()->json(['message' => 'Ключевое слово не указано'], 400);
        }

        // Приводим ключевое слово к нижнему регистру для поиска без учета регистра
        $lowercaseKeyword = mb_strtolower($keyword);

        // Ищем статьи, содержащие точное совпадение с ключевым словом
        $results = Article::whereRaw('LOWER(content) LIKE ?', ['%' . $lowercaseKeyword . '%'])->get();

        // Проверяем, есть ли результаты
        if ($results->isEmpty()) {
            return response()->json(['message' => 'Совпадений не найдено'], 404);
        }

        // Подготовка результатов с подсчётом количества вхождений ключевого слова
        $searchResults = $results->map(function ($article) use ($lowercaseKeyword) {
            // Приводим контент статьи к нижнему регистру и считаем количество вхождений
            $content = mb_strtolower($article->content);
            $matchCount = substr_count($content, $lowercaseKeyword);

            return [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'count' => $matchCount // Добавляем количество вхождений
            ];
        });

        // Возвращаем результаты поиска
        return response()->json($searchResults);
    }


}


