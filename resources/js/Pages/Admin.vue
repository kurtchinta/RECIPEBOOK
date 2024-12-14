<template>
  <Head title="Recipe Book Manager" />
  <AuthenticatedLayout>
    <div class="flex h-screen bg-gray-100">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-md">
        <div class="p-6 flex flex-col items-center">
          <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=random`" alt="User Avatar" class="w-24 h-24 rounded-full mb-4 border-4 border-green-500 shadow-lg" />
          <h2 class="text-xl font-bold text-gray-800">{{ $page.props.auth.user.name }}</h2>
          <p class="text-sm text-gray-600 mb-2">{{ $page.props.auth.user.email }}</p>
          <p class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
            {{ getRole($page.props.auth.user.role_id) }}
          </p>
        </div>
        <nav class="mt-6">
          <a v-for="item in navItems" :key="item.name" :href="item.href" 
             class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
            <component :is="item.icon" class="h-5 w-5 mr-3" />
            {{ item.name }}
          </a>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
        <div class="container mx-auto px-6 py-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>
          
          <!-- Dashboard Stats -->
          <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div
              v-for="stat in computedStatistics"
              :key="stat.title"
              class="bg-white p-6 rounded-xl shadow-lg transform hover:scale-105 hover:shadow-xl transition-all duration-300"
            >
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-700">{{ stat.title }}</h3>
                <component :is="stat.icon" class="h-8 w-8 text-gray-500" />
              </div>
              <p class="text-4xl font-extrabold text-gray-800">{{ stat.value }}</p>
            </div>
          </section>

          <!-- Chef Recent Recipe -->
<section id="recent-recipes" class="mb-12">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold text-gray-800">Recent Chef Recipe</h2>
    </div>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              <th class="px-6 py-3">Chef Name</th>
              <th class="px-6 py-3">Recent Recipe</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="chef in filteredChefs" :key="chef.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img class="h-10 w-10 rounded-full" :src="`https://ui-avatars.com/api/?name=${chef.name}&background=random`" :alt="chef.name" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ chef.name }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ chef.recent_recipe }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

          <!-- User Management -->
          <section id="users" class="mb-12">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-3xl font-bold text-gray-800">User Management</h2>
              <button @click="showAddUserModal = true" 
                      class="bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center shadow-lg">
                <PlusIcon class="h-5 w-5 mr-2" />
                Add User
              </button>
            </div>
            <div class="grid grid-cols-1 gap-6 mb-10">
              <div v-for="role in ['admin', 'chef', 'user']" :key="role" class="bg-white p-4 rounded-lg shadow-md overflow-x-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 capitalize">{{ role }}s</h2>
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(user, index) in groupedUsers[role]" :key="user.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" :src="`https://ui-avatars.com/api/?name=${user.name}&background=random`" alt="" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="{
                          'bg-green-100 text-green-800': getRole(user.role_id) === 'Admin',
                          'bg-blue-100 text-blue-800': getRole(user.role_id) === 'Chef',
                          'bg-yellow-100 text-yellow-800': getRole(user.role_id) === 'User'
                        }" class="px-3 py-1 rounded-full text-xs font-medium">
                          {{ getRole(user.role_id) }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button
                          v-if="user.role_id !== 1"
                          @click="editUser(user)"
                          class="text-blue-600 hover:text-blue-900 mr-3 transition-colors duration-300"
                        >
                          <PencilIcon class="h-5 w-5" />
                        </button>
                        <button
                          v-if="user.role_id !== 1"
                          @click="deleteUser(user.id)"
                          class="text-red-600 hover:text-red-900 transition-colors duration-300"
                        >
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- Recipe Overview -->
          <section id="recipes" class="mb-12">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-3xl font-bold text-gray-800">Recipe Overview</h2>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipe Name</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chef</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="recipe in recipes" :key="recipe.recipe_id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">{{ recipe.recipe_name }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="getCategoryClass(recipe.category_name)" class="px-3 py-1 rounded-full text-xs font-medium">
                          {{ recipe.category_name }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ recipe.chef_name }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(recipe.created_at) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button
                          @click="deleteRecipe(recipe.recipe_id)"
                          class="text-red-600 hover:text-red-900 transition-colors ms-5 duration-300"
                        >
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- Activity Logs -->
          <section id="activity" class="mb-12">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-3xl font-bold text-gray-800">Activity Logs</h2>
              <button @click="refreshActivityLogs" 
                      class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center shadow-lg">
                <ArrowPathIcon class="h-5 w-5 mr-2" />
                Refresh Logs
              </button>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                      <th class="px-6 py-3">Activity</th>
                      <th class="px-6 py-3">Date</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="log in activityLogs" :key="log.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <component :is="getActivityIcon(log.action)" class="h-6 w-6 text-gray-500" />
                          </div>
                          <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">{{ log.action }} by {{ log.user.name }}</p>
                            <p class="text-xs text-gray-500">{{ log.user.email }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatDate(log.created_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>
        </div>
      </main>
    </div>

    <!-- Confirmation Modal -->
    <TransitionRoot appear :show="showConfirmModal" as="template">
      <Dialog as="div" @close="closeConfirmModal" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/75" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                  {{ confirmModalConfig.title }}
                </DialogTitle>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">{{ confirmModalConfig.message }}</p>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button
                    @click="closeConfirmModal"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                  >
                    Cancel
                  </button>
                  <button
                    @click="confirmAction"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                  >
                    Confirm
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Add/Edit User Modal -->
    <TransitionRoot appear :show="showAddUserModal" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">
        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                  {{ editingUser ? 'Edit User' : 'Add New User' }}
                </DialogTitle>
                <form @submit.prevent="submitUser" class="mt-4 space-y-4">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input
                      type="text"
                      id="name"
                      v-model="userForm.name"
                      required
                      :disabled="editingUser"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      placeholder="Enter name"
                    />
                  </div>
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                      type="email"
                      id="email"
                      v-model="userForm.email"
                      required
                      :disabled="editingUser"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      placeholder="Enter email"
                    />
                  </div>

                  <!-- Only show password field for adding a new user -->
                  <div v-if="!editingUser">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                      type="password"
                      id="password"
                      v-model="userForm.password"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      placeholder="Enter password"
                    />
                  </div>

                  <!-- Role Selection Dropdown -->
                  <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select
                      id="role"
                      v-model="userForm.role_id"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                      <option value="" disabled>Select a role</option>
                      <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.role_user }}</option>
                    </select>
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="closeModal"
                      class="inline-flex justify-center rounded-md border border-transparent bg-gray-200 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                    >
                      {{ editingUser ? 'Update User' : 'Add User' }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </AuthenticatedLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { 
  ChartBarIcon, 
  ClockIcon, 
  ArrowPathIcon, 
  UserGroupIcon, 
  PlusIcon, 
  PencilIcon, 
  TrashIcon,
  XMarkIcon,
  UserIcon,
  ShieldCheckIcon,
  BookOpenIcon,
} from '@heroicons/vue/24/solid';

// Define the props that are passed from the backend
const props = defineProps({
  statistics: {
    type: Array,
    default: () => ({
      total_users: 0,
      total_recipes: 0,
      total_chefs: 0,
      total_admins: 0,
    }),
    
  },
  users: {
    type: Array,
    default: () => [],
  },
  roles: {
    type: Array,
    default: () => [],
  },

  recipes: {
    type: Array,
    default: () => [], 
  },
  recentRecipes: {
    type: Array,
    default: () => [], 
  }
});

const filteredChefs = computed(() => {
  // First, map recentRecipes by user_id for easier lookup
  const recentRecipesMap = props.recentRecipes.reduce((map, recipe) => {
    map[recipe.user_id] = recipe.recent_recipe;
    return map;
  }, {});

  // Filter chefs and add the recent recipe to each chef
  return props.users
    .filter(user => user.role.id === 2) // Assuming '2' is the chef role ID
    .map(chef => ({
      ...chef,
      recent_recipe: recentRecipesMap[chef.id] || 'No recent recipes available',
    }));
});

const recipes = ref(props.recipes); // Initialize the recipes from props

// Delete a recipe
const deleteRecipe = (id) => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Delete Recipe',
    message: 'Are you sure you want to delete this recipe?',
    onConfirm: () => {
      // Update the URL to match the correct route for deletion
      router.delete(`/admin/recipes/${id}`, {
        onSuccess: () => {
          recipes.value = recipes.value.filter(recipe => recipe.recipe_id !== id);
          showAlert("Recipe deleted successfully!", "success");
        },
        onError: (error) => {
          console.error('Error deleting recipe:', error);
          showAlert('Failed to delete recipe. Please try again.', "error");
        },
      });
    }
  };
};

