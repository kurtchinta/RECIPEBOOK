<template>
  <Head title="Browse Recipes" />
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gradient-to-br from-orange-100 to-yellow-100">
      <!-- Header -->
      <header class="bg-gradient-to-r from-orange-500 to-yellow-500 shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
          <div class="grid grid-cols-6 gap-4 p-4 transform -rotate-12">
            <UtensilsCrossed v-for="i in 6" :key="`utensils-${i}`" class="w-8 h-8 text-white" />
            <Pizza v-for="i in 6" :key="`pizza-${i}`" class="w-8 h-8 text-white" />
          </div>
        </div>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
              <div class="text-left">
                <p class="text-2xl font-bold text-white">{{ $page.props.auth.user.name }}</p>
                <p class="text-sm text-yellow-100">{{ $page.props.auth.user.email }}</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white p-8 rounded-2xl shadow-lg">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-6">Explore Delicious Recipes</h2>
          
          <div class="mb-8 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="flex-grow">
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search recipes" 
                class="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-orange-500 focus:border-orange-500"
                @input="searchRecipes"
              >
            </div>
            <div class="sm:w-48">
              <select 
                v-model="selectedCategory"
                class="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-orange-500 focus:border-orange-500"
                @change="filterRecipes"
              >
                <option value="">All Categories</option>
                <option v-for="category in allowedCategories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="recipe in filteredRecipes" :key="recipe.id" 
                 class="bg-gradient-to-br from-orange-50 to-yellow-50 overflow-hidden shadow-lg rounded-xl transition-all duration-300 transform hover:scale-105">
              <img :src="recipe.image" :alt="recipe.title" class="w-full h-48 object-cover">
              <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ recipe.title }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ recipe.category }}</p>
                <div class="flex items-center justify-between text-gray-500">
                  <span class="flex items-center">
                    <Clock class="w-4 h-4 mr-1" />
                    {{ recipe.cookTime }} mins
                  </span>
                  <span class="flex items-center">
                    <Users class="w-4 h-4 mr-1" />
                    {{ recipe.servings }} servings
                  </span>
                </div>
                <button 
                  @click="viewRecipeDetails(recipe.id)" 
                  class="mt-4 w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white font-bold py-2 px-4 rounded-full transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg"
                >
                  View Recipe
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </AuthenticatedLayout>

  <!-- Recipe Details Modal -->
  <div v-if="selectedRecipe" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white p-6 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-3xl font-bold text-gray-800">{{ selectedRecipe.title }}</h2>
        <button @click="closeRecipeDetails" class="text-gray-500 hover:text-gray-700">
          <X class="h-6 w-6" />
        </button>
      </div>
      <img :src="selectedRecipe.image" :alt="selectedRecipe.title" class="w-full h-64 object-cover rounded-xl mb-4">
      <div class="flex justify-between items-center text-gray-600 mb-4">
        <span class="flex items-center">
          <Clock class="w-5 h-5 mr-2" />
          {{ selectedRecipe.cookTime }} mins
        </span>
        <span class="flex items-center">
          <Users class="w-5 h-5 mr-2" />
          {{ selectedRecipe.servings }} servings
        </span>
        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">
          {{ selectedRecipe.category }}
        </span>
      </div>
      <p class="text-gray-700 mb-4">{{ selectedRecipe.description }}</p>
      <!-- Add more recipe details here (ingredients, instructions, etc.) -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Clock, Users, UtensilsCrossed, Pizza, X } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Mock recipe data
const recipes = ref([
  { id: 1, title: 'Grilled Steak', category: 'Main Course', image: '/placeholder.svg?height=300&width=400', cookTime: 30, servings: 2, description: 'Juicy steak grilled to perfection.' },
  { id: 2, title: 'Caesar Salad', category: 'Appetizer', image: '/placeholder.svg?height=300&width=400', cookTime: 10, servings: 4, description: 'Classic salad with romaine lettuce and croutons.' },
  { id: 3, title: 'Chocolate Cake', category: 'Dessert', image: '/placeholder.svg?height=300&width=400', cookTime: 45, servings: 6, description: 'Rich and moist chocolate cake.' },
  { id: 4, title: 'Iced Tea', category: 'Beverages', image: '/placeholder.svg?height=300&width=400', cookTime: 5, servings: 1, description: 'Refreshing cold tea with lemon.' },
  { id: 5, title: 'Spaghetti Carbonara', category: 'Main Course', image: '/placeholder.svg?height=300&width=400', cookTime: 20, servings: 4, description: 'Creamy pasta dish with bacon and eggs.' },
  { id: 6, title: 'Fruit Smoothie', category: 'Beverages', image: '/placeholder.svg?height=300&width=400', cookTime: 5, servings: 2, description: 'Healthy blend of fresh fruits and yogurt.' },
])

const allowedCategories = ['Main Course', 'Appetizer', 'Dessert', 'Beverages']

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedRecipe = ref(null)

const filteredRecipes = computed(() => {
  return recipes.value.filter(recipe => {
    const matchesCategory = selectedCategory.value === '' || recipe.category === selectedCategory.value
    const matchesSearch = recipe.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchesCategory && matchesSearch
  })
})

const searchRecipes = () => {
  // In a real application, you might want to debounce this function
  console.log('Searching:', searchQuery.value)
}

const filterRecipes = () => {
  console.log('Filtering by category:', selectedCategory.value)
}

const viewRecipeDetails = (id) => {
  selectedRecipe.value = recipes.value.find(r => r.id === id)
}

const closeRecipeDetails = () => {
  selectedRecipe.value = null
}
</script>

<style scoped>
/* Add any additional component-specific styles here */
</style>