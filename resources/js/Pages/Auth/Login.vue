<template>
    <div class="min-h-screen flex items-center justify-center p-6" style="background-image: radial-gradient(at 0% 0%, rgba(79,70,229,0.04) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(245,158,11,0.04) 0px, transparent 50%), radial-gradient(at 50% 100%, rgba(79,70,229,0.04) 0px, transparent 50%);">


        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#4F46E5] to-[#6366F1] flex items-center justify-center text-white shadow-xl shadow-[#4F46E5]/20 mb-4">
                    <span class="material-symbols-outlined" style="font-size: 2.25rem;">school</span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight">SISurat</h1>
                <p class="text-[0.65rem] uppercase tracking-[0.2em] font-bold text-slate-400 mt-2">Campus Portal</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-3xl p-10 shadow-2xl shadow-slate-200/50 border border-slate-100">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-extrabold tracking-tight mb-2">Sistem Pengajuan Surat</h2>
                    <p class="text-slate-500 font-medium">Silakan masuk ke akun Anda</p>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Email Kampus</label>
                        <div class="relative">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center text-slate-400 pointer-events-none">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <input v-model="form.email" type="email" placeholder="nama@kampus.ac.id" required autofocus
                                class="w-full pl-11 pr-4 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium focus:outline-none focus:bg-white focus:border-[#4F46E5] focus:ring-4 focus:ring-[#4F46E5]/10 transition-all"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Kata Sandi</label>
                        <div class="relative">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center text-slate-400 pointer-events-none">
                                <span class="material-symbols-outlined">lock</span>
                            </div>
                            <input v-model="form.password" type="password" placeholder="••••••••" required
                                class="w-full pl-11 pr-4 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium focus:outline-none focus:bg-white focus:border-[#4F46E5] focus:ring-4 focus:ring-[#4F46E5]/10 transition-all"/>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full py-4 bg-[#4F46E5] text-white font-bold rounded-2xl shadow-xl shadow-[#4F46E5]/30 hover:bg-[#4338CA] hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2 mt-2 disabled:opacity-70">
                        <span v-if="loading" class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
                        <span>{{ loading ? 'Memproses...' : 'Masuk Sekarang' }}</span>
                        <span v-if="!loading" class="material-symbols-outlined text-xl">login</span>
                    </button>
                </form>
            </div>

            <p class="text-center mt-10 text-slate-400 text-sm font-medium">© 2026 Politeknik Pajajaran</p>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from 'vue';
const showFlash = inject('showFlash');
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const updateUser = inject('updateUser');
const form = ref({ email: '', password: '' });
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    try {
        const response = await axios.post('/api/login', {
            email: form.value.email,
            password: form.value.password,
        });
        
        await updateUser();
        showFlash('Login berhasil!');
        setTimeout(() => {
            router.push(response.data.redirect || '/dashboard');
        }, 500);
    } catch (e) {
        showFlash(e.response?.data?.message || 'Terjadi kesalahan.', 'error');
    } finally {
        loading.value = false;
    }
};
</script>
