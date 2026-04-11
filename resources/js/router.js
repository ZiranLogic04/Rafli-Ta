import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";

import Login from "./Pages/Auth/Login.vue";
import PublicTemplates from "./Pages/Public/Templates.vue";

import Dashboard from "./Pages/Dashboard.vue";
import UserLettersIndex from "./Pages/User/Letters/Index.vue";
import UserLettersCreate from "./Pages/User/Letters/Create.vue";
import UserApprovalsIndex from "./Pages/User/Approvals/Index.vue";

import AdminTypesIndex from "./Pages/Admin/Types/Index.vue";
import AdminUsersIndex from "./Pages/Admin/Users/Index.vue";

const routes = [
    { path: "/login", name: "login", component: Login, meta: { guest: true } },
    {
        path: "/templates",
        name: "public.templates",
        component: PublicTemplates,
        meta: { public: true },
    },

    // User routes
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
        meta: { auth: true },
    },
    {
        path: "/letters",
        name: "user.letters.index",
        component: UserLettersIndex,
        meta: { auth: true },
    },
    {
        path: "/letters/create",
        name: "user.letters.create",
        component: UserLettersCreate,
        meta: { auth: true },
    },
    {
        path: "/approvals",
        name: "user.approvals.index",
        component: UserApprovalsIndex,
        meta: { auth: true },
    },

    // Admin routes
    {
        path: "/admin/dashboard",
        name: "admin.dashboard",
        redirect: "/dashboard",
        meta: { auth: true, admin: true },
    },
    {
        path: "/admin/types",
        name: "admin.types.index",
        component: AdminTypesIndex,
        meta: { auth: true, admin: true },
    },
    {
        path: "/admin/users",
        name: "admin.users.index",
        component: AdminUsersIndex,
        meta: { auth: true, admin: true },
    },

    // Catch-all redirect
    { path: "/", redirect: "/dashboard" },
    { path: "/:pathMatch(.*)*", redirect: "/dashboard" },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Cache user to avoid repeated API calls
let cachedUser = null;
let userFetched = false;

const fetchUser = async () => {
    if (userFetched) return cachedUser;
    try {
        const res = await window.axios.get("/api/user");
        cachedUser = res.data;
        userFetched = true;
        return cachedUser;
    } catch {
        userFetched = true;
        return null;
    }
};

const resetUserCache = () => {
    cachedUser = null;
    userFetched = false;
};

// Navigation guard
router.beforeEach(async (to, from, next) => {
    const user = await fetchUser();

    if (to.meta.auth && !user) {
        return next({ name: "login" });
    }

    if (to.meta.guest && user) {
        return next({ name: "dashboard" });
    }

    if (to.meta.admin && user && user.role !== "admin") {
        return next({ name: "dashboard" });
    }

    next();
});

export { resetUserCache };
export default router;
