import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Auth/LoginView.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../views/Auth/RegisterView.vue'),
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: { auth: true }
    },
    // Blog Routes
    {
        path: '/blog-post',
        name:'blog-list',
        component:()=>import('../views/Dashboard/Blog/blog-Post/index.vue')
    },
    {
        path: '/blog-post-create',
        name:'blog-post-create',
        component:()=>import('../views/Dashboard/Blog/blog-Post/create.vue')
    },
    {
        path: '/blog-post/:id/edit',
        name:'blog-post-edit',
        component:()=>import('../views/Dashboard/Blog/blog-Post/edit.vue')
    },
    // Blog Category Routes
    {
        path: '/blog-category',
        name:'blog-category-list',
        component:()=>import('../views/Dashboard/Blog/blog-category/index.vue')
    },
    {
        path: '/blog-category-create',
        name:'blog-category-create',
        component:()=>import('../views/Dashboard/Blog/blog-category/create.vue')
    },
    {
        path: '/blog-category/:id/edit',
        name:'blog-category-edit',
        component:()=>import('../views/Dashboard/Blog/blog-category/edit.vue')
    },
    {
        path: '/',
        redirect: '/dashboard'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    if (authStore.token && !authStore.user) {
        await authStore.fetchUser();
    }

    if (to.meta.auth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
