<template>
  <Head title="Chef Management" />
  <AuthenticatedLayout>
    <div class="flex flex-col lg:flex-row min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <!-- Sidebar -->
      <aside class="w-full lg:w-64 bg-white shadow-lg lg:min-h-screen transition-all duration-300 ease-in-out">
        <div class="p-6 flex flex-col items-center">
          <div class="relative">
            <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=random`" alt="User Avatar" class="w-24 h-24 rounded-full mb-4 border-4 border-blue-500 shadow-lg" />
          </div>
          <h2 class="text-xl font-bold text-gray-800">{{ $page.props.auth.user.name }}</h2>
          <p class="text-sm text-gray-600 mb-2">{{ $page.props.auth.user.email }}</p>
          <p class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold shadow-md">
            {{ getRole($page.props.auth.user.role_id) }}
          </p>
        </div>
        <nav class="mt-6 sticky top-0">
          <a v-for="item in navItems" :key="item.name" :href="item.href" 
            class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 transition-colors duration-200">
            <component :is="item.icon" class="h-5 w-5 mr-3" />
            <span>{{ item.name }}</span>
          </a>
        </nav>
      </aside>
      <!-- Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white shadow-xl transition-all duration-300 ease-in-out custom-scrollbar">
        <div class="container mx-auto px-6 py-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-8">Chef Dashboard</h1>
          
          <!-- Dashboard Stats -->
          <section id="dashboard" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div v-for="stat in dashboardStats" :key="stat.title" 
                class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-700">{{ stat.title }}</h3>
                <component :is="stat.icon" class="h-8 w-8 text-gray-500" />
              </div>
              <p class="text-3xl font-bold text-gray-700">{{ stat.value }}</p>
            </div>
          </section>
          
          <!-- Recipe Management -->
          <section id="recipes">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-4xl font-bold text-gray-800">Recipe Collection</h1>
              <button @click="openRecipeModal()" 
                      class="bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-all duration-300 flex items-center shadow-lg transform hover:scale-105">
                <PlusIcon class="h-5 w-5 mr-2" />
                Add New Recipe
              </button>
            </div>
            
            <!-- Recipe Grid -->
            <div v-if="recipes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
              <div v-for="recipe in recipes" :key="recipe.id" 
                  class="bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 transform hover:scale-105 border border-gray-200 group">
                <div class="relative overflow-hidden">
                  <img :src="asset('storage/' + recipe.url_image)" :alt="recipe.recipe_name" class="w-full h-72 object-cover transition-transform duration-300 group-hover:scale-110">
                </div>
                <div class="p-6 relative">
                  <h3 class="text-2xl font-semibold text-gray-900 mb-2 line-clamp-2">{{ recipe.recipe_name }}</h3>
                  <p :style="{ color: getCategoryColor(recipe.category_id).text, backgroundColor: getCategoryColor(recipe.category_id).bg }" 
                     class="text-sm mb-4 font-medium inline-block px-2 py-1 rounded-full transition-all duration-300 group-hover:shadow-md">
                    {{ getCategoryName(recipe.category_id) }}
                  </p>
                  <div class="flex items-center justify-between text-gray-500 mb-4">
                    <span class="flex items-center">
                      <ClockIcon class="w-4 h-4 mr-1" />
                      {{ recipe.prep_time }}
                    </span>
                    <span class="flex items-center">
                      <UsersIcon class="w-4 h-4 mr-1" />
                      {{ recipe.servings }} servings
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <button @click="viewRecipeDetails(recipe)" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md transition duration-300 ease-in-out hover:bg-blue-700 flex items-center transform hover:scale-105 shadow-md">
                      <EyeIcon class="h-5 w-5 mr-2" />
                      View Details
                    </button>
                    <div class="flex space-x-2">
                      <button @click="openRecipeModal(recipe)"
                              class="bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition-colors duration-300 shadow-md">
                        <PencilIcon class="h-5 w-5" />
                      </button>
                      <button @click="confirmDeleteRecipe(recipe.id)"
                              class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors duration-300 shadow-md">
                        <TrashIcon class="h-5 w-5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12 bg-white rounded-xl shadow-md">
              <BookOpenIcon class="h-24 w-24 text-gray-400 mx-auto mb-4 animate-pulse" />
              <p class="text-gray-500 text-lg">No recipes found. Add a new recipe to get started!</p>
              <button @click="openRecipeModal()" 
                      class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-colors duration-300 inline-flex items-center">
                <PlusIcon class="h-5 w-5 mr-2" />
                Create Your First Recipe
              </button>
            </div>
          </section>
        </div>
      </main>

      <!-- Add/Edit Recipe Modal -->
      <TransitionRoot appear :show="showRecipeModal" as="template">
        <Dialog as="div" @close="closeRecipeModal" class="relative z-50">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-black bg-opacity-25" />
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
                <DialogPanel class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ editingRecipe ? 'Edit Recipe' : 'Create New Recipe' }}
                  </DialogTitle>
                  <form @submit.prevent="confirmSubmitRecipe" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="recipe_name" class="block text-sm font-medium text-gray-700">Recipe Name</label>
                        <input
                          type="text"
                          id="recipe_name"
                          v-model="recipeForm.recipe_name"
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          placeholder="Enter recipe name"
                        />
                      </div>
                      <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select
                          id="category"
                          v-model="recipeForm.category_id"
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                          <option value="" disabled>Select a category</option>
                          <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.category_name }}
                          </option>
                        </select>
                      </div>
                    </div>
                    <div>
                      <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                      <textarea
                        id="description"
                        v-model="recipeForm.description"
                        required
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        placeholder="Enter recipe description"
                      ></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="ingredients" class="block text-sm font-medium text-gray-700">Ingredients</label>
                        <textarea
                          id="ingredients"
                          v-model="recipeForm.ingredients"
                          required
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          placeholder="Enter recipe ingredients (one per line)"
                        ></textarea>
                      </div>
                      <div>
                        <label for="procedure" class="block text-sm font-medium text-gray-700">Procedure</label>
                        <textarea
                          id="procedure"
                          v-model="recipeForm.procedure"
                          required
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          placeholder="Enter recipe procedure (step by step)"
                        ></textarea>
                      </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="prep_time" class="block text-sm font-medium text-gray-700">Preparation Time</label>
                        <input
                          type="text"
                          id="prep_time"
                          v-model="recipeForm.prep_time"
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          placeholder="E.g. 30 minutes"
                        />
                      </div>
                      <div>
                        <label for="servings" class="block text-sm font-medium text-gray-700">Servings</label>
                        <input
                          type="number"
                          id="servings"
                          v-model="recipeForm.servings"
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          placeholder="Enter serving size"
                        />
                      </div>
                    </div>
                    <div>
                      <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                      <div class="flex flex-col items-center space-y-4">
                        <label for="image" class="cursor-pointer w-full">
                          <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-blue-500 rounded-md blur opacity-25 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative px-7 py-4 bg-white ring-1 ring-gray-900/5 rounded-lg leading-none flex items-center justify-center space-x-6">
                              <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              <span class="text-gray-800 group-hover:text-purple-600 transition duration-200">{{ recipeForm.url_image ? 'Change Image' : 'Choose Image' }}</span>
                            </div>
                          </div>
                          <input
                            type="file"
                            id="image"
                            @change="handleImageUpload"
                            accept="image/*"
                            class="hidden"
                          />
                        </label>
                        <div v-if="previewImage" class="relative">
                          <div class="w-64 h-64 bg-white shadow-lg rounded-md p-2 transform rotate-2 transition-all duration-300 hover:rotate-0">
                            <img :src="previewImage" alt="Recipe preview" class="w-full h-full object-cover rounded" />
                            <button 
                              @click.prevent="removeImage" 
                              type="button"
                              class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full p-2 shadow-lg transform transition-all duration-300 hover:scale-110 focus:outline-none"
                            >
                              <XIcon class="h-4 w-4" />
                            </button>
                          </div>
                        </div>
                        <p v-if="imageError" class="text-red-500 text-sm mt-2">{{ imageError }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                          Recommended image size: 800x600 pixels (Max: 5MB)
                        </p>
                      </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                      <button 
                        type="button" 
                        @click="closeRecipeModal" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                      >
                        Cancel
                      </button>
                      <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                      >
                        {{ editingRecipe ? 'Update Recipe' : 'Add Recipe' }}
                      </button>
                    </div>
                  </form>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>

      <!-- Recipe Details Modal -->
      <TransitionRoot appear :show="!!selectedRecipe" as="template">
        <Dialog as="div" @close="closeRecipeDetails" class="relative z-50">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-black bg-opacity-25" />
          </TransitionChild>

          <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
              <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                  <div class="flex justify-between items-center mb-4">
                    <DialogTitle as="h2" class="text-3xl font-bold text-gray-800">
                      {{ selectedRecipe?.recipe_name }}
                    </DialogTitle>
                    <button @click="closeRecipeDetails" class="text-gray-500 hover:text-gray-700 transition-colors duration-300">
                      <XIcon class="h-6 w-6" />
                    </button>
                  </div>
                  <div class="relative overflow-hidden rounded-xl">
                    <img :src="asset('storage/' + selectedRecipe?.url_image)" :alt="selectedRecipe?.recipe_name" class="w-full h-128 object-cover transition-transform duration-300 hover:scale-105">
                    <div class="absolute bottom-4 right-4 flex space-x-2">
                      <span class="flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium shadow">
                        <ClockIcon class="w-4 h-4 mr-1" />
                        {{ selectedRecipe?.prep_time }}
                      </span>
                      <span class="flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium shadow">
                        <UsersIcon class="w-4 h-4 mr-1" />
                        {{ selectedRecipe?.servings }} servings
                      </span>
                    </div>
                  </div>
                  <span :style="{ color: getCategoryColor(selectedRecipe?.category_id).text,
                    backgroundColor: getCategoryColor(selectedRecipe?.category_id).bg }" class="px-3 py-1 rounded-full text-sm font-medium inline-block mb-4 shadow mt-4">
                    {{ getCategoryName(selectedRecipe?.category_id) }}
                  </span>
                  <div class="space-y-6 max-h-[calc(100vh-24rem)] overflow-y-auto pr-4 custom-scrollbar">
                    <div class="bg-gray-50 p-4 rounded-lg shadow-inner">
                      <h3 class="text-2xl font-semibold mb-2 text-gray-800">Description</h3>
                      <p class="text-gray-700">{{ selectedRecipe?.description }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-inner">
                      <h3 class="text-2xl font-semibold mb-2 text-gray-800">Ingredients</h3>
                      <ul class="list-disc pl-5 space-y-2">
                        <li v-for="ingredient in selectedRecipe?.ingredients.split('\n')" :key="ingredient" class="text-gray-700">
                          {{ ingredient }}
                        </li>
                      </ul>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-inner">
                      <h3 class="text-2xl font-semibold mb-2 text-gray-800">Procedure</h3>
                      <ol class="list-decimal pl-5 space-y-2">
                        <li v-for="step in selectedRecipe?.procedure.split('\n')" :key="step" class="text-gray-700">
                          {{ step }}
                        </li>
                      </ol>
                    </div>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>

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
            <div class="fixed inset-0 bg-black bg-opacity-25" />
          </TransitionChild>

          <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
              <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ confirmModalConfig.title }}
                  </DialogTitle>
                  <p class="text-sm text-gray-500 mb-4">
                    {{ confirmModalConfig.message }}
                  </p>
                  <div class="mt-4 flex justify-end space-x-3">
                    <button @click="closeConfirmModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-300">
                      Cancel
                    </button>
                    <button @click="confirmAction" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                      Confirm
                    </button>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>

      <!-- Creative Alert -->
      <TransitionRoot appear :show="showCreativeAlert" as="template">
        <Dialog as="div" @close="closeCreativeAlert" class="relative z-50">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-black bg-opacity-25" />
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
                  <div class="flex items-center justify-center mb-4">
                    <div class="bg-green-100 rounded-full p-3">
                      <CheckIcon class="h-6 w-6 text-green-600" />
                    </div>
                  </div>
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 text-center mb-2">
                    Recipe {{ creativeAlertConfig.action }} Successfully!
                  </DialogTitle>
                  <p class="text-sm text-gray-500 text-center mb-4">
                    {{ creativeAlertConfig.message }}
                  </p>
                  <div class="mt-4 flex justify-center">
                    <button
                      type="button"
                      class="inline-flex justify-center rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition-colors duration-300"
                      @click="closeCreativeAlert"
                    >
                      Got it, thanks!
                    </button>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { 
  ChartBarIcon, 
  ClockIcon, 
  UserGroupIcon, 
  PlusIcon, 
  PencilIcon, 
  TrashIcon,
  XMarkIcon as XIcon,
  HomeIcon,
  BookOpenIcon,
  UsersIcon,
  BookmarkIcon,
  FireIcon,
  TagIcon,
  CheckIcon,
  EyeIcon,
} from '@heroicons/vue/24/solid';
import { ChefHatIcon } from 'lucide-vue-next';

const props = defineProps({
  chefs: Array,
  roles: Array,
  users: Array,
  stats: {
    type: Array,
    default: () => [{
      total_main_courses: 0,
      total_appetizers: 0,
      total_desserts: 0,
      total_beverages: 0,
    }],
  },
  recipes: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  activityLogs: {
    type: Array,
    default: () => []
  }
});
onMounted(() => {
  console.log(props.recipes);  // Check if recipes are passed correctly
});
// Reactive data
const recipes = ref(props.recipes);
const categories = ref(props.categories);
const roles = ref(props.roles || []);
const totalRecipes = ref(0);
const totalRecipesCreated = ref(0);
const totalCategories = ref(0);
const recentRecipes = ref([]);

const showRecipeModal = ref(false);
const showConfirmModal = ref(false);
const showCreativeAlert = ref(false);
const editingRecipe = ref(null);
const selectedRecipe = ref(null);
const imageError = ref('');

const recipeForm = ref({
  recipe_name: '',
  description: '',
  ingredients: '',
  procedure: '',
  category_id: '',
  prep_time: '',
  servings: '',
  url_image: null,
});

const confirmModalConfig = ref({
  title: '',
  message: '',
  onConfirm: null
});

const creativeAlertConfig = ref({
  action: '',
  message: '',
});

// Navigation items
const navItems = [
  { name: 'Dashboard', href: '#dashboard', icon: HomeIcon },
  { name: 'Recipes', href: '#recipes', icon: BookOpenIcon },
];

// Computed properties
const dashboardStats = computed(() => [
  { title: 'Total Main Courses', value: props.stats[0].total_main_courses, icon: BookOpenIcon },
  { title: 'Total Appetizers', value: props.stats[0].total_appetizers, icon: FireIcon },
  { title: 'Total Desserts', value: props.stats[0].total_desserts, icon: TagIcon },
  { title: 'Total Beverages', value: props.stats[0].total_beverages, icon: BookmarkIcon },
]);

const previewImage = computed(() => {
  if (recipeForm.value.url_image instanceof File) {
    return URL.createObjectURL(recipeForm.value.url_image);
  }
  return recipeForm.value.url_image ? asset('storage/' + recipeForm.value.url_image) : null;
});

// Methods
const getRole = (roleId) => {
  const role = roles.value.find(r => r.id === roleId);
  return role ? role.role_user : "Unknown";
};

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId);
  return category ? category.category_name : 'Uncategorized';
};

const getCategoryColor = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId);
  if (category) {
    switch (category.category_name.toLowerCase()) {
      case 'main course':
        return { text: '#FFA500', bg: '#FFF3E0' };
      case 'appetizer':
        return { text: '#FFD700', bg: '#FFFDE7' };
      case 'dessert':
        return { text: '#FF69B4', bg: '#FCE4EC' };
      case 'beverages':
        return { text: '#0000FF', bg: '#CCE5FF' }; // Deeper blue background
      case 'side dish':
        return { text: '#32CD32', bg: '#F1F8E9' };
      default:
        return { text: '#000000', bg: '#FFFFFF' };
    }
  }
  return { text: '#000000', bg: '#FFFFFF' };
};

const openRecipeModal = (recipe = null) => {
  editingRecipe.value = recipe;
  if (recipe) {
    recipeForm.value = { ...recipe };
  } else {
    recipeForm.value = {
      recipe_name: '',
      description: '',
      ingredients: '',
      procedure: '',
      category_id: '',
      prep_time: '',
      servings: '',
      url_image: null,
    };
  }
  showRecipeModal.value = true;
};

const closeRecipeModal = () => {
  showRecipeModal.value = false;
  editingRecipe.value = null;
  imageError.value = '';
};

const closeConfirmModal = () => {
  showConfirmModal.value = false;
  confirmModalConfig.value = {
    title: '',
    message: '',
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

const confirmDeleteRecipe = (recipeId) => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Delete Recipe',
    message: 'Are you sure you want to delete this recipe?',
    onConfirm: () => {
      router.delete(route("chef.deleteRecipe", { recipe: recipeId }), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          recipes.value = recipes.value.filter((recipe) => recipe.id !== recipeId);
          showCreativeAlert.value = true;
          creativeAlertConfig.value = {
            action: 'Deleted',
            message: 'Your recipe has been removed from your collection.'
          };
        },
        onError: (error) => {
          console.error("Error deleting recipe:", error);
          showAlert("Error: Unable to delete the recipe.", "error");
        },
      });
    },
  };
};

const confirmSubmitRecipe = () => {
  if (!validateImageSize()) {
    return;
  }
  submitRecipe();
};

const validateImageSize = () => {
  if (recipeForm.value.url_image instanceof File) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (recipeForm.value.url_image.size > maxSize) {
      imageError.value = 'Image size exceeds 5MB limit. Please choose a smaller image.';
      return false;
    }
  }
  imageError.value = '';
  return true;
};

const submitRecipe = () => {
  const formData = new FormData();
  Object.keys(recipeForm.value).forEach(key => {
    if (key === 'url_image' && recipeForm.value[key] instanceof File) {
      formData.append(key, recipeForm.value[key]);
    } else {
      formData.append(key, recipeForm.value[key]);
    }
  });

  if (editingRecipe.value) {
    router.post(route("chef.updateRecipe", { recipe: recipeForm.value.id }), formData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        const recipeIndex = recipes.value.findIndex((r) => r.id === recipeForm.value.id);
        if (recipeIndex > -1) {
          recipes.value[recipeIndex] = { ...recipeForm.value };
        }
        closeRecipeModal();
        showCreativeAlert.value = true;
        creativeAlertConfig.value = {
          action: 'Edited',
          message: 'Your recipe has been updated in your collection.'
        };
      },
      onError: (errors) => {
        showAlert("There was an error updating the recipe: " + Object.values(errors).join(", "), "error");
      },
    });
  } else {
    router.post(route("chef.storeRecipe"), formData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        closeRecipeModal();
        fetchDashboardData();
        showCreativeAlert.value = true;
        creativeAlertConfig.value = {
          action: 'Added',
          message: 'Your new recipe has been added to your collection.'
        };
      },
      onError: (errors) => {
        showAlert("There was an error adding the recipe: " + Object.values(errors).join(", "), "error");
      },
    });
  }
};

const viewRecipeDetails = (recipe) => {
  selectedRecipe.value = recipe;
};

const closeRecipeDetails = () => {
  selectedRecipe.value = null;
};

const closeCreativeAlert = () => {
  showCreativeAlert.value = false;
};

const updateDashboardStats = () => {
  totalRecipes.value = recipes.value.length;
  totalRecipesCreated.value = recipes.value.length;
  totalCategories.value = categories.value.length;
};

const fetchDashboardData = async () => {
  try {
    const response = await fetch(route('chef.getDashboardStats'));
    const data = await response.json();
    totalRecipes.value = data.totalRecipes;
    totalRecipesCreated.value = data.totalRecipesCreated;
    totalCategories.value = data.totalCategories;
    recentRecipes.value = data.recentRecipes;
    categories.value = data.categories;
    recipes.value = data.recipes;
    updateDashboardStats();
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
    showAlert('Unable to fetch dashboard statistics.', 'error');
  }
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (!validateImageType(file)) {
      imageError.value = 'Invalid file type. Please upload an image file.';
      return;
    }
    if (!validateImageDimensions(file)) {
      return;
    }
    recipeForm.value.url_image = file;
    imageError.value = '';
  }
};

const validateImageType = (file) => {
  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
  return allowedTypes.includes(file.type);
};

const validateImageDimensions = (file) => {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      if (img.width < 800 || img.height < 600) {
        imageError.value = 'Image dimensions are too small. Minimum size is 800x600 pixels.';
        resolve(false);
      } else {
        imageError.value = '';
        resolve(true);
      }
    };
    img.src = URL.createObjectURL(file);
  });
};

const removeImage = () => {
  recipeForm.value.url_image = null;
  imageError.value = '';
};

const asset = (path) => {
  // This function simulates Laravel's asset() helper
  // You might need to adjust this based on your actual asset handling in Vue
  return '/' + path.replace(/^\/+/, '');
};

// Lifecycle hooks
onMounted(() => {
  fetchDashboardData();
});
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
