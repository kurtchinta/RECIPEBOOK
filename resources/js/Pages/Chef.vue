<template>
  <Head title="Chef's Command Center" />
  <AuthenticatedLayout>
    <div class="flex h-screen bg-gray-100">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-md">
        <div class="p-6 flex flex-col items-center">
          <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=random`" alt="User Avatar" class="w-24 h-24 rounded-full mb-4" />
          <p class="mt-2 text-xl font-semibold text-gray-800">{{ getRole($page.props.auth.user.role_id) }}</p>
          <h2 class="text-xl font-semibold text-gray-800">{{ $page.props.auth.user.name }}</h2>
          <p class="text-sm text-gray-600">{{ $page.props.auth.user.email }}</p>
        </div>
        <nav class="mt-6">
          <a v-for="item in navItems" :key="item.name" :href="item.href" 
             class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition-colors duration-200">
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
          
          <!-- Recent Recipes -->
          <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Recipes</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="recipe in recentRecipes" :key="recipe.id" 
                   class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ recipe.recipe_name }}</h3>
                <p class="text-sm text-gray-600 mb-2">{{ getCategoryName(recipe.category_id) }}</p>
                <p class="text-sm text-gray-500">Created: {{ formatDate(recipe.created_at) }}</p>
              </div>
            </div>
          </section>
          
          <!-- Recipe Management -->
          <section>
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-3xl font-bold text-gray-800">Recipe Collection</h2>
              <button @click="showAddRecipeForm = true" 
                      class="bg-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center shadow-lg">
                <PlusIcon class="h-5 w-5 mr-2" />
                Add New Recipe
              </button>
            </div>
            
            <!-- Recipe Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="recipe in recipes" :key="recipe.id" 
                   class="bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 transform hover:scale-105">
                <img :src="recipe.url_image || '/placeholder.svg'" :alt="recipe.recipe_name" class="w-full h-48 object-cover">
                <div class="p-6">
                  <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ recipe.recipe_name }}</h3>
                  <p class="text-sm text-gray-600 mb-4">{{ getCategoryName(recipe.category_id) }}</p>
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
                      View Recipe
                    </button>
                    <div class="flex space-x-2">
                      <button @click="editRecipe(recipe)"
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
          </section>
        </div>
      </main>

      <!-- Add/Edit Recipe Modal -->
      <TransitionRoot appear :show="showAddRecipeForm" as="template">
        <Dialog as="div" @close="closeRecipeModal" class="relative z-50">
          <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
              <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ editingRecipe ? 'Edit Recipe' : 'Create New Recipe' }}
                  </DialogTitle>
                  <form @submit.prevent="confirmSubmitRecipe" class="space-y-4">
                    <div v-for="field in recipeFormFields" :key="field.id">
                      <label :for="field.id" class="block text-sm font-medium text-gray-700">
                        {{ field.label }} <span class="text-red-500" v-if="field.props?.required">*</span>
                      </label>
                      <component 
                        :is="field.component"
                        v-model="recipeForm[field.id]"
                        :id="field.id"
                        v-bind="field.props || {}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      >
                        <option v-if="field.id === 'category_id'" value="">Select a category</option>
                        <option v-if="field.id === 'category_id'" v-for="category in categories" :key="category.id" :value="category.id">
                          {{ category.category_name }}
                        </option>
                      </component>
                      <p v-if="!recipeForm[field.id] && field.props?.required" class="mt-1 text-sm text-red-500">
                        This field is required
                      </p>
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
                      <button type="button" @click="closeRecipeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                      </button>
                      <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                      {{ getCategoryName(selectedRecipe?.category_id) }}
                    </span>
                  </div>
                  <p class="text-gray-700 mb-4">{{ selectedRecipe?.description }}</p>
                  <div class="mb-4">
                    <h3 class="text-xl font-semibold mb-2">Ingredients</h3>
                    <p class="text-gray-700">{{ selectedRecipe?.ingredients }}</p>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold mb-2">Procedure</h3>
                    <p class="text-gray-700">{{ selectedRecipe?.procedure }}</p>
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
  showAddRecipeForm: Boolean,
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

const showAddRecipeForm = ref(false);
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

// Confirmation modal
const showConfirmModal = ref(false);
const confirmModalConfig = ref({
  title: '',
  message: '',
  onConfirm: null
});

// Navigation items
const navItems = [
  { name: 'Dashboard', href: route('chef.dashboard'), icon: HomeIcon },
  { name: 'Recipes', href: '#recipes', icon: BookOpenIcon },
  { name: 'Statistics', href: '#statistics', icon: ChartBarIcon },
];

// Computed properties
const dashboardStats = computed(() => [
  { title: 'Total Recipes', value: totalRecipes.value, icon: BookOpenIcon },
  { title: 'Recipes Created', value: totalRecipesCreated.value, icon: FireIcon },
  { title: 'Total Categories', value: totalCategories.value, icon: TagIcon },
  { title: 'Recent Recipes', value: recentRecipes.value.length, icon: BookmarkIcon },
]);

const recipeFormFields = [
  { id: 'recipe_name', label: 'Recipe Name', component: 'input', props: { type: 'text', required: true } },
  { id: 'description', label: 'Description', component: 'textarea', props: { rows: 3, required: true } },
  { id: 'ingredients', label: 'Ingredients', component: 'textarea', props: { rows: 3, required: true } },
  { id: 'procedure', label: 'Procedure', component: 'textarea', props: { rows: 3, required: true } },
  { id: 'category_id', label: 'Category', component: 'select', props: { required: true } },
  { id: 'prep_time', label: 'Preparation Time', component: 'input', props: { type: 'text', required: true, placeholder: 'e.g., 30 minutes' } },
  { id: 'servings', label: 'Servings', component: 'input', props: { type: 'number', required: true, min: 1 } },
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

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
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
  showConfirmModal.value = true;
  confirmModalConfig.value = {
    title: type.charAt(0).toUpperCase() + type.slice(1),
    message: message,
    onConfirm: closeConfirmModal
  };
};

const closeRecipeModal = () => {
  showAddRecipeForm.value = false;
  editingRecipe.value = null;
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
};

const editRecipe = (recipe) => {
  editingRecipe.value = recipe;
  recipeForm.value = { ...recipe };
  showAddRecipeForm.value = true;
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
      showAlert("Recipe deleted successfully!", "success");
    },
    onError: (error) => {
      console.error("Error deleting recipe:", error);
      showAlert("Error: Unable to delete the recipe.", "error");
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
  
  const requiredFields = [
    'recipe_name',
    'description',
    'ingredients',
    'procedure',
    'category_id',
    'prep_time',
    'servings'
  ];

  const emptyFields = requiredFields.filter(field => !recipeForm.value[field]);
  
  if (emptyFields.length > 0) {
    showAlert("Please fill in all required fields: " + emptyFields.join(", "), "error");
    return;
  }

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
      onSuccess: (page) => {
        closeRecipeModal();
        updateRecipes(page.props.recipes);
        updateDashboardStats();
        showAlert("Recipe updated successfully!", "success");
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join(", ");
        showAlert("Error updating recipe: " + errorMessages, "error");
      },
    });
  } else {
    router.post(route('chef.storeRecipe'), formData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        closeRecipeModal();
        updateRecipes(page.props.recipes);
        updateDashboardStats();
        showAlert("Recipe added successfully!", "success");
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join(", ");
        showAlert("Error adding recipe: " + errorMessages, "error");
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
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
    showAlert("Error: Unable to fetch dashboard statistics.", "error");
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchDashboardData();
  updateDashboardStats();
});
</script>