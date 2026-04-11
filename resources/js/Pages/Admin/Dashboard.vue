<template>
    <AppLayout>
        <div class="admin-dashboard">
            <div class="welcome-card">
                <div class="welcome-card__glow"></div>
                <div class="welcome-card__content">
                    <div class="welcome-card__text">
                        <h2 class="welcome-card__title">
                            Halo,
                            <span class="welcome-card__title--gradient">
                                {{ user?.name || "Administrator" }}
                            </span>
                        </h2>
                        <p class="welcome-card__subtitle">
                            Pantau statistik surat sekaligus melihat kategori
                            pengajuan utama yang tersedia di sistem.
                        </p>
                    </div>
                    <div class="welcome-card__badge">
                        <span>Administrator</span>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card stat-card--pending">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--amber">
                            <span class="material-symbols-outlined">pending</span>
                        </div>
                        <span class="stat-card__label">Pending</span>
                    </div>
                    <p class="stat-card__value">{{ stats.pending }}</p>
                    <p class="stat-card__desc">Menunggu persetujuan</p>
                </div>
                <div class="stat-card stat-card--approved">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--emerald">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <span class="stat-card__label">Disetujui</span>
                    </div>
                    <p class="stat-card__value">{{ stats.approved }}</p>
                    <p class="stat-card__desc">Surat disetujui</p>
                </div>
                <div class="stat-card stat-card--rejected">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--rose">
                            <span class="material-symbols-outlined">cancel</span>
                        </div>
                        <span class="stat-card__label">Ditolak</span>
                    </div>
                    <p class="stat-card__value">{{ stats.rejected }}</p>
                    <p class="stat-card__desc">Surat ditolak</p>
                </div>
                <div class="stat-card stat-card--users">
                    <div class="stat-card__header">
                        <div class="stat-card__icon stat-card__icon--indigo">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <span class="stat-card__label">User</span>
                    </div>
                    <p class="stat-card__value">{{ stats.users }}</p>
                    <p class="stat-card__desc">Total pengguna</p>
                </div>
            </div>

            <div class="letter-types-grid">
                <div
                    v-for="group in groupedLetterTypes"
                    :key="group.id"
                    class="card-shimmer letter-type-card"
                >
                    <div
                        :class="[
                            'letter-type-card__icon',
                            getIconConfig(group.name).bgClass,
                            getIconConfig(group.name).textClass,
                        ]"
                    >
                        <span class="material-symbols-outlined">
                            {{ getIconConfig(group.name).icon }}
                        </span>
                    </div>
                    <h3 class="letter-type-card__title">{{ group.name }}</h3>
                    <p class="letter-type-card__desc">
                        {{ groupDescription(group) }}
                    </p>
                    <p
                        v-if="group.children.length > 0"
                        class="letter-type-card__count"
                    >
                        {{ group.children.length }} jenis tersedia
                    </p>
                    <router-link
                        :to="{ path: '/letters/create', query: { type_id: group.id } }"
                        :class="[
                            'letter-type-card__btn',
                            getIconConfig(group.name).btnClass,
                        ]"
                    >
                        <span class="material-symbols-outlined">add_circle</span>
                        Buat Surat
                    </router-link>
                </div>
            </div>

            <div class="recent-letters">
                <div class="recent-letters__header">
                    <div class="recent-letters__title">
                        <div class="recent-letters__icon">
                            <span class="material-symbols-outlined">inbox</span>
                        </div>
                        <h3>Surat Terbaru</h3>
                    </div>
                    <router-link to="/admin/letters" class="recent-letters__link">Lihat Semua</router-link>
                </div>
                <div class="recent-letters__table-wrapper">
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Pengaju</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th class="text-right">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="letter in letters" :key="letter.id">
                                    <td class="font-bold">{{ letter.user?.name || '-' }}</td>
                                    <td class="text-muted">
                                        {{
                                            letter.type?.parent?.name
                                                ? `${letter.type.parent.name} / ${letter.type.name}`
                                                : letter.type?.name || '-'
                                        }}
                                    </td>
                                    <td>
                                        <span class="status-badge" :class="statusClass(letter.status)">{{ statusLabel(letter.status) }}</span>
                                    </td>
                                    <td class="text-right text-muted">{{ formatDate(letter.created_at) }}</td>
                                </tr>
                                <tr v-if="letters.length === 0">
                                    <td colspan="4" class="empty-row">Belum ada pengajuan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, inject, onMounted, ref } from "vue";
import axios from "axios";
import AppLayout from "../../Layouts/AppLayout.vue";

const user = inject("user");
const letters = ref([]);
const letterTypes = ref([]);
const stats = ref({ pending: 0, approved: 0, rejected: 0, users: 0 });

const formatDate = (d) => new Date(d).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });
const statusClass = (s) => ({ pending: "status-badge--pending", approved: "status-badge--approved", rejected: "status-badge--rejected" })[s] || "";
const statusLabel = (s) => ({ pending: "Menunggu", approved: "Disetujui", rejected: "Ditolak" })[s] || s;

const getIconConfig = (name) => {
    const n = name.toLowerCase();

    if (n.includes("tugas")) {
        return {
            icon: "travel_explore",
            bgClass: "icon-bg--indigo",
            textClass: "icon-text--indigo",
            btnClass: "btn-type--indigo",
        };
    }

    if (n.includes("rekomendasi")) {
        return {
            icon: "verified",
            bgClass: "icon-bg--amber",
            textClass: "icon-text--amber",
            btnClass: "btn-type--amber",
        };
    }

    if (n.includes("izin") || n.includes("cuti")) {
        return {
            icon: "event_busy",
            bgClass: "icon-bg--emerald",
            textClass: "icon-text--emerald",
            btnClass: "btn-type--emerald",
        };
    }

    if (n.includes("keterangan")) {
        return {
            icon: "school",
            bgClass: "icon-bg--blue",
            textClass: "icon-text--blue",
            btnClass: "btn-type--blue",
        };
    }

    if (n.includes("undangan")) {
        return {
            icon: "mail",
            bgClass: "icon-bg--violet",
            textClass: "icon-text--violet",
            btnClass: "btn-type--violet",
        };
    }

    if (n.includes("keputusan") || n.includes("edaran") || n === "sk") {
        return {
            icon: "gavel",
            bgClass: "icon-bg--rose",
            textClass: "icon-text--rose",
            btnClass: "btn-type--rose",
        };
    }

    return {
        icon: "description",
        bgClass: "icon-bg--slate",
        textClass: "icon-text--slate",
        btnClass: "btn-type--slate",
    };
};

const groupedLetterTypes = computed(() => {
    const groupMap = new Map();

    for (const type of letterTypes.value) {
        if (!type.parent_id) {
            groupMap.set(type.id, {
                id: type.id,
                name: type.name,
                directType: type,
                children: [],
            });
        }
    }

    for (const type of letterTypes.value) {
        if (!type.parent_id || !type.parent) continue;

        if (!groupMap.has(type.parent.id)) {
            groupMap.set(type.parent.id, {
                id: type.parent.id,
                name: type.parent.name,
                directType: null,
                children: [],
            });
        }

        const group = groupMap.get(type.parent.id);
        group.children.push(type);
    }

    return Array.from(groupMap.values())
        .filter((group) => group.directType || group.children.length > 0)
        .sort((a, b) => a.name.localeCompare(b.name));
});

const groupDescription = (group) => {
    if (group.children.length > 0) {
        return `Kategori ${group.name} memiliki ${group.children.length} jenis surat turunan yang dipilih di halaman pengajuan.`;
    }

    return `Kategori ${group.name} dapat langsung dipakai untuk membuat pengajuan surat.`;
};

onMounted(async () => {
    const res = await axios.get("/api/dashboard");
    letters.value = res.data.letters || [];
    letterTypes.value = res.data.letterTypes || [];
    stats.value = res.data.stats || stats.value;
});
</script>

<style scoped>
.admin-dashboard {
    max-width: 72rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    padding: 0.5rem;
}

@media (min-width: 768px) {
    .admin-dashboard {
        padding: 1.5rem;
    }
}

/* Welcome Card */
.welcome-card {
    position: relative;
    background: white;
    border-radius: 1.5rem;
    padding: 2rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
    overflow: hidden;
}

@media (min-width: 768px) {
    .welcome-card {
        padding: 2.5rem;
    }
}

.welcome-card__glow {
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
    transition: background 0.3s;
}

.welcome-card:hover .welcome-card__glow {
    background: rgba(79, 70, 229, 0.1);
}

.welcome-card__content {
    position: relative;
    z-index: 10;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .welcome-card__content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.welcome-card__text {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.welcome-card__title {
    font-size: 1.875rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

@media (min-width: 768px) {
    .welcome-card__title {
        font-size: 2.25rem;
    }
}

.welcome-card__title--gradient {
    background: linear-gradient(to right, #4F46E5, #6366F1);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.welcome-card__subtitle {
    color: var(--slate-500);
    font-size: 1.125rem;
    font-weight: 500;
    max-width: 36rem;
}

.welcome-card__badge span {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    background: var(--amber-50);
    color: var(--amber-700);
    border: 1px solid var(--amber-100);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 640px) {
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

.stat-card__icon--indigo {
    background: var(--indigo-50);
    color: var(--indigo-600);
}

.stat-card__icon .material-symbols-outlined {
    font-size: 1.5rem;
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

.stat-card__desc {
    font-size: 0.75rem;
    color: var(--slate-500);
    margin-top: 0.25rem;
    font-weight: 500;
}

/* Letter Types Grid */
.letter-types-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .letter-types-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.letter-type-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 2rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
    padding: 2rem;
    transition: transform 0.3s;
}

.letter-type-card:hover {
    transform: translateY(-0.5rem);
}

.letter-type-card__icon {
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    transition: transform 0.5s;
    font-weight: 700;
}

.letter-type-card:hover .letter-type-card__icon {
    transform: scale(1.1);
}

.letter-type-card__icon .material-symbols-outlined {
    font-size: 2.25rem;
}

.icon-bg--indigo { background: var(--indigo-100); }
.icon-bg--amber { background: var(--amber-100); }
.icon-bg--emerald { background: var(--emerald-100); }
.icon-bg--blue { background: var(--blue-100); }
.icon-bg--violet { background: var(--violet-100); }
.icon-bg--rose { background: var(--rose-100); }
.icon-bg--slate { background: var(--slate-100); }

.icon-text--indigo { color: var(--indigo-600); }
.icon-text--amber { color: var(--amber-600); }
.icon-text--emerald { color: var(--emerald-600); }
.icon-text--blue { color: var(--blue-600); }
.icon-text--violet { color: var(--violet-600); }
.icon-text--rose { color: var(--rose-600); }
.icon-text--slate { color: var(--slate-600); }

.letter-type-card__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.75rem;
}

.letter-type-card__desc {
    color: var(--slate-500);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    line-height: 1.625;
    font-weight: 500;
}

.letter-type-card__count {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--slate-400);
    margin-bottom: 2rem;
}

.letter-type-card__btn {
    margin-top: auto;
    width: 100%;
    padding: 1rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 1rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    cursor: pointer;
    background: white;
    border: 2px solid var(--slate-200);
    color: var(--slate-600);
}

.letter-type-card__btn:hover {
    background: var(--slate-800);
    color: white;
    border-color: var(--slate-800);
    box-shadow: 0 10px 15px -3px rgba(100, 116, 139, 0.2);
}

.letter-type-card__btn .material-symbols-outlined {
    font-size: 1.25rem;
}

.btn-type--indigo {
    background: white;
    border: 2px solid var(--indigo-100);
    color: var(--indigo-600);
}
.btn-type--indigo:hover {
    background: var(--indigo-600);
    color: white;
    border-color: var(--indigo-600);
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2);
}

.btn-type--amber {
    background: white;
    border: 2px solid var(--amber-100);
    color: var(--amber-600);
}
.btn-type--amber:hover {
    background: var(--amber-600);
    color: white;
    border-color: var(--amber-600);
    box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.2);
}

.btn-type--emerald {
    background: white;
    border: 2px solid var(--emerald-100);
    color: var(--emerald-600);
}
.btn-type--emerald:hover {
    background: var(--emerald-600);
    color: white;
    border-color: var(--emerald-600);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
}

.btn-type--blue {
    background: white;
    border: 2px solid var(--blue-100);
    color: var(--blue-600);
}
.btn-type--blue:hover {
    background: var(--blue-600);
    color: white;
    border-color: var(--blue-600);
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
}

.btn-type--violet {
    background: white;
    border: 2px solid var(--violet-100);
    color: var(--violet-600);
}
.btn-type--violet:hover {
    background: var(--violet-600);
    color: white;
    border-color: var(--violet-600);
    box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.2);
}

.btn-type--rose {
    background: white;
    border: 2px solid var(--rose-100);
    color: var(--rose-600);
}
.btn-type--rose:hover {
    background: var(--rose-600);
    color: white;
    border-color: var(--rose-600);
    box-shadow: 0 10px 15px -3px rgba(244, 63, 94, 0.2);
}

/* Recent Letters */
.recent-letters__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.recent-letters__title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.recent-letters__icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    background: var(--slate-900);
    display: flex;
    align-items: center;
    justify-content: center;
}

.recent-letters__icon .material-symbols-outlined {
    color: white;
    font-size: 1.125rem;
}

.recent-letters__title h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--slate-900);
}

.recent-letters__link {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--indigo-600);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-decoration: none;
    border-bottom: 2px solid rgba(79, 70, 229, 0.2);
    padding-bottom: 0.125rem;
    transition: color 0.2s;
}

.recent-letters__link:hover {
    color: var(--primary-dark);
}

.recent-letters__table-wrapper {
    background: white;
    border-radius: 2rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.data-table thead tr {
    background: rgba(248, 250, 252, 0.5);
}

.data-table thead th {
    padding: 1.25rem 2rem;
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-align: left;
}

.data-table thead th.text-right {
    text-align: right;
}

.data-table tbody td {
    padding: 1.25rem 2rem;
    font-size: 0.875rem;
}

.data-table tbody td.text-right {
    text-align: right;
}

.data-table tbody td.font-bold {
    font-weight: 700;
    color: var(--slate-700);
}

.data-table tbody td.text-muted {
    color: var(--slate-500);
    font-weight: 500;
}

.data-table tbody tr:hover {
    background: rgba(248, 250, 252, 0.5);
}

.empty-row {
    padding: 2rem !important;
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
</style>
