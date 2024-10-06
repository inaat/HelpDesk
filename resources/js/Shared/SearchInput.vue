<template>
    <div class="flex items-center">
        <div class="flex w-full bg-white rounded shadow">
            <input class="relative px-6 py-3 w-full rounded focus:shadow-outline" autocomplete="off" type="text" name="search" :placeholder="__('Search...')" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />
        </div>
        <button v-if="!disableReset" class="ml-3 rtl:mr-3 text-gray-500 hover:text-gray-700 focus:text-indigo-500 text-sm" type="button" @click="$emit('reset')">{{ __('Reset') }}</button>
    </div>
</template>

<script>
import Dropdown from '@/Shared/Dropdown'

export default {
    components: {
        Dropdown,
    },
    props: {
        modelValue: String,
        disableTrash: String,
        maxWidth: {
            type: Number,
            default: 300,
        },
        disableReset: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:modelValue', 'reset'],
}
</script>
