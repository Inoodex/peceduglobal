<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useRouter } from 'vue-router';
import { Settings, Info, Eye, EyeOff, Loader2 } from 'lucide-vue-next';

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
    email: '',
    password: '',
});

const showPassword = ref(false);

const handleSubmit = async () => {
    try {
        await authStore.login(form.value);
        router.push('/dashboard');
    } catch (error) {
        // Error handled in store
    }
};
</script>

<template>
    <div class="min-h-screen flex bg-white">
        <!-- Left Column - Illustration -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-gray-50 via-gray-50 to-gray-100 flex-col justify-between p-12">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">M</span>
                </div>
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-md">
                <h1 class="text-3xl font-bold text-gray-900 leading-tight mb-2">Hi, Welcome back</h1>
                <p class="text-gray-600 text-sm">More effectively with optimized workflows.</p>

                <!-- Illustration placeholder -->
                <div class="mt-12">
                    <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                        <div class="grid grid-cols-4 gap-2">
                            <div class="col-span-2 row-span-2 bg-gradient-to-br from-blue-100 to-blue-50 rounded-lg p-3"></div>
                            <div class="bg-gradient-to-br from-orange-100 to-orange-50 rounded-lg p-3"></div>
                            <div class="bg-gradient-to-br from-yellow-100 to-yellow-50 rounded-lg p-3"></div>
                            <div class="bg-gradient-to-br from-green-100 to-green-50 rounded-lg p-3"></div>
                            <div class="bg-gradient-to-br from-purple-100 to-purple-50 rounded-lg p-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom icons -->
            <div class="flex items-center space-x-3 opacity-40">
                <div class="w-5 h-5 bg-orange-500 rounded-full"></div>
                <div class="w-5 h-5 bg-blue-500 rounded-full"></div>
                <div class="w-5 h-5 bg-yellow-500 rounded-full"></div>
                <div class="w-5 h-5 bg-green-500 rounded-full"></div>
                <div class="w-5 h-5 bg-purple-500 rounded-full"></div>
            </div>
        </div>

        <!-- Right Column - Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 py-12 lg:px-20">
            <!-- Top right help -->
            <!-- <div class="flex justify-end mb-6">
                <div class="flex items-center text-gray-500 cursor-pointer hover:text-gray-900 transition-colors">
                    <span class="text-sm mr-2">Need help?</span>
                    <Settings class="w-4 h-4" />
                </div>
            </div> -->

            <div class="max-w-sm">
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">Sign in to your account</h2>
                <p class="text-gray-500 text-sm mb-5">
                    Don't have an account?
                    <router-link to="/register" class="text-emerald-600 font-medium hover:text-emerald-700">
                        Get started
                    </router-link>
                </p>

                <!-- Info box -->
                <div class="bg-cyan-50 border border-cyan-100 rounded-xl p-4 mb-5 flex items-start">
                    <Info class="w-4 h-4 text-cyan-600 mr-2.5 mt-0.5 shrink-0" />
                    <p class="text-xs text-cyan-800 leading-relaxed">
                        Use user@gmail.com with password @password.
                    </p>
                </div>

                <!-- Error message -->
                <div v-if="authStore.error" class="bg-red-50 text-red-600 p-3 rounded-lg text-xs mb-5">
                    {{ authStore.error }}
                </div>

                <form class="space-y-4" @submit.prevent="handleSubmit">
                    <!-- Email field -->
                    <div>
                        <label for="email-address" class="block text-xs font-medium text-gray-700 mb-1.5">
                            Email address
                        </label>
                        <input
                            id="email-address"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent text-gray-900 placeholder-gray-400 text-sm"
                            placeholder="demo@gmail.com"
                        >
                    </div>

                    <!-- Password field -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="password" class="block text-xs font-medium text-gray-700">
                                Password
                            </label>
                            <a href="#" class="text-xs text-gray-500 hover:text-gray-900">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent text-gray-900 placeholder-gray-400 pr-10 text-sm"
                                placeholder="••••••••"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <Eye v-if="!showPassword" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Sign in button -->
                    <button
                        type="submit"
                        :disabled="authStore.loading"
                        class="w-full bg-gray-900 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:cursor-pointer"
                    >
                        <span v-if="authStore.loading" class="flex items-center justify-center">
                            <Loader2 class="animate-spin w-4 h-4 mr-2" />
                            Signing in...
                        </span>
                        <span v-else>Sign in</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
