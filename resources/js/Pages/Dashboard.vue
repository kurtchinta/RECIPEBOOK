<template>
  <AuthenticatedLayout>
    <Head :title="`Welcome, ${$page.props.auth.user.name}`" />
    <div class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200">
      <!-- Header -->
      <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
              <div class="text-left">
                <p class="text-lg font-semibold text-gray-900">{{ $page.props.auth.user.name }}</p>
                <p class="text-sm text-gray-500">{{ $page.props.auth.user.email }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                {{ getRole($page.props.auth.user.role_id ) }}
              </span>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white p-8 rounded-2xl shadow-lg">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-6">Welcome to Your Recipe Book Dashboard!</h2>
          <p class="text-xl text-gray-600 mb-8">Ready to {{ getWelcomeMessage(getRole($page.props.auth.user.role_id )) }}?</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div v-for="(feature, index) in getRoleFeatures($page.props.auth.user.role_id)" :key="index" 
                class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
              <div class="flex flex-col items-center text-center">
                <div class="bg-blue-500 p-3 rounded-full mb-4">
                  <component :is="feature.icon" class="h-8 w-8 text-white" />
                </div>
                <h3 class="font-bold text-gray-800 text-lg mb-2">{{ feature.title }}</h3>
                <p class="text-gray-600 text-sm">{{ feature.description }}</p>
              </div>
            </div>
          </div>
          
          <div class="text-center">
            <Link :href="getDashboardRoute($page.props.auth.user.role_id)" 
                  class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:-translate-y-1">
              <component :is="getDashboardIcon($page.props.auth.user.role_id)" class="mr-2 h-5 w-5" />
              {{ getDashboardButtonText($page.props.auth.user.role_id) }}
            </Link>
          </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="(stat, index) in getQuickStats($page.props.auth.user.role_id)" :key="index"
              class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500 mb-1">{{ stat.title }}</p>
                <p class="text-2xl font-semibold text-gray-800">{{ stat.value }}</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-full">
                <component :is="stat.icon" class="h-6 w-6 text-blue-500" />
              </div>
            </div>
            <div class="mt-4">
              <div class="flex items-center">
                <span class="text-green-500 mr-2">
                  <component :is="stat.trendIcon" class="h-4 w-4" />
                </span>
                <span class="text-sm text-gray-600">{{ stat.change }}</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
  ChefHat, Users, Book, Activity, Utensils, Star, TrendingUp, 
  ClipboardList, UserPlus, FileText, Eye, Search, Heart, 
  Award, ThumbsUp, MessageCircle, Bookmark, Coffee, Clock
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  users: {
    type: Array,
    required: true,
  },
  roles: {
    type: Array,
    default: () => [],
  },
});

// Get role based on user ID
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
const getWelcomeMessage = (roleId) => {
  const messages = {
    1: "manage the Recipe Book platform", // Admin
    2: "create culinary masterpieces",    // Chef
    3: "explore delicious recipes",       // User
  };
  return messages[roleId] || "use the Recipe Book platform"; // Default message
};


const getRoleFeatures = (roleId) => {
  const features = {
    1: [
      { icon: Users, title: "User Management", description: "Oversee all platform users" },
      { icon: Book, title: "Recipe Oversight", description: "Curate and manage recipes" },
      { icon: Activity, title: "Platform Insights", description: "Monitor site activity" },
    ],
    2: [
      { icon: Utensils, title: "Recipe Creation", description: "Craft your signature dishes" },
    ],
    3: [
      { icon: Search, title: "Recipe Discovery", description: "Find your next favorite meal" },
    ]
  };
  return features[roleId] || [];
};

const getDashboardRoute = (roleId) => {
  const routes = {
    1: route('admin'),
    2: route('chef'),
    3: route('user')
  };
  return routes[roleId] || route('dashboard');
};

const getDashboardIcon = (roleId) => {
  const icons = {
    1: ClipboardList,
    2: ChefHat,
    3: Book
  };
  return icons[roleId] || Activity;
};

const getDashboardButtonText = (roleId) => {
  const texts = {
    1: 'Go to Admin Dashboard',
    2: 'Manage Your Recipes',
    3: 'Explore Recipes'
  };
  return texts[roleId] || 'Go to Dashboard';
};

const getQuickStats = (roleId) => {
  const stats = {
    1: [
      { title: 'Total Users', value: '5,231',  icon: Users, },
      { title: 'Total Chefs', value: '142',  icon: ChefHat, },
      { title: 'Total Recipes', value: '1,205',  icon: Book, },
    ],
    2: [
      { title: 'Your Recipes', value: '28',  icon: Book, trendIcon: TrendingUp },
      { title: 'Total Views', value: '15.2K',  icon: Eye, trendIcon: TrendingUp },
    ],
  };
  return stats[roleId] || [];
};
</script>

<style scoped>
/* Add any additional component-specific styles here */
</style>