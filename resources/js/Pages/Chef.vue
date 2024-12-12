<template>
  <Head title="Chef Management" />
  <AuthenticatedLayout>
    <div class="flex h-screen bg-gray-100">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-md">
        <div class="p-6 flex flex-col items-center">
          <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=random`" alt="User Avatar" class="w-24 h-24 rounded-full mb-4 border-4 border-blue-500 shadow-lg" />
          <h2 class="text-xl font-bold text-gray-800">{{ $page.props.auth.user.name }}</h2>
          <p class="text-sm text-gray-600 mb-2">{{ $page.props.auth.user.email }}</p>
          <p class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
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
          <h1 class="text-4xl font-bold text-gray-800 mb-8">Chef Dashboard</h1>
          
          <!-- Dashboard Stats -->
          <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div v-for="stat in dashboardStats" :key="stat.title" 
                 class="bg-white p-6 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-700">{{ stat.title }}</h3>
                <component :is="stat.icon" class="h-8 w-8 text-gray-500" />
              </div>
              <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
            </div>
          </section>
          
          <!-- Recipe Management -->
          <section>
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-3xl font-bold text-gray-800">Recipe Collection</h2>
              <button @click="openRecipeModal()" 
                      class="bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center shadow-lg">
                <PlusIcon class="h-5 w-5 mr-2" />
                Add New Recipe
              </button>
            </div>
            
            <!-- Recipe Grid -->
            <div v-if="recipes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="recipe in recipes" :key="recipe.id" 
                   class="bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 transform hover:scale-105">
                <img :src="recipe.url_image || '/placeholder.svg'" :alt="recipe.recipe_name" class="w-full h-48 object-cover">
                <div class="p-6">
                  <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ recipe.recipe_name }}</h3>
                  <p :style="{ color: getCategoryColor(recipe.category_id).text, backgroundColor: getCategoryColor(recipe.category_id).bg }" class="text-sm mb-4 font-medium inline-block px-2 py-1 rounded-full">
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
                            class="bg-blue-600 text-white px-4 py-2 rounded-md transition duration-300 ease-in-out hover:bg-blue-700">
                      View more details
                    </button>
                    <div class="flex space-x-2">
                      <button @click="openRecipeModal(recipe)"
                              class="bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition-colors duration-300">
                        <PencilIcon class="h-5 w-5" />
                      </button>
                      <button @click="confirmDeleteRecipe(recipe.id)"
                              class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors duration-300">
                        <TrashIcon class="h-5 w-5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <BookOpenIcon class="h-24 w-24 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500 text-lg">No recipes found. Add a new recipe to get started!</p>
            </div>
          </section>
        </div>
      </main>

      <!-- Add/Edit Recipe Modal -->
      <TransitionRoot appear :show="showRecipeModal" as="template">
        <Dialog as="div" @close="closeRecipeModal" class="relative z-50">
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
                <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ editingRecipe ? 'Edit Recipe' : 'Create New Recipe' }}
                  </DialogTitle>
                  <form @submit.prevent="confirmSubmitRecipe" class="space-y-4">
                    <div v-for="field in recipeFormFields" :key="field.id">
                      <label :for="field.id" class="block text-sm font-medium text-gray-700">
                        {{ field.label }}
                      </label>
                      <component 
                        :is="field.component"
                        v-model="recipeForm[field.id]"
                        :id="field.id"
                        v-bind="field.props || {}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      >
                        <template v-if="field.id === 'category_id'">
                          <option value="">Select a category</option>
                          <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.category_name }}
                          </option>
                        </template>
                      </component>
                    </div>
                    <div>
                      <label for="image" class="block text-sm font-medium text-gray-700">Recipe Image</label>
                      <input
                        type="file"
                        id="image"
                        @change="handleImageUpload"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-md file:border-0
                          file:text-sm file:font-semibold
                          file:bg-blue-50 file:text-blue-700
                          hover:file:bg-blue-100"
                      />
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
          <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
              <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                  <div class="flex justify-between items-center mb-4">
                    <DialogTitle as="h2" class="text-3xl font-bold text-gray-800">
                      {{ selectedRecipe?.recipe_name }}
                    </DialogTitle>
                    <button @click="closeRecipeDetails" class="text-gray-500 hover:text-gray-700">
                      <XIcon class="h-6 w-6" />
                    </button>
                  </div>
                  <img :src="selectedRecipe?.url_image || '/placeholder.svg'" :alt="selectedRecipe?.recipe_name" class="w-full h-64 object-cover rounded-xl mb-4">
                  <div class="flex justify-between items-center text-gray-600 mb-4">
                    <span class="flex items-center">
                      <ClockIcon class="w-5 h-5 mr-2" />
                      {{ selectedRecipe?.prep_time }}
                    </span>
                    <span class="flex items-center">
                      <UsersIcon class="w-5 h-5 mr-2" />
                      {{ selectedRecipe?.servings }} servings
                    </span>
                    <span :style="{ color: getCategoryColor(selectedRecipe?.category_id).text, backgroundColor: getCategoryColor(selectedRecipe?.category_id).bg }" class="px-3 py-1 rounded-full text-sm font-medium">
                      {{ getCategoryName(selectedRecipe?.category_id) }}
                    </span>
                  </div>
                  <div class="space-y-4 max-h-[calc(100vh-24rem)] overflow-y-auto pr-4 custom-scrollbar">
                    <div>
                      <h3 class="text-xl font-semibold mb-2">Description</h3>
                      <p class="text-gray-700">{{ selectedRecipe?.description }}</p>
                    </div>
                    <div>
                      <h3 class="text-xl font-semibold mb-2">Ingredients</h3>
                      <ul class="list-disc list-inside text-gray-700">
                        <li v-for="ingredient in selectedRecipe?.ingredients.split('\n')" :key="ingredient" class="mb-2 break-words">
                          {{ ingredient.trim() }}
                        </li>
                      </ul>
                    </div>
                    <div>
                      <h3 class="text-xl font-semibold mb-2">Procedure</h3>
                      <ul class="list-decimal list-inside text-gray-700">
                        <li v-for="(step, index) in selectedRecipe?.procedure.split('\n')" :key="index" class="mb-2 break-words">
                          {{ step.trim() }}
                        </li>
                      </ul>
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
                    <button @click="closeConfirmModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                      Cancel
                    </button>
                    <button @click="confirmAction" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      Confirm
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
} from '@heroicons/vue/24/solid';

const props = defineProps({
  chefs: Array,
  roles: Array,
  users: Array,
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
const editingRecipe = ref(null);
const selectedRecipe = ref(null);
const recipeForm = ref({
  recipe_name: '',
  description: '',
  ingredients: '',
  procedure: '',
  category_id: '',
  prep_time: '',
  servings: '',
  image: null,
});

const confirmModalConfig = ref({
  title: '',
  message: '',
  onConfirm: null
});

// Navigation items
const navItems = [
  { name: 'Dashboard', href: '#dashboard', icon: HomeIcon },
  { name: 'Recipes', href: '#recipes', icon: BookOpenIcon },
];

// Computed properties
const dashboardStats = computed(() => [
  { title: 'Total Recipes', value: totalRecipes.value, icon: BookOpenIcon },
  { title: 'Recipes Created', value: totalRecipesCreated.value, icon: FireIcon },
  { title: 'Total Categories', value: totalCategories.value, icon: TagIcon },
  { title: 'Recent Recipes', value: recentRecipes.value.length, icon: BookmarkIcon },
]);

const recipeFormFields = [
  { id: 'recipe_name', label: 'Recipe Name', component: 'input', props: { type: 'text' } },
  { id: 'description', label: 'Description', component: 'textarea' },
  { id: 'ingredients', label: 'Ingredients', component: 'textarea' },
  { id: 'procedure', label: 'Procedure', component: 'textarea' },
  { id: 'category_id', label: 'Category', component: 'select' },
  { id: 'prep_time', label: 'Preparation Time', component: 'input', props: { type: 'text', placeholder: 'e.g., 30 minutes' } },
  { id: 'servings', label: 'Servings', component: 'input', props: { type: 'number', min: 1 } },
];

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
      case 'beverage':
        return { text: '#00CED1', bg: '#E0F7FA' };
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
      image: null,
    };
  }
  showRecipeModal.value = true;
};

const closeRecipeModal = () => {
  showRecipeModal.value = false;
  editingRecipe.value = null;
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

const confirmDeleteRecipe = (recipeId) => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Delete Recipe',
    message: 'Are you sure you want to delete this recipe?',
    onConfirm: () => deleteRecipe(recipeId)
  };
};

const deleteRecipe = (recipeId) => {
  router.delete(route('chef.deleteRecipe', recipeId), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      recipes.value = recipes.value.filter(recipe => recipe.id !== recipeId);
      updateDashboardStats();
      showConfirmModal.value = true;
      confirmModalConfig.value = {
        title: 'Success',
        message: 'Recipe deleted successfully!',
        onConfirm: closeConfirmModal
      };
    },
    onError: (error) => {
      console.error("Error deleting recipe:", error);
      showConfirmModal.value = true;
      confirmModalConfig.value = {
        title: 'Error',
        message: 'Unable to delete the recipe.',
        onConfirm: closeConfirmModal
      };
    },
  });
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    recipeForm.value.image = file;
  }
};

const confirmSubmitRecipe = () => {
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: editingRecipe.value ? 'Update Recipe' : 'Add Recipe',
    message: `Are you sure you want to ${editingRecipe.value ? 'update' : 'add'} this recipe?`,
    onConfirm: submitRecipe
  };
};

const submitRecipe = () => {
  const formData = new FormData();

  Object.keys(recipeForm.value).forEach(key => {
    if (recipeForm.value[key] !== null && recipeForm.value[key] !== undefined) {
      if (key === 'image' && recipeForm.value[key] instanceof File) {
        formData.append(key, recipeForm.value[key]);
      } else {
        formData.append(key, recipeForm.value[key].toString());
      }
    }
  });

  if (editingRecipe.value) {
    formData.append('_method', 'PUT');
    router.post(route('chef.updateRecipe', editingRecipe.value.id), formData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: handleRecipeSuccess,
      onError: handleRecipeError,
    });
  }
};

const handleRecipeSuccess = (page) => {
  closeRecipeModal();
  updateRecipes(page.props.recipes);
  updateDashboardStats();
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Success',
    message: `Recipe ${editingRecipe.value ? 'updated' : 'added'} successfully!`,
    onConfirm: closeConfirmModal
  };
};

const handleRecipeError = (errors) => {
  const errorMessages = Object.values(errors).join(", ");
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: 'Error',
    message: `Error ${editingRecipe.value ? 'updating' : 'adding'} recipe: ${errorMessages}`,
    onConfirm: closeConfirmModal
  };
};

const viewRecipeDetails = (recipe) => {
  selectedRecipe.value = recipe;
};

const closeRecipeDetails = () => {
  selectedRecipe.value = null;
};

const updateRecipes = (newRecipes) => {
  recipes.value = newRecipes;
  recentRecipes.value = newRecipes.slice(0, 5);
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
    showConfirmModal.value = true;
    confirmModalConfig.value = {
      title: 'Error',
      message: 'Unable to fetch dashboard statistics.',
      onConfirm: closeConfirmModal
    };
  }
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
}
</style>