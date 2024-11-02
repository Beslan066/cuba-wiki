<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wikipedia Importer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
<div id="app" class="container mx-auto p-4">
    <nav class="flex space-x-4 mb-4">
        <button @click="currentPage = 'ImportArticles'" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Импорт статей
        </button>
        <button @click="currentPage = 'SearchArticles'" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Поиск статей
        </button>
    </nav>
    <component :is="currentPage"></component>
</div>

<script>
    import { createApp } from 'vue';
    import ImportArticles from './components/ImportArticles.vue';
    import SearchArticles from './components/SearchArticles.vue';

    const app = createApp({
        data() {
            return {
                currentPage: 'ImportArticles'
            };
        },
        components: {
            ImportArticles,
            SearchArticles
        }
    });
    app.mount('#app');
</script>
</body>
</html>
