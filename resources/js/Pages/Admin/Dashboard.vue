<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto flex flex-col gap-10 p-2 md:p-6">
            <!-- Welcome Header Berdasarkan Template -->
            <div class="relative bg-white rounded-3xl p-8 md:p-10 shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#4F46E5]/5 rounded-full -mr-20 -mt-20 blur-3xl transition-all group-hover:bg-[#4F46E5]/10"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#4F46E5] to-[#6366F1]">{{ user?.name || 'Administrator' }}</span>
                        </h2>
                        <p class="text-slate-500 text-lg font-medium max-w-xl">
                            Pantau dan kelola seluruh proses persuratan kampus secara efisien.
                        </p>
                    </div>
                    <div class="flex shrink-0">
                        <span class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 text-xs font-bold uppercase tracking-wider">
                            Administrator
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">pending</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pending</span>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900">{{ stats.pending }}</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Menunggu persetujuan</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">check_circle</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Disetujui</span>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900">{{ stats.approved }}</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Surat disetujui</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">cancel</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ditolak</span>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900">{{ stats.rejected }}</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Surat ditolak</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">group</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">User</span>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900">{{ stats.users }}</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Total pengguna</p>
                </div>
            </div>

            <!-- Kartu Akses Surat Dinamis -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div v-for="type in letterTypes" :key="type.id" 
                    class="card-shimmer group relative flex flex-col bg-white rounded-[2rem] shadow-lg shadow-slate-200/40 border border-slate-100 p-8 transition-all hover:-translate-y-2">
                    <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500 font-bold', getIconConfig(type.name).bg, getIconConfig(type.name).text]">
                        <span class="material-symbols-outlined text-4xl">{{ getIconConfig(type.name).icon }}</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3">{{ type.name }}</h3>
                    <p class="text-slate-500 text-sm mb-8 leading-relaxed font-medium">
                        {{ type.description || 'Kelola dan buat permohonan ' + type.name + ' secara efisien.' }}
                    </p>
                    <router-link :to="{ path: '/letters/create', query: { type_id: type.id } }" 
                        :class="['mt-auto w-full py-4 px-6 text-sm font-bold rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2', getIconConfig(type.name).btn]">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        {{ getIconConfig(type.name).btnText }}
                    </router-link>
                </div>
            </div>

            <!-- Recent Letters Table -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center"><span class="material-symbols-outlined text-white text-[18px]">inbox</span></div>
                        <h3 class="text-xl font-bold text-slate-900">Surat Terbaru</h3>
                    </div>
                    <router-link to="/admin/letters" class="text-xs font-bold text-[#4F46E5] uppercase tracking-widest hover:text-[#4338CA] transition-colors border-b-2 border-[#4F46E5]/20 pb-0.5">Lihat Semua</router-link>
                </div>
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nama Pengaju</th>
                                    <th class="px-8 py-5 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Jenis</th>
                                    <th class="px-8 py-5 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                                    <th class="px-8 py-5 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-right">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="letter in letters" :key="letter.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5 font-bold text-slate-700">{{ letter.user?.name || '-' }}</td>
                                    <td class="px-8 py-5 text-slate-500 font-medium">{{ letter.type?.name || '-' }}</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase" :class="statusClass(letter.status)">{{ statusLabel(letter.status) }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-right text-slate-500 font-medium">{{ formatDate(letter.created_at) }}</td>
                                </tr>
                                <tr v-if="letters.length === 0"><td colspan="4" class="px-8 py-8 text-center text-slate-500">Belum ada pengajuan.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';

const user = inject('user');
const letters = ref([]);
const letterTypes = ref([]);
const stats = ref({ pending: 0, approved: 0, rejected: 0, users: 0 });

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
const statusClass = (s) => ({ pending: 'bg-amber-50 text-amber-700 border border-amber-100', approved: 'bg-emerald-50 text-emerald-700 border border-emerald-100', rejected: 'bg-rose-50 text-rose-700 border border-rose-100' }[s] || '');
const statusLabel = (s) => ({ pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' }[s] || s);

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
    letters.value = res.data.letters || [];
    letterTypes.value = res.data.letterTypes || [];
    stats.value = res.data.stats || stats.value;
});
</script>
