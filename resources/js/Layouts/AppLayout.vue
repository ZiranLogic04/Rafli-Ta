<template>
    <div class="flex h-screen w-full overflow-hidden">
        <!-- SIDEBAR -->
        <aside class="hidden md:flex flex-col w-72 bg-white border-r border-slate-100 h-full shrink-0">
            <div class="flex items-center gap-3 px-8 py-8">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-[#4F46E5] to-[#6366F1] text-white shadow-lg shadow-[#4F46E5]/20">
                    <span class="material-symbols-outlined font-light">school</span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-lg font-extrabold tracking-tight text-slate-900 leading-none">SISurat</h1>
                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mt-1">Campus Portal</p>
                </div>
            </div>

            <nav class="flex-1 flex flex-col gap-1.5 px-4 py-4 overflow-y-auto">
                <!-- Dashboard -->
                <router-link
                    :to="user?.role === 'admin' ? '/admin/dashboard' : '/dashboard'"
                    class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                    :class="isActive('dashboard') ? 'bg-[#4F46E5] text-white shadow-md shadow-[#4F46E5]/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#4F46E5]'"
                >
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-sm font-semibold">Dashboard</span>
                </router-link>

                <template v-if="user">
                    <template v-if="user.role === 'admin'">
                        <router-link to="/letters" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all" :class="isActive('user.letters') ? 'bg-[#4F46E5] text-white shadow-md shadow-[#4F46E5]/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#4F46E5]'">
                            <span class="material-symbols-outlined">description</span>
                            <span class="text-sm font-medium">Surat Saya</span>
                        </router-link>
                        <router-link to="/admin/letters" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all" :class="isActive('admin.letters') ? 'bg-[#4F46E5] text-white shadow-md shadow-[#4F46E5]/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#4F46E5]'">
                            <span class="material-symbols-outlined">inbox</span>
                            <span class="text-sm font-medium">Persetujuan Surat</span>
                        </router-link>
                        <router-link to="/admin/types" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all" :class="isActive('admin.types') ? 'bg-[#4F46E5] text-white shadow-md shadow-[#4F46E5]/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#4F46E5]'">
                            <span class="material-symbols-outlined">category</span>
                            <span class="text-sm font-medium">Jenis Surat</span>
                        </router-link>
                        <router-link to="/admin/users" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all" :class="isActive('admin.users') ? 'bg-[#4F46E5] text-white shadow-md shadow-[#4F46E5]/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#4F46E5]'">
                            <span class="material-symbols-outlined">group</span>
                            <span class="text-sm font-medium">Data Pengguna</span>
                        </router-link>
                    </template>
                    <template v-else>
                        <router-link to="/letters" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all" :class="isActive('user.letters') ? 'bg-[#4F46E5] text-white shadow-md shadow-[#4F46E5]/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#4F46E5]'">
                            <span class="material-symbols-outlined">description</span>
                            <span class="text-sm font-medium">Riwayat Surat</span>
                        </router-link>
                    </template>
                </template>

            </nav>

            <!-- User Profile Footer -->
            <div class="p-6" v-if="user">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 cursor-pointer group">
                    <div class="relative w-10 h-10 rounded-xl overflow-hidden ring-2 ring-white shadow-sm">
                        <img :alt="user.name" class="w-full h-full object-cover" :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`"/>
                    </div>
                    <div class="flex flex-col overflow-hidden">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ user.name }}</p>
                        <p class="text-[11px] font-medium text-slate-500 truncate capitalize">{{ user.role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex flex-col flex-1 h-full overflow-hidden relative">
            <!-- TOP HEADER -->
            <header class="h-20 flex items-center justify-between px-8 bg-white/80 backdrop-blur-md border-b border-slate-100 shrink-0 z-20">
                <div class="flex items-center gap-4">
                    <div class="hidden sm:block">
                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest">Selamat Datang</h2>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center bg-slate-100 rounded-full px-4 py-1.5 border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        <span class="text-[11px] font-bold text-slate-600">SISTEM AKTIF</span>
                    </div>
                    <button @click="logout" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </div>
            </header>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 overflow-y-auto p-6 md:p-10 mesh-gradient">
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, provide, watch, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const user = inject('user');
const flash = inject('flash', ref(null)); // Keep for reactive UI if needed, but App.vue handles it

const isActive = (name) => {
    const path = route.path;
    if (name === 'dashboard') return path === '/dashboard' || path === '/admin/dashboard';
    if (name === 'user.letters') return path.startsWith('/letters');
    if (name === 'admin.letters') return path === '/admin/letters';
    if (name === 'admin.types') return path === '/admin/types';
    if (name === 'admin.users') return path === '/admin/users';
    return false;
};

const showFlash = inject('showFlash');

const logout = async () => {
    try {
        await axios.post('/api/logout');
    } catch {}
    user.value = null;
    router.push('/login');
};

// provide('showFlash', showFlash); // Removed, provided by App.vue
</script>
