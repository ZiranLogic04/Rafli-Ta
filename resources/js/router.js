import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

import Login from './Pages/Auth/Login.vue';

import UserDashboard from './Pages/User/Dashboard.vue';
import UserLettersIndex from './Pages/User/Letters/Index.vue';
import UserLettersCreate from './Pages/User/Letters/Create.vue';

import AdminDashboard from './Pages/Admin/Dashboard.vue';
import AdminLettersIndex from './Pages/Admin/Letters/Index.vue';
import AdminTypesIndex from './Pages/Admin/Types/Index.vue';
import AdminUsersIndex from './Pages/Admin/Users/Index.vue';

const routes = [
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },

    // User routes
    { path: '/dashboard', name: 'dashboard', component: UserDashboard, meta: { auth: true } },
    { path: '/letters', name: 'user.letters.index', component: UserLettersIndex, meta: { auth: true } },
    { path: '/letters/create', name: 'user.letters.create', component: UserLettersCreate, meta: { auth: true } },

    // Admin routes
    { path: '/admin/dashboard', name: 'admin.dashboard', component: AdminDashboard, meta: { auth: true, admin: true } },
    { path: '/admin/letters', name: 'admin.letters.index', component: AdminLettersIndex, meta: { auth: true, admin: true } },
    { path: '/admin/types', name: 'admin.types.index', component: AdminTypesIndex, meta: { auth: true, admin: true } },
    { path: '/admin/users', name: 'admin.users.index', component: AdminUsersIndex, meta: { auth: true, admin: true } },

    // Catch-all redirect
    { path: '/', redirect: '/login' },
    { path: '/:pathMatch(.*)*', redirect: '/login' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard
router.beforeEach(async (to, from, next) => {
    // Try to get current user
    let user = null;
    try {
        const res = await window.axios.get('/api/user');
        user = res.data;
    } catch (e) {
        user = null;
    }

    if (to.meta.auth && !user) {
        return next({ name: 'login' });
    }

    if (to.meta.guest && user) {
        if (user.role === 'admin') {
            return next({ name: 'admin.dashboard' });
        }
        return next({ name: 'dashboard' });
    }

    // If admin route but user is not admin
    if (to.meta.admin && user && user.role !== 'admin') {
        return next({ name: 'dashboard' });
    }

    next();
});

export default router;
