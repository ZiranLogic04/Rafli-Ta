<template>
    <div class="relative min-h-screen">
        <!-- Flash notification -->
        <div v-if="flash" class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] notify-slide-in">
            <div class="flex items-center gap-3 px-5 py-3 rounded-2xl shadow-xl border bg-white" :class="flash.type === 'success' ? 'border-emerald-100 text-emerald-800' : 'border-red-100 text-red-800'">
                <span class="material-symbols-outlined text-xl">{{ flash.type === 'success' ? 'check_circle' : 'warning' }}</span>
                <span class="text-sm font-bold">{{ flash.message }}</span>
                <button @click="flash = null" class="ml-2 opacity-50 hover:opacity-100"><span class="material-symbols-outlined text-[18px]">close</span></button>
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
