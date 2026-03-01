<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200/60 pb-8">
                <div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Riwayat Pengajuan</h2>
                    <p class="text-slate-500 mt-2 font-medium text-lg">Pantau status pengajuan surat akademik dan administrasi Anda.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <input v-model="search" @keyup.enter="fetchLetters" class="pl-12 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium focus:ring-2 focus:ring-[#4F46E5]/50 focus:border-[#4F46E5] transition-all w-72 shadow-sm" placeholder="Cari nomor atau jenis surat..." />
                        <span class="material-symbols-outlined absolute left-4 top-3 text-slate-400 group-focus-within:text-[#4F46E5] transition-colors">search</span>
                        <button v-if="search" @click="search = ''; fetchLetters()" class="absolute right-4 top-3 text-slate-400 hover:text-rose-500">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/30 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Jenis Surat</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nomor Surat</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="letter in letters" :key="letter.id" class="bg-white hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 ring-1 ring-indigo-100/50 group-hover:scale-105 transition-transform duration-300">
                                            <span class="material-symbols-outlined text-[28px] font-light">description</span>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="font-bold text-slate-800 text-[15px]">{{ letter.type?.name || 'Surat' }}</span>
                                            <span class="text-xs text-slate-500 font-medium">Diajukan oleh Anda</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="text-slate-700 font-semibold text-sm">{{ formatDate(letter.created_at) }}</span>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase" :class="statusClass(letter.status)">{{ statusLabel(letter.status) }}</span>
                                </td>
                                <td class="px-6 py-6 font-bold text-slate-700">{{ letter.letter_number || '-' }}</td>
                                <td class="px-8 py-6 text-center">
                                    <a v-if="letter.status === 'approved'" :href="`/api/letters/${letter.id}/download`" class="inline-flex items-center gap-1 text-[#4F46E5] hover:underline text-xs font-bold" target="_blank">
                                        <span class="material-symbols-outlined text-[16px]">download</span>
                                        Unduh DOCX
                                    </a>
                                    <span v-else class="text-xs text-slate-400">—</span>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0">
                                <td colspan="5" class="px-8 py-12 text-center text-slate-500">Belum ada pengajuan surat.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.lastPage > 1" class="flex items-center justify-center gap-2 mt-6">
                <button v-for="p in pagination.lastPage" :key="p" @click="page = p; fetchLetters()" class="w-10 h-10 rounded-xl text-sm font-bold transition-all" :class="p === page ? 'bg-[#4F46E5] text-white shadow-lg shadow-[#4F46E5]/20' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50'">{{ p }}</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '../../../Layouts/AppLayout.vue';

const letters = ref([]);
const search = ref('');
const page = ref(1);
const pagination = ref({ lastPage: 1 });

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
const statusClass = (s) => ({ pending: 'bg-amber-50 text-amber-700 border border-amber-100', approved: 'bg-emerald-50 text-emerald-700 border border-emerald-100', rejected: 'bg-rose-50 text-rose-700 border border-rose-100' }[s] || '');
const statusLabel = (s) => ({ pending: 'Sedang Diproses', approved: 'Disetujui', rejected: 'Ditolak' }[s] || s);

const fetchLetters = async () => {
    const res = await axios.get('/api/letters', { params: { search: search.value, page: page.value } });
    letters.value = res.data.data || [];
    pagination.value = { lastPage: res.data.last_page || 1 };
};

onMounted(fetchLetters);
</script>
