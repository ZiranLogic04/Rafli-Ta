<template>
    <AppLayout>
        <div class="w-full flex flex-col gap-8 pb-10">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-3">
                <router-link to="/dashboard" class="flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </router-link>
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                        <span>Dashboard</span>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-[#4F46E5]">Buat Surat</span>
                    </div>
                    <h1 class="text-2xl font-extrabold text-slate-900">Pengajuan Dokumen</h1>
                </div>
            </div>

            <!-- Header Card -->
            <div v-if="letterType" class="bg-white rounded-3xl p-8 border border-slate-100 shadow-xl shadow-slate-200/50 flex flex-col md:flex-row gap-6 items-start justify-between relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/5 rounded-full -mr-20 -mt-20 blur-3xl transition-all group-hover:bg-indigo-600/10"></div>
                <div class="relative z-10 flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ letterType.name }}</h2>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-widest border border-slate-200">Draf Pengajuan</span>
                    </div>
                    <p class="text-slate-500 font-medium">Template standar untuk pengajuan dokumen jenis {{ letterType.name }} resmi sesuai ketentuan.</p>
                    
                    <!-- Important Guide for User -->
                    <div class="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[24px]">priority_high</span>
                        </div>
                        <div>
                            <div class="text-amber-700 font-bold text-xs uppercase tracking-wider mb-1">PENTING: JANGAN UBAH KODE NOMOR</div>
                            <p class="text-[12px] text-amber-600 leading-relaxed font-medium">
                                Di dalam template ini terdapat kode <code class="bg-amber-100 px-1.5 py-0.5 rounded font-bold text-amber-800">${letter_number}</code>. 
                                Mohon **JANGAN DIUBAH ATAU DIHAPUS**. Kode ini diperlukan sistem untuk mengisi nomor surat resmi secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 relative z-10">
                    <span class="material-symbols-outlined text-[32px]">description</span>
                </div>
            </div>

            <div class="flex items-center gap-2 mb-2 px-2 mt-4">
                <span class="material-symbols-outlined text-[#4F46E5] text-[20px]">analytics</span>
                <h3 class="text-lg font-bold text-slate-900">Alur Pengajuan</h3>
            </div>

            <!-- Step 1: Download -->
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 flex flex-col md:flex-row gap-6 hover:border-[#4F46E5]/30 transition-colors shadow-sm">
                <div class="w-12 h-12 rounded-full bg-[#4F46E5] text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-lg shadow-[#4F46E5]/20">1</div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Unduh Template</h4>
                    <p class="text-slate-500 text-sm mb-5">Silakan unduh template dokumen (.docx) yang disediakan.</p>
                    <a v-if="letterType" :href="`/api/letters/template/${letterType.id}`" class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-bold text-sm hover:bg-[#4F46E5] hover:text-white hover:border-[#4F46E5] transition-all group">
                        <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-white">download</span>
                        Template_{{ letterType.name?.replace(/ /g, '_') }}.docx
                    </a>
                </div>
            </div>

            <!-- Step 2: Upload -->
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 flex flex-col md:flex-row gap-6 hover:border-[#4F46E5]/30 transition-colors shadow-sm">
                <div class="w-12 h-12 rounded-full bg-[#4F46E5] text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-lg shadow-[#4F46E5]/20">2</div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Unggah Dokumen</h4>
                    <p class="text-slate-500 text-sm mb-5">Lengkapi data pada template, kemudian unggah kembali.</p>
                    <div class="relative w-full rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-8 flex flex-col items-center justify-center gap-3 transition-colors hover:border-[#4F46E5] hover:bg-[#4F46E5]/5 cursor-pointer" :class="selectedFile ? 'border-[#4F46E5] bg-[#4F46E5]/5' : ''" @click="$refs.fileInput.click()">
                        <input ref="fileInput" type="file" accept=".docx,.pdf" class="hidden" @change="onFileSelect" />
                        <span class="material-symbols-outlined text-[40px] text-slate-300">cloud_upload</span>
                        <div class="text-center">
                            <p class="text-sm font-bold text-slate-700">Klik atau seret file</p>
                            <p class="text-[11px] font-medium text-slate-400 mt-1 uppercase tracking-wider">Format: .DOCX, .PDF (Maks 5MB)</p>
                        </div>
                    </div>
                    <div v-if="selectedFile" class="mt-4 flex items-center gap-3 p-4 rounded-xl bg-[#4F46E5]/10 border border-[#4F46E5]/20 text-[#4F46E5]">
                        <span class="material-symbols-outlined">description</span>
                        <span class="font-bold text-sm truncate">{{ selectedFile.name }}</span>
                        <span class="material-symbols-outlined ml-auto text-[18px] cursor-pointer hover:text-rose-500 transition-colors" @click.stop="selectedFile = null">close</span>
                    </div>
                </div>
            </div>

            <!-- Step 3: Submit -->
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 flex flex-col md:flex-row gap-6 hover:border-[#4F46E5]/30 transition-colors shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -mr-10 -mt-10 blur-2xl"></div>
                <div class="w-12 h-12 rounded-full bg-[#4F46E5] text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-lg shadow-[#4F46E5]/20 relative z-10">3</div>
                <div class="flex-1 relative z-10">
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Ajukan Persetujuan</h4>
                    <p class="text-slate-500 text-sm mb-6">Pastikan semua data sudah benar.</p>
                    <button @click="submitLetter" :disabled="!selectedFile || submitting" class="w-full py-4 px-6 bg-[#4F46E5] text-white text-sm font-bold rounded-2xl shadow-lg shadow-[#4F46E5]/20 hover:bg-[#4338CA] transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <span v-if="submitting" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span class="material-symbols-outlined text-[20px]" v-else>send</span>
                        {{ submitting ? 'Mengirim...' : 'Kirim Pengajuan Sekarang' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import AppLayout from '../../../Layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();
const showFlash = inject('showFlash');
const letterType = ref(null);
const selectedFile = ref(null);
const submitting = ref(false);

const onFileSelect = (e) => {
    selectedFile.value = e.target.files[0] || null;
};

const submitLetter = async () => {
    if (!selectedFile.value || !letterType.value) return;
    submitting.value = true;
    try {
        const fd = new FormData();
        fd.append('letter_type_id', letterType.value.id);
        fd.append('file', selectedFile.value);
        await axios.post('/api/letters', fd);
        showFlash('Surat berhasil diajukan!');
        router.push('/letters');
    } catch (e) {
        showFlash(e.response?.data?.message || 'Gagal mengirim.', 'error');
    } finally {
        submitting.value = false;
    }
};

onMounted(async () => {
    const typeId = route.query.type_id;
    if (typeId) {
        const res = await axios.get('/api/letters/create-data', { params: { type_id: typeId } });
        letterType.value = res.data.letterType;
    }
});
</script>
