<template>
    <AppLayout>
        <div class="prodi-page">
            <div class="page-header-row">
                <div>
                    <h1 class="page-title">Kelola Prodi</h1>
                    <p class="page-subtitle">
                        Atur daftar Program Studi dan Kode Singkat untuk penomoran surat.
                    </p>
                </div>
                <button @click="openCreateModal" class="btn-primary-action">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Prodi
                </button>
            </div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-num">#</th>
                                <th>Nama Program Studi</th>
                                <th>Kode (Shortcode)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in prodis" :key="p.id">
                                <td class="text-muted font-bold">{{ i + 1 }}</td>
                                <td>
                                    <div class="prodi-name">
                                        <div class="prodi-name__icon">
                                            <span class="material-symbols-outlined">school</span>
                                        </div>
                                        <span class="prodi-name__text">{{ p.name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="code-badge">{{ p.code }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button @click="openEditModal(p)" class="btn-icon btn-icon--edit">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="openDeleteModal(p)" class="btn-icon btn-icon--delete">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="prodis.length === 0">
                                <td colspan="4" class="empty-row">Belum ada data prodi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
                <div class="modal-card">
                    <form @submit.prevent="saveProdi">
                        <div class="modal-card__header">
                            <div>
                                <h3 class="modal-card__title">
                                    {{ editingProdi ? "Edit" : "Tambah" }} Prodi
                                </h3>
                            </div>
                            <button type="button" @click="showModal = false" class="modal-close">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="modal-card__body">
                            <div class="form-group">
                                <label class="form-label">Nama Program Studi</label>
                                <input v-model="form.name" required class="form-input-lg" type="text" placeholder="Contoh: Teknik Informatika" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kode Singkat (Untuk Nomor Surat)</label>
                                <input v-model="form.code" required class="form-input-lg form-input--mono" type="text" placeholder="Contoh: TI" />
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
                        <h3 class="confirm-modal__title">Hapus Prodi?</h3>
                        <p class="confirm-modal__desc">
                            Prodi <b>{{ deletingProdi?.name }}</b> akan dihapus.
                        </p>
                        <div class="confirm-modal__actions">
                            <button @click="showDeleteModal = false" class="btn-cancel">Batal</button>
                            <button @click="deleteProdi" class="btn-danger">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import axios from "axios";
import AppLayout from "../../../Layouts/AppLayout.vue";

const showFlash = inject("showFlash");
const prodis = ref([]);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingProdi = ref(null);
const deletingProdi = ref(null);
const form = ref({ name: "", code: "" });
const saving = ref(false);

const fetchProdis = async () => {
    const res = await axios.get("/api/admin/prodis");
    prodis.value = res.data;
};

const openCreateModal = () => {
    editingProdi.value = null;
    form.value = { name: "", code: "" };
    showModal.value = true;
};

const openEditModal = (p) => {
    editingProdi.value = p;
    form.value = { name: p.name, code: p.code };
    showModal.value = true;
};

const openDeleteModal = (p) => {
    deletingProdi.value = p;
    showDeleteModal.value = true;
};

const saveProdi = async () => {
    saving.value = true;
    try {
        if (editingProdi.value) {
            await axios.put(`/api/admin/prodis/${editingProdi.value.id}`, form.value);
        } else {
            await axios.post("/api/admin/prodis", form.value);
        }
        showFlash(editingProdi.value ? "Prodi berhasil diperbarui." : "Prodi berhasil ditambahkan.");
        showModal.value = false;
        fetchProdis();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal menyimpan.", "error");
    } finally {
        saving.value = false;
    }
};

const deleteProdi = async () => {
    try {
        await axios.delete(`/api/admin/prodis/${deletingProdi.value.id}`);
        showFlash("Prodi berhasil dihapus.");
        showDeleteModal.value = false;
        fetchProdis();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

onMounted(fetchProdis);
</script>

<style scoped>
.prodi-page {
    max-width: 72rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

.page-header-row {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1rem;
}

@media (min-width: 768px) {
    .page-header-row {
        flex-direction: row;
        align-items: flex-end;
    }
}

.page-title {
    font-size: 1.875rem;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.025em;
}

.page-subtitle {
    color: #64748B;
    margin-top: 0.25rem;
    font-weight: 500;
}

.btn-primary-action {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    background: #4F46E5;
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 1rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.btn-primary-action:hover {
    background: #4338CA;
}

.table-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid #F1F5F9;
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead tr {
    background: #F8FAFC;
    border-bottom: 1px solid #F1F5F9;
}

.data-table thead th {
    padding: 1.25rem 2rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
}

.data-table thead th.text-center {
    text-align: center;
}

.data-table thead th.col-num {
    width: 3rem;
}

.data-table tbody tr {
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.15s;
}

.data-table tbody tr:hover {
    background: rgba(248, 250, 252, 0.5);
}

.data-table tbody td {
    padding: 1.25rem 2rem;
    font-size: 0.875rem;
}

.data-table tbody td.text-center {
    text-align: center;
}

.data-table tbody td.font-bold {
    font-weight: 700;
    color: #94A3B8;
}

.empty-row {
    padding: 3rem !important;
    text-align: center;
    color: #64748B;
}

.prodi-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.prodi-name__icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: #EEF2FF;
    color: #4F46E5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.prodi-name__text {
    font-weight: 700;
    color: #1E293B;
}

.code-badge {
    padding: 0.375rem 0.75rem;
    background: #F1F5F9;
    color: #475569;
    border-radius: 0.5rem;
    font-family: monospace;
    font-size: 0.75rem;
    font-weight: 700;
}

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

.btn-icon--edit {
    background: #FFFBEB;
    color: #D97706;
}

.btn-icon--delete {
    background: #FFF1F2;
    color: #E11D48;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
}

.modal-card {
    background: white;
    width: 100%;
    max-width: 32rem;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-card__header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-card__title {
    font-size: 1.25rem;
    font-weight: 800;
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
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}

.form-input-lg {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    font-size: 0.875rem;
}

.form-input--mono {
    font-family: monospace;
}

.modal-card__footer {
    padding: 1.25rem 2rem;
    background: #F8FAFC;
    border-top: 1px solid #F1F5F9;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    border: 1px solid #E2E8F0;
    background: white;
    font-weight: 700;
    cursor: pointer;
}

.btn-save {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background: #4F46E5;
    color: white;
    font-weight: 700;
    border: none;
    cursor: pointer;
}

.confirm-modal {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

.confirm-modal__icon--danger {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background: #FFF1F2;
    color: #F43F5E;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.confirm-modal__actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    width: 100%;
}

.btn-danger {
    background: #F43F5E;
    color: white;
    padding: 0.75rem;
    border-radius: 1rem;
    border: none;
    font-weight: 700;
    cursor: pointer;
}
</style>
