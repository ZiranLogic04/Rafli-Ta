<template>
    <AppLayout>
        <div class="letters-page">
            <!-- Header -->
            <div class="page-header">
                <div class="page-header__glow"></div>
                <div class="page-header__content">
                    <h2 class="page-header__title">Persetujuan Surat</h2>
                    <p class="page-header__subtitle">
                        Pantau seluruh pengajuan surat dan status nomor otomatisnya.
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--indigo">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <span class="stat-card__label">Total</span>
                    </div>
                    <p class="stat-card__value">{{ total }}</p>
                </div>
                <div class="stat-card">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--amber">
                            <span class="material-symbols-outlined">pending</span>
                        </div>
                        <span class="stat-card__label">Pending</span>
                    </div>
                    <p class="stat-card__value stat-card__value--amber">{{ pending }}</p>
                </div>
                <div class="stat-card">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--emerald">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <span class="stat-card__label">Disetujui</span>
                    </div>
                    <p class="stat-card__value stat-card__value--emerald">{{ approved }}</p>
                </div>
                <div class="stat-card">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--rose">
                            <span class="material-symbols-outlined">cancel</span>
                        </div>
                        <span class="stat-card__label">Ditolak</span>
                    </div>
                    <p class="stat-card__value stat-card__value--rose">{{ rejected }}</p>
                </div>
            </div>

            <!-- Search -->
            <div class="filter-bar">
                <h3 class="filter-bar__title">Daftar Pengajuan</h3>
                <div class="filter-bar__controls">
                    <select
                        v-model="typeFilter"
                        @change="page = 1; fetchLetters();"
                        class="form-select"
                    >
                        <option value="">Semua Jenis</option>
                        <option v-for="t in typeOptions" :key="t.id" :value="t.id">
                            {{ t.name }}
                        </option>
                    </select>
                    <select
                        v-model="statusFilter"
                        @change="page = 1; fetchLetters();"
                        class="form-select"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <div class="search-input">
                        <input
                            v-model="search"
                            @keyup.enter="fetchLetters"
                            class="search-input__field"
                            placeholder="Cari..."
                            type="text"
                        />
                        <span class="material-symbols-outlined search-input__icon">search</span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Pengaju</th>
                                <th>Tujuan</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>No. Surat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="letter in letters" :key="letter.id">
                                <td class="font-bold">{{ letter.user?.name || "-" }}</td>
                                <td class="text-muted text-sm">{{ targetDisplay(letter) }}</td>
                                <td class="text-muted font-medium">
                                    {{
                                        letter.type?.parent?.name
                                            ? `${letter.type.parent.name} / ${letter.type.name}`
                                            : letter.type?.name || "-"
                                    }}
                                </td>
                                <td class="text-muted text-sm">{{ formatDate(letter.created_at) }}</td>
                                <td>
                                    <span class="status-badge" :class="statusClass(letter.status)">{{ statusLabel(letter.status) }}</span>
                                </td>
                                <td>
                                    <span v-if="letter.letter_number" class="letter-number">
                                        <span class="letter-number__code">{{ letter.letter_number }}</span>
                                        <button
                                            @click="openEditNumberModal(letter)"
                                            class="letter-number__edit"
                                            title="Edit Nomor Surat"
                                        >
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                    </span>
                                    <span v-else class="text-muted text-xs">Otomatis saat disetujui</span>
                                </td>
                                <td class="text-center">
                                    <div v-if="letter.status === 'pending'" class="action-buttons">
                                        <button @click="approveLetter(letter.id)" class="btn-approve">
                                            <span class="material-symbols-outlined">check</span>
                                            Setujui
                                        </button>
                                        <button @click="openRejectModal(letter)" class="btn-reject">
                                            <span class="material-symbols-outlined">close</span>
                                            Tolak
                                        </button>
                                    </div>
                                    <span v-else class="text-muted text-xs">&mdash;</span>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0">
                                <td colspan="7" class="empty-row">Tidak ada data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.lastPage > 1" class="pagination">
                <button
                    v-for="p in pagination.lastPage"
                    :key="p"
                    @click="page = p; fetchLetters();"
                    class="pagination-btn"
                    :class="{ active: p === page }"
                >
                    {{ p }}
                </button>
            </div>

            <!-- Reject Modal -->
            <div v-if="showRejectModal" class="modal-overlay" @click.self="showRejectModal = false">
                <div class="modal-content modal-content--lg">
                    <h3 class="modal-content__title">Tolak Pengajuan</h3>
                    <p class="modal-content__desc">Berikan alasan penolakan untuk pengaju.</p>
                    <textarea
                        v-model="rejectNote"
                        rows="4"
                        class="form-textarea"
                        placeholder="Tulis alasan penolakan..."
                    ></textarea>
                    <div class="modal-actions">
                        <button @click="showRejectModal = false" class="btn-cancel">Batal</button>
                        <button @click="rejectLetter" :disabled="!rejectNote" class="btn-danger">Tolak Surat</button>
                    </div>
                </div>
            </div>

            <!-- Edit Number Modal -->
            <div v-if="showEditNumberModal" class="modal-overlay" @click.self="showEditNumberModal = false">
                <div class="modal-content modal-content--md">
                    <h3 class="modal-content__title">Edit Nomor Surat</h3>
                    <p class="modal-content__desc">Ubah nomor surat untuk pengajuan ini.</p>
                    <input
                        v-model="editNumberValue"
                        class="form-input-mono"
                        placeholder="Masukkan nomor surat..."
                    />
                    <div class="modal-actions">
                        <button @click="showEditNumberModal = false" class="btn-cancel">Batal</button>
                        <button @click="updateLetterNumber" :disabled="!editNumberValue" class="btn-primary-modal">Simpan</button>
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
const letters = ref([]);
const search = ref("");
const page = ref(1);
const pagination = ref({ lastPage: 1 });
const typeFilter = ref("");
const statusFilter = ref("");
const typeOptions = ref([]);
const total = ref(0);
const pending = ref(0);
const approved = ref(0);
const rejected = ref(0);
const showRejectModal = ref(false);
const rejectNote = ref("");
const rejectingLetterId = ref(null);
const showEditNumberModal = ref(false);
const editNumberValue = ref("");
const editingNumberId = ref(null);

