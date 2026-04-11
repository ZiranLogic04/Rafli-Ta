<template>
    <AppLayout>
        <div class="types-page">
            <!-- Header Card -->
            <div class="page-header">
                <div class="page-header__left">
                    <div class="page-header__icon">
                        <span class="material-symbols-outlined">category</span>
                    </div>
                    <div>
                        <h1 class="page-header__title">Kelola Jenis Surat</h1>
                        <p class="page-header__subtitle">Atur kategori dan jenis surat.</p>
                    </div>
                </div>
                <div class="page-header__actions">
                    <button @click="showGuideModal = true" class="btn-secondary">
                        <span class="material-symbols-outlined">help</span>
                        Panduan
                    </button>
                    <button @click="openCreateParentModal" class="btn-secondary">
                        <span class="material-symbols-outlined">category</span>
                        Tambah Jenis Surat
                    </button>
                    <button @click="openCreateChildModal" class="btn-primary-action">
                        <span class="material-symbols-outlined">description</span>
                        Tambah Surat
                    </button>
                </div>
            </div>

            <!-- Filter + Table -->
            <div class="table-card">
                <div class="table-card__filter">
                    <span class="material-symbols-outlined table-card__filter-icon">filter_list</span>
                    <span class="table-card__filter-label">Filter:</span>
                    <select v-model="filterParent" class="table-card__filter-select">
                        <option :value="null">Semua</option>
                        <option v-for="p in rootTypes" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <span class="table-card__filter-count">{{ filteredTypes.length }} surat</span>
                </div>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-num">#</th>
                                <th>Nama Surat</th>
                                <th>Jenis Surat</th>
                                <th>Kode</th>
                                <th>Template</th>
                                <th class="text-center">Surat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(type, i) in filteredTypes" :key="type.id">
                                <td class="text-muted font-bold">{{ i + 1 }}</td>
                                <td>
                                    <div class="type-name">
                                        <div class="type-name__icon">
                                            <span class="material-symbols-outlined">description</span>
                                        </div>
                                        <span class="type-name__text">{{ type.name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted font-medium">{{ type.parent?.name || "-" }}</td>
                                <td>
                                    <span class="code-badge">{{ type.code }}</span>
                                </td>
                                <td>
                                    <a v-if="type.template_path" :href="`/api/admin/types/${type.id}/download`" class="download-link">
                                        <span class="material-symbols-outlined">download</span>
                                        {{ type.original_filename || "Download" }}
                                    </a>
                                    <span v-else class="text-muted text-xs italic">Belum ada</span>
                                </td>
                                <td class="text-center">
                                    <span class="count-badge">{{ type.letter_count || 0 }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button @click="openEditModal(type)" class="btn-icon btn-icon--edit">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="openDeleteModal(type)" class="btn-icon btn-icon--delete">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredTypes.length === 0">
                                <td colspan="7" class="empty-row">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">
                                            <span class="material-symbols-outlined">description</span>
                                        </div>
                                        <h3 class="empty-state__title">Tidak ada data</h3>
                                        <p class="empty-state__desc">{{ filterParent ? 'Tidak ada surat untuk filter ini.' : 'Tambahkan surat pertama Anda.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
                <div class="modal-card">
                    <form @submit.prevent="saveType">
                        <div class="modal-card__header">
                            <div>
                                <h3 class="modal-card__title">
                                    {{ editingType ? "Edit" : (isCreatingChild ? "Tambah Surat" : "Tambah Jenis Surat") }}
                                </h3>
                                <p class="modal-card__desc">
                                    {{ editingType ? "Perbarui informasi" : (isCreatingChild ? "Tambahkan jenis surat spesifik di bawah kategori." : "Tambahkan kategori utama jenis surat.") }}
                                </p>
                            </div>
                            <button type="button" @click="showModal = false" class="modal-close">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="modal-card__body">
                            <div v-if="isCreatingChild" class="form-group">
                                <label class="form-label">Jenis Surat</label>
                                <select v-model="form.parent_id" required class="form-select-lg">
                                    <option :value="null">-- Pilih jenis surat --</option>
                                    <option v-for="p in parentOptions" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama</label>
                                <input v-model="form.name" required class="form-input-lg" type="text" :placeholder="isCreatingChild ? 'Contoh: Mutasi' : 'Contoh: Surat Keputusan'" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kode</label>
                                <input v-model="form.code" required class="form-input-lg form-input--mono" type="text" :placeholder="isCreatingChild ? 'Contoh: MUT' : 'Contoh: SK'" />
                            </div>
                            <div v-if="isCreatingChild" class="form-group">
                                <label class="form-label">Template (.docx) <span v-if="editingType" class="text-optional">- Opsional</span></label>
                                <div class="file-upload" @click="$refs.fileInput.click()">
                                    <input ref="fileInput" type="file" accept=".docx" class="hidden" @change="onFileSelect" />
                                    <span class="material-symbols-outlined file-upload__icon">upload_file</span>
                                    <span class="file-upload__text">{{ selectedFileName || "Pilih file Word (.docx)" }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-card__footer">
                            <button type="button" @click="showModal = false" class="btn-cancel">Batal</button>
                            <button type="submit" :disabled="saving" class="btn-save">
                                {{ saving ? "Menyimpan..." : "Simpan" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Modal -->
            <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
                <div class="modal-card modal-card--sm">
                    <div class="confirm-modal">
                        <div class="confirm-modal__icon confirm-modal__icon--danger">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <h3 class="confirm-modal__title">Hapus Surat?</h3>
                        <p class="confirm-modal__desc">
                            Surat <b>{{ deletingType?.name }}</b> akan dihapus. Data riwayat surat tetap tersimpan.
                        </p>
                        <div class="confirm-modal__actions">
                            <button @click="showDeleteModal = false" class="btn-cancel">Batal</button>
                            <button @click="deleteType" class="btn-danger">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guide Modal -->
            <div v-if="showGuideModal" class="modal-overlay modal-overlay--padded" @click.self="showGuideModal = false">
                <div class="guide-modal">
                    <div class="guide-modal__header">
                        <div class="guide-modal__title-row">
                            <div class="guide-modal__icon">
                                <span class="material-symbols-outlined">lightbulb</span>
                            </div>
                            <div>
                                <h3 class="guide-modal__title">Panduan</h3>
                                <p class="guide-modal__desc">Cara mengelola jenis dan surat</p>
                            </div>
                        </div>
                        <button @click="showGuideModal = false" class="modal-close">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="guide-modal__body">
                        <div class="guide-steps">
                            <div class="guide-step">
                                <div class="guide-step__number">1</div>
                                <div>
                                    <h4 class="guide-step__title">Tambah Jenis Surat</h4>
                                    <p class="guide-step__desc">Buat kategori utama seperti <b>Surat Keputusan</b> atau <b>Surat Tugas</b>. Isi nama lengkap dan kode singkat (SK, ST).</p>
                                </div>
                            </div>
                            <div class="guide-step">
                                <div class="guide-step__number">2</div>
                                <div>
                                    <h4 class="guide-step__title">Tambah Surat</h4>
                                    <p class="guide-step__desc">Buat jenis surat spesifik di bawah kategori. Pilih jenis surat, isi nama lanjutan (contoh: <b>Mutasi</b>), kode lanjutan (<b>MUT</b>), dan upload template.</p>
                                </div>
                            </div>
                            <div class="guide-step">
                                <div class="guide-step__number">3</div>
                                <div>
                                    <h4 class="guide-step__title">Template</h4>
                                    <p class="guide-step__desc">Template (.docx) adalah contoh format surat yang bisa diunduh pengguna sebagai acuan saat membuat surat.</p>
                                </div>
                            </div>
                        </div>
                        <div class="guide-example">
                            <h5 class="guide-example__title">Contoh:</h5>
                            <div class="guide-example__content">
                                <div><b>Jenis Surat:</b> Surat Keputusan (kode: SK)</div>
                                <div class="guide-example__child">├─ <b>Surat:</b> Mutasi (kode: MUT) → jadi SK-MUT</div>
                                <div class="guide-example__child">└─ <b>Surat:</b> Kenaikan Pangkat (kode: KP) → jadi SK-KP</div>
                            </div>
                        </div>
                    </div>
                    <div class="guide-modal__footer">
                        <button @click="showGuideModal = false" class="btn-save">Saya Mengerti</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject, computed } from "vue";
import axios from "axios";
import AppLayout from "../../../Layouts/AppLayout.vue";

const showFlash = inject("showFlash");
const types = ref([]);
const filterParent = ref(null);
const showModal = ref(false);
const showGuideModal = ref(false);
const showDeleteModal = ref(false);
const editingType = ref(null);
const deletingType = ref(null);
const isCreatingChild = ref(false);
const form = ref({ name: "", code: "", parent_id: null });
const selectedFile = ref(null);
const selectedFileName = ref("");
const saving = ref(false);

const fetchTypes = async () => {
    const res = await axios.get("/api/admin/types");
    types.value = res.data;
};

const openCreateParentModal = () => {
    editingType.value = null;
    isCreatingChild.value = false;
    form.value = { name: "", code: "", parent_id: null };
    selectedFile.value = null;
    selectedFileName.value = "";
    showModal.value = true;
};

const openCreateChildModal = () => {
    editingType.value = null;
    isCreatingChild.value = true;
    form.value = { name: "", code: "", parent_id: null };
    selectedFile.value = null;
    selectedFileName.value = "";
    showModal.value = true;
};

const openEditModal = (type) => {
    editingType.value = type;
    isCreatingChild.value = !!type.parent_id;
    form.value = { name: type.name, code: type.code, parent_id: type.parent_id || null };
    selectedFile.value = null;
    selectedFileName.value = "";
    showModal.value = true;
};

const openDeleteModal = (type) => {
    deletingType.value = type;
    showDeleteModal.value = true;
};

const onFileSelect = (e) => {
    selectedFile.value = e.target.files[0] || null;
    selectedFileName.value = selectedFile.value?.name || "";
};

const saveType = async () => {
    saving.value = true;
    try {
        const fd = new FormData();
        fd.append("name", form.value.name);
        fd.append("code", form.value.code);
        if (form.value.parent_id) fd.append("parent_id", form.value.parent_id);
        if (selectedFile.value) fd.append("template_path", selectedFile.value);

        if (editingType.value) {
            fd.append("_method", "PUT");
            await axios.post(`/api/admin/types/${editingType.value.id}`, fd);
        } else {
            await axios.post("/api/admin/types", fd);
        }
        showFlash(editingType.value ? "Berhasil diperbarui." : "Berhasil ditambahkan.");
        showModal.value = false;
        fetchTypes();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal menyimpan.", "error");
    } finally {
        saving.value = false;
    }
};

const deleteType = async () => {
    try {
        await axios.delete(`/api/admin/types/${deletingType.value.id}`);
        showFlash("Berhasil dihapus.");
        showDeleteModal.value = false;
        fetchTypes();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

onMounted(fetchTypes);

const rootTypes = computed(() =>
    types.value.filter((t) => !t.parent_id).sort((a, b) => a.name.localeCompare(b.name))
);

const filteredTypes = computed(() => {
    let list = types.value.filter((t) => t.parent_id);

    if (filterParent.value) {
        list = list.filter((t) => t.parent_id === filterParent.value);
    }

    return list.sort((a, b) => a.name.localeCompare(b.name));
});

const parentOptions = computed(() => {
    if (!editingType.value) return rootTypes.value;
    return rootTypes.value.filter((t) => t.id !== editingType.value.id);
});
</script>

<style scoped>
.types-page {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

/* Page Header */
.page-header {
    background: white;
    border-radius: 1.5rem;
    padding: 1.5rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
}

@media (min-width: 768px) {
    .page-header {
        padding: 2rem;
    }
}

.page-header__left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

@media (min-width: 768px) {
    .page-header__left {
        flex-direction: row;
    }
}

.page-header__icon {
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #4F46E5, #6366F1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.page-header__icon .material-symbols-outlined {
    font-size: 1.75rem;
}

.page-header__title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

.page-header__subtitle {
    color: var(--slate-500);
    font-size: 0.875rem;
    font-weight: 500;
}

.page-header__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

@media (min-width: 768px) {
    .page-header {
        display: flex;
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: white;
    color: var(--indigo-600);
    border: 1px solid var(--indigo-100);
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-secondary:hover {
    background: var(--indigo-50);
}

.btn-secondary .material-symbols-outlined {
    font-size: 1.125rem;
}

.btn-primary-action {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: var(--primary);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.btn-primary-action:hover {
    background: var(--primary-dark);
}

.btn-primary-action:active {
    transform: scale(0.95);
}

.btn-primary-action .material-symbols-outlined {
    font-size: 1.125rem;
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid var(--slate-100);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-card__filter {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--slate-100);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-card__filter-icon {
    color: var(--slate-400);
    font-size: 1.25rem;
}

.table-card__filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table-card__filter-select {
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid var(--slate-200);
    background: var(--slate-50);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.table-card__filter-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.table-card__filter-count {
    margin-left: auto;
    font-size: 0.75rem;
    color: var(--slate-400);
}

.table-scroll {
    overflow-x: auto;
}

.data-table thead tr {
    background: var(--primary);
}

.data-table thead th {
    padding: 1.25rem 2rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    white-space: nowrap;
}

.data-table thead th.text-center {
    text-align: center;
}

.data-table thead th.col-num {
    width: 3rem;
    padding: 1.25rem 2rem;
}

.data-table tbody tr {
    border-bottom: 1px solid var(--slate-50);
    transition: background 0.15s;
}

.data-table tbody tr:hover {
    background: rgba(248, 250, 252, 0.5);
}

.data-table tbody td {
    padding: 1.5rem 1.5rem;
    font-size: 0.875rem;
    white-space: nowrap;
}

.data-table tbody td.text-center {
    text-align: center;
}

.data-table tbody td.text-muted {
    color: var(--slate-400);
}

.data-table tbody td.font-bold {
    font-weight: 700;
    color: var(--slate-400);
}

.data-table tbody td.font-medium {
    font-weight: 500;
    color: var(--slate-600);
}

.data-table tbody td.text-xs {
    font-size: 0.75rem;
}

.data-table tbody td.italic {
    font-style: italic;
}

/* Type Name */
.type-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.type-name__icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: var(--indigo-50);
    color: var(--indigo-600);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.type-name__icon .material-symbols-outlined {
    font-size: 1.25rem;
}

.type-name__text {
    font-weight: 700;
    color: var(--slate-800);
    font-size: 0.875rem;
}

/* Code Badge */
.code-badge {
    padding: 0.375rem 0.75rem;
    background: var(--slate-100);
    color: var(--slate-600);
    border-radius: 0.5rem;
    font-family: monospace;
    font-size: 0.75rem;
    font-weight: 700;
}

/* Download Link */
.download-link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--indigo-600);
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
}

.download-link:hover {
    text-decoration: underline;
}

.download-link .material-symbols-outlined {
    font-size: 1rem;
}

/* Count Badge */
.count-badge {
    display: inline-flex;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    background: var(--slate-100);
    color: var(--slate-700);
    font-size: 0.75rem;
    font-weight: 700;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon .material-symbols-outlined {
    font-size: 1.125rem;
}

.btn-icon--edit {
    background: var(--amber-50);
    color: var(--amber-600);
}

.btn-icon--edit:hover {
    background: var(--amber-100);
}

.btn-icon--delete {
    background: var(--rose-50);
    color: var(--rose-600);
}

.btn-icon--delete:hover {
    background: var(--rose-100);
}

/* Empty State */
.empty-row {
    padding: 4rem !important;
    text-align: center;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-state__icon {
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
    background: var(--slate-100);
    color: var(--slate-400);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.empty-state__icon .material-symbols-outlined {
    font-size: 2.5rem;
}

.empty-state__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-600);
    margin-bottom: 0.25rem;
}

.empty-state__desc {
    font-size: 0.75rem;
    color: var(--slate-400);
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
}

.modal-overlay--padded {
    padding: 1rem;
}

.modal-card {
    background: white;
    width: 100%;
    max-width: 32rem;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-card--sm {
    max-width: 28rem;
}

.modal-card__header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--slate-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-card__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
}

.modal-card__desc {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-weight: 500;
}

.modal-close {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    border: none;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.modal-close:hover {
    background: var(--slate-100);
}

.modal-close .material-symbols-outlined {
    color: var(--slate-400);
}

.modal-card__body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}

.form-select-lg {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-700);
    transition: all 0.2s;
}

.form-select-lg:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.form-input-lg {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-900);
    transition: all 0.2s;
}

.form-input-lg:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.form-input--mono {
    font-family: monospace;
}

.text-optional {
    color: var(--amber-500);
}

/* File Upload */
.file-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 8rem;
    padding: 1rem;
    border: 2px dashed var(--slate-200);
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.file-upload:hover {
    border-color: rgba(79, 70, 229, 0.5);
    background: var(--slate-50);
}

.file-upload__icon {
    font-size: 2rem;
    color: var(--slate-300);
    margin-bottom: 0.5rem;
    transition: color 0.2s;
}

.file-upload:hover .file-upload__icon {
    color: var(--indigo-600);
}

.file-upload__text {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-500);
    transition: color 0.2s;
}

.file-upload:hover .file-upload__text {
    color: var(--indigo-600);
}

.hidden {
    display: none;
}

.modal-card__footer {
    padding: 1.25rem 2rem;
    background: var(--slate-50);
    border-top: 1px solid var(--slate-100);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-600);
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: var(--slate-100);
}

.btn-save {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background: var(--primary);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.btn-save:hover {
    background: var(--primary-dark);
}

.btn-save:disabled {
    opacity: 0.6;
}

/* Confirm Modal */
.confirm-modal {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2rem;
}

.confirm-modal__icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.confirm-modal__icon .material-symbols-outlined {
    font-size: 2rem;
}

.confirm-modal__icon--danger {
    background: var(--rose-50);
    color: var(--rose-500);
}

.confirm-modal__title {
    font-size: 1.125rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.5rem;
}

.confirm-modal__desc {
    font-size: 0.875rem;
    color: var(--slate-500);
    margin-bottom: 1.5rem;
}

.confirm-modal__actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    width: 100%;
}

.btn-danger {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background: var(--rose-500);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background: var(--rose-600);
}

/* Guide Modal */
.guide-modal {
    background: white;
    width: 100%;
    max-width: 42rem;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.guide-modal__header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--slate-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.guide-modal__title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.guide-modal__icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: var(--indigo-50);
    color: var(--indigo-600);
    display: flex;
    align-items: center;
    justify-content: center;
}

.guide-modal__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
}

.guide-modal__desc {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-weight: 500;
}

.guide-modal__body {
    padding: 2rem;
    overflow-y: auto;
}

.guide-steps {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.guide-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.guide-step__number {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: var(--indigo-600);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 700;
    flex-shrink: 0;
}

.guide-step__title {
    font-weight: 700;
    color: var(--slate-800);
}

.guide-step__desc {
    font-size: 0.875rem;
    color: var(--slate-500);
}

.guide-example {
    background: var(--slate-50);
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid var(--slate-200);
    margin-top: 1.5rem;
}

.guide-example__title {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 1rem;
}

.guide-example__content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--slate-700);
}

.guide-example__child {
    padding-left: 1rem;
}

.guide-modal__footer {
    padding: 1.25rem 2rem;
    background: var(--slate-50);
    border-top: 1px solid var(--slate-100);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-shrink: 0;
}
</style>
