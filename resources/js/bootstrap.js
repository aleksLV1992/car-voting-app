import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import VotingPage from './views/VotingPage.vue';
import StatisticsPage from './views/StatisticsPage.vue';

const routes = [
    { path: '/', name: 'voting', component: VotingPage },
    { path: '/statistics', name: 'statistics', component: StatisticsPage },
];

const router = createRouter({
    history: createWebHistory('/'),
    routes,
});

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.mount('#app');
