<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

// Định nghĩa props để nhận class CSS tùy chỉnh từ bên ngoài truyền vào
const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

// Sử dụng defineModel (Tính năng cực mạnh của Vue 3.4+)
// Giúp tự động đồng bộ dữ liệu v-model với component cha mà không cần viết emit rườm rà
const modelValue = defineModel<string | number>()
</script>

<template>
  <textarea
    v-model="modelValue"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
  />
</template>