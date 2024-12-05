<template>
  <Head title="Recipe Book Manager" />
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gradient-to-b from-gray-100 to-gray-200 overflow-x-hidden">
      <!-- Header -->
      <header class="bg-gradient-to-r from-blue-600 to-green-600 shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
          <div class="grid grid-cols-6 gap-4 p-4 transform -rotate-12">
            <ChefHat v-for="i in 6" :key="`chef-${i}`" class="w-8 h-8 text-white" />
            <UtensilsCrossed v-for="i in 6" :key="`utensils-${i}`" class="w-8 h-8 text-white" />
            <Pizza v-for="i in 6" :key="`pizza-${i}`" class="w-8 h-8 text-white" />
          </div>
        </div>
        <div class="container mx-auto px-4 py-8 sm:py-12 relative">
          <div class="flex flex-col items-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-2 animate-fade-in text-center">
              Hello, {{ getRole($page.props.auth.user.role_id) }} {{ $page.props.auth.user.name }}!
            </h1>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-yellow-300 text-center leading-tight mt-2">
              Recipe Book Management
            </h2>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Statistics -->
          <section class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
              <h3 class="text-2xl font-bold text-white">Statistics</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4">
                <div v-for="stat in statistics" :key="stat.title" class="bg-gray-100 rounded-lg p-4">
                  <h4 class="text-lg font-semibold text-gray-700">{{ stat.title }}</h4>
                  <p class="text-3xl font-bold text-blue-600">{{ stat.value }}</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Activity Logs -->
          <section class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-600 px-6 py-4">
              <h3 class="text-2xl font-bold text-white">Activity Logs</h3>
            </div>
            <div class="p-6">
              <ul class="space-y-2">
                <li v-for="log in activityLogs" :key="log.id" class="text-gray-700">
                  {{ log.message }} - {{ formatDate(log.created_at) }}
                </li>
              </ul>
            </div>
          </section>
        </div>

        <!-- User Management -->
        <section class="mt-8 bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-yellow-600 px-6 py-4">
            <h3 class="text-2xl font-bold text-white">User Management</h3>
          </div>
          <div class="p-6 overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-100">
                  <th class="px-4 py-2 text-left">Name</th>
                  <th class="px-4 py-2 text-left">Email</th>
                  <th class="px-4 py-2 text-left">Role</th>
                  <th class="px-4 py-2 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id" class="border-t">
                  <td class="px-4 py-2">{{ user.name }}</td>
                  <td class="px-4 py-2">{{ user.email }}</td>
                  <td class="px-4 py-2">{{ getRole(user.role_id) }}</td>
                  <td class="px-4 py-2">
                    <button v-if="user.role_id !== 1" @click="editUser(user)" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                    <button v-if="user.role_id !== 1" @click="deleteUser(user.id)" class="text-red-600 hover:text-red-800">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Recipe Overview -->
        <section class="mt-8 bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-red-600 px-6 py-4">
            <h3 class="text-2xl font-bold text-white">Recipe Overview</h3>
          </div>
          <div class="p-6 overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-100">
                  <th class="px-4 py-2 text-left">Title</th>
                  <th class="px-4 py-2 text-left">Author</th>
                  <th class="px-4 py-2 text-left">Created At</th>
                  <th class="px-4 py-2 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="recipe in recipes" :key="recipe.id" class="border-t">
                  <td class="px-4 py-2">{{ recipe.title }}</td>
                  <td class="px-4 py-2">{{ recipe.author }}</td>
                  <td class="px-4 py-2">{{ formatDate(recipe.created_at) }}</td>
                  <td class="px-4 py-2">
                    <button @click="deleteRecipe(recipe.id)" class="text-red-600 hover:text-red-800">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  admins: Array,
  roles: Array,
  users: Array,
  recipes: Array,
  activityLogs: Array,
});

// Reactive data
const users = ref(props.users || []);
const recipes = ref(props.recipes || []);
const activityLogs = ref(props.activityLogs || []);

// Computed properties
const statistics = computed(() => [
  { title: 'Total Users', value: users.value.length },
  { title: 'Total Recipes', value: recipes.value.length },
  { title: 'Total Chefs', value: users.value.filter(u => u.role_id === 2).length },
  { title: 'Total Admins', value: users.value.filter(u => u.role_id === 1).length },
]);

// Methods
const getRole = (role_id) => {
  if (role_id === 1) {
    return "Admin";
  } else if (role_id === 2) {
    return "Chef";
  } else if (role_id === 3) {
    return "User";
  } else {
    return "Unknown";
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const editUser = (user) => {
  // Implement edit user logic
  console.log('Edit user:', user);
};

const deleteUser = (userId) => {
  // Implement delete user logic
  console.log('Delete user:', userId);
};

const deleteRecipe = (recipeId) => {
  // Implement delete recipe logic
  console.log('Delete recipe:', recipeId);
};
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fade-in {
  animation: fadeIn 0.5s ease-in-out;
}
</style>

