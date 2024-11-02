import { createRouter, createWebHistory } from 'vue-router';
import ImportArticles from '../components/ImportArticles.vue';
import SearchArticles from '../components/SearchArticles.vue';

const routes = [
    {
        path: '/',
        name: 'ImportArticles',
        component: ImportArticles
    },
    {
        path: '/search',
        name: 'SearchArticles',
        component: SearchArticles
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;