const formatDate = (d) =>
    new Date(d).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
const statusClass = (s) =>
    ({
        pending: "status-badge--pending",
        approved: "status-badge--approved",
        rejected: "status-badge--rejected",
    })[s] || "";
const statusLabel = (s) =>
    ({ pending: "Menunggu", approved: "Disetujui", rejected: "Ditolak" })[s] || s;

const roleLabel = (role) =>
    ({
        direktur: "Direktur",
        wadir: "Wadir",
        kaprodi: "Kaprodi",
        staf: "Staf TU",
        dosen: "Dosen",
    })[role] || role;

const targetDisplay = (letter) => {
    if (letter.target_user?.name) return letter.target_user.name;

    if (letter.target_role === "wadir") {
        const lvl = letter.target_wadir_level ? ` ${letter.target_wadir_level}` : "";
        return `Wadir${lvl}`;
    }

    if (
        ["kaprodi", "dosen"].includes(letter.target_role) &&
        letter.target_jurusan
    ) {
        return `${roleLabel(letter.target_role)} (${letter.target_jurusan})`;
    }

    if (letter.target_role) return roleLabel(letter.target_role);
    if (letter.current_approver_role) return `Role: ${roleLabel(letter.current_approver_role)}`;
    return "-";
};

const fetchLetters = async () => {
    const res = await axios.get("/api/admin/letters", {
        params: {
            search: search.value,
            page: page.value,
            type_id: typeFilter.value,
            status: statusFilter.value,
        },
    });
    letters.value = res.data.letters?.data || [];
    pagination.value = { lastPage: res.data.letters?.last_page || 1 };
    typeOptions.value = res.data.letterTypes || [];
    total.value = res.data.total;
    pending.value = res.data.pending;
    approved.value = res.data.approved;
    rejected.value = res.data.rejected;
};

