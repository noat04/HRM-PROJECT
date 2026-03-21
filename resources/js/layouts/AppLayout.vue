<script setup lang="ts">
import { ref } from 'vue'; // Cần thiết để sử dụng ref()
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Slider } from '@/components/ui/slider';
import type { BreadcrumbItem } from '@/types';
const sliderValue = ref([50]);
type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <slot />
        </div>
    </AppLayout>
</template>