// Map statistics to dashboard cards
const computedStatistics = computed(() => [
  { title: "Total Users", value: props.statistics[0].total_users, icon: UserIcon },
  { title: "Total Recipes", value: props.statistics[0].total_recipes, icon: BookOpenIcon },
  { title: "Total Chefs", value: props.statistics[0].total_chefs, icon: UserGroupIcon },
  { title: "Total Admins", value: props.statistics[0].total_admins, icon: ShieldCheckIcon },
]);

const activityLogs = ref(props.activityLogs);
const showAddUserModal = ref(false);
const editingUser = ref(null);

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role_id: null,
});

// Confirmation modal
const showConfirmModal = ref(false);
const confirmModalConfig = ref({
  title: '',
  message: '',
  onConfirm: null
});

// Navigation items
const navItems = [
  { name: 'Dashboard', href: '#statistics', icon: ChartBarIcon },
  { name: 'Users', href: '#users', icon: UserGroupIcon },
  { name: 'Recipes', href: '#recipes', icon: BookOpenIcon },
  { name: 'Activity', href: '#activity', icon: ClockIcon },
];

const sortedUsers = computed(() =>
  props.users.slice().sort((a, b) => a.role_id - b.role_id)
);

const groupedUsers = computed(() => {
  const grouped = {
    admin: [],
    chef: [],
    user: []
  };
  props.users.forEach(user => {
    const role = getRole(user.role_id).toLowerCase();
    if (grouped[role]) {
      grouped[role].push(user);
    }
  });
  return grouped;
});

