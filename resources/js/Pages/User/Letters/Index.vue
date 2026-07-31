<template>
    <AppLayout>
        <div class="letters-page">
            <!-- Header Card -->
            <div class="card">
                <div class="letters-header">
                    <div class="header-left">
                        <div class="header-icon-row">
                            <div class="header-icon">
                                <span class="material-symbols-outlined icon-size-lg">description</span>
                            </div>
                            <div>
                                <h1 class="page-title">
                                    Surat Saya
                                </h1>
                                <p class="page-subtitle">
                                    Daftar surat yang telah Anda buat beserta nomor surat resminya.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="search-wrapper">
                        <input
                            v-model="search"
                            @keyup.enter="fetchLetters"
                            class="form-input search-input"
                            placeholder="Cari nomor surat atau keterangan..."
                        />
                        <span class="material-symbols-outlined search-icon">search</span>
                        <button
                            v-if="search"
                            @click="search = ''; fetchLetters();"
                            class="search-clear"
                        >
                            <span class="material-symbols-outlined icon-size-sm">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Surat</th>
                                <th>Penandatangan</th>
                                <th>Jenis & Tujuan</th>
                                <th>Keterangan</th>
                                <th>Tanggal Surat Dibuat</th>
                                <th class="header-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="letter in letters"
                                :key="letter.id"
                                class="letter-row"
                            >
                                <td class="nowrap">
                                    <div class="letter-number-box">
                                        <template v-if="editingId === letter.id">
                                            <input 
                                                v-model="editNumber" 
                                                class="edit-input" 
                                                @keyup.enter="saveEdit(letter.id)"
                                                @keyup.escape="editingId = null"
                                                v-focus
                                            />
                                            <button @mousedown.prevent @click="saveEdit(letter.id)" class="edit-save-btn" title="Simpan">
                                                <span class="material-symbols-outlined">check</span>
                                            </button>
                                            <button @mousedown.prevent @click="editingId = null" class="edit-cancel-btn" title="Batal">
                                                <span class="material-symbols-outlined">close</span>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <span class="letter-number" @dblclick="startEdit(letter)">
                                                {{ letter.letter_number || "Menunggu" }}
                                            </span>
                                            <div class="number-actions">
                                                <button @click="copyNumber(letter.letter_number)" class="number-action-btn" title="Copy">
                                                    <span class="material-symbols-outlined">content_copy</span>
                                                </button>
                                                <button @click="startEdit(letter)" class="number-action-btn" title="Edit">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td>
                                    <span class="signatory-text">{{ letter.signatory_name || '-' }}</span>
                                </td>
                                <td>
                                    <div class="letter-type-cell">
                                        <div class="letter-type-info">
                                            <span class="target-text">
                                                {{ letter.target_info }}
                                            </span>
                                            <span class="letter-type-name">
                                                {{ letter.type?.name || "Surat" }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="notes-text" :title="letter.notes">
                                        {{ truncateNotes(letter.notes) || '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="date-text">{{ formatDate(letter.created_at) }}</span>
                                </td>
                                <td class="cell-center">
                                    <div class="action-buttons">
                                        <button
                                            @click="deleteLetter(letter.id)"
                                            class="action-btn delete-btn"
                                            title="Hapus Surat"
                                        >
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0">
                                <td colspan="6" class="empty-table-cell">
                                    <div class="empty-state-content">
                                        <div class="empty-icon-box">
                                            <span class="material-symbols-outlined icon-size-xl">inbox</span>
                                        </div>
                                        <h3 class="empty-title">Belum ada surat</h3>
                                        <p class="empty-subtitle">
                                            Surat yang Anda buat akan muncul di sini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.lastPage > 1"
                class="pagination"
            >
                <button
                    v-for="p in pagination.lastPage"
                    :key="p"
                    @click="page = p; fetchLetters();"
                    class="pagination-btn"
                    :class="p === page ? 'active' : ''"
                >
                    {{ p }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import axios from "axios";
import AppLayout from "../../../Layouts/AppLayout.vue";

const letters = ref([]);
const search = ref("");
const page = ref(1);
const pagination = ref({ lastPage: 1 });
const showFlash = inject("showFlash");

const editingId = ref(null);
const editNumber = ref("");

const vFocus = {
    mounted: (el) => el.focus()
};

const copyNumber = (num) => {
    if (!num) return;
    navigator.clipboard.writeText(num);
    showFlash("Nomor surat disalin!");
};

const startEdit = (letter) => {
    editingId.value = letter.id;
    editNumber.value = letter.letter_number;
};

const saveEdit = async (id) => {
    if (!editNumber.value) return;
    try {
        await axios.patch(`/api/letters/${id}/number`, {
            letter_number: editNumber.value
        });
        showFlash("Nomor surat diperbarui!");
        editingId.value = null;
        fetchLetters();
    } catch (error) {
        let msg = "Gagal memperbarui nomor surat.";
        if (error.response?.data?.errors) {
            msg = Object.values(error.response.data.errors).flat()[0];
        } else if (error.response?.data?.message) {
            msg = error.response.data.message;
        }
        showFlash(msg, "error");
    }
};

const formatDate = (d) =>
    new Date(d).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });

const truncateNotes = (notes) => {
    if (!notes) return "";
    return notes.length > 30 ? notes.substring(0, 30) + '...' : notes;
};

const roleLabel = (role) =>
    ({
        direktur: "Direktur",
        wadir: "Wadir",
        kaprodi: "Kaprodi",
        staf: "Staf TU",
        dosen: "Dosen",
    })[role] || role;

const targetDisplay = (letter) => {
    if (letter.target_name) {
        if (letter.target_jurusan) return `${letter.target_name} (${letter.target_jurusan})`;
        return letter.target_name;
    }
    
    if (letter.target_user?.name) {
        let role = letter.target_user.role;
        return `${letter.target_user.name} (${roleLabel(role)})`;
    }

    return "-";
};

const fetchLetters = async () => {
    const res = await axios.get("/api/letters", {
        params: { search: search.value, page: page.value },
    });
    letters.value = res.data.data || [];
    pagination.value = { lastPage: res.data.last_page || 1 };
};



const deleteLetter = async (id) => {
    if (!confirm("Apakah Anda yakin ingin menghapus surat ini?")) return;
    try {
        await axios.delete(`/api/letters/${id}`);
        showFlash("Surat berhasil dihapus!");
        fetchLetters();
    } catch (error) {
        showFlash(error.response?.data?.message || "Gagal menghapus surat", "error");
    }
};

onMounted(fetchLetters);
</script>

<style scoped>
.letters-page {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
    position: relative;
}

/* Header */
.letters-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

@media (min-width: 768px) {
    .letters-header {
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.header-icon-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.header-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, var(--primary), var(--accent-indigo));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    flex-shrink: 0;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

.page-subtitle {
    color: var(--slate-500);
    font-size: 0.875rem;
    font-weight: 500;
}

/* Search */
.search-wrapper {
    position: relative;
}

.search-input {
    padding-left: 3rem;
    background: white;
    border: 1px solid var(--slate-200);
    width: 18rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.search-input:focus {
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 0.75rem;
    color: var(--slate-400);
    transition: color 0.2s;
}

.search-wrapper:focus-within .search-icon {
    color: var(--primary);
}

.search-clear {
    position: absolute;
    right: 1rem;
    top: 0.75rem;
    color: var(--slate-400);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.search-clear:hover {
    color: var(--rose-500);
}

/* Table */
.table-card {
    padding: 0;
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 480px; /* ~5 rows + header */
    width: 100%;
}

.data-table {
    width: 100%;
    min-width: 1000px;
    border-collapse: collapse;
}

.data-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--primary);
}

.data-table th, 
.data-table td {
    white-space: nowrap;
}

.data-table td .notes-text {
    white-space: normal;
    min-width: 200px;
    display: block;
}

.data-table thead th.header-center {
    text-align: center;
}

/* Letter row */
.letter-row {
    background: white;
    transition: background 0.15s;
}

.letter-row:hover {
    background: rgba(248, 250, 252, 0.5);
}

.letter-type-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.letter-type-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.letter-type-name {
    font-weight: 500;
    color: var(--slate-500);
    font-size: 0.75rem;
}

.target-text {
    font-size: 1rem;
    font-weight: 800;
    color: var(--slate-900);
    display: block;
    margin-bottom: 0.125rem;
}

.signatory-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--slate-700);
}

.notes-text {
    font-size: 0.875rem;
    color: var(--slate-600);
}

.date-text {
    font-size: 0.875rem;
    color: var(--slate-500);
    font-weight: 500;
}

/* Letter Number */
.letter-number-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.letter-number {
    font-size: 0.875rem;
    font-family: monospace;
    font-weight: 800;
    color: var(--primary);
    background: var(--indigo-50);
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    border: 1px dashed var(--indigo-200);
    cursor: pointer;
    transition: all 0.2s;
}

.letter-number:hover {
    background: var(--indigo-100);
}

.number-actions {
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.letter-row:hover .number-actions {
    opacity: 1;
}

.number-action-btn {
    padding: 0.25rem;
    border-radius: 0.375rem;
    color: var(--slate-400);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.number-action-btn:hover {
    color: var(--primary);
    background: var(--indigo-50);
}

.number-action-btn .material-symbols-outlined {
    font-size: 16px;
}

.edit-input {
    font-family: monospace;
    font-size: 0.875rem;
    font-weight: 800;
    color: var(--primary);
    background: white;
    border: 2px solid var(--primary);
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    width: 100%;
    min-width: 120px;
    max-width: 180px;
}

.edit-save-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 0.375rem;
    padding: 0.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.edit-cancel-btn {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 0.375rem;
    padding: 0.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.action-btn {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    text-decoration: none;
}

.download-btn {
    background: rgba(79, 70, 229, 0.1);
    color: var(--primary);
}

.download-btn:hover {
    background: var(--primary);
    color: white;
}

.upload-btn {
    background: var(--slate-100);
    color: var(--slate-600);
}

.upload-btn:hover {
    background: var(--slate-200);
    color: var(--slate-800);
}

.upload-btn-primary {
    background: var(--emerald-50);
    color: var(--emerald-600);
    width: auto;
    padding: 0 1rem;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.75rem;
}

.upload-btn-primary:hover {
    background: var(--emerald-100);
    color: var(--emerald-700);
}

.delete-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.delete-btn:hover {
    background: #ef4444;
    color: white;
}

/* Empty Table Cell */
.empty-table-cell {
    padding: 4rem 2rem !important;
    text-align: center;
}

.empty-state-content {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-icon-box {
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

.empty-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-600);
    margin-bottom: 0.25rem;
}

.empty-subtitle {
    font-size: 0.75rem;
    color: var(--slate-400);
}

.cell-center {
    text-align: center;
}

/* Icon sizes */
.icon-size-lg { font-size: 28px; }
.icon-size-md { font-size: 24px; }
.icon-size-sm { font-size: 18px; }
.icon-size-xl { font-size: 40px; }

/* Hidden Input & Overlay */
.hidden-input {
    display: none;
}

.upload-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

.upload-spinner {
    background: white;
    padding: 2rem 3rem;
    border-radius: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.upload-spinner .animate-spin {
    color: var(--primary);
}

.upload-spinner p {
    font-weight: 600;
    color: var(--slate-800);
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