const approveLetter = async (id) => {
    try {
        await axios.post(`/api/admin/letters/${id}/approve`);
        showFlash("Surat berhasil disetujui.");
        fetchLetters();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const openRejectModal = (letter) => {
    rejectingLetterId.value = letter.id;
    rejectNote.value = "";
    showRejectModal.value = true;
};

const rejectLetter = async () => {
    try {
        await axios.post(
            `/api/admin/letters/${rejectingLetterId.value}/reject`,
            { rejection_note: rejectNote.value },
        );
        showFlash("Surat telah ditolak.");
        showRejectModal.value = false;
        fetchLetters();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const openEditNumberModal = (letter) => {
    editingNumberId.value = letter.id;
    editNumberValue.value = letter.letter_number || "";
    showEditNumberModal.value = true;
};

const updateLetterNumber = async () => {
    try {
        await axios.patch(
            `/api/admin/letters/${editingNumberId.value}/letter-number`,
            { letter_number: editNumberValue.value },
        );
        showFlash("Nomor surat berhasil diperbarui.");
        showEditNumberModal.value = false;
        fetchLetters();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
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
}

/* Page Header */
.page-header {
    position: relative;
    background: white;
    border-radius: 1.5rem;
    padding: 2rem;
    border: 1px solid var(--slate-100);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.page-header__glow {
    position: absolute;
    top: 0;
    right: 0;
    width: 16rem;
    height: 16rem;
    background: rgba(79, 70, 229, 0.05);
    border-radius: 50%;
    margin-right: -5rem;
    margin-top: -5rem;
    filter: blur(3rem);
}

.page-header__content {
    position: relative;
    z-index: 10;
}

.page-header__title {
    font-size: 1.875rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

.page-header__subtitle {
    color: var(--slate-500);
    font-size: 1rem;
    font-weight: 500;
    margin-top: 0.25rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    background: white;
    border-radius: 1.5rem;
    padding: 1.5rem;
    border: 1px solid var(--slate-100);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.stat-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.stat-card__icon {
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card__icon .material-symbols-outlined {
    font-size: 1.5rem;
}

.stat-card__icon--indigo {
    background: var(--indigo-50);
    color: var(--indigo-600);
}

.stat-card__icon--amber {
    background: var(--amber-50);
    color: var(--amber-600);
}

.stat-card__icon--emerald {
    background: var(--emerald-50);
    color: var(--emerald-600);
}

.stat-card__icon--rose {
    background: var(--rose-50);
    color: var(--rose-600);
}

.stat-card__label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.stat-card__value {
    font-size: 1.875rem;
    font-weight: 800;
    color: var(--slate-900);
}

.stat-card__value--amber {
    color: var(--amber-600);
}

.stat-card__value--emerald {
    color: var(--emerald-600);
}

.stat-card__value--rose {
    color: var(--rose-600);
}

/* Filter Bar */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.filter-bar__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--slate-900);
}

.filter-bar__controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}

.filter-bar__controls .form-select {
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid var(--slate-200);
    background: white;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-700);
    transition: all 0.2s;
}

.filter-bar__controls .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4);
}

/* Search Input */
.search-input {
    position: relative;
}

.search-input__field {
    padding: 0.75rem 1rem;
    padding-left: 3rem;
    border: 1px solid var(--slate-200);
    border-radius: 0.75rem;
    background: white;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    width: 18rem;
}

.search-input__field:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.search-input__icon {
    position: absolute;
    left: 1rem;
    top: 0.75rem;
    color: var(--slate-400);
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
    padding: 1.25rem 1.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    white-space: nowrap;
}

.data-table thead th.text-center {
    text-align: center;
}

.data-table tbody td {
    padding: 1.25rem 1.5rem;
    font-size: 0.875rem;
}

.data-table tbody td.text-center {
    text-align: center;
}

.data-table tbody td.font-bold {
    font-weight: 700;
    color: var(--slate-700);
}

.data-table tbody td.text-muted {
    color: var(--slate-500);
}

.data-table tbody td.text-sm {
    font-size: 0.875rem;
}

.data-table tbody td.text-xs {
    font-size: 0.75rem;
}

.data-table tbody td.font-medium {
    font-weight: 500;
}

.data-table tbody tr:hover {
    background: rgba(248, 250, 252, 0.5);
}

.empty-row {
    padding: 3rem !important;
    text-align: center;
    color: var(--slate-500);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-badge--pending {
    background: var(--amber-50);
    color: var(--amber-700);
    border: 1px solid var(--amber-100);
}

.status-badge--approved {
    background: var(--emerald-50);
    color: var(--emerald-700);
    border: 1px solid var(--emerald-100);
}

.status-badge--rejected {
    background: var(--rose-50);
    color: var(--rose-700);
    border: 1px solid var(--rose-100);
}

/* Letter Number */
.letter-number {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.letter-number__code {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    background: var(--slate-100);
    color: var(--slate-700);
    font-size: 0.75rem;
    font-family: monospace;
}

.letter-number__edit {
    opacity: 0;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 0.375rem;
    background: var(--amber-50);
    color: var(--amber-600);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.letter-number__edit:hover {
    background: var(--amber-100);
}

.letter-number__edit .material-symbols-outlined {
    font-size: 0.875rem;
}

.letter-number:hover .letter-number__edit {
    opacity: 1;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-approve {
    padding: 0.5rem 1rem;
    background: var(--emerald-500);
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-approve:hover {
    background: var(--emerald-600);
}

.btn-approve .material-symbols-outlined {
    font-size: 1rem;
}

.btn-reject {
    padding: 0.5rem 1rem;
    background: var(--rose-500);
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-reject:hover {
    background: var(--rose-600);
}

.btn-reject .material-symbols-outlined {
    font-size: 1rem;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.pagination-btn {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 700;
    border: 1px solid var(--slate-200);
    background: white;
    color: var(--slate-500);
    cursor: pointer;
    transition: all 0.2s;
}

.pagination-btn:hover {
    background: var(--slate-50);
}

.pagination-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.modal-content {
    background: white;
    border-radius: 1.5rem;
    padding: 2rem;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-content--lg {
    max-width: 32rem;
}

.modal-content--md {
    max-width: 28rem;
}

.modal-content__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.5rem;
}

.modal-content__desc {
    font-size: 0.875rem;
    color: var(--slate-500);
    margin-bottom: 1.5rem;
}

.form-textarea {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    resize: none;
    color: var(--slate-900);
}

.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.form-input-mono {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-family: monospace;
    transition: all 0.2s;
    color: var(--slate-900);
}

.form-input-mono:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.modal-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    border: 1px solid var(--slate-200);
    color: var(--slate-700);
    font-weight: 700;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: var(--slate-50);
}

.btn-danger {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background: var(--rose-500);
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background: var(--rose-600);
}

.btn-danger:disabled {
    opacity: 0.5;
}

.btn-primary-modal {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background: var(--primary);
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary-modal:hover {
    background: var(--primary-dark);
}

.btn-primary-modal:disabled {
    opacity: 0.5;
}
</style>
