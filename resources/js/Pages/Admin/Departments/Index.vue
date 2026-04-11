<template>
    <AppLayout>
        <div class="departments-page">
            <div class="page-header-row">
                <div>
                    <h1 class="page-title">Kelola Jurusan</h1>
                    <p class="page-subtitle">
                        Atur data jurusan untuk penempatan Kaprodi dan Dosen.
                    </p>
                </div>
                <button @click="openCreateModal" class="btn-primary-action">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Jurusan
                </button>
            </div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-num">#</th>
                                <th>Nama Jurusan</th>
                                <th>Kode</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(dept, i) in departments" :key="dept.id">
                                <td class="text-muted font-bold">{{ i + 1 }}</td>
                                <td>
                                    <div class="dept-name">
                                        <div class="dept-name__icon">
                                            <span class="material-symbols-outlined">school</span>
                                        </div>
                                        <span class="dept-name__text">{{ dept.name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="code-badge">{{ dept.code }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button @click="openEditModal(dept)" class="btn-icon btn-icon--edit">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="openDeleteModal(dept)" class="btn-icon btn-icon--delete">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="departments.length === 0">
                                <td colspan="4" class="empty-row">Belum ada data jurusan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
                <div class="modal-card">
                    <form @submit.prevent="saveDepartment">
                        <div class="modal-card__header">
                            <div>
                                <h3 class="modal-card__title">
                                    {{ editingDept ? "Edit" : "Tambah" }} Jurusan
                                </h3>
                            </div>
                            <button type="button" @click="showModal = false" class="modal-close">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="modal-card__body">
                            <div class="form-group">
                                <label class="form-label">Nama Jurusan</label>
                                <input v-model="form.name" required class="form-input-lg" type="text" placeholder="Contoh: Teknik Informatika" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kode</label>
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
                        <h3 class="confirm-modal__title">Hapus Jurusan?</h3>
                        <p class="confirm-modal__desc">
                            Jurusan <b>{{ deletingDept?.name }}</b> akan dihapus.
                        </p>
                        <div class="confirm-modal__actions">
                            <button @click="showDeleteModal = false" class="btn-cancel">Batal</button>
                            <button @click="deleteDepartment" class="btn-danger">Hapus</button>
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
const departments = ref([]);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingDept = ref(null);
const deletingDept = ref(null);
const form = ref({ name: "", code: "" });
const saving = ref(false);

const fetchDepartments = async () => {
    const res = await axios.get("/api/admin/departments");
    departments.value = res.data;
};

const openCreateModal = () => {
    editingDept.value = null;
    form.value = { name: "", code: "" };
    showModal.value = true;
};

const openEditModal = (dept) => {
    editingDept.value = dept;
    form.value = { name: dept.name, code: dept.code };
    showModal.value = true;
};

const openDeleteModal = (dept) => {
    deletingDept.value = dept;
    showDeleteModal.value = true;
};

const saveDepartment = async () => {
    saving.value = true;
    try {
        if (editingDept.value) {
            await axios.put(`/api/admin/departments/${editingDept.value.id}`, form.value);
        } else {
            await axios.post("/api/admin/departments", form.value);
        }
        showFlash(editingDept.value ? "Jurusan berhasil diperbarui." : "Jurusan berhasil ditambahkan.");
        showModal.value = false;
        fetchDepartments();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal menyimpan.", "error");
    } finally {
        saving.value = false;
    }
};

const deleteDepartment = async () => {
    try {
        await axios.delete(`/api/admin/departments/${deletingDept.value.id}`);
        showFlash("Jurusan berhasil dihapus.");
        showDeleteModal.value = false;
        fetchDepartments();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

onMounted(fetchDepartments);
</script>

<style scoped>
.departments-page {
    max-width: 72rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

/* Page Header Row */
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
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

.page-subtitle {
    color: var(--slate-500);
    margin-top: 0.25rem;
    font-weight: 500;
}

.btn-primary-action {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    background: var(--primary);
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
    background: var(--primary-dark);
}

.btn-primary-action:active {
    transform: scale(0.95);
}

.btn-primary-action .material-symbols-outlined {
    font-size: 1.25rem;
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.data-table thead tr {
    background: var(--slate-50);
    border-bottom: 1px solid var(--slate-100);
}

.data-table thead th {
    padding: 1.25rem 2rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-500);
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
    border-bottom: 1px solid var(--slate-100);
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

.data-table tbody td.text-muted {
    color: var(--slate-400);
}

.data-table tbody td.font-bold {
    font-weight: 700;
    color: var(--slate-400);
}

.empty-row {
    padding: 3rem !important;
    text-align: center;
    color: var(--slate-500);
}

/* Dept Name */
.dept-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dept-name__icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: var(--indigo-50);
    color: var(--indigo-600);
    display: flex;
    align-items: center;
    justify-content: center;
}

.dept-name__icon .material-symbols-outlined {
    font-size: 1.25rem;
}

.dept-name__text {
    font-weight: 700;
    color: var(--slate-800);
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
</style>
