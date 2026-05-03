<template>
    <AppLayout>
        <div class="approvals-page">
            <!-- Header -->
            <div class="card">
                <div class="approvals-header">
                    <div class="header-left">
                        <div class="header-icon">
                            <span class="material-symbols-outlined icon-size-lg"
                                >folder_open</span
                            >
                        </div>
                        <div>
                            <h1 class="page-title">Semua Surat</h1>
                            <p class="page-subtitle">
                                {{
                                    isAdmin
                                        ? "Pantau seluruh surat yang tercatat di dalam sistem."
                                        : "Lihat surat-surat yang ditujukan kepada Anda."
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="search-wrapper">
                        <input
                            v-model="search"
                            @keyup.enter="fetchLetters()"
                            class="form-input search-input"
                            placeholder="Cari pengaju, nomor surat..."
                        />
                        <span class="material-symbols-outlined search-icon"
                            >search</span
                        >
                        <button
                            v-if="search"
                            @click="
                                search = '';
                                fetchLetters();
                            "
                            class="search-clear"
                        >
                            <span class="material-symbols-outlined icon-size-sm"
                                >close</span
                            >
                        </button>
                    </div>
                </div>
            </div>



            <!-- Filters -->
            <div class="filters-bar">
                <select
                    v-if="isAdmin"
                    v-model="typeFilter"
                    @change="fetchLetters()"
                    class="form-select filter-select"
                >
                    <option :value="null">Semua Jenis</option>
                    <option v-for="t in letterTypes" :key="t.id" :value="t.id">
                        {{ t.name }}
                    </option>
                </select>
                <div class="filter-search">
                    <input
                        v-model="search"
                        @keyup.enter="fetchLetters()"
                        class="filter-search-input"
                        placeholder="Cari pengaju, jenis surat, atau nomor..."
                    />
                    <span class="material-symbols-outlined filter-search-icon"
                        >search</span
                    >
                    <button
                        v-if="search"
                        @click="
                            search = '';
                            fetchLetters();
                        "
                        class="filter-search-clear"
                    >
                        <span class="material-symbols-outlined icon-size-sm"
                            >close</span
                        >
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="card table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Surat</th>
                                <th v-if="isAdmin">Pengaju</th>
                                <th>Penandatangan</th>
                                <th>Jenis & Tujuan</th>
                                <th>Keterangan</th>
                                <th>Tanggal</th>
                                <th class="header-center">File Surat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="letter in letters"
                                :key="letter.id"
                                class="letter-row"
                            >
                                <td class="nowrap">
                                    <div class="letter-number-row">
                                        <span class="letter-number-badge">
                                            {{
                                                letter.letter_number ||
                                                "Sedang Diproses"
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td v-if="isAdmin" class="nowrap">
                                    <span class="applicant-name">{{
                                        letter.user?.name || "-"
                                    }}</span>
                                </td>
                                <td class="nowrap">
                                    <span class="signatory-text">{{
                                        letter.signatory_name || "-"
                                    }}</span>
                                </td>
                                <td class="nowrap">
                                    <div class="letter-type-cell">
                                        <div class="letter-type-info">
                                            <span class="target-text">
                                                {{ letter.target_info }}
                                            </span>
                                            <span class="letter-type-name">
                                                {{
                                                    letter.type?.parent?.name
                                                        ? `${letter.type.parent.name} / ${letter.type.name}`
                                                        : letter.type?.name || "-"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="notes-text" :title="letter.notes">
                                        {{ truncateNotes(letter.notes) || '-' }}
                                    </span>
                                </td>
                                <td class="nowrap">
                                    <span class="date-text">{{
                                        formatDate(letter.created_at)
                                    }}</span>
                                </td>
                                <td class="nowrap">
                                    <div class="action-buttons">
                                        <a
                                            v-if="letter.file_path"
                                            :href="`/api/letters/${letter.id}/download`"
                                            target="_blank"
                                            class="action-btn action-download"
                                            title="Unduh Surat"
                                        >
                                            <span
                                                class="material-symbols-outlined icon-size-base-sm"
                                                >download</span
                                            >
                                            Unduh
                                        </a>
                                        <span v-else class="no-file-text"
                                            >Belum ada file</span
                                        >
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0">
                                <td
                                    :colspan="isAdmin ? 8 : 7"
                                    class="empty-table-cell"
                                >
                                    <div class="empty-state-content">
                                        <div class="empty-icon-box">
                                            <span
                                                class="material-symbols-outlined icon-size-xl"
                                                >inbox</span
                                            >
                                        </div>
                                        <h3 class="empty-title">
                                            Tidak ada surat
                                        </h3>
                                        <p class="empty-subtitle">
                                            Belum ada surat yang tercatat di
                                            sistem.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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


const showEditNumberModal = ref(false);
const editNumberValue = ref("");
const editingNumberId = ref(null);

const formatDate = (d) =>
    new Date(d).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
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

const targetName = (letter) => {
    if (letter.target_name) {
        if (letter.target_jurusan)
            return `${letter.target_name} (${letter.target_jurusan})`;
        return letter.target_name;
    }
    if (letter.target_user?.name)
        return `${letter.target_user.name} (${roleLabel(letter.target_user.role)})`;
    return "-";
};

const fetchLetters = async () => {
    const res = await axios.get("/api/approvals", {
        params: { search: search.value, type_id: typeFilter.value },
    });
    letters.value = res.data.letters || [];

    letterTypes.value = res.data.letterTypes || [];
};

const openEditNumber = (letter) => {
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
        showFlash(
            e.response?.data?.message || "Gagal memperbarui nomor surat.",
            "error",
        );
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
    grid-template-columns: repeat(1, 1fr);
    gap: 1rem;
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
    overflow-y: auto;
    max-height: 480px;
    width: 100%;
}

.data-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
}

.data-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--primary);
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

.signatory-text {
    font-size: 0.875rem;
    color: var(--slate-700);
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

.notes-text {
    font-size: 0.875rem;
    color: var(--slate-600);
    white-space: normal;
    min-width: 200px;
    display: block;
}

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

.date-text {
    font-size: 0.875rem;
    color: var(--slate-600);
    font-weight: 500;
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
    text-decoration: none;
}

.action-download {
    background: rgba(79, 70, 229, 0.1);
    color: var(--primary);
}

.action-download:hover {
    background: var(--primary);
    color: white;
}

.no-file-text {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-style: italic;
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

/* Icon sizes */
.icon-size-base {
    font-size: 24px;
}
.icon-size-lg {
    font-size: 28px;
}
.icon-size-sm {
    font-size: 18px;
}
.icon-size-xs {
    font-size: 16px;
}
.icon-size-xl {
    font-size: 40px;
}
.icon-size-base-sm {
    font-size: 20px;
}

/* Modals */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 1.5rem;
    padding: 2rem;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-md {
    max-width: 28rem;
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
.modal-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid var(--slate-200);
    background: var(--slate-50);
    font-size: 0.875rem;
    transition: all 0.2s;
    margin-bottom: 1.5rem;
}
.modal-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    background: white;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}
.modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}
.modal-btn-cancel {
    background: white;
    border: 1px solid var(--slate-200);
    color: var(--slate-600);
}
.modal-btn-cancel:hover {
    background: var(--slate-50);
}
.modal-btn-save {
    background: var(--primary);
    color: white;
}
.modal-btn-save:hover:not(:disabled) {
    background: var(--primary-hover);
}
.modal-btn-save:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
