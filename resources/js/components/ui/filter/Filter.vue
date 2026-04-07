<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Input } from '@/components/ui/input';
import { Search, ChevronDown } from 'lucide-vue-next';

interface FilterOption {
    label: string;
    value: string | number;
}

interface FilterConfig {
    key: string;
    type: 'text' | 'select' | 'multi-checkbox'; // Bổ sung type mới
    placeholder?: string;
    options?: FilterOption[];
    className?: string;
}

const props = defineProps<{
    config: FilterConfig[];
    modelValue: Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);

// Cập nhật giá trị chung (Cho text và select thường)
const updateFilter = (key: string, value: any) => {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
};

// ==========================================
// LOGIC RIÊNG CHO MULTI-CHECKBOX
// ==========================================
const openDropdown = ref<string | null>(null);

const toggleDropdown = (key: string) => {
    openDropdown.value = openDropdown.value === key ? null : key;
};

// Đóng menu khi click ra ngoài
const closeDropdown = (e: Event) => {
    if (!(e.target as HTMLElement).closest('.multi-select-container')) {
        openDropdown.value = null;
    }
};
onMounted(() => document.addEventListener('click', closeDropdown));
onUnmounted(() => document.removeEventListener('click', closeDropdown));

// Xử lý khi tick vào từng checkbox
const handleMultiSelect = (key: string, value: string | number, isChecked: boolean) => {
    // Đảm bảo dữ liệu cũ luôn là mảng
    const currentArr = Array.isArray(props.modelValue[key]) ? [...props.modelValue[key]] : [];
    
    if (isChecked) {
        currentArr.push(value); // Thêm vào mảng nếu tick
    } else {
        const index = currentArr.indexOf(value);
        if (index > -1) currentArr.splice(index, 1); // Rút khỏi mảng nếu bỏ tick
    }
    
    emit('update:modelValue', { ...props.modelValue, [key]: currentArr });
};
</script>

<template>
    <div class="flex flex-col lg:flex-row flex-wrap items-stretch lg:items-center gap-3 w-full">
        <template v-for="item in config" :key="item.key">
            
            <div v-if="item.type === 'text'" :class="['relative w-full shrink-0', item.className || 'lg:w-64']">
                <Search v-if="item.key === 'search'" class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                    :modelValue="modelValue[item.key]"
                    @update:modelValue="(val) => updateFilter(item.key, val)"
                    :type="item.key.includes('salary') ? 'number' : 'text'"
                    :placeholder="item.placeholder"
                    :class="['bg-white w-full', item.key === 'search' ? 'pl-8' : 'pl-3']"
                />
            </div>

            <div v-else-if="item.type === 'multi-checkbox'" :class="['relative multi-select-container w-full shrink-0', item.className || 'lg:w-48']">
                <div @click="toggleDropdown(item.key)" class="flex items-center justify-between w-full px-3 py-2 text-sm bg-white border border-input rounded-md cursor-pointer shadow-sm hover:bg-gray-50 h-10">
                    <span class="truncate pr-2 text-gray-600">
                        {{ item.placeholder }}
                        <span v-if="modelValue[item.key]?.length > 0" class="ml-2 px-2 py-0.5 rounded-full bg-blue-600 text-white text-xs font-bold">
                            {{ modelValue[item.key].length }} đã chọn
                        </span>
                    </span>
                    <ChevronDown class="h-4 w-4 opacity-50" />
                </div>

                <div v-if="openDropdown === item.key" class="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                    <div class="p-2 space-y-1">
                        <label 
                            v-for="opt in item.options" 
                            :key="opt.value" 
                            class="flex items-center space-x-3 px-2 py-2 hover:bg-gray-50 rounded cursor-pointer transition-colors"
                        >
                            <input
                                type="checkbox"
                                :value="opt.value"
                                :checked="modelValue[item.key]?.includes(opt.value)"
                                @change="(e) => handleMultiSelect(item.key, opt.value, (e.target as HTMLInputElement).checked)"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4"
                            />
                            <span class="text-sm font-medium text-gray-700 truncate">{{ opt.label }}</span>
                        </label>
                        <div v-if="!item.options || item.options.length === 0" class="px-2 py-2 text-sm text-gray-500 text-center">
                            Không có dữ liệu
                        </div>
                    </div>
                </div>
            </div>

        </template>

        <div class="flex gap-2 w-full lg:w-auto shrink-0 justify-end mt-1 lg:mt-0">
            <slot />
        </div>
    </div>
</template>