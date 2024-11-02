<template>
    <div class="p-4 bg-white shadow rounded flex">
        <div class="flex-1">
            <h2 class="text-2xl font-bold mb-4">Поиск статей</h2>
            <div class="flex items-center mb-4">
                <input
                    v-model="searchKeyword"
                    type="text"
                    placeholder="Введите ключевое слово для поиска"
                    class="border rounded p-2 flex-1"
                />
                <button
                    @click="searchArticles"
                    class="ml-2 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                >
                    Поиск
                </button>
                <button
                    @click="resetSearch"
                    class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                >
                    Сбросить
                </button>
            </div>
            <ul class="mt-4 space-y-2">
                <li
                    v-for="result in searchResults"
                    :key="result.id"
                    class="cursor-pointer p-2 border rounded hover:bg-gray-100"
                    @click="selectArticle(result)"
                >
                    <strong>{{ result.title }}</strong> (Вхождения: {{ result.count }})
                </li>
            </ul>

        </div>
        <div v-if="selectedArticle" class="ml-4 w-1/2">
            <h3 class="text-xl font-semibold mb-2">{{ selectedArticle.title }}</h3>
            <p v-html="highlightedContent"></p>
        </div>
    </div>
</template>



<script>
import axios from 'axios';

export default {
    data() {
        return {
            searchKeyword: '',
            searchResults: [],
            selectedArticle: null
        };
    },
    computed: {
        highlightedContent() {
            if (!this.selectedArticle || !this.searchKeyword) return '';
            let content = this.selectedArticle.content;
            const words = this.searchKeyword.match(/[a-zA-Zа-яА-Я0-9]+/g).map(w => w.toLowerCase());

            words.forEach(word => {
                const regex = new RegExp(`(${word})`, 'gi'); // Используем backticks
                content = content.replace(regex, '<span class="text-green-600">$1</span>');
            });
            return content;
        }
    },
    methods: {
        async searchArticles() {
            try {
                const response = await axios.get(`/api/search?keyword=${encodeURIComponent(this.searchKeyword)}`);
                this.searchResults = response.data;
                this.selectedArticle = null; // Сбрасываем выбранную статью
            } catch (error) {
                if (error.response && error.response.status === 404) {
                    alert(error.response.data.message);
                } else {
                    console.error('Ошибка при поиске статей:', error);
                    alert('Произошла ошибка при выполнении поиска. Попробуйте позже.');
                }
            }
        },
        resetSearch() {
            this.searchKeyword = '';
            this.searchResults = [];
            this.selectedArticle = null; // Сбрасываем выбранную статью
        },
        selectArticle(article) {
            this.selectedArticle = article; // Устанавливаем выбранную статью
        }
    }
};
</script>



<style>
.text-green-600 {
    font-weight: bold;
    text-decoration: underline;
}
</style>

