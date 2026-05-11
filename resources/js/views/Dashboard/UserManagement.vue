<template>
  <MainLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Access Control</h1>
          <p class="text-gray-500 dark:text-gray-400 text-sm">Manage users, roles and custom permissions</p>
        </div>

        <!-- Tab Switcher -->
        <div class="bg-gray-100 dark:bg-gray-800 p-1 rounded-xl flex gap-1">
          <button
            @click="activeTab = 'users'"
            class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all"
            :class="activeTab === 'users' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500'"
          >
            Users
          </button>
          <button
            @click="activeTab = 'permissions'"
            class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all"
            :class="activeTab === 'permissions' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500'"
          >
            Permissions
          </button>
        </div>
      </div>

      <!-- USERS TAB -->
      <div v-if="activeTab === 'users'" class="space-y-6">
        <div class="flex justify-between items-center">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search users..."
              class="pl-10 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary outline-none transition-all w-64 text-sm"
            />
          </div>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
          <Loader2 class="animate-spin text-primary" :size="40" />
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="user in filteredUsers"
            :key="user.id"
            class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition-all group"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl">
                  {{ user.full_name.charAt(0) }}
                </div>
                <div>
                  <h3 class="font-bold text-gray-900 dark:text-white">{{ user.full_name }}</h3>
                  <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>
              </div>
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                :class="getRoleClass(user.role)"
              >
                {{ user.role }}
              </span>
            </div>

            <div class="space-y-3 min-h-[40px]">
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="perm in (user.permissions || [])"
                  :key="perm.id"
                  class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded text-[10px] font-medium"
                >
                  {{ perm.name }}
                </span>
                <span v-if="!user.permissions || user.permissions.length === 0" class="text-[10px] text-gray-400 italic">No custom permissions</span>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-50 dark:border-gray-800 flex justify-end">
              <button
                @click="editUser(user)"
                class="flex items-center gap-2 text-sm font-semibold text-primary hover:bg-primary/10 px-3 py-1.5 rounded-lg transition-colors"
              >
                <ShieldCheck :size="16" />
                Manage Access
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- PERMISSIONS TAB -->
      <div v-if="activeTab === 'permissions'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-bold">Dynamic Permissions</h2>
          <button
            @click="isPermModalOpen = true"
            class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-primary-dark transition-all"
          >
            <Plus :size="18" />
            Create Permission
          </button>
        </div>

        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
          <table class="w-full text-left">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Name</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Slug</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Description</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="perm in availablePermissions" :key="perm.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                      <Lock :size="16" />
                    </div>
                    <span class="font-bold text-sm">{{ perm.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <code class="text-[11px] bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-primary">{{ perm.slug }}</code>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ perm.description || 'No description' }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="deletePermission(perm.id)" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                    <Trash2 :size="18" />
                  </button>
                </td>
              </tr>
              <tr v-if="availablePermissions.length === 0">
                <td colspan="4" class="px-6 py-10 text-center text-gray-500">No permissions found. Create one to get started.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manage Access</h2>
            <p class="text-sm text-gray-500">{{ editingUser.full_name }}</p>
          </div>
          <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <X :size="24" />
          </button>
        </div>

        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
          <div>
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider">User Role</label>
            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="role in ['admin', 'counselor', 'student']"
                :key="role"
                @click="editingUser.role = role"
                class="px-4 py-3 rounded-xl border-2 text-sm font-bold transition-all text-center capitalize"
                :class="editingUser.role === role
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-gray-100 dark:border-gray-800 text-gray-500 hover:border-gray-200'"
              >
                {{ role }}
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider">Custom Permissions</label>
            <div class="grid grid-cols-1 gap-2">
              <div
                v-for="perm in availablePermissions"
                :key="perm.id"
                @click="togglePermission(perm.id)"
                class="flex items-center justify-between p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors"
                :class="hasPermission(perm.id) ? 'border-primary/30 bg-primary/5' : ''"
              >
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="hasPermission(perm.id) ? 'bg-primary/20 text-primary' : 'bg-gray-100 dark:bg-gray-800 text-gray-400'">
                    <Lock :size="18" />
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ perm.name }}</p>
                    <p class="text-[11px] text-gray-500">{{ perm.description }}</p>
                  </div>
                </div>
                <div class="w-10 h-5 rounded-full relative bg-gray-300 dark:bg-gray-700 transition-colors" :class="{ 'bg-primary': hasPermission(perm.id) }">
                   <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform" :class="{ 'translate-x-5': hasPermission(perm.id) }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex gap-3">
          <button @click="isModalOpen = false" class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-600">Cancel</button>
          <button @click="saveUserChanges" :disabled="saving" class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-bold flex justify-center items-center gap-2">
            <Loader2 v-if="saving" class="animate-spin" :size="18" />
            <span v-else>Save Changes</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Create Permission Modal -->
    <div v-if="isPermModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-xl font-bold">Create New Permission</h2>
        </div>
        <form @submit.prevent="saveNewPermission" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Permission Name</label>
            <input
              v-model="newPerm.name"
              type="text"
              placeholder="e.g. Manage Universities"
              required
              class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Description</label>
            <textarea
              v-model="newPerm.description"
              rows="3"
              placeholder="What does this permission allow?"
              class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-primary"
            ></textarea>
          </div>
          <div class="flex gap-3 pt-4">
            <button type="button" @click="isPermModalOpen = false" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-bold">Cancel</button>
            <button type="submit" :disabled="savingPerm" class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-bold flex justify-center items-center gap-2">
              <Loader2 v-if="savingPerm" class="animate-spin" :size="18" />
              <span v-else>Create</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import {
  Search, Users, ShieldCheck, X, Loader2, Plus, Lock, Trash2
} from 'lucide-vue-next';

