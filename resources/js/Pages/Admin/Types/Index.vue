<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Kelola Jenis Surat</h1>
                    <p class="text-slate-500 mt-1 font-medium">Manajemen template dan klasifikasi surat resmi kampus.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showGuideModal = true" class="flex items-center gap-2 px-6 py-3.5 bg-white text-[#4F46E5] border border-indigo-100 text-sm font-bold rounded-2xl shadow-sm hover:bg-indigo-50 transition-all">
                        <span class="material-symbols-outlined text-[20px]">help</span>
                        Panduan Template
                    </button>
                    <button @click="openCreateModal" class="flex items-center gap-2 px-6 py-3.5 bg-[#4F46E5] text-white text-sm font-bold rounded-2xl shadow-lg shadow-[#4F46E5]/20 hover:bg-[#4338CA] transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Tambah Jenis Surat
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/30 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider w-12">#</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Template</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(type, i) in types" :key="type.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5 text-slate-400 font-bold">{{ i + 1 }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><span class="material-symbols-outlined text-xl">description</span></div>
                                        <span class="font-bold text-slate-800">{{ type.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5"><span class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg font-mono text-xs font-bold">{{ type.code }}</span></td>
                                <td class="px-6 py-5">
                                    <a v-if="type.template_path" :href="`/api/admin/types/${type.id}/download`" class="inline-flex items-center gap-1 text-[#4F46E5] hover:underline text-xs font-bold">
                                        <span class="material-symbols-outlined text-[16px]">download</span>
                                        {{ type.original_filename || 'Download' }}
                                    </a>
                                    <span v-else class="text-xs text-slate-400 italic">Belum ada</span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(type)" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-all"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                        <button @click="openDeleteModal(type)" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-all"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="types.length === 0"><td colspan="5" class="px-8 py-12 text-center text-slate-500">Belum ada jenis surat.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60" @click.self="showModal = false">
                <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                    <form @submit.prevent="saveType">
                        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-900">{{ editingType ? 'Edit' : 'Tambah' }} Jenis Surat</h3>
                                <p class="text-xs text-slate-400 font-medium">{{ editingType ? 'Perbarui informasi' : 'Tambahkan jenis surat baru' }}</p>
                            </div>
                            <button type="button" @click="showModal = false" class="w-10 h-10 rounded-xl hover:bg-slate-100 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-slate-400">close</span></button>
                        </div>
                        <div class="p-8 space-y-6">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Surat</label>
                                <input v-model="form.name" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all" type="text"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kode Surat</label>
                                <input v-model="form.code" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-mono transition-all" type="text"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Template (.docx) <span v-if="editingType" class="text-amber-500">- Opsional</span></label>
                                <div class="relative group">
                                    <input ref="fileInput" type="file" accept=".docx" class="hidden" @change="onFileSelect"/>
                                    <div @click="$refs.fileInput.click()" class="flex flex-col items-center justify-center w-full h-32 px-4 border-2 border-dashed border-slate-200 rounded-2xl cursor-pointer hover:border-[#4F46E5]/50 hover:bg-slate-50 transition-all text-center">
                                        <span class="material-symbols-outlined text-3xl text-slate-300 mb-2 group-hover:text-[#4F46E5] transition-colors">upload_file</span>
                                        <span class="text-xs font-bold text-slate-500 group-hover:text-[#4F46E5] transition-colors">{{ selectedFileName || 'Pilih file Word (.docx)' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showModal = false" class="px-6 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all">Batal</button>
                            <button type="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#4F46E5] text-white text-sm font-bold hover:bg-[#4338CA] transition-all shadow-lg shadow-[#4F46E5]/20 disabled:opacity-60">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Modal -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60" @click.self="showDeleteModal = false">
                <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-8">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-4"><span class="material-symbols-outlined text-3xl">warning</span></div>
                        <h3 class="text-lg font-extrabold text-slate-900 mb-2">Hapus Jenis Surat?</h3>
                        <p class="text-sm text-slate-500 mb-6">Jenis surat <b>{{ deletingType?.name }}</b> akan dihapus. Data riwayat surat tetap tersimpan.</p>
                        <div class="grid grid-cols-2 gap-4 w-full">
                            <button @click="showDeleteModal = false" class="py-3 px-6 rounded-2xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition-all">Batal</button>
                            <button @click="deleteType" class="py-3 px-6 rounded-2xl bg-rose-500 text-white font-bold text-sm hover:bg-rose-600 transition-all">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Guide Modal -->
            <div v-if="showGuideModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 p-4" @click.self="showGuideModal = false">
                <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><span class="material-symbols-outlined">lightbulb</span></div>
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-900">Panduan Template Otomatis</h3>
                                <p class="text-xs text-slate-400 font-medium">Cara menyiapkan dokumen .docx agar nomor surat terisi otomatis</p>
                            </div>
                        </div>
                        <button @click="showGuideModal = false" class="w-10 h-10 rounded-xl hover:bg-slate-100 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-slate-400">close</span></button>
                    </div>
                    
                    <div class="p-8 overflow-y-auto space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">1</div>
                                <div>
                                    <h4 class="font-bold text-slate-800">Siapkan File Word (.docx)</h4>
                                    <p class="text-sm text-slate-500">Buat atau buka draf surat Anda di Microsoft Word atau Google Docs.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">2</div>
                                <div>
                                    <h4 class="font-bold text-slate-800">Pasang Kode Placeholder</h4>
                                    <p class="text-sm text-slate-500 mb-3">Ketik kode di bawah ini tepat di posisi nomor surat akan muncul:</p>
                                    <div class="group relative">
                                        <code class="block w-full p-4 bg-slate-900 text-emerald-400 rounded-2xl font-mono text-sm shadow-inner overflow-x-auto">
                                            ${letter_number}
                                        </code>
                                        <button @click="copyPlaceholder" class="absolute right-3 top-3 px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold rounded-lg transition-all backdrop-blur-sm">Copy</button>
                                    </div>
                                    <p class="text-[11px] text-amber-600 font-bold mt-2 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">warning</span>
                                        PENTING: Jangan tambahkan spasi atau ubah format tulisan di dalam kurung kurawal.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">3</div>
                                <div>
                                    <h4 class="font-bold text-slate-800">Unggah & Selesai!</h4>
                                    <p class="text-sm text-slate-500">Unggah file tersebut sebagai **Template**. Saat Admin mengeklik "Setujui", sistem akan otomatis mengganti kode tersebut dengan nomor surat resmi.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Visual Example -->
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                            <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Contoh Visual:</h5>
                            <div class="bg-white border border-slate-200 shadow-sm p-6 rounded-lg font-serif">
                                <p class="text-center font-bold underline mb-1 uppercase">Surat Keterangan Aktif</p>
                                <p class="text-center text-xs mb-6 font-sans">Nomor: <span class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-1 rounded">${letter_number}</span></p>
                                <p class="text-[10px] text-slate-500 leading-relaxed">Yang bertanda tangan di bawah ini menerangkan bahwa...</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end shrink-0">
                        <button @click="showGuideModal = false" class="px-8 py-3 rounded-2xl bg-[#4F46E5] text-white text-sm font-bold hover:bg-[#4338CA] transition-all shadow-lg shadow-[#4F46E5]/20">Saya Mengerti</button>
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
const types = ref([]);
const showModal = ref(false);
const showGuideModal = ref(false);
const showDeleteModal = ref(false);
const editingType = ref(null);
const deletingType = ref(null);
const form = ref({ name: '', code: '' });
const selectedFile = ref(null);
const selectedFileName = ref('');
const saving = ref(false);

const fetchTypes = async () => {
    const res = await axios.get('/api/admin/types');
    types.value = res.data;
};

const copyPlaceholder = () => {
    navigator.clipboard.writeText('${letter_number}');
    showFlash('Kode placeholder disalin ke clipboard!');
};

const openCreateModal = () => {
    editingType.value = null;
    form.value = { name: '', code: '' };
    selectedFile.value = null;
    selectedFileName.value = '';
    showModal.value = true;
};

const openEditModal = (type) => {
    editingType.value = type;
    form.value = { name: type.name, code: type.code };
    selectedFile.value = null;
    selectedFileName.value = '';
    showModal.value = true;
};

const openDeleteModal = (type) => {
    deletingType.value = type;
    showDeleteModal.value = true;
};

const onFileSelect = (e) => {
    selectedFile.value = e.target.files[0] || null;
    selectedFileName.value = selectedFile.value?.name || '';
};

const saveType = async () => {
    saving.value = true;
    try {
        const fd = new FormData();
        fd.append('name', form.value.name);
        fd.append('code', form.value.code);
        if (selectedFile.value) fd.append('template_path', selectedFile.value);

        if (editingType.value) {
            fd.append('_method', 'PUT');
            await axios.post(`/api/admin/types/${editingType.value.id}`, fd);
        } else {
            await axios.post('/api/admin/types', fd);
        }
        showFlash(editingType.value ? 'Jenis surat diperbarui.' : 'Jenis surat ditambahkan.');
        showModal.value = false;
        fetchTypes();
    } catch (e) {
        showFlash(e.response?.data?.message || 'Gagal menyimpan.', 'error');
    } finally { saving.value = false; }
};

const deleteType = async () => {
    try {
        await axios.delete(`/api/admin/types/${deletingType.value.id}`);
        showFlash('Jenis surat dihapus.');
        showDeleteModal.value = false;
        fetchTypes();
    } catch (e) { showFlash(e.response?.data?.message || 'Gagal.', 'error'); }
};

onMounted(fetchTypes);
</script>
