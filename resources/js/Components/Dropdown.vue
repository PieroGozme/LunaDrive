<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-white',
    },
    /** bottom | top | right */
    placement: {
        type: String,
        default: 'bottom',
    },
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const positionClasses = computed(() => {
    if (props.placement === 'top') {
        if (props.align === 'left') {
            return 'bottom-full left-0 mb-2 origin-bottom-left';
        }
        return 'bottom-full right-0 mb-2 origin-bottom-right';
    }

    if (props.placement === 'right') {
        return 'left-full bottom-0 ml-2 origin-bottom-left';
    }

    if (props.align === 'left') {
        return 'mt-2 start-0 origin-top-left';
    }

    if (props.align === 'right') {
        return 'mt-2 end-0 origin-top-right';
    }

    return 'mt-2 origin-top';
});
</script>

<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        />

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 rounded-md shadow-lg"
                :class="[widthClass, positionClasses]"
                @click="open = false"
            >
                <div
                    class="rounded-md ring-1 ring-black/5"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
