<template>
    <AppLayout>
        <div class="approvals-page">
            <!-- Header -->
            <div class="card">
                <div class="approvals-header">
                    <div class="header-left">
                        <div class="header-icon">
                            <span class="material-symbols-outlined icon-size-lg">pending_actions</span>
                        </div>
                        <div>
                            <h1 class="page-title">
                                Perlu Persetujuan
                            </h1>
                            <p class="page-subtitle">
                                {{ isAdmin ? 'Pantau seluruh surat yang menunggu persetujuan.' : 'Surat yang menunggu persetujuan Anda.' }}
                            </p>
                        </div>
                    </div>
                    <div class="search-wrapper">
                        <input
                            v-model="search"
                            @keyup.enter="page = 1; fetchLetters();"
                            class="form-input search-input"
                            placeholder="Cari pengaju, jenis surat..."
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

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-indigo">
                        <span class="material-symbols-outlined icon-size-base">description</span>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ total }}</p>
                        <p class="stat-label">Total</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-amber">
                        <span class="material-symbols-outlined icon-size-base">pending</span>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value stat-value-amber">{{ pending }}</p>
                        <p class="stat-label">Pending</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-emerald">
                        <span class="material-symbols-outlined icon-size-base">check_circle</span>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value stat-value-emerald">{{ approved }}</p>
                        <p class="stat-label">Disetujui</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-rose">
                        <span class="material-symbols-outlined icon-size-base">cancel</span>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value stat-value-rose">{{ rejected }}</p>
                        <p class="stat-label">Ditolak</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <select
                    v-if="isAdmin"
                    v-model="typeFilter"
                    @change="page = 1; fetchLetters();"
                    class="form-select filter-select"
                >
                    <option :value="null">Semua Jenis</option>
                    <option v-for="t in letterTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <select
                    v-if="isAdmin"
                    v-model="statusFilter"
                    @change="page = 1; fetchLetters();"
                    class="form-select filter-select"
                >
                    <option :value="null">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <div class="filter-search">
                    <input
                        v-model="search"
                        @keyup.enter="page = 1; fetchLetters();"
                        class="filter-search-input"
                        placeholder="Cari pengaju, jenis surat..."
                    />
                    <span class="material-symbols-outlined filter-search-icon">search</span>
                    <button v-if="search" @click="search = ''; fetchLetters();" class="filter-search-clear">
                        <span class="material-symbols-outlined icon-size-sm">close</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="card table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th v-if="isAdmin">Pengaju</th>
                                <th>Tujuan</th>
                                <th>Jenis Surat</th>
                                <th>Nomor Surat</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th class="header-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="letter in letters" :key="letter.id" class="letter-row">
                                <td v-if="isAdmin" class="nowrap">
                                    <span class="applicant-name">{{ letter.user?.name || "-" }}</span>
                                </td>
                                <td class="nowrap">
                                    <div class="target-info">
                                        <span class="target-name">{{ targetName(letter) }}</span>
                                        <span class="target-role-text">{{ targetRole(letter) }}</span>
                                    </div>
                                </td>
                                <td class="nowrap">
                                    <div class="type-info">
                                        <span class="type-name">{{ letter.type?.name || "-" }}</span>
                                        <span v-if="letter.type?.parent?.name" class="type-parent">{{ letter.type.parent.name }}</span>
                                    </div>
                                </td>
                                <td class="nowrap">
                                    <div v-if="letter.letter_number" class="letter-number-row">
                                        <span class="letter-number-badge">
                                            {{ letter.letter_number }}
                                        </span>
                                        <button
                                            v-if="isAdmin"
                                            @click="openEditNumber(letter)"
                                            class="edit-number-btn"
                                            title="Edit Nomor Surat"
                                        >
                                            <span class="material-symbols-outlined icon-size-xs">edit</span>
                                        </button>
                                    </div>
                                    <span v-else class="no-number">Belum ada</span>
                                </td>
                                <td class="nowrap">
                                    <span class="status-badge" :class="statusClass(letter.status)">
                                        <span class="status-dot" :class="statusDot(letter.status)"></span>
                                        {{ statusLabel(letter.status) }}
                                    </span>
                                </td>
                                <td class="nowrap">
                                    <span class="date-text">{{ formatDate(letter.created_at) }}</span>
                                </td>
                                <td class="nowrap">
                                    <div class="action-buttons">
                                        <button
                                            v-if="letter.file_path"
                                            @click="downloadLetter(letter.id)"
                                            class="action-btn action-download"
                                        >
                                            <span class="material-symbols-outlined icon-size-base-sm">download</span>
                                            Unduh
                                        </button>
                                        <button
                                            v-if="letter.status === 'pending'"
                                            @click="approveLetter(letter.id)"
                                            class="action-btn action-approve"
                                        >
                                            <span class="material-symbols-outlined icon-size-base-sm">check_circle</span>
                                            Setujui
                                        </button>
                                        <button
                                            v-if="letter.status === 'pending'"
                                            @click="openReject(letter.id)"
                                            class="action-btn action-reject"
                                        >
                                            <span class="material-symbols-outlined icon-size-base-sm">cancel</span>
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0">
                                <td :colspan="isAdmin ? 7 : 6" class="empty-table-cell">
                                    <div class="empty-state-content">
                                        <div class="empty-icon-box">
                                            <span class="material-symbols-outlined icon-size-xl">check_circle</span>
                                        </div>
                                        <h3 class="empty-title">Semua sudah selesai</h3>
                                        <p class="empty-subtitle">Tidak ada surat yang menunggu persetujuan.</p>
                                    </div>
                                </td>
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
                    :class="p === page ? 'active' : ''"
                >
                    {{ p }}
                </button>
            </div>

            <!-- Reject Modal -->
            <div v-if="showRejectModal" class="modal-overlay" @click.self="showRejectModal = false">
                <div class="modal-content modal-lg">
                    <h3 class="modal-title">Tolak Pengajuan</h3>
                    <p class="modal-desc">Berikan alasan penolakan untuk pengaju.</p>
                    <textarea v-model="rejectNote" rows="4" class="modal-textarea" placeholder="Tulis alasan penolakan..."></textarea>
                    <div class="modal-actions">
                        <button @click="showRejectModal = false" class="modal-btn modal-btn-cancel">Batal</button>
                        <button @click="rejectLetter" :disabled="!rejectNote" class="modal-btn modal-btn-reject">Tolak Surat</button>
                    </div>
                </div>
            </div>

            <!-- Edit Number Modal -->
            <div v-if="showEditNumberModal" class="modal-overlay" @click.self="showEditNumberModal = false">
                <div class="modal-content modal-md">
                    <h3 class="modal-title">Edit Nomor Surat</h3>
                    <p class="modal-desc">Ubah nomor surat untuk pengajuan ini.</p>
                    <input v-model="editNumberValue" class="modal-input" placeholder="Masukkan nomor surat..." />
                    <div class="modal-actions">
                        <button @click="showEditNumberModal = false" class="modal-btn modal-btn-cancel">Batal</button>
                        <button @click="updateLetterNumber" :disabled="!editNumberValue" class="modal-btn modal-btn-save">Simpan</button>
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
const user = inject("user");
const isAdmin = user?.value?.role === "admin";

const letters = ref([]);
const letterTypes = ref([]);
const search = ref("");
const typeFilter = ref(null);
const statusFilter = ref(null);
const page = ref(1);
const pagination = ref({ lastPage: 1 });
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
    new Date(d).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });

const statusClass = (s) =>
    ({
        pending: "badge-pending",
        approved: "badge-approved",
        rejected: "badge-rejected",
    })[s] || "";

const statusDot = (s) =>
    ({
        pending: "dot-pending",
        approved: "dot-approved",
        rejected: "dot-rejected",
    })[s] || "";

const statusLabel = (s) =>
    ({ pending: "Pending", approved: "Disetujui", rejected: "Ditolak" })[s] || s;

const roleLabel = (role) =>
    ({ direktur: "Direktur", wadir: "Wadir", kaprodi: "Kaprodi", staf: "Staf TU", dosen: "Dosen" })[role] || role;

const targetName = (letter) => {
    if (letter.target) return letter.target;
    if (letter.target_user?.name) return letter.target_user.name;
    if (letter.target_role === "wadir") return `Wadir${letter.target_wadir_level ? ` ${letter.target_wadir_level}` : ""}`;
    if (["kaprodi", "dosen"].includes(letter.target_role) && letter.target_jurusan) return `${letter.target_jurusan}`;
    if (letter.target_role) return roleLabel(letter.target_role);
    if (letter.current_approver_role) return roleLabel(letter.current_approver_role);
    return "-";
};

const targetRole = (letter) => {
    if (letter.target_user?.role) return roleLabel(letter.target_user.role);
    if (letter.target_role) return roleLabel(letter.target_role);
    if (letter.current_approver_role) return roleLabel(letter.current_approver_role);
    return "";
};

const fetchLetters = async () => {
    const res = await axios.get("/api/approvals", {
        params: { page: page.value, search: search.value, type_id: typeFilter.value, status: statusFilter.value },
    });
    letters.value = res.data.letters?.data || [];
    total.value = res.data.total || 0;
    pending.value = res.data.pending || 0;
    approved.value = res.data.approved || 0;
    rejected.value = res.data.rejected || 0;
    pagination.value = { lastPage: res.data.letters?.last_page || 1 };
    letterTypes.value = res.data.letterTypes || [];
};

const approveLetter = async (id) => {
    try {
        await axios.post(`/api/approvals/${id}/approve`);
        showFlash("Surat berhasil disetujui.");
        fetchLetters();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const downloadLetter = async (id) => {
    try {
        const res = await axios.get(`/api/letters/${id}/download`, { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `surat-${id}.${res.data.type.includes('pdf') ? 'pdf' : 'docx'}`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal mengunduh surat.", "error");
    }
};

const openReject = (id) => {
    rejectingLetterId.value = id;
    rejectNote.value = "";
    showRejectModal.value = true;
};

const rejectLetter = async () => {
    try {
        await axios.post(`/api/approvals/${rejectingLetterId.value}/reject`, { rejection_note: rejectNote.value });
        showFlash("Surat telah ditolak.");
        showRejectModal.value = false;
        fetchLetters();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const openEditNumber = (letter) => {
    editingNumberId.value = letter.id;
    editNumberValue.value = letter.letter_number || "";
    showEditNumberModal.value = true;
};

const updateLetterNumber = async () => {
    try {
        await axios.patch(`/api/admin/letters/${editingNumberId.value}/letter-number`, { letter_number: editNumberValue.value });
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
.approvals-page {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

/* Header */
.approvals-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

@media (min-width: 768px) {
    .approvals-header {
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (min-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: 1rem;
    border: 1px solid var(--slate-100);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-indigo {
    background: var(--indigo-50);
    color: var(--indigo-600);
}

.stat-icon-amber {
    background: var(--amber-50);
    color: var(--amber-600);
}

.stat-icon-emerald {
    background: var(--emerald-50);
    color: var(--emerald-600);
}

.stat-icon-rose {
    background: var(--rose-50);
    color: var(--rose-600);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
}

.stat-value-amber {
    color: var(--amber-600);
}

.stat-value-emerald {
    color: var(--emerald-600);
}

.stat-value-rose {
    color: var(--rose-600);
}

.stat-label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.15em;
}

/* Filters */
.filters-bar {
    background: white;
    border-radius: 1rem;
    border: 1px solid var(--slate-100);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}

.filter-select {
    background: var(--slate-50);
}

.filter-search {
    position: relative;
    flex: 1;
    min-width: 200px;
}

.filter-search-input {
    width: 100%;
    padding: 0.625rem 2.5rem 0.625rem 2.5rem;
    border-radius: 0.75rem;
    border: 1px solid var(--slate-200);
    background: var(--slate-50);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-700);
    transition: all 0.2s;
}

.filter-search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4);
    background: white;
}

.filter-search-icon {
    position: absolute;
    left: 0.75rem;
    top: 0.625rem;
    color: var(--slate-400);
    font-size: 1.25rem;
}

.filter-search-clear {
    position: absolute;
    right: 0.75rem;
    top: 0.625rem;
    color: var(--slate-400);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.filter-search-clear:hover {
    color: var(--rose-500);
}

/* Table */
.table-card {
    padding: 0;
    overflow: hidden;
    border-radius: 1rem;
}

.table-scroll {
    overflow-x: auto;
}

.data-table thead th.text-center {
    text-align: center;
}

.letter-row {
    transition: background 0.15s;
}

.letter-row:hover {
    background: rgba(248, 250, 252, 0.5);
}

.nowrap {
    white-space: nowrap;
}

.applicant-name {
    font-weight: 600;
    color: var(--slate-800);
    font-size: 0.875rem;
}

.target-info,
.type-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.target-name,
.type-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--slate-800);
}

.target-role-text,
.type-parent {
    font-size: 0.6875rem;
    color: var(--slate-400);
    font-weight: 500;
}

.letter-number-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.letter-number-badge {
    display: inline-flex;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    background: var(--slate-100);
    color: var(--slate-700);
    font-size: 0.75rem;
    font-family: monospace;
    font-weight: 600;
}

.edit-number-btn {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.375rem;
    background: var(--slate-100);
    color: var(--slate-400);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.edit-number-btn:hover {
    background: var(--amber-500);
    color: white;
}

.no-number {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-style: italic;
}

.date-text {
    font-size: 0.875rem;
    color: var(--slate-600);
    font-weight: 500;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.6875rem;
    font-weight: 700;
}

.status-dot {
    width: 0.375rem;
    height: 0.375rem;
    border-radius: 50%;
}

.dot-pending {
    background: var(--amber-500);
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.dot-approved {
    background: var(--emerald-500);
}

.dot-rejected {
    background: var(--rose-500);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.action-download {
    background: var(--slate-100);
    color: var(--slate-600);
}

.action-download:hover {
    background: var(--primary);
    color: white;
}

.action-approve {
    background: var(--emerald-50);
    color: var(--emerald-700);
}

.action-approve:hover {
    background: var(--emerald-500);
    color: white;
}

.action-reject {
    background: var(--rose-50);
    color: var(--rose-700);
}

.action-reject:hover {
    background: var(--rose-500);
    color: white;
}

/* Empty Table Cell */
.empty-table-cell {
    padding: 4rem 1.5rem !important;
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

/* Modals */
.modal-lg {
    max-width: 32rem;
}

.modal-md {
    max-width: 24rem;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.5rem;
}

.modal-desc {
    font-size: 0.875rem;
    color: var(--slate-500);
    margin-bottom: 1.5rem;
}

.modal-textarea {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-700);
    transition: all 0.2s;
    resize: none;
}

.modal-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4);
}

.modal-input {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-family: monospace;
    color: var(--slate-700);
    transition: all 0.2s;
}

.modal-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4);
}

.modal-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 700;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.modal-btn-cancel {
    background: white;
    color: var(--slate-700);
    border: 1px solid var(--slate-200);
}

.modal-btn-cancel:hover {
    background: var(--slate-50);
}

.modal-btn-reject {
    background: var(--rose-500);
    color: white;
}

.modal-btn-reject:hover:not(:disabled) {
    background: var(--rose-600);
}

.modal-btn-reject:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal-btn-save {
    background: var(--primary);
    color: white;
}

.modal-btn-save:hover:not(:disabled) {
    background: var(--primary-dark);
}

.data-table thead th.header-center {
    text-align: center;
}

/* Icon sizes */
.icon-size-lg {
    font-size: 28px;
}

.icon-size-sm {
    font-size: 18px;
}

.icon-size-base {
    font-size: 1.5rem;
}

.icon-size-base-sm {
    font-size: 16px;
}

.icon-size-xs {
    font-size: 14px;
}

.icon-size-xl {
    font-size: 40px;
}
</style>
