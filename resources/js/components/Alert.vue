<template>
    <div class="alert" :class="`alert-${type}`" v-if="visible">
        <div class="alert-icon">
            <svg v-if="type === 'success'" class="alert-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg v-else-if="type === 'error'" class="alert-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg v-else-if="type === 'warning'" class="alert-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <svg v-else-if="type === 'info'" class="alert-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="alert-content">{{ message }}</div>
        <button @click="close" class="alert-close">
            <svg class="alert-close-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

type AlertType = 'success' | 'error' | 'warning' | 'info';

interface Props {
    modelValue: boolean;
    type: AlertType;
    message: string;
    duration?: number;
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void;
    (e: 'close'): void;
}

const props = withDefaults(defineProps<Props>(), {
    duration: 3000,
});

const emit = defineEmits<Emits>();

const visible = ref(props.modelValue);

let timeoutId: ReturnType<typeof setTimeout> | null = null;

const close = (): void => {
    visible.value = false;
    emit('update:modelValue', false);
    emit('close');
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
};

watch(
    () => props.modelValue,
    (newValue) => {
        visible.value = newValue;
        if (newValue && props.duration > 0) {
            timeoutId = setTimeout(close, props.duration);
        }
    },
    { immediate: true }
);
</script>

<style lang="scss" scoped>
.alert {
    position: fixed;
    top: 1rem;
    right: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
    max-width: 24rem;
    border: 1px solid;

    &-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    &-error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    &-warning {
        background-color: #fff3cd;
        border-color: #ffeeba;
        color: #856404;
    }

    &-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    &-icon {
        flex-shrink: 0;

        &-svg {
            width: 1.25rem;
            height: 1.25rem;
        }
    }

    &-content {
        flex: 1;
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    &-close {
        flex-shrink: 0;
        padding: 0.25rem;
        background: transparent;
        border: none;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;

        &:hover {
            opacity: 1;
        }

        &-svg {
            width: 1rem;
            height: 1rem;
        }
    }
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
