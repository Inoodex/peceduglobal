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
        component:()=>import('../views/Dashboard/Blog/blog-Post/index.vue'),
        meta: { auth: true, permission: 'manage_blogs' }
    },
    {
        path: '/blog-post-create',
        name:'blog-post-create',
        component:()=>import('../views/Dashboard/Blog/blog-Post/create.vue'),
        meta: { auth: true, permission: 'manage_blogs' }
    },
    {
        path: '/blog-post/:id/edit',
        name:'blog-post-edit',
        component:()=>import('../views/Dashboard/Blog/blog-Post/edit.vue'),
        meta: { auth: true, permission: 'manage_blogs' }
    },
    // Blog Category Routes
    {
        path: '/blog-category',
        name:'blog-category-list',
        component:()=>import('../views/Dashboard/Blog/blog-category/index.vue'),
        meta: { auth: true, permission: 'manage_blogs' }
    },
    {
        path: '/blog-category-create',
        name:'blog-category-create',
        component:()=>import('../views/Dashboard/Blog/blog-category/create.vue'),
        meta: { auth: true, permission: 'manage_blogs' }
    },
    {
        path: '/blog-category/:id/edit',
        name:'blog-category-edit',
        component:()=>import('../views/Dashboard/Blog/blog-category/edit.vue'),
        meta: { auth: true, permission: 'manage_blogs' }
    },
    // Page Manager Routes
    {
        path: '/dashboard/page-manager',
        name: 'page-manager',
        component: () => import('../views/Dashboard/PageManager/index.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    {
        path: '/dashboard/page-manager/create',
        name: 'page-manager-create',
        component: () => import('../views/Dashboard/PageManager/create.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    {
        path: '/dashboard/page-manager/edit/:id',
        name: 'page-manager-edit',
        component: () => import('../views/Dashboard/PageManager/edit.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    // Block Manager Routes
    {
        path: '/dashboard/block-manager',
        name: 'block-manager',
        component: () => import('../views/Dashboard/BlockManager/index.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    {
        path: '/dashboard/block-manager/create',
        name: 'block-manager-create',
        component: () => import('../views/Dashboard/BlockManager/create.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    {
        path: '/dashboard/block-manager/edit/:id',
        name: 'block-manager-edit',
        component: () => import('../views/Dashboard/BlockManager/edit.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    // Element Manager Routes
    {
        path: '/dashboard/element-manager',
        name: 'element-manager',
        component: () => import('../views/Dashboard/ElementManager/index.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    {
        path: '/dashboard/element-manager/create',
        name: 'element-manager-create',
        component: () => import('../views/Dashboard/ElementManager/create.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    {
        path: '/dashboard/element-manager/edit/:id',
        name: 'element-manager-edit',
        component: () => import('../views/Dashboard/ElementManager/edit.vue'),
        meta: { auth: true, permission: 'manage_pages' }
    },
    // Country Manager Routes
    {
        path: '/dashboard/country-manager',
        name: 'country-manager',
        component: () => import('../views/Dashboard/CountryManager/index.vue'),
        meta: { auth: true, permission: 'manage_countries' }
    },
    {
        path: '/dashboard/country-manager/create',
        name: 'country-manager-create',
        component: () => import('../views/Dashboard/CountryManager/create.vue'),
        meta: { auth: true, permission: 'manage_countries' }
    },
    {
        path: '/dashboard/country-manager/edit/:id',
        name: 'country-manager-edit',
        component: () => import('../views/Dashboard/CountryManager/edit.vue'),
        meta: { auth: true, permission: 'manage_countries' }
    },
    // University Manager Routes
    {
        path: '/dashboard/university-manager',
        name: 'university-manager',
        component: () => import('../views/Dashboard/Education/University/index.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/university-manager/create',
        name: 'university-manager-create',
        component: () => import('../views/Dashboard/Education/University/create.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/university-manager/edit/:id',
        name: 'university-manager-edit',
        component: () => import('../views/Dashboard/Education/University/create.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    // Course Manager Routes
    {
        path: '/dashboard/course-manager',
        name: 'course-manager',
        component: () => import('../views/Dashboard/Education/Course/index.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/course-manager/create',
        name: 'course-manager-create',
        component: () => import('../views/Dashboard/Education/Course/create.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/course-manager/edit/:id',
        name: 'course-manager-edit',
        component: () => import('../views/Dashboard/Education/Course/create.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/course-level-manager',
        name: 'course-level-manager',
        component: () => import('../views/Dashboard/Education/CourseLevel/index.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/course-intakes',
        name: 'course-intakes',
        component: () => import('../views/Dashboard/Education/CourseIntake/index.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/course-intakes/create',
        name: 'course-intakes-create',
        component: () => import('../views/Dashboard/Education/CourseIntake/create.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },
    {
        path: '/dashboard/course-intakes/edit/:id',
        name: 'course-intakes-edit',
        component: () => import('../views/Dashboard/Education/CourseIntake/create.vue'),
        meta: { auth: true, permission: 'manage_education' }
    },

    {
        path: '/dashboard/students',
        name: 'student-list',
        component: () => import('../views/Dashboard/Consultancy/Student/index.vue'),
        meta: { auth: true, permission: 'edit_student' }
    },
    {
        path: '/dashboard/students/create',
        name: 'student-create',
        component: () => import('../views/Dashboard/Consultancy/Student/create.vue'),
        meta: { auth: true, permission: 'edit_student' }
    },
    {
        path: '/dashboard/applications',
        name: 'application-list',
        component: () => import('../views/Dashboard/Consultancy/Application/index.vue'),
        meta: { auth: true, permission: 'view_applications' }
    },
    {
        path: '/dashboard/applications/create',
        name: 'application-create',
        component: () => import('../views/Dashboard/Consultancy/Application/create.vue'),
        meta: { auth: true, permission: 'view_applications' }
    },
    {
        path: '/dashboard/user-management',
        name: 'user-management',
        component: () => import('../views/Dashboard/UserManagement.vue'),
        meta: { auth: true, role: 'admin' }
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
    } else if (to.meta.permission) {
        const userRole = authStore.user?.role;
        const userPermissions = authStore.user?.permissions?.map((perm) => perm.slug) || [];
        if (userRole !== 'admin' && !userPermissions.includes(to.meta.permission)) {
            next('/dashboard');
            return;
        }
        next();
    } else if (to.meta.role && authStore.user?.role !== to.meta.role && authStore.user?.role !== 'admin') {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