const activeTab = ref('users');
const users = ref([]);
const availablePermissions = ref([]);
const loading = ref(true);
const saving = ref(false);
const savingPerm = ref(false);
const searchQuery = ref('');
const isModalOpen = ref(false);
const isPermModalOpen = ref(false);
const editingUser = ref(null);
const newPerm = ref({ name: '', description: '' });

const fetchData = async () => {
  loading.value = true;
  try {
    const [usersRes, permsRes] = await Promise.all([
      axios.get('/auth/admin/users'),
      axios.get('/auth/admin/permissions')
    ]);
    users.value = usersRes.data.data;
    availablePermissions.value = permsRes.data.data;
  } catch (error) {
    console.error('Failed to fetch data', error);
  } finally {
    loading.value = false;
  }
};

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value;
  const query = searchQuery.value.toLowerCase();
  return users.value.filter(u =>
    u.full_name.toLowerCase().includes(query) ||
    u.email.toLowerCase().includes(query)
  );
});

const getRoleClass = (role) => {
  switch (role) {
    case 'admin': return 'bg-red-100 text-red-600 dark:bg-red-500/10';
    case 'counselor': return 'bg-blue-100 text-blue-600 dark:bg-blue-500/10';
    default: return 'bg-gray-100 text-gray-600 dark:bg-gray-500/10';
  }
};

const editUser = (user) => {
  editingUser.value = JSON.parse(JSON.stringify(user));
  // Extract permission IDs from the full permission objects
  if (editingUser.value.permissions && Array.isArray(editingUser.value.permissions)) {
    editingUser.value.permissions = editingUser.value.permissions.map(p => p.id);
  } else {
    editingUser.value.permissions = [];
  }
  isModalOpen.value = true;
};

const hasPermission = (id) => {
  return editingUser.value?.permissions?.includes(id);
};

const togglePermission = (id) => {
  const index = editingUser.value.permissions.indexOf(id);
  if (index === -1) {
    editingUser.value.permissions.push(id);
  } else {
    editingUser.value.permissions.splice(index, 1);
  }
};

const saveUserChanges = async () => {
  saving.value = true;
  try {
    await axios.put(`/auth/admin/users/${editingUser.value.id}/role`, { role: editingUser.value.role });
    await axios.put(`/auth/admin/users/${editingUser.value.id}/permissions`, { permissions: editingUser.value.permissions });
    await fetchData();
    isModalOpen.value = false;
  } catch (error) {
    console.error('Failed to save changes', error);
  } finally {
    saving.value = false;
  }
};

const saveNewPermission = async () => {
  savingPerm.value = true;
  try {
    await axios.post('/auth/admin/permissions', newPerm.value);
    newPerm.value = { name: '', description: '' };
    isPermModalOpen.value = false;
    await fetchData();
  } catch (error) {
    console.error('Failed to create permission', error);
  } finally {
    savingPerm.value = false;
  }
};

const deletePermission = async (id) => {
  if (!confirm('Are you sure you want to delete this permission?')) return;
  try {
    await axios.delete(`/auth/admin/permissions/${id}`);
    await fetchData();
  } catch (error) {
    console.error('Failed to delete permission', error);
  }
};

onMounted(fetchData);
</script>
