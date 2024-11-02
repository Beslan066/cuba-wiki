<template>
    <div class="p-4 bg-white shadow rounded">
        <h2 class="text-2xl font-bold mb-4">Импорт статей</h2>
        <div class="flex items-center mb-4">
            <input
                v-model="keyword"
                type="text"
                placeholder="Введите ключевое слово"
                class="border rounded p-2 flex-1"
            />
            <button
                @click="importArticle"
                class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                Скопировать
            </button>
        </div>
        <p v-if="message" class="mt-2" :class="{'text-green-600': success, 'text-red-600': !success}">
            {{ message }}
        </p>

        <!-- Таблица для отображения статей -->
        <table class="min-w-full border-collapse border border-gray-300 mt-4">
            <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2">Заголовок</th>
                <th class="border border-gray-300 p-2">Размер (КБ)</th>
                <th class="border border-gray-300 p-2">Количество слов</th>
                <th class="border border-gray-300 p-2">Ссылка</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="article in articles" :key="article.id" class="hover:bg-gray-50">
                <td class="border border-gray-300 p-2">{{ article.title }}</td>
                <td class="border border-gray-300 p-2">{{ article.size }} КБ</td>
                <td class="border border-gray-300 p-2">{{ article.word_count }}</td>
                <td class="border border-gray-300 p-2">
                    <a :href="article.url" target="_blank" class="text-blue-600 underline">
                        {{ article.url }}
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            keyword: '',
            message: '',
            success: false,
            articles: []
        };
    },
    methods: {
        async importArticle() {
            try {
                const response = await axios.post('/api/import', { keyword: this.keyword });
                this.message = 'Статья успешно импортирована!';
                this.success = true;
                this.fetchArticles();
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 409) {
                        this.message = 'Статья уже импортирована';
                    } else {
                        this.message = 'Ошибка при импорте статьи';
                    }
                } else {
                    this.message = 'Ошибка сети';
                }
                this.success = false;
            } finally {
                this.keyword = '';
            }
        },

        async fetchArticles() {
            try {
                const response = await axios.get('/api/articles');
                this.articles = response.data;
            } catch (error) {
                console.error('Ошибка при получении статей:', error);
            }
        },

        formatSize(sizeInBytes) {
            if (sizeInBytes < 1024) {
                return `${sizeInBytes} байт`;
            } else if (sizeInBytes < 1048576) {
                return `${(sizeInBytes / 1024).toFixed(2)} КБ`;
            } else if (sizeInBytes < 1073741824) {
                return `${(sizeInBytes / 1048576).toFixed(2)} МБ`;
            } else {
                return `${(sizeInBytes / 1073741824).toFixed(2)} ГБ`;
            }
        }
    },
    mounted() {
        this.fetchArticles();
    }
};
</script>

