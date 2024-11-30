<template>
      <AuthenticatedLayout>

    <div class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
      <h1 class="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8 text-center text-gray-800">Admin Dashboard</h1>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <!-- User Management -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm">
          <h2 class="text-lg sm:text-xl font-semibold mb-3 flex items-center text-gray-700">
            <Users class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
            User Management
          </h2>
          <button 
            class="w-full bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition flex items-center justify-center text-sm sm:text-base"
            @click="toggleSection('users')"
          >
            <UserPlus class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
            Manage Users
          </button>
          <div v-if="activeSection === 'users'" class="mt-4 space-y-2">
            <div v-for="user in users" :key="user.id" class="flex items-center justify-between bg-gray-50 p-2 rounded">
              <span class="text-sm sm:text-base text-gray-700">{{ user.name }}</span>
              <div>
                <button @click="editUser(user)" class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs sm:text-sm hover:bg-blue-600 transition mr-2">
                  Edit
                </button>
                <button @click="deleteUser(user.id)" class="bg-red-500 text-white px-3 py-1 rounded-full text-xs sm:text-sm hover:bg-red-600 transition">
                  Delete
                </button>
              </div>
            </div>
            <button @click="showAddUserForm = true" class="mt-4 bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition text-sm sm:text-base w-full">
              Add New User
            </button>
          </div>
        </div>
        
        <!-- Chef Management -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm">
          <h2 class="text-lg sm:text-xl font-semibold mb-3 flex items-center text-gray-700">
            <ChefHat class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
            Chef Management
          </h2>
          <button 
            class="w-full bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition flex items-center justify-center text-sm sm:text-base"
            @click="toggleSection('chefs')"
          >
            <UserPlus class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
            Manage Chefs
          </button>
          <div v-if="activeSection === 'chefs'" class="mt-4 space-y-2">
            <div v-for="chef in chefs" :key="chef.id" class="flex items-center justify-between bg-gray-50 p-2 rounded">
              <span class="text-sm sm:text-base text-gray-700">{{ chef.name }}</span>
              <div>
                <button @click="editChef(chef)" class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs sm:text-sm hover:bg-blue-600 transition mr-2">
                  Edit
                </button>
                <button @click="deleteChef(chef.id)" class="bg-red-500 text-white px-3 py-1 rounded-full text-xs sm:text-sm hover:bg-red-600 transition">
                  Delete
                </button>
              </div>
            </div>
            <button @click="showAddUserForm = true" class="mt-4 bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition text-sm sm:text-base w-full">
              Add New Chef
            </button>
          </div>
        </div>
        
        <!-- Recipe Overview -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm">
          <h2 class="text-lg sm:text-xl font-semibold mb-3 flex items-center text-gray-700">
            <Book class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
            Recipe Overview
          </h2>
          <button 
            class="w-full bg-purple-500 text-white px-4 py-2 rounded-full hover:bg-purple-600 transition flex items-center justify-center text-sm sm:text-base"
            @click="toggleSection('recipes')"
          >
            <ListPlus class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
            View All Recipes
          </button>
          <div v-if="activeSection === 'recipes'" class="mt-4 space-y-2">
            <div v-for="recipe in recentRecipes" :key="recipe.id" class="bg-gray-50 p-2 rounded">
              <span class="text-sm sm:text-base text-gray-700">{{ recipe.title }}</span>
            </div>
          </div>
        </div>
        
        <!-- Activity Logs -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm">
          <h2 class="text-lg sm:text-xl font-semibold mb-3 flex items-center text-gray-700">
            <Activity class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
            Activity Logs
          </h2>
          <button 
            class="w-full bg-gray-500 text-white px-4 py-2 rounded-full hover:bg-gray-600 transition flex items-center justify-center text-sm sm:text-base"
            @click="toggleSection('logs')"
          >
            <ClipboardList class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
            View Logs
          </button>
          <div v-if="activeSection === 'logs'" class="mt-4 space-y-2">
            <div v-for="log in activityLogs" :key="log.id" class="bg-gray-50 p-2 rounded">
              <span class="text-sm sm:text-base text-gray-700">{{ log.message }}</span>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Add User Modal -->
      <div v-if="showAddUserForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Add New User</h2>
          <form @submit.prevent="addUser">
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input v-model="newUser.name" type="text" id="name" required class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="newUser.email" type="email" id="email" required class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
              <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select v-model="newUser.role" id="role" required class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="user">User</option>
                <option value="chef">Chef</option>
              </select>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="showAddUserForm = false" class="mr-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-400 transition text-sm sm:text-base">Cancel</button>
              <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition text-sm sm:text-base">Add User</button>
            </div>
          </form>
        </div>
      </div>
  
      <!-- Edit User Modal -->
      <div v-if="showEditUserForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Edit User</h2>
          <form @submit.prevent="updateUser">
            <div class="mb-4">
              <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input v-model="editingUser.name" type="text" id="edit-name" required class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
              <label for="edit-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="editingUser.email" type="email" id="edit-email" required class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
              <label for="edit-role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select v-model="editingUser.role" id="edit-role" required class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="user">User</option>
                <option value="chef">Chef</option>
              </select>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="showEditUserForm = false" class="mr-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-400 transition text-sm sm:text-base">Cancel</button>
              <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition text-sm sm:text-base">Update User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</AuthenticatedLayout>
  </template>
  
  <script setup>
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import { ref, reactive } from 'vue'
  import { Users, UserPlus, ChefHat, Book, ListPlus, Activity, ClipboardList } from 'lucide-vue-next'
  
  const activeSection = ref(null)
  const showAddUserForm = ref(false)
  const showEditUserForm = ref(false)
  const newUser = reactive({ name: '', email: '', role: 'user' })
  const editingUser = reactive({ id: null, name: '', email: '', role: '' })
  
  const users = ref([
    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'user' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'user' },
  ])
  
  const chefs = ref([
    { id: 3, name: 'Gordon Ramsay', email: 'gordon@example.com', role: 'chef' },
    { id: 4, name: 'Julia Child', email: 'julia@example.com', role: 'chef' },
  ])
  
  const recentRecipes = ref([
    { id: 1, title: 'Chocolate Cake' },
    { id: 2, title: 'Spaghetti Carbonara' },
    { id: 3, title: 'Chicken Tikka Masala' },
  ])
  
  const activityLogs = ref([
    { id: 1, message: 'User John Doe logged in' },
    { id: 2, message: 'Chef Gordon Ramsay added a new recipe' },
    { id: 3, message: 'User Jane Smith updated their profile' },
  ])
  
  const toggleSection = (section) => {
    activeSection.value = activeSection.value === section ? null : section
  }
  
  const addUser = () => {
    const id = Math.max(...users.value.map(u => u.id), ...chefs.value.map(c => c.id)) + 1
    const user = { id, ...newUser }
    if (user.role === 'chef') {
      chefs.value.push(user)
    } else {
      users.value.push(user)
    }
    showAddUserForm.value = false
    newUser.name = ''
    newUser.email = ''
    newUser.role = 'user'
  }
  
  const editUser = (user) => {
    Object.assign(editingUser, user)
    showEditUserForm.value = true
  }
  
  const updateUser = () => {
    const index = users.value.findIndex(u => u.id === editingUser.id)
    if (index !== -1) {
      users.value[index] = { ...editingUser }
    } else {
      const chefIndex = chefs.value.findIndex(c => c.id === editingUser.id)
      if (chefIndex !== -1) {
        chefs.value[chefIndex] = { ...editingUser }
      }
    }
    showEditUserForm.value = false
  }
  
  const deleteUser = (id) => {
    users.value = users.value.filter(u => u.id !== id)
    chefs.value = chefs.value.filter(c => c.id !== id)
  }
  
  const editChef = (chef) => {
    editUser(chef)
  }
  
  const deleteChef = (id) => {
    deleteUser(id)
  }
  </script>