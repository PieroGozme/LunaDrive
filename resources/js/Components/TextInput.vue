<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border border-ink-300 bg-white text-ink-950 shadow-sm placeholder:text-ink-400 focus:border-luna-500 focus:ring-luna-500"
        v-model="model"
        ref="input"
    />
</template>
