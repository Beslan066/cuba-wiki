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


    public function index()
    {

        // Получаем все статьи из БД
        $articles = Article::query()->orderBy('id', 'desc')->get();
        return response()->json($articles);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $keyword = $request->input('keyword');

        $existingArticle = Article::where('title', $keyword)->first();
        if ($existingArticle) {
            return response()->json(['message' => 'Статья уже импортирована'], 409);
        }

        $wikiApiUrl = 'https://ru.wikipedia.org/w/api.php';
        $response = file_get_contents($wikiApiUrl . '?action=query&format=json&prop=extracts&titles=' . urlencode($keyword) . '&explaintext=1');
        $data = json_decode($response, true);

        if (isset($data['query']['pages'])) {
            $pages = $data['query']['pages'];
            foreach ($pages as $page) {
                if (isset($page['extract']) && !empty($page['extract'])) {
                    $content = $page['extract'];
                    $sizeInBytes = strlen($content);
                    $wordCount = preg_match_all('/\p{L}+/u', $content);

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

        return response()->json(['message' => 'Статья не найдена'], 404);
    }


    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        if (!$keyword) {
            return response()->json(['message' => 'Ключевое слово не указано'], 400);
        }

        // Приводим ключевое слово к нижнему регистру для поиска без учета регистра
        $lowercaseKeyword = mb_strtolower($keyword);

        // Ищем статьи, содержащие точное совпадение с ключевым словом
        $results = Article::whereRaw('LOWER(content) LIKE ?', ['%' . $lowercaseKeyword . '%'])->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'Совпадений не найдено'], 404);
        }

        // Подготовка результатов с подсчётом количества вхождений
        $searchResults = $results->map(function ($article) use ($lowercaseKeyword) {
            // Подсчёт количества вхождений ключевого слова в контенте
            $content = mb_strtolower($article->content);
            $matchCount = substr_count($content, $lowercaseKeyword);

            return [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'count' => $matchCount // Добавляем количество вхождений
            ];
        });

        return response()->json($searchResults);
    }


}


