<template>
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <component :is="icon" class="w-8 h-8 text-blue-500 mr-4" />
        <div>
          <h4 class="text-lg font-semibold text-gray-700">{{ title }}</h4>
          <p class="text-3xl font-bold text-gray-900">{{ value }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { Book, Users, ChefHat, Shield } from 'lucide-vue-next';
  
  const props = defineProps({
    title: String,
    value: Number,
    icon: String,
  });
  
  const iconComponents = {
    Book,
    Users,
    ChefHat,
    Shield,
  };
  
  const icon = computed(() => iconComponents[props.icon] || Book);
  </script>
  
  