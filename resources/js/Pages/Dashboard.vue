<template>
    <AppLayout>
        <div class="dashboard-container">
            <section class="hero-section">
                <div class="hero-decoration"></div>
                <div class="hero-content">
                    <div class="hero-text">
                        <h2 class="hero-title">
                            Halo,
                            <span class="hero-title-gradient">
                                {{ user?.name || "Pengguna" }}
                            </span>
                        </h2>
                        <p class="hero-subtitle">
                            Pilih surat yang ingin dibuat dari akses yang tersedia
                            pada akun Anda.
                        </p>
                    </div>

                    <div class="hero-badge-section">
                        <span class="hero-badge">
                            {{ user?.role === "admin" ? "Administrator" : "Pengguna Aktif" }}
                        </span>
                        <p class="hero-role">
                            Peran: {{ user?.role || "-" }}
                        </p>
                    </div>
                </div>
            </section>



            <section>
                <div class="section-header">
                    <div>
                        <h3 class="section-title">Akses Surat</h3>
                        <p class="section-subtitle">
                            Pilih jenis surat yang ingin dibuat.
                        </p>
                    </div>
                </div>

                <div
                    v-if="activeLetterTypes.length > 0"
                    class="grid-3"
                >
                    <article
                        v-for="type in activeLetterTypes"
                        :key="type.id"
                        class="card-shimmer letter-card"
                    >
                        <div
                            class="letter-icon-box"
                            :class="[
                                getIconConfig(type.name, type.id).bg,
                                getIconConfig(type.name, type.id).text,
                            ]"
                        >
                            <span class="material-symbols-outlined letter-icon">
                                {{ getIconConfig(type.name, type.id).icon }}
                            </span>
                        </div>

                        <h3 class="letter-title">
                            {{ type.name }}
                        </h3>
                        <p class="letter-description">
                            {{ type.parent?.name || 'Umum' }} - Klik untuk membuat surat ini.
                        </p>

                        <router-link
                            :to="{ path: '/letters/create', query: { type_id: type.id } }"
                            class="letter-btn"
                            :class="getIconConfig(type.name, type.id).btn"
                        >
                            <span class="material-symbols-outlined letter-btn-icon">
                                add_circle
                            </span>
                            Buat Surat
                        </router-link>
                    </article>
                </div>

                <div
                    v-else
                    class="empty-state"
                >
                    <p class="empty-state-text">
                        Belum ada surat yang bisa dibuat dari akun ini.
                    </p>
                </div>
            </section>

            <section>
                <div class="section-header section-header-with-link">
                    <div class="section-title-row">
                        <div class="section-icon-box">
                            <span class="material-symbols-outlined section-icon">
                                history
                            </span>
                        </div>
                        <div>
                            <h3 class="section-title">
                                {{ user?.role === "admin" ? "Surat Terbaru" : "Riwayat Terbaru" }}
                            </h3>
                            <p class="section-subtitle">
                                {{ user?.role === "admin"
                                    ? "Pantauan pengajuan terbaru dari seluruh sistem."
                                    : "Beberapa surat terakhir yang Anda ajukan." }}
                            </p>
                        </div>
                    </div>

                    <router-link
                        :to="user?.role === 'admin' ? '/approvals' : '/letters'"
                        class="see-all-link"
                    >
                        Lihat Semua
                    </router-link>
                </div>

                <div class="table-wrapper">
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th
                                        v-if="user?.role === 'admin'"
                                    >
                                        Pengaju
                                    </th>
                                    <th>
                                        Jenis
                                    </th>
                                    <th>
                                        No. Surat
                                    </th>
                                    <th class="text-right">
                                        Tanggal
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="letter in letters"
                                    :key="letter.id"
                                >
                                    <td
                                        v-if="user?.role === 'admin'"
                                        class="font-bold"
                                    >
                                        {{ letter.user?.name || "-" }}
                                    </td>
                                    <td class="text-muted">
                                        {{
                                            letter.type?.parent?.name
                                                ? `${letter.type.parent.name} / ${letter.type.name}`
                                                : letter.type?.name || "-"
                                        }}
                                    </td>
                                    <td>
                                        <span class="letter-number-badge">
                                            {{ letter.letter_number || 'Sedang Diproses' }}
                                        </span>
                                    </td>
                                    <td class="text-right text-muted">
                                        {{ formatDate(letter.created_at) }}
                                    </td>
                                </tr>
                                <tr v-if="letters.length === 0">
                                    <td
                                        :colspan="user?.role === 'admin' ? 4 : 3"
                                        class="empty-row"
                                    >
                                        Belum ada data surat.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, inject, onMounted, ref } from "vue";
import axios from "axios";
import AppLayout from "../Layouts/AppLayout.vue";

const user = inject("user");
const letters = ref([]);
const letterTypes = ref([]);
const stats = ref({
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
    users: 0,
    approvalInboxCount: 0,
});

const formatDate = (date) =>
    new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });

