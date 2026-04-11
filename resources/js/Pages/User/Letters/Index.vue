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
                                    Lihat riwayat dan status surat yang sudah Anda ajukan.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="search-wrapper">
                        <input
                            v-model="search"
                            @keyup.enter="fetchLetters"
                            class="form-input search-input"
                            placeholder="Cari nomor atau jenis surat..."
                        />
                        <span
                            class="material-symbols-outlined search-icon"
                            >search</span
                        >
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
                                <th>Jenis Surat</th>
                                <th>Ditujukan Ke</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Nomor Surat</th>
                                <th class="header-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="letter in letters"
                                :key="letter.id"
                                class="letter-row"
                            >
                                <td>
                                    <div class="letter-type-cell">
                                        <div
                                            class="status-icon-box"
                                            :class="statusIconBg(letter.status)"
                                        >
                                            <span
                                                class="material-symbols-outlined icon-size-md"
                                                :class="statusIconColor(letter.status)"
                                                >{{ statusIcon(letter.status) }}</span
                                            >
                                        </div>
                                        <div class="letter-type-info">
                                            <span class="letter-type-name">
                                                {{ letter.type?.name || "Surat" }}
                                            </span>
                                            <span
                                                v-if="letter.type?.parent?.name"
                                                class="letter-parent-name"
                                            >
                                                {{ letter.type.parent.name }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="target-text">{{ targetDisplay(letter) }}</span>
                                </td>
                                <td>
                                    <span class="date-text">{{ formatDate(letter.created_at) }}</span>
                                </td>
                                <td>
                                    <span
                                        class="status-badge"
                                        :class="statusClass(letter.status)"
                                    >
                                        <span
                                            class="status-dot"
                                            :class="statusDot(letter.status)"
                                        ></span>
                                        {{ statusLabel(letter.status) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        v-if="letter.letter_number"
                                        class="letter-number"
                                    >
                                        {{ letter.letter_number }}
                                    </span>
                                    <span v-else class="no-number">Belum ada</span>
                                </td>
                                <td class="cell-center">
                                    <div class="action-buttons">
                                        <!-- Rejection Note Tooltip -->
                                        <button
                                            v-if="letter.status === 'rejected' && letter.rejection_note"
                                            class="action-btn rejection-info btn-relative"
                                            @mouseenter="showTooltip(letter.id)"
                                            @mouseleave="hideTooltip(letter.id)"
                                        >
                                            <span class="material-symbols-outlined icon-size-sm">info</span>
                                            <div class="rejection-tooltip" :class="{ 'tooltip-visible': visibleTooltips.has(letter.id) }">
                                                <p class="tooltip-title">Alasan Penolakan</p>
                                                <p class="tooltip-text">{{ letter.rejection_note }}</p>
                                            </div>
                                        </button>
                                        <!-- Download -->
                                        <a
                                            v-if="letter.status === 'approved'"
                                            :href="`/api/letters/${letter.id}/download`"
                                            target="_blank"
                                            class="action-btn download-btn"
                                            title="Unduh Surat"
                                        >
                                            <span class="material-symbols-outlined icon-size-sm">download</span>
                                        </a>
                                        <span v-else-if="letter.status !== 'rejected'" class="action-placeholder">—</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="letters.length === 0">
                                <td colspan="5" class="empty-table-cell">
                                    <div class="empty-state-content">
                                        <div class="empty-icon-box">
                                            <span class="material-symbols-outlined icon-size-xl">inbox</span>
                                        </div>
                                        <h3 class="empty-title">Belum ada surat</h3>
                                        <p class="empty-subtitle">
                                            Surat yang Anda ajukan akan muncul di sini.
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
import { ref, onMounted } from "vue";
import axios from "axios";
import AppLayout from "../../../Layouts/AppLayout.vue";

const letters = ref([]);
const search = ref("");
const page = ref(1);
const pagination = ref({ lastPage: 1 });
const visibleTooltips = ref(new Set());

const showTooltip = (id) => {
    visibleTooltips.value.add(id);
};

const hideTooltip = (id) => {
    visibleTooltips.value.delete(id);
};

const formatDate = (d) =>
    new Date(d).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });

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
    ({
        pending: "Menunggu Persetujuan",
        approved: "Disetujui",
        rejected: "Ditolak",
    })[s] || s;

const statusIcon = (s) =>
    ({
        pending: "pending_actions",
        approved: "check_circle",
        rejected: "cancel",
    })[s] || "description";

const statusIconBg = (s) =>
    ({
        pending: "icon-bg-pending",
        approved: "icon-bg-approved",
        rejected: "icon-bg-rejected",
    })[s] || "icon-bg-default";

const statusIconColor = (s) =>
    ({
        pending: "icon-color-pending",
        approved: "icon-color-approved",
        rejected: "icon-color-rejected",
    })[s] || "icon-color-default";

const roleLabel = (role) =>
    ({
        direktur: "Direktur",
        wadir: "Wadir",
        kaprodi: "Kaprodi",
        staf: "Staf TU",
        dosen: "Dosen",
    })[role] || role;

const targetDisplay = (letter) => {
    if (letter.target) return letter.target;
    if (letter.target_user?.name) return letter.target_user.name;

    if (letter.target_role === "wadir") {
        const lvl = letter.target_wadir_level
            ? ` ${letter.target_wadir_level}`
            : "";
        return `Wadir${lvl}`;
    }

    if (
        ["kaprodi", "dosen"].includes(letter.target_role) &&
        letter.target_jurusan
    ) {
        return `${roleLabel(letter.target_role)} (${letter.target_jurusan})`;
    }

    if (letter.target_role) return roleLabel(letter.target_role);
    if (letter.current_approver_role)
        return `Role: ${roleLabel(letter.current_approver_role)}`;
    return "-";
};

const fetchLetters = async () => {
    const res = await axios.get("/api/letters", {
        params: { search: search.value, page: page.value },
    });
    letters.value = res.data.data || [];
    pagination.value = { lastPage: res.data.last_page || 1 };
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
}

.data-table thead th.text-center {
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

.status-icon-box {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icon-bg-pending { background: var(--amber-50); }
.icon-bg-approved { background: var(--emerald-50); }
.icon-bg-rejected { background: var(--rose-50); }
.icon-bg-default { background: var(--slate-50); }

.icon-color-pending { color: var(--amber-600); }
.icon-color-approved { color: var(--emerald-600); }
.icon-color-rejected { color: var(--rose-600); }
.icon-color-default { color: var(--slate-600); }

.letter-type-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.letter-type-name {
    font-weight: 700;
    color: var(--slate-800);
    font-size: 0.875rem;
}

.letter-parent-name {
    font-size: 0.6875rem;
    color: var(--slate-400);
    font-weight: 500;
}

.target-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--slate-700);
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

/* Letter Number */
.letter-number {
    font-size: 0.75rem;
    font-family: monospace;
    font-weight: 700;
    color: var(--slate-600);
    background: var(--slate-100);
    padding: 0.25rem 0.625rem;
    border-radius: 0.5rem;
}

.no-number {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-style: italic;
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

.rejection-info {
    background: var(--rose-50);
    color: var(--rose-600);
    position: relative;
}

.rejection-info:hover {
    background: var(--rose-100);
}

.download-btn {
    background: rgba(79, 70, 229, 0.1);
    color: var(--primary);
}

.download-btn:hover {
    background: var(--primary);
    color: white;
}

.action-placeholder {
    font-size: 0.75rem;
    color: var(--slate-300);
}

/* Rejection Tooltip */
.rejection-tooltip {
    position: absolute;
    bottom: 100%;
    margin-bottom: 0.5rem;
    left: 50%;
    transform: translateX(-50%);
    display: none;
    width: 14rem;
    padding: 0.75rem;
    border-radius: 0.75rem;
    background: var(--slate-800);
    color: white;
    font-size: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
    z-index: 10;
}

.tooltip-visible {
    display: block;
}

.tooltip-title {
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.tooltip-text {
    font-weight: 500;
    opacity: 0.9;
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
.data-table thead th.header-center {
    text-align: center;
}

.cell-center {
    text-align: center;
}

/* Icon sizes */
.icon-size-lg {
    font-size: 28px;
}

.icon-size-md {
    font-size: 24px;
}

.icon-size-sm {
    font-size: 18px;
}

.icon-size-xl {
    font-size: 40px;
}
</style>
