<template>
      <Head title="Chef Management" />
    <AuthenticatedLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex justify-between items-center">
            <!-- Profile and Logout (Left Side) -->
            <div class="flex items-center space-x-4">
              <div class="relative">
                <img 
                  :src="user.profilePicture || '/placeholder.svg?height=40&width=40'" 
                  alt="Profile Picture" 
                  class="w-10 h-10 rounded-full object-cover cursor-pointer"
                  @click="toggleProfileMenu"
                >
                <input 
                  type="file" 
                  ref="fileInput" 
                  class="hidden" 
                  accept="image/*"
                  @change="uploadProfilePicture"
                >
                <div v-if="showProfileMenu" class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                  <button @click="$refs.fileInput.click()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                    Change Picture
                  </button>
                  <button @click="logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                    Logout
                  </button>
                </div>
              </div>
              <div class="text-left">
                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
            </div>
  
            <!-- Dashboard Title (Center) -->
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Chef Dashboard</h1>
  
            <!-- Empty div for flex spacing -->
            <div class="w-[144px]"></div>
          </div>
        </div>
      </header>
  
      <!-- Main Content (Centered) -->
      <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Recipe Management -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <h2 class="text-lg sm:text-xl font-semibold mb-4 flex items-center text-gray-700">
                <BookOpen class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
                Recipe Management
              </h2>
              <button 
                class="w-full bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition flex items-center justify-center text-sm sm:text-base"
                @click="showAddRecipeForm = true"
              >
                <PlusCircle class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                Add New Recipe
              </button>
            </div>
            
            <!-- My Recipes -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <h2 class="text-lg sm:text-xl font-semibold mb-4 flex items-center text-gray-700">
                <Utensils class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
                My Recipes
              </h2>
              <button class="w-full bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition flex items-center justify-center text-sm sm:text-base">
                <List class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                View All My Recipes
              </button>
            </div>
            
            <!-- Recipe Stats -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <h2 class="text-lg sm:text-xl font-semibold mb-4 flex items-center text-gray-700">
                <BarChart class="mr-2 h-5 w-5 sm:h-6 sm:w-6" />
                Recipe Statistics
              </h2>
              <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center bg-gray-50 p-3 rounded-lg">
                  <Bookmark class="mr-2 text-blue-500 h-5 w-5 sm:h-6 sm:w-6" />
                  <div>
                    <p class="text-sm text-gray-600">Total Recipes</p>
                    <p class="text-xl font-bold text-gray-800">{{ totalRecipes }}</p>
                  </div>
                </div>
                <div class="flex items-center bg-gray-50 p-3 rounded-lg">
                  <Star class="mr-2 text-yellow-500 h-5 w-5 sm:h-6 sm:w-6" />
                  <div>
                    <p class="text-sm text-gray-600">Most Popular</p>
                    <p class="text-xl font-bold text-gray-800">{{ mostPopularRecipe }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
  
      <!-- Add Recipe Modal -->
      <div v-if="showAddRecipeForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Add New Recipe</h2>
          <form @submit.prevent="addRecipe">
            <div class="mb-4">
              <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <input 
                v-model="newRecipe.title"
                type="text" 
                id="title" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <div class="mb-4">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea 
                v-model="newRecipe.description"
                id="description" 
                rows="3" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              ></textarea>
            </div>
            <div class="mb-4">
              <label for="ingredients" class="block text-sm font-medium text-gray-700 mb-1">Ingredients</label>
              <textarea 
                v-model="newRecipe.ingredients"
                id="ingredients" 
                rows="3" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              ></textarea>
            </div>
            <div class="mb-4">
              <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select 
                v-model="newRecipe.category"
                id="category" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="Appetizer">Appetizer</option>
                <option value="Main Course">Main Course</option>
                <option value="Dessert">Dessert</option>
                <option value="Beverage">Beverage</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload Picture</label>
              <div class="mt-1 flex items-center">
                <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                  <Upload class="h-full w-full text-gray-300" />
                </span>
                <button type="button" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-full shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Change
                </button>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="showAddRecipeForm = false" class="mr-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-400 transition text-sm sm:text-base">Cancel</button>
              <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition text-sm sm:text-base">Add Recipe</button>
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
  import { BookOpen, PlusCircle, Utensils, List, BarChart, Bookmark, Star, Upload } from 'lucide-vue-next'
  
  const showAddRecipeForm = ref(false)
  const showProfileMenu = ref(false)
  const newRecipe = reactive({
    title: '',
    description: '',
    ingredients: '',
    category: 'Main Course'
  })
  
  const user = reactive({
    name: 'Gordon Ramsay',
    email: 'gordon@example.com',
    profilePicture: null
  })
  
  const totalRecipes = ref(25)
  const mostPopularRecipe = ref('Chocolate Cake')
  
  const addRecipe = () => {
    // Here you would typically send the new recipe to your backend
    console.log('New recipe:', newRecipe)
    showAddRecipeForm.value = false
    // Reset the form
    Object.assign(newRecipe, {
      title: '',
      description: '',
      ingredients: '',
      category: 'Main Course'
    })
    // Increment total recipes count
    totalRecipes.value++
  }
  
  const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value
  }
  
  const uploadProfilePicture = (event) => {
    const file = event.target.files[0]
    if (file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        user.profilePicture = e.target.result
      }
      reader.readAsDataURL(file)
    }
  }
  
  const logout = () => {
    // Implement logout logic here
    console.log('Logging out...')
  }
  </script>