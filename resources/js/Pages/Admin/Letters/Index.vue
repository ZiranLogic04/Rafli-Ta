<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto flex flex-col gap-8 pb-10">
            <!-- Header -->
            <div class="relative bg-white rounded-3xl p-8 border border-slate-100 overflow-hidden shadow-sm">
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#4F46E5]/5 rounded-full -mr-20 -mt-20 blur-3xl"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Persetujuan Surat</h2>
                    <p class="text-slate-500 text-base font-medium mt-1">Kelola pengajuan surat staf dan dosen.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">description</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total</span>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900">{{ total }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">pending</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pending</span>
                    </div>
                    <p class="text-3xl font-extrabold text-amber-600">{{ pending }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">check_circle</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Disetujui</span>
                    </div>
                    <p class="text-3xl font-extrabold text-emerald-600">{{ approved }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center"><span class="material-symbols-outlined text-2xl">cancel</span></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ditolak</span>
                    </div>
                    <p class="text-3xl font-extrabold text-rose-600">{{ rejected }}</p>
                </div>
            </div>

            <!-- Search -->
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Daftar Pengajuan</h3>
                <div class="relative group">
                    <input v-model="search" @keyup.enter="fetchLetters" class="pl-12 pr-4 py-3 rounded-xl border-slate-200 bg-white text-sm font-medium focus:ring-2 focus:ring-[#4F46E5]/50 focus:border-[#4F46E5] transition-all w-72 shadow-sm border" placeholder="Cari..." type="text"/>
                    <span class="material-symbols-outlined absolute left-4 top-3 text-slate-400">search</span>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/30 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Pengaju</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">No. Surat</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="letter in letters" :key="letter.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5 font-bold text-slate-700">{{ letter.user?.name || '-' }}</td>
                                <td class="px-6 py-5 text-slate-500 font-medium">{{ letter.type?.name || '-' }}</td>
                                <td class="px-6 py-5 text-slate-500 text-sm">{{ formatDate(letter.created_at) }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase" :class="statusClass(letter.status)">{{ statusLabel(letter.status) }}</span>
                                </td>
                                <td class="px-6 py-5 font-bold text-slate-700 font-mono text-xs">{{ letter.letter_number || '-' }}</td>
                                <td class="px-6 py-5 text-center">
                                    <div v-if="letter.status === 'pending'" class="flex items-center justify-center gap-2">
                                        <button @click="approveLetter(letter.id)" class="px-4 py-2 bg-emerald-500 text-white text-xs font-bold rounded-xl hover:bg-emerald-600 transition-all flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">check</span> Setujui
                                        </button>
                                        <button @click="openRejectModal(letter)" class="px-4 py-2 bg-rose-500 text-white text-xs font-bold rounded-xl hover:bg-rose-600 transition-all flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">close</span> Tolak
                                        </button>
                                    </div>
                                    <span v-else class="text-xs text-slate-400">—</span>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0"><td colspan="6" class="px-6 py-12 text-center text-slate-500">Tidak ada data.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.lastPage > 1" class="flex items-center justify-center gap-2">
                <button v-for="p in pagination.lastPage" :key="p" @click="page = p; fetchLetters()" class="w-10 h-10 rounded-xl text-sm font-bold transition-all" :class="p === page ? 'bg-[#4F46E5] text-white shadow-lg shadow-[#4F46E5]/20' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50'">{{ p }}</button>
            </div>

            <!-- Reject Modal -->
            <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60" @click.self="showRejectModal = false">
                <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl">
                    <h3 class="text-xl font-extrabold text-slate-900 mb-2">Tolak Pengajuan</h3>
                    <p class="text-sm text-slate-500 mb-6">Berikan alasan penolakan untuk pengaju.</p>
                    <textarea v-model="rejectNote" rows="4" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all resize-none" placeholder="Tulis alasan penolakan..."></textarea>
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <button @click="showRejectModal = false" class="py-3 px-6 rounded-2xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition-all">Batal</button>
                        <button @click="rejectLetter" :disabled="!rejectNote" class="py-3 px-6 rounded-2xl bg-rose-500 text-white font-bold text-sm hover:bg-rose-600 transition-all disabled:opacity-50">Tolak Surat</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import AppLayout from '../../../Layouts/AppLayout.vue';

const showFlash = inject('showFlash');
const letters = ref([]);
const search = ref('');
const page = ref(1);
const pagination = ref({ lastPage: 1 });
const total = ref(0); const pending = ref(0); const approved = ref(0); const rejected = ref(0);
const showRejectModal = ref(false);
const rejectNote = ref('');
const rejectingLetterId = ref(null);

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
const statusClass = (s) => ({ pending: 'bg-amber-50 text-amber-700 border border-amber-100', approved: 'bg-emerald-50 text-emerald-700 border border-emerald-100', rejected: 'bg-rose-50 text-rose-700 border border-rose-100' }[s] || '');
const statusLabel = (s) => ({ pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' }[s] || s);

const fetchLetters = async () => {
    const res = await axios.get('/api/admin/letters', { params: { search: search.value, page: page.value } });
    letters.value = res.data.letters?.data || [];
    pagination.value = { lastPage: res.data.letters?.last_page || 1 };
    total.value = res.data.total; pending.value = res.data.pending; approved.value = res.data.approved; rejected.value = res.data.rejected;
};

const approveLetter = async (id) => {
    try {
        await axios.post(`/api/admin/letters/${id}/approve`);
        showFlash('Surat berhasil disetujui.');
        fetchLetters();
    } catch (e) { showFlash(e.response?.data?.message || 'Gagal.', 'error'); }
};

const openRejectModal = (letter) => {
    rejectingLetterId.value = letter.id;
    rejectNote.value = '';
    showRejectModal.value = true;
};

const rejectLetter = async () => {
    try {
        await axios.post(`/api/admin/letters/${rejectingLetterId.value}/reject`, { rejection_note: rejectNote.value });
        showFlash('Surat telah ditolak.');
        showRejectModal.value = false;
        fetchLetters();
    } catch (e) { showFlash(e.response?.data?.message || 'Gagal.', 'error'); }
};

onMounted(fetchLetters);
</script>
