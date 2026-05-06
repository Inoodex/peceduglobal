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
    // Page Manager Routes
    {
        path: '/dashboard/page-manager',
        name: 'page-manager',
        component: () => import('../views/Dashboard/PageManager/index.vue'),
        meta: { auth: true }
    },
    {
        path: '/dashboard/page-manager/create',
        name: 'page-manager-create',
        component: () => import('../views/Dashboard/PageManager/create.vue'),
        meta: { auth: true }
    },
    {
        path: '/dashboard/page-manager/edit/:id',
        name: 'page-manager-edit',
        component: () => import('../views/Dashboard/PageManager/edit.vue'),
        meta: { auth: true }
    },
    // Block Manager Routes
    {
        path: '/dashboard/block-manager',
        name: 'block-manager',
        component: () => import('../views/Dashboard/BlockManager/index.vue'),
        meta: { auth: true }
    },
    {
        path: '/dashboard/block-manager/create',
        name: 'block-manager-create',
        component: () => import('../views/Dashboard/BlockManager/create.vue'),
        meta: { auth: true }
    },
    {
        path: '/dashboard/block-manager/edit/:id',
        name: 'block-manager-edit',
        component: () => import('../views/Dashboard/BlockManager/edit.vue'),
        meta: { auth: true }
    },
    // Country Manager Routes
    {
        path: '/dashboard/country-manager',
        name: 'country-manager',
        component: () => import('../views/Dashboard/CountryManager/index.vue'),
        meta: { auth: true }
    },
    {
        path: '/dashboard/country-manager/create',
        name: 'country-manager-create',
        component: () => import('../views/Dashboard/CountryManager/create.vue'),
        meta: { auth: true }
    },
    {
        path: '/dashboard/country-manager/edit/:id',
        name: 'country-manager-edit',
        component: () => import('../views/Dashboard/CountryManager/edit.vue'),
        meta: { auth: true }
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
