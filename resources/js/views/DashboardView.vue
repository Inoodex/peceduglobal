<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { LogOut, User, Mail, Calendar } from 'lucide-vue-next';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-indigo-600">Laravel Vue SPA</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ authStore.user?.name }}</span>
                        <button @click="handleLogout" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition ease-in-out duration-150">
                            <LogOut class="h-4 w-4 mr-2" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <div class="flex items-center space-x-4 mb-8">
                        <div class="h-20 w-20 bg-indigo-100 rounded-full flex items-center justify-center">
                            <User class="h-10 w-10 text-indigo-600" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Welcome, {{ authStore.user?.name }}!</h2>
                            <p class="text-gray-500">You are successfully logged in.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-4 flex items-center space-x-3">
                            <Mail class="h-5 w-5 text-gray-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email Address</p>
                                <p class="text-gray-900">{{ authStore.user?.email }}</p>
                            </div>
                        </div>
                        <div class="border rounded-lg p-4 flex items-center space-x-3">
                            <Calendar class="h-5 w-5 text-gray-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-500">Member Since</p>
                                <p class="text-gray-900">{{ new Date(authStore.user?.created_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
