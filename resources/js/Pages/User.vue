<template>
  <Head title="User Dashboard" />
  <AuthenticatedLayout>
    <div class="flex flex-col lg:flex-row min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <!-- Sidebar -->
      <aside class="w-full lg:w-64 bg-white shadow-xl">
        <div class="p-6 flex flex-col items-center">
          <img 
            :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=random`" 
            alt="User Avatar" 
            class="w-24 h-24 rounded-full mb-4 shadow-lg"
            :class="avatarBorderColor"
          />
          <h2 class="text-xl font-bold text-gray-800">{{ $page.props.auth.user.name }}</h2>
          <p class="text-sm text-gray-600 mb-2">{{ $page.props.auth.user.email }}</p>
          <p :class="roleBadgeColor" class="px-3 py-1 rounded-full text-xs font-semibold">
            {{ userRole }}
          </p>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white shadow-xl transition-all duration-300 ease-in-out custom-scrollbar">
        <div class="container mx-auto px-4 lg:px-6 py-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-8">Discover new recipes</h1>
          
          <!-- Search and Filter -->
          <div class="mb-8 bg-white p-6 rounded-xl shadow-md">
            <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
              <div class="flex-grow">
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  placeholder="Search recipes" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                  @input="searchRecipes"
                >
              </div>
              <div class="md:w-48">
                <select 
                  v-model="selectedCategory"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                  @change="filterRecipes"
                >
                  <option value="">All Categories</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.category_name }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Recipe Grid -->
          <div v-if="filteredRecipes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="recipe in filteredRecipes" :key="recipe.id" 
                 class="bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 transform hover:scale-105 border border-gray-200 group">
              <div class="relative overflow-hidden">
                <img :src="getRecipeImage(recipe)" :alt="recipe.recipe_name" class="w-full h-72 object-cover transition-transform duration-300 group-hover:scale-110">
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
                <button @click="viewRecipeDetails(recipe)" 
                        class="bg-blue-600 text-white px-32 mx-auto d-block py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-700 flex items-center transform hover:scale-105 shadow-md">
                  <EyeIcon class="h-5 w-5 mr-2" />
                  View Details
                </button>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12 bg-white rounded-xl shadow-md">
            <BookOpenIcon class="h-24 w-24 text-gray-400 mx-auto mb-4 animate-pulse" />
            <p class="text-gray-500 text-lg">No recipes found. Try adjusting your search or filter.</p>
          </div>
        </div>
      </main>

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
                    <img :src="getRecipeImage(selectedRecipe)" :alt="selectedRecipe?.recipe_name" class="w-full h-128 object-cover transition-transform duration-300 hover:scale-105">
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
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { 
  ClockIcon, 
  UsersIcon, 
  BookOpenIcon,
  XMarkIcon as XIcon,
  EyeIcon,
} from '@heroicons/vue/24/solid';

const page = usePage();

const props = defineProps({
  recipes: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  roles: {
    type: Array,
    default: () => []
  },
});

// Reactive data
const recipes = ref(props.recipes);
const categories = ref(props.categories);
const searchQuery = ref('');
const selectedCategory = ref('');
const selectedRecipe = ref(null);

// Computed properties
const filteredRecipes = computed(() => {
  return recipes.value.filter(recipe => {
    const matchesCategory = selectedCategory.value === '' || recipe.category_id === parseInt(selectedCategory.value);
    const matchesSearch = recipe.recipe_name.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchesCategory && matchesSearch;
  });
});

const userRole = computed(() => {
  const roleId = page.props.auth.user.role_id;
  const role = props.roles.find(r => r.id === roleId);
  return role ? role.role_user : "Unknown";
});

const avatarBorderColor = computed(() => {
  switch (userRole.value) {
    case 'Admin':
      return 'border-4 border-green-500';
    case 'Chef':
      return 'border-4 border-blue-500';
    case 'User':
      return 'border-4 border-yellow-500';
    default:
      return 'border-4 border-gray-500';
  }
});

const roleBadgeColor = computed(() => {
  switch (userRole.value) {
    case 'Admin':
      return 'bg-green-100 text-green-800';
    case 'Chef':
      return 'bg-blue-100 text-blue-800';
    case 'User':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
});

// Methods
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

const searchRecipes = () => {
  // In a real application, you might want to debounce this function
  console.log('Searching:', searchQuery.value);
};

const filterRecipes = () => {
  console.log('Filtering by category:', selectedCategory.value);
};

const viewRecipeDetails = (recipe) => {
  selectedRecipe.value = recipe;
};

const closeRecipeDetails = () => {
  selectedRecipe.value = null;
};

const getRecipeImage = (recipe) => {
  if (recipe && recipe.url_image) {
    // Assuming the images are stored in the public/storage directory
    return `/storage/${recipe.url_image}`;
  }
  return '/placeholder.svg'; // Fallback to a placeholder image
};

// Lifecycle hooks
onMounted(() => {
  // Fetch recipes if not provided as props
  if (recipes.value.length === 0) {
    fetchRecipes();
  }
});

const fetchRecipes = async () => {
  try {
    const response = await fetch(route('user.getRecipes'));
    const data = await response.json();
    recipes.value = data.recipes;
    categories.value = data.categories;
  } catch (error) {
    console.error('Error fetching recipes:', error);
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