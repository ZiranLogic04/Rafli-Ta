<template>
    <div class="templates-page">
        <!-- Header -->
        <header class="tmpl-header">
            <div class="tmpl-header-brand">
                <div class="tmpl-logo">
                    <span class="material-symbols-outlined font-light">school</span>
                </div>
                <div class="tmpl-brand-text">
                    <h1 class="tmpl-title">SISurat</h1>
                    <p class="tmpl-subtitle">Politeknik Pajajaran</p>
                </div>
            </div>
            <div class="tmpl-header-actions">
                <router-link to="/login" class="btn-primary tmpl-login-btn">
                    Masuk Portal
                </router-link>
            </div>
        </header>

        <!-- Main -->
        <main class="mesh-gradient tmpl-main">
            <div class="tmpl-container">
                <!-- Hero -->
                <div class="tmpl-hero">
                    <h2 class="tmpl-hero-title">Surat Resmi</h2>
                    <p class="tmpl-hero-desc">Pilih dan unduh surat yang Anda butuhkan. Tidak perlu login, langsung pakai.</p>
                    <div class="tmpl-search-wrap">
                        <div class="tmpl-search-group">
                            <input
                                v-model="search"
                                class="tmpl-search"
                                placeholder="Cari template... (misal: SK, Tugas, Undangan)"
                                type="text"
                            />
                            <span class="material-symbols-outlined tmpl-search-icon">search</span>
                        </div>
                    </div>
                </div>

                <!-- Template List -->
                <div class="grid-2 tmpl-grid">
                    <div
                        v-for="type in filteredTypes"
                        :key="type.id"
                        class="card-lg tmpl-group-card"
                    >
                        <div class="tmpl-card-content">
                            <div class="tmpl-card-left">
                                <div
                                    class="icon-box-lg tmpl-group-icon"
                                    :class="[
                                        getIconConfig(type.name, type.id).bg,
                                        getIconConfig(type.name, type.id).text,
                                    ]"
                                >
                                    <span class="material-symbols-outlined tmpl-group-icon-text">
                                        {{ getIconConfig(type.name, type.id).icon }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="tmpl-group-name">{{ type.name }}</h3>
                                    <p class="tmpl-group-code">Kode: {{ type.code }}</p>
                                </div>
                            </div>

                            <div class="tmpl-card-right">
                                <a
                                    v-if="type.has_template"
                                    :href="`/api/public/templates/${type.id}/download`"
                                    class="btn-primary tmpl-download-btn"
                                >
                                    <span class="material-symbols-outlined">download</span>
                                    Unduh
                                </a>
                                <span v-else class="tmpl-disabled-btn">
                                    <span class="material-symbols-outlined">block</span>
                                    Belum Tersedia
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredTypes.length === 0"
                    class="tmpl-empty-state"
                >
                    <div class="tmpl-empty-content">
                        <div class="tmpl-empty-icon">
                            <span class="material-symbols-outlined">search_off</span>
                        </div>
                        <h3 class="tmpl-empty-title">Tidak ada data ditemukan</h3>
                        <p class="tmpl-empty-desc">Coba gunakan kata kunci lain.</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredTypes.length === 0"
                    class="tmpl-empty-state"
                >
                    <div class="tmpl-empty-content">
                        <div class="tmpl-empty-icon">
                            <span class="material-symbols-outlined">search_off</span>
                        </div>
                        <h3 class="tmpl-empty-title">Template tidak ditemukan</h3>
                        <p class="tmpl-empty-desc">Coba gunakan kata kunci pencarian yang berbeda.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="tmpl-footer">
            <div class="tmpl-footer-inner">
                <div class="tmpl-footer-brand">
                    <div class="tmpl-footer-logo">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <p class="tmpl-footer-copy">&copy; 2026 Politeknik Pajajaran</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import axios from "axios";

const types = ref([]);
const search = ref("");

const filteredTypes = computed(() => {
    if (!search.value) return types.value;
    const q = search.value.toLowerCase();
    return types.value.filter(t => 
        t.name.toLowerCase().includes(q) || 
        t.code.toLowerCase().includes(q)
    );
});

const displayTypes = (group) => {
    if (group.children.length > 0) {
        return [...group.children].sort((a, b) => a.name.localeCompare(b.name));
    }
    return group.directType ? [group.directType] : [];
};

const getIconConfig = (name, id = 0) => {
    const n = name?.toLowerCase() || "";

    if (n.includes("tugas")) {
        return {
            icon: "travel_explore",
            bg: "bg-indigo-100",
            text: "text-indigo-600",
        };
    }

    if (n.includes("rekomendasi")) {
        return {
            icon: "verified",
            bg: "bg-amber-100",
            text: "text-amber-600",
        };
    }

    if (n.includes("tugas") || n.includes("st")) {
        return {
            icon: "assignment",
            bg: "bg-emerald-100",
            text: "text-emerald-600",
        };
    }

    if (n.includes("pengantar")) {
        return {
            icon: "forward_to_inbox",
            bg: "bg-amber-100",
            text: "text-amber-600",
        };
    }

    if (n.includes("pernyataan")) {
        return {
            icon: "verified_user",
            bg: "bg-blue-100",
            text: "text-blue-600",
        };
    }

    if (n.includes("sk") || n.includes("keputusan")) {
        return {
            icon: "gavel",
            bg: "bg-rose-100",
            text: "text-rose-600",
        };
    }

    if (n.includes("mou") || n.includes("kerjasama")) {
        return {
            icon: "handshake",
            bg: "bg-violet-100",
            text: "text-violet-600",
        };
    }

    if (n.includes("ia") || n.includes("implementation")) {
        return {
            icon: "architecture",
            bg: "bg-indigo-100",
            text: "text-indigo-600",
        };
    }

    if (n.includes("keluar") || n.includes("su")) {
        return {
            icon: "outgoing_mail",
            bg: "bg-cyan-100",
            text: "text-cyan-600",
        };
    }

    const fallbacks = [
        { icon: "description", bg: "bg-slate-100", text: "text-slate-600" },
        { icon: "article", bg: "bg-orange-100", text: "text-orange-600" },
        { icon: "folder_open", bg: "bg-teal-100", text: "text-teal-600" },
        { icon: "edit_note", bg: "bg-pink-100", text: "text-pink-600" },
        { icon: "drafts", bg: "bg-lime-100", text: "text-lime-600" },
    ];

    return fallbacks[id % fallbacks.length];
};

const getGroupHoverBorder = (name) => {
    const n = name.toLowerCase();
    if (n.includes('keputusan') || n === 'sk') return 'hover-rose';
    if (n.includes('tugas')) return 'hover-blue';
    if (n.includes('undangan')) return 'hover-violet';
    if (n.includes('keterangan')) return 'hover-indigo';
    if (n.includes('edaran')) return 'hover-emerald';
    if (n.includes('memo')) return 'hover-amber';
    return 'hover-slate';
};

const getGroupBtnStyle = (name) => {
    const n = name.toLowerCase();
    if (n.includes('keputusan') || n === 'sk') return 'btn-rose';
    if (n.includes('tugas')) return 'btn-blue';
    if (n.includes('undangan')) return 'btn-violet';
    if (n.includes('keterangan')) return 'btn-indigo';
    if (n.includes('edaran')) return 'btn-emerald';
    if (n.includes('memo')) return 'btn-amber';
    return 'btn-slate';
};

onMounted(async () => {
    const res = await axios.get("/api/public/templates");
    types.value = res.data || [];
});
</script>

<style scoped>
.templates-page {
    min-height: 100vh;
    background-color: #FDFCFB;
}

/* ===== HEADER ===== */
.tmpl-header {
    height: 5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--slate-100);
    position: sticky;
    top: 0;
    z-index: 50;
}

@media (min-width: 768px) {
    .tmpl-header {
        padding: 0 3rem;
    }
}

.tmpl-header-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.tmpl-logo {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, var(--primary), var(--accent-indigo));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.tmpl-brand-text {
    display: flex;
    flex-direction: column;
}

.tmpl-title {
    font-size: 1.125rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    color: var(--slate-900);
    line-height: 1;
}

.tmpl-subtitle {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    font-weight: 700;
    color: var(--slate-400);
    margin-top: 0.25rem;
}

.tmpl-header-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.tmpl-login-btn {
    padding: 0.625rem 1.5rem;
    font-size: 0.875rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* ===== MAIN ===== */
.tmpl-main {
    padding: 1.5rem;
}

@media (min-width: 768px) {
    .tmpl-main {
        padding: 3rem;
    }
}

.tmpl-container {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

/* ===== HERO ===== */
.tmpl-hero {
    text-align: center;
    max-width: 48rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tmpl-hero-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

@media (min-width: 768px) {
    .tmpl-hero-title {
        font-size: 3rem;
    }
}

.tmpl-hero-desc {
    color: var(--slate-500);
    font-weight: 500;
    font-size: 1.125rem;
}

@media (min-width: 768px) {
    .tmpl-hero-desc {
        font-size: 1.25rem;
    }
}

.tmpl-search-wrap {
    padding-top: 1.5rem;
}

.tmpl-search-group {
    position: relative;
    max-width: 36rem;
    margin: 0 auto;
}

.tmpl-search {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border-radius: 1.5rem;
    border: 1px solid var(--slate-200);
    background: white;
    font-size: 1rem;
    font-weight: 500;
    color: var(--slate-900);
    transition: all 0.2s;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.4);
}

.tmpl-search:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1), 0 20px 25px -5px rgba(203, 213, 225, 0.4);
}

.tmpl-search::placeholder {
    color: var(--slate-400);
}

.tmpl-search-icon {
    position: absolute;
    left: 1rem;
    top: 1rem;
    color: var(--slate-400);
    font-size: 1.5rem;
    transition: color 0.2s;
}

/* ===== GRID ===== */
.tmpl-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .tmpl-grid {
        grid-template-columns: 1fr;
    }
}

/* ===== GROUP CARD ===== */
.tmpl-group-card {
    padding: 1.5rem !important;
}

.tmpl-card-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}

.tmpl-card-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.tmpl-group-icon {
    width: 3.5rem !important;
    height: 3.5rem !important;
    flex-shrink: 0;
}

.tmpl-group-icon-text {
    font-size: 1.75rem !important;
}

.tmpl-group-name {
    font-size: 1.125rem;
    font-weight: 800;
    color: var(--slate-900);
    line-height: 1.2;
}

.tmpl-group-code {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0.25rem;
}

.tmpl-card-right {
    flex-shrink: 0;
}

.tmpl-download-btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.tmpl-disabled-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    color: var(--slate-300);
    font-size: 0.875rem;
    font-weight: 700;
}

/* ===== COLOR UTILS ===== */
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
.bg-cyan-100 { background: var(--cyan-100); }
.text-cyan-600 { color: var(--cyan-600); }
.bg-orange-100 { background: var(--orange-100); }
.text-orange-600 { color: var(--orange-600); }
.bg-teal-100 { background: var(--teal-100); }
.text-teal-600 { color: var(--teal-600); }
.bg-pink-100 { background: var(--pink-100); }
.text-pink-600 { color: var(--pink-600); }
.bg-lime-100 { background: var(--lime-100); }
.text-lime-600 { color: var(--lime-600); }

@media (max-width: 640px) {
    .tmpl-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.5rem;
        gap: 1.25rem;
    }
    
    .tmpl-card-right {
        width: 100%;
    }
    
    .tmpl-download-btn {
        width: 100%;
        justify-content: center;
    }
}

/* ===== FOOTER ===== */
.tmpl-footer {
    padding: 3rem 1.5rem;
    border-top: 1px solid var(--slate-100);
    background: white;
}

.tmpl-footer-inner {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

@media (min-width: 768px) {
    .tmpl-footer-inner {
        flex-direction: row;
    }
}

.tmpl-footer-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.tmpl-footer-logo {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    background: rgba(79, 70, 229, 0.1);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.tmpl-footer-logo .material-symbols-outlined {
    font-size: 1.25rem;
}

.tmpl-footer-copy {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-600);
}
</style>

