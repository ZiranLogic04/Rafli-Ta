<template>
    <div class="app-root">
        <!-- Flash notification -->
        <div v-if="flash" class="flash-notification notify-slide-in">
            <div class="flash-content" :class="flash.type === 'success' ? 'flash-success' : 'flash-error'">
                <span class="material-symbols-outlined flash-icon">{{ flash.type === 'success' ? 'check_circle' : 'warning' }}</span>
                <span class="flash-message">{{ flash.message }}</span>
                <button @click="flash = null" class="flash-close"><span class="material-symbols-outlined flash-close-icon">close</span></button>
            </div>
        </div>
        <router-view />
    </div>
</template>

<script setup>
import { ref, onMounted, provide } from 'vue';
import axios from 'axios';

const user = ref(null);
const flash = ref(null);

const showFlash = (message, type = 'success') => {
    flash.value = { message, type };
    setTimeout(() => { flash.value = null; }, 4000);
};

const fetchUser = async () => {
    try {
        const res = await axios.get('/api/user');
        user.value = res.data;
    } catch {
        user.value = null;
    }
};

onMounted(fetchUser);
provide('user', user);
provide('updateUser', fetchUser);
provide('showFlash', showFlash);
</script>

<style scoped>
.app-root {
    position: relative;
    min-height: 100vh;
}

.flash-notification {
    position: fixed;
    top: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
}

.flash-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    background: white;
}

.flash-success {
    border: 1px solid var(--emerald-100);
    color: var(--emerald-700);
}

.flash-error {
    border: 1px solid var(--rose-100);
    color: var(--rose-700);
}

.flash-icon {
    font-size: 1.25rem;
}

.flash-message {
    font-size: 0.875rem;
    font-weight: 700;
}

.flash-close {
    margin-left: 0.5rem;
    opacity: 0.5;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    transition: opacity 0.2s;
}

.flash-close:hover {
    opacity: 1;
}

.flash-close-icon {
    font-size: 18px;
}
</style>
