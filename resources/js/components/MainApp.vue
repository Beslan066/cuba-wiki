<template>
    <div>
        <nav class="mb-4 space-x-4">
            <router-link
                to="/"
                class="px-4 py-2 rounded text-white"
                :class="{'bg-blue-500': $route.path === '/', 'bg-gray-400': $route.path !== '/'}"
            >
                Импорт статей
            </router-link>
            <router-link
                to="/search"
                class="px-4 py-2 rounded text-white"
                :class="{'bg-green-500': $route.path === '/search', 'bg-gray-400': $route.path !== '/search'}"
            >
                Поиск статей
            </router-link>
        </nav>
        <router-view></router-view>
    </div>
</template>

<script>
export default {
    name: 'MainApp'
};
</script>