// Methods
const getRole = (role_id) => {
  const role = props.roles.find(r => r.id === role_id);
  return role ? role.role_user : "Unknown";
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const closeConfirmModal = () => {
  showConfirmModal.value = false;
  confirmModalConfig.value = {
    title: '',
    message: '',
    onConfirm: null
  };
};

const confirmAction = () => {
  if (confirmModalConfig.value.onConfirm) {
    confirmModalConfig.value.onConfirm();
  }
  closeConfirmModal();
};

const showAlert = (message, type) => {
  // Implement your alert logic here
  console.log(`${type}: ${message}`);
};

const deleteUser = (userId) => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Delete User',
    message: 'Are you sure you want to delete this user?',
    onConfirm: () => {
      router.delete(route("admin.deleteUser", { user: userId }), {
        onSuccess: () => {
          props.users = props.users.filter((user) => user.id !== userId);
          showAlert("User deleted successfully!", "success");
        },
        onError: (error) => {
          console.error("Error deleting user:", error);
          showAlert("Error: Unable to delete the user.", "error");
        },
      });
    }
  };
};

const editUser = (user) => {
  if (getRole(user.role_id) === "Admin") {
    showAlert("Admin roles cannot be edited or demoted.", "warning");
    return;
  }

  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Edit User',
    message: 'Are you sure you want to edit this user?',
    onConfirm: () => {
      showAddUserModal.value = true;
      editingUser.value = user;
      userForm.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        role_id: user.role_id,
        password: ""
      };
    }
  };
};

const submitUser = () => {
  if (editingUser.value) {
    if (
      getRole(editingUser.value.role_id) === "Admin" &&
      userForm.value.role_id !== editingUser.value.role_id
    ) {
      showAlert("Once promoted to Admin, users cannot be demoted.", "warning");
      return;
    }

    router.put(route("admin.updateUserRole", { user: userForm.value.id }), {
      role_id: userForm.value.role_id,
    }, {
      onSuccess: () => {
        const userIndex = props.users.findIndex((u) => u.id === userForm.value.id);
        if (userIndex > -1) {
          props.users[userIndex] = { ...userForm.value };
        }
        closeModal();
        showAlert("User updated successfully!", "success");
      },
      onError: () => {
        showAlert("There was an error updating the user.", "error");
      },
    });
  } else {
    router.post(route("admin.addUser"), userForm.value, {
      onSuccess: () => {
        const updatedUsers = [...props.users, { ...userForm.value }];
        props.users = updatedUsers;
        closeModal();
        showAlert("User added successfully!", "success");
      },
      onError: (errors) => {
        showAlert("There was an error adding the user: " + Object.values(errors).join(", "), "error");
      },
    });
  }
};

const closeModal = () => {
  showAddUserModal.value = false;
  editingUser.value = null;
  userForm.value = { id: "", name: "", email: "", password: "", role_id: "" };
};

const refreshActivityLogs = () => {
  router.get(route("admin.getActivityLogs"), {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      activityLogs.value = page.props.activityLogs;
      showAlert("Activity logs refreshed successfully!", "success");
    },
    onError: (error) => {
      console.error("Error refreshing activity logs:", error);
      showAlert("Error: Unable to refresh activity logs.", "error");
    },
  });
};

const getActivityIcon = (type) => {
  switch (type) {
    case 'user':
      return UserIcon;
    case 'recipe':
      return BookOpenIcon;
    default:
      return ClockIcon;
  }
};

const getCategoryClass = (category) => {
  switch (category.toLowerCase()) {
    case 'main course':
      return 'bg-orange-100 text-orange-800';
    case 'appetizer':
      return 'bg-yellow-100 text-yellow-800';
    case 'dessert':
      return 'bg-pink-100 text-pink-800';
    case 'beverages':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #4F46E5 #E5E7EB;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #E5E7EB;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #4F46E5;
  border-radius: 4px;
  border: 2px solid #E5E7EB;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #4338CA;
}
</style>