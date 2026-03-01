<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto flex flex-col gap-10 p-2 md:p-6">
            <!-- Welcome Header Berdasarkan Template -->
            <div class="relative bg-white rounded-3xl p-8 md:p-10 shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#4F46E5]/5 rounded-full -mr-20 -mt-20 blur-3xl transition-all group-hover:bg-[#4F46E5]/10"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#4F46E5] to-[#6366F1]">{{ user?.name || 'Pengguna' }}</span>
                        </h2>
                        <p class="text-slate-500 text-lg font-medium max-w-xl">
                            Kebutuhan persuratan Anda tersedia dalam satu platform. Apa yang ingin Anda kerjakan hari ini?
                        </p>
                    </div>
                    <div class="flex shrink-0">
                        <div class="flex flex-col items-end gap-2 text-right">
                            <span class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 text-xs font-bold uppercase tracking-wider">
                                Semester Aktif
                            </span>
                            <p class="text-[11px] font-medium text-slate-400 italic capitalize">Peran: {{ user?.role }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Utama Dinamis -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div v-for="type in letterTypes" :key="type.id" 
                    class="card-shimmer group relative flex flex-col bg-white rounded-[2rem] shadow-lg shadow-slate-200/40 border border-slate-100 p-8 transition-all hover:-translate-y-2">
                    <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500 font-bold', getIconConfig(type.name).bg, getIconConfig(type.name).text]">
                        <span class="material-symbols-outlined text-4xl">{{ getIconConfig(type.name).icon }}</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3">{{ type.name }}</h3>
                    <p class="text-slate-500 text-sm mb-8 leading-relaxed font-medium">
                        {{ type.description || 'Pengajuan permohonan ' + type.name + ' secara resmi melalui sistem.' }}
                    </p>
                    <router-link :to="{ path: '/letters/create', query: { type_id: type.id } }" 
                        :class="['mt-auto w-full py-4 px-6 text-sm font-bold rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2', getIconConfig(type.name).btn]">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        {{ getIconConfig(type.name).btnText }}
                    </router-link>
                </div>
            </div>

            <!-- Pesan jika tidak ada surat -->
            <div v-if="letterTypes.length === 0" class="text-center py-20 bg-white rounded-[2rem] border border-dashed border-slate-200">
                <p class="text-slate-400 font-medium">Anda belum memiliki izin akses untuk jenis surat apapun.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';

const user = inject('user');
const letterTypes = ref([]);

const getIconConfig = (name) => {
    const n = name.toLowerCase();
    
    // Surat Tugas / Perjalanan
    if (n.includes('tugas')) {
        return { 
            icon: 'travel_explore', bg: 'bg-indigo-100', text: 'text-indigo-600', 
            btn: 'bg-white border-2 border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-200', 
            btnText: 'Buat Surat Baru' 
        };
    }
    
    // Rekomendasi
    if (n.includes('rekomendasi')) {
        return { 
            icon: 'verified', bg: 'bg-amber-100', text: 'text-amber-600', 
            btn: 'bg-white border-2 border-amber-100 text-amber-600 hover:bg-amber-600 hover:text-white hover:border-amber-600 hover:shadow-lg hover:shadow-amber-200', 
            btnText: 'Ajukan Rekomendasi' 
        };
    }
    
    // Izin / Cuti
    if (n.includes('izin') || n.includes('cuti')) {
        return { 
            icon: 'event_busy', bg: 'bg-emerald-100', text: 'text-emerald-600', 
            btn: 'bg-white border-2 border-emerald-100 text-emerald-600 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 hover:shadow-lg hover:shadow-emerald-200', 
            btnText: 'Ajukan Perizinan' 
        };
    }

    // Keterangan / Kuliah
    if (n.includes('keterangan')) {
        return { 
            icon: 'school', bg: 'bg-blue-100', text: 'text-blue-600', 
            btn: 'bg-white border-2 border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 hover:shadow-lg hover:shadow-blue-200', 
            btnText: 'Buat Surat' 
        };
    }

    // Undangan
    if (n.includes('undangan')) {
        return { 
            icon: 'mail', bg: 'bg-violet-100', text: 'text-violet-600', 
            btn: 'bg-white border-2 border-violet-100 text-violet-600 hover:bg-violet-600 hover:text-white hover:border-violet-600 hover:shadow-lg hover:shadow-violet-200', 
            btnText: 'Buat Surat' 
        };
    }

    // Keputusan / Edaran
    if (n.includes('keputusan') || n.includes('edaran')) {
        return { 
            icon: 'gavel', bg: 'bg-rose-100', text: 'text-rose-600', 
            btn: 'bg-white border-2 border-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 hover:shadow-lg hover:shadow-rose-200', 
            btnText: 'Buat Surat' 
        };
    }

    // Default
    return { 
        icon: 'description', bg: 'bg-slate-100', text: 'text-slate-600', 
        btn: 'bg-white border-2 border-slate-200 text-slate-600 hover:bg-slate-800 hover:text-white hover:border-slate-800 hover:shadow-lg hover:shadow-slate-200', 
        btnText: 'Buat Surat' 
    };
};

onMounted(async () => {
    const res = await axios.get('/api/dashboard');
    letterTypes.value = res.data.letterTypes || [];
});
</script>
