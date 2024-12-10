<template>
  <Head title="Recipe Book Manager" />
  <AuthenticatedLayout>
    <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
      <!-- Sidebar -->
      <aside class="w-full lg:w-64 bg-white shadow-md">
        <div class="p-6 flex flex-col items-center">
          <div class="mb-6 text-center">
            <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=random`" alt="User Avatar" class="w-24 h-24 rounded-full mx-auto" />
            <p class="mt-2 text-xl font-semibold text-gray-800">{{ getRole($page.props.auth.user.role_id) }}</p>
            <p class="mt-1 text-lg text-gray-600">{{ $page.props.auth.user.name }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $page.props.auth.user.email }}</p>
          </div>
        </div>
        <nav class="flex flex-col space-y-1 px-4">
          <a v-for="(item, index) in navItems" :key="index" :href="item.href" 
             class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors duration-200">
            <component :is="item.icon" class="h-5 w-5 mr-3" />
            {{ item.name }}
          </a>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
        <div class="container mx-auto px-4 py-8">
          <!-- Statistics -->
          <section id="statistics" class="mb-12">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">Dashboard Overview</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <div v-for="stat in statistics" :key="stat.title" 
                   class="bg-white rounded-xl shadow-md p-6 transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-semibold text-gray-600">{{ stat.title }}</h3>
                  <component :is="stat.icon" class="h-8 w-8 text-gray-500" />
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
              </div>
            </div>
          </section>

          <!-- User Management -->
          <section id="users" class="mb-12">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-3xl font-semibold text-gray-800">User Management</h2>
              <button @click="showAddUserModal = true" 
                      class="bg-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center">
                <PlusIcon class="h-5 w-5 mr-2" />
                Add User
              </button>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                      <th class="px-6 py-3">Name</th>
                      <th class="px-6 py-3">Email</th>
                      <th class="px-6 py-3">Role</th>
                      <th class="px-6 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="user in sortedUsers" :key="user.id" class="hover:bg-gray-50">
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
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ user.email }}</div>
                      </td>
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
              <h2 class="text-3xl font-semibold text-gray-800">Recipe Overview</h2>
              <button @click="refreshRecipes" 
                      class="text-blue-600 hover:text-blue-800 transition-colors duration-300">
                <ArrowPathIcon class="h-6 w-6" />
              </button>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                      <th class="px-6 py-3">Title</th>
                      <th class="px-6 py-3">Author</th>
                      <th class="px-6 py-3">Created At</th>
                      <th class="px-6 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="recipe in recipes" :key="recipe.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full object-cover" :src="recipe.image || 'https://via.placeholder.com/150'" :alt="recipe.title" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ recipe.title }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ recipe.author }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(recipe.created_at) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button
                          @click="deleteRecipe(recipe.id)"
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

        <!-- Activity Logs -->
<section id="activity" class="mb-12">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-semibold text-gray-800">Activity Logs</h2>
    <button @click="refreshActivityLogs" 
            class="text-blue-600 hover:text-blue-800 transition-colors duration-300">
      <ArrowPathIcon class="h-6 w-6" />
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
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
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
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                  placeholder="Enter email"
                />
              </div>

              <!-- Only show password and role fields for adding a user -->
              <div v-if="!editingUser">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input
                  type="password"
                  id="password"
                  v-model="userForm.password"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
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
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                >
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
                  {{ editingUser ? 'Edit User' : 'Add User' }}
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
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Dialog, DialogPanel, DialogTitle, DialogOverlay, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { 
  ChartBarIcon, 
  ClockIcon, 
  ArrowPathIcon, 
  UserGroupIcon, 
  PlusIcon, 
  PencilIcon, 
  TrashIcon,
  XMarkIcon,
  Bars3Icon,
  UserIcon,
  ShieldCheckIcon,
  BookOpenIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  admins: Array,
  roles: Array,
  users: Array,
  showAddUserModal: Boolean,
  recipes: {
    type: Array,
    default: () => []
  },
  activityLogs: {
    type: Array,
    default: () => []
  }
});

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role_id: null,  // Set to null initially to handle the dynamic value
});

// Methods
const getRole = (role_id) => {
  // Use the roles array to dynamically get the role name
  const role = props.roles.find(r => r.id === role_id);
  return role ? role.role_user : "Unknown";  // Return the role's name, or "Unknown" if not found
};

const formatDate = (dateString) => new Date(dateString).toLocaleDateString();

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
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: type.charAt(0).toUpperCase() + type.slice(1),
    message: message,
    onConfirm: closeConfirmModal
  };
};

const deleteUser = (userId) => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Delete User',
    message: 'Are you sure you want to delete this user?',
    onConfirm: () => {
      router.delete(route("admin.deleteUser", { user: userId }), {
        onSuccess: () => {
          users.value = users.value.filter((user) => user.id !== userId);
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
        const userIndex = users.value.findIndex((u) => u.id === userForm.value.id);
        if (userIndex > -1) {
          users.value[userIndex] = { ...userForm.value };
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
        users.value.push(userForm.value);
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

const deleteRecipe = (recipeId) => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Delete Recipe',
    message: 'Are you sure you want to delete this recipe?',
    onConfirm: () => {
      router.delete(route("admin.deleteRecipe", { recipe: recipeId }), {
        onSuccess: () => {
          recipes.value = recipes.value.filter((recipe) => recipe.id !== recipeId);
          showAlert("Recipe deleted successfully!", "success");
        },
        onError: (error) => {
          console.error("Error deleting recipe:", error);
          showAlert("Error: Unable to delete the recipe.", "error");
        },
      });
    }
  };
};
// Fetch and update activity logs
const refreshActivityLogs = () => {
  router.get(route("admin.getActivityLogs"), {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      activityLogs.value = page.props.activityLogs; // Update the activity logs with the fresh data
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

// Reactive data initialization
const users = ref([...props.users]);
const recipes = ref(props.recipes);
const activityLogs = ref(props.activityLogs);
const showAddUserModal = ref(false);
const editingUser = ref(null);

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

// Computed properties for statistics
const statistics = computed(() => [
  { title: "Total Users", value: users.value.length, icon: UserIcon },
  { title: "Total Recipes", value: recipes.value.length, icon: BookOpenIcon },
  { title: "Total Chefs", value: users.value.filter((u) => u.role_id === 2).length, icon: UserGroupIcon },
  { title: "Total Admins", value: users.value.filter((u) => u.role_id === 1).length, icon: ShieldCheckIcon },
]);

const sortedUsers = computed(() =>
  users.value.slice().sort((a, b) => a.role_id - b.role_id)
);
</script>
<style scoped>
/* Add any additional styles here */
</style>