const getIconConfig = (name, id = 0) => {
    const n = name?.toLowerCase() || "";

    if (n.includes("tugas")) {
        return {
            icon: "travel_explore",
            bg: "bg-indigo-100",
            text: "text-indigo-600",
            btn: "btn-letter btn-letter-indigo",
        };
    }

    if (n.includes("rekomendasi")) {
        return {
            icon: "verified",
            bg: "bg-amber-100",
            text: "text-amber-600",
            btn: "btn-letter btn-letter-amber",
        };
    }

    if (n.includes("tugas") || n.includes("st")) {
        return {
            icon: "assignment",
            bg: "bg-emerald-100",
            text: "text-emerald-600",
            btn: "btn-letter btn-letter-emerald",
        };
    }

    if (n.includes("pengantar")) {
        return {
            icon: "forward_to_inbox",
            bg: "bg-amber-100",
            text: "text-amber-600",
            btn: "btn-letter btn-letter-amber",
        };
    }

    if (n.includes("pernyataan")) {
        return {
            icon: "verified_user",
            bg: "bg-blue-100",
            text: "text-blue-600",
            btn: "btn-letter btn-letter-blue",
        };
    }

    if (n.includes("sk") || n.includes("keputusan")) {
        return {
            icon: "gavel",
            bg: "bg-rose-100",
            text: "text-rose-600",
            btn: "btn-letter btn-letter-rose",
        };
    }

    if (n.includes("mou") || n.includes("kerjasama")) {
        return {
            icon: "handshake",
            bg: "bg-violet-100",
            text: "text-violet-600",
            btn: "btn-letter btn-letter-violet",
        };
    }

    if (n.includes("ia") || n.includes("implementation")) {
        return {
            icon: "architecture",
            bg: "bg-indigo-100",
            text: "text-indigo-600",
            btn: "btn-letter btn-letter-indigo",
        };
    }

    if (n.includes("keluar") || n.includes("su")) {
        return {
            icon: "outgoing_mail",
            bg: "bg-cyan-100",
            text: "text-cyan-600",
            btn: "btn-letter btn-letter-cyan",
        };
    }

    const fallbacks = [
        { icon: "description", bg: "bg-slate-100", text: "text-slate-600", btn: "btn-letter btn-letter-slate" },
        { icon: "article", bg: "bg-orange-100", text: "text-orange-600", btn: "btn-letter btn-letter-orange" },
        { icon: "folder_open", bg: "bg-teal-100", text: "text-teal-600", btn: "btn-letter btn-letter-teal" },
        { icon: "edit_note", bg: "bg-pink-100", text: "text-pink-600", btn: "btn-letter btn-letter-pink" },
        { icon: "drafts", bg: "bg-lime-100", text: "text-lime-600", btn: "btn-letter btn-letter-lime" },
    ];

    return fallbacks[id % fallbacks.length];
};

const activeLetterTypes = computed(() => {
    // Tampilkan semua jenis surat yang merupakan sub-jenis (memiliki parent)
    const parentsWithChildren = new Set(letterTypes.value.filter(t => t.parent_id).map(t => t.parent_id));
    
    return letterTypes.value.filter(t => {
        // Tampilkan jika ini adalah child
        if (t.parent_id) return true;
        // Tampilkan jika ini adalah parent tapi tidak punya child sama sekali
        if (!parentsWithChildren.has(t.id)) return true;
        return false;
    }).sort((a, b) => a.name.localeCompare(b.name));
});



onMounted(async () => {
    const res = await axios.get("/api/dashboard");
    letters.value = res.data.letters || [];
    letterTypes.value = res.data.letterTypes || [];
    stats.value = { ...stats.value, ...(res.data.stats || {}) };
});
</script>

<style scoped>
.dashboard-container {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

/* Hero Section */
.hero-section {
    position: relative;
    background: white;
    border-radius: 1.5rem;
    padding: 2rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
    overflow: hidden;
}

@media (min-width: 768px) {
    .hero-section {
        padding: 2.5rem;
    }
}

.hero-decoration {
    position: absolute;
    top: 0;
    right: 0;
    width: 18rem;
    height: 18rem;
    background: rgba(79, 70, 229, 0.05);
    border-radius: 50%;
    margin-right: -6rem;
    margin-top: -6rem;
    filter: blur(3rem);
}

.hero-content {
    position: relative;
    z-index: 10;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .hero-content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.hero-text {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.hero-title {
    font-size: 1.875rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

@media (min-width: 768px) {
    .hero-title {
        font-size: 2.25rem;
    }
}

.hero-title-gradient {
    background: linear-gradient(to right, var(--primary), var(--accent-indigo));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.hero-subtitle {
    color: var(--slate-500);
    font-size: 1.125rem;
    font-weight: 500;
    max-width: 42rem;
}

.hero-badge-section {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
}

@media (min-width: 768px) {
    .hero-badge-section {
        align-items: flex-end;
    }
}

.hero-badge {
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

.hero-role {
    font-size: 0.6875rem;
    font-weight: 500;
    color: var(--slate-400);
    font-style: italic;
    text-transform: capitalize;
}



/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.section-header-with-link {
    margin-bottom: 1.5rem;
}

.section-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-icon-box {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    background: var(--slate-900);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.section-icon {
    color: white;
    font-size: 18px;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--slate-900);
}

.section-subtitle {
    font-size: 0.875rem;
    color: var(--slate-500);
}

.see-all-link {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    transition: color 0.2s;
    border-bottom: 2px solid rgba(79, 70, 229, 0.2);
    padding-bottom: 0.125rem;
    white-space: nowrap;
}

.see-all-link:hover {
    color: var(--primary-dark);
}

/* Letter Cards */
.letter-card {
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

.letter-card:hover {
    transform: translateY(-0.5rem);
}

.letter-icon-box {
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    font-weight: 700;
    transition: transform 0.5s;
}

.letter-card:hover .letter-icon-box {
    transform: scale(1.1);
}

.letter-icon {
    font-size: 2.25rem;
}

.letter-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.75rem;
}

.letter-description {
    color: var(--slate-500);
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    line-height: 1.625;
    font-weight: 500;
}

.letter-btn {
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
}

.letter-btn-icon {
    font-size: 20px;
}

/* Letter Button Variants */
.btn-letter {
    background: white;
    border: 2px solid;
}

.btn-letter-indigo {
    border-color: var(--indigo-100);
    color: var(--indigo-600);
}
.btn-letter-indigo:hover {
    background: var(--indigo-600);
    color: white;
    border-color: var(--indigo-600);
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2);
}

.btn-letter-amber {
    border-color: var(--amber-100);
    color: var(--amber-600);
}
.btn-letter-amber:hover {
    background: var(--amber-600);
    color: white;
    border-color: var(--amber-600);
    box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.2);
}

.btn-letter-emerald {
    border-color: var(--emerald-100);
    color: var(--emerald-600);
}
.btn-letter-emerald:hover {
    background: var(--emerald-600);
    color: white;
    border-color: var(--emerald-600);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
}

.btn-letter-blue {
    border-color: var(--blue-100);
    color: var(--blue-600);
}
.btn-letter-blue:hover {
    background: var(--blue-600);
    color: white;
    border-color: var(--blue-600);
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
}

.btn-letter-violet {
    border-color: var(--violet-100);
    color: var(--violet-600);
}
.btn-letter-violet:hover {
    background: var(--violet-600);
    color: white;
    border-color: var(--violet-600);
    box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.2);
}

.btn-letter-rose {
    border-color: var(--rose-100);
    color: var(--rose-600);
}
.btn-letter-rose:hover {
    background: var(--rose-600);
    color: white;
    border-color: var(--rose-600);
    box-shadow: 0 10px 15px -3px rgba(244, 63, 94, 0.2);
}

.btn-letter-slate {
    border-color: var(--slate-200);
    color: var(--slate-600);
}
.btn-letter-slate:hover {
    background: var(--slate-800);
    color: white;
    border-color: var(--slate-800);
    box-shadow: 0 10px 15px -3px rgba(30, 41, 59, 0.2);
}

/* Icon Background Classes */
.bg-indigo-100 { background: var(--indigo-100); }
.text-indigo-600 { color: var(--indigo-600); }
.bg-amber-100 { background: var(--amber-100); }
.text-amber-600 { color: var(--amber-600); }
.bg-emerald-100 { background: var(--emerald-100); }
.text-emerald-600 { color: var(--emerald-600); }
.bg-blue-100 { background: var(--blue-100); }
.text-blue-600 { color: var(--blue-600); }
.bg-violet-100 { background: var(--violet-100); }
.text-violet-600 { color: var(--violet-600); }
.bg-rose-100 { background: var(--rose-100); }
.text-rose-600 { color: var(--rose-600); }
.bg-slate-100 { background: var(--slate-100); }
.text-slate-600 { color: var(--slate-600); }
.bg-indigo-50 { background: var(--indigo-50); }
.bg-amber-50 { background: var(--amber-50); }
.bg-emerald-50 { background: var(--emerald-50); }
.bg-rose-50 { background: var(--rose-50); }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 1rem;
    background: white;
    border-radius: 2rem;
    border: 2px dashed var(--slate-200);
}

.empty-state-text {
    color: var(--slate-500);
    font-weight: 500;
}

/* Table */
.table-wrapper {
    background: white;
    border-radius: 2rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
    overflow: hidden;
    max-height: 400px;
    overflow-y: auto;
}

.table-wrapper::-webkit-scrollbar {
    width: 6px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background-color: var(--slate-200);
    border-radius: 3px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: transparent;
}

.table-scroll {
    overflow-x: auto;
}

.text-right {
    text-align: right;
}

.font-bold {
    font-weight: 700;
    color: var(--slate-700);
}

.text-muted {
    color: var(--slate-500);
    font-weight: 500;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
}

.empty-row {
    padding: 2rem;
    text-align: center;
    color: var(--slate-500);
}
</style>
