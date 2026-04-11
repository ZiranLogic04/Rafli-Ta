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
                    <h2 class="tmpl-hero-title">Template Surat Resmi</h2>
                    <p class="tmpl-hero-desc">Pilih dan unduh template surat yang Anda butuhkan. Tidak perlu login, langsung pakai.</p>
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

                <!-- Template Groups -->
                <div class="grid-2 tmpl-grid">
                    <div
                        v-for="group in filteredGroups"
                        :key="group.id"
                        class="card-lg tmpl-group-card"
                    >
                        <div class="tmpl-group-header">
                            <div
                                class="icon-box-lg tmpl-group-icon"
                                :class="getGroupStyle(group.name)"
                            >
                                <span class="material-symbols-outlined tmpl-group-icon-text">
                                    {{ getGroupIcon(group.name) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="tmpl-group-name">{{ group.name }}</h3>
                                <p class="tmpl-group-count">
                                    {{ group.children.length > 0 ? `${group.children.length} jenis surat` : 'Klik untuk unduh template' }}
                                </p>
                            </div>
                        </div>

                        <div class="tmpl-types-list">
                            <div
                                v-for="type in displayTypes(group)"
                                :key="type.id"
                                class="tmpl-type-item"
                                :class="getGroupHoverBorder(group.name)"
                            >
                                <div class="tmpl-type-info">
                                    <span class="tmpl-type-name">{{ type.name }}</span>
                                    <span class="tmpl-type-code">Kode: {{ type.code }}</span>
                                </div>
                                <a
                                    v-if="type.has_template"
                                    :href="`/api/public/templates/${type.id}/download`"
                                    class="tmpl-download-btn"
                                    :class="getGroupBtnStyle(group.name)"
                                >
                                    <span class="material-symbols-outlined">download</span>
                                    Unduh
                                </a>
                                <span
                                    v-else
                                    class="tmpl-disabled-btn"
                                >
                                    <span class="material-symbols-outlined">block</span>
                                    Belum Tersedia
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredGroups.length === 0"
                    class="tmpl-empty-state"
                >
                    <div class="tmpl-empty-content">
                        <div class="tmpl-empty-icon">
                            <span class="material-symbols-outlined">search_off</span>
                        </div>
                        <h3 class="tmpl-empty-title">Tidak ada template ditemukan</h3>
                        <p class="tmpl-empty-desc">Coba kata kunci pencarian lainnya.</p>
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

const groupedTypes = computed(() => {
    const groupMap = new Map();

    for (const type of types.value) {
        if (!type.parent_id) {
            groupMap.set(type.id, {
                id: type.id,
                name: type.name,
                directType: type,
                children: [],
            });
        }
    }

    for (const type of types.value) {
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
        group.directType = null;
        group.children.push(type);
    }

    return Array.from(groupMap.values())
        .filter((group) => group.directType || group.children.length > 0)
        .sort((a, b) => a.name.localeCompare(b.name));
});

const filteredGroups = computed(() => {
    if (!search.value) return groupedTypes.value;

    const q = search.value.toLowerCase();
    return groupedTypes.value.filter((group) => {
        if (group.name.toLowerCase().includes(q)) return true;
        return group.children.some((c) => c.name.toLowerCase().includes(q));
    });
});

const displayTypes = (group) => {
    if (group.children.length > 0) {
        return [...group.children].sort((a, b) => a.name.localeCompare(b.name));
    }
    return group.directType ? [group.directType] : [];
};

const getGroupIcon = (name) => {
    const n = name.toLowerCase();
    if (n.includes('keputusan') || n === 'sk') return 'gavel';
    if (n.includes('tugas')) return 'travel_explore';
    if (n.includes('undangan')) return 'mail';
    if (n.includes('keterangan')) return 'school';
    if (n.includes('edaran')) return 'campaign';
    if (n.includes('memo')) return 'note_alt';
    return 'description';
};

const getGroupStyle = (name) => {
    const n = name.toLowerCase();
    if (n.includes('keputusan') || n === 'sk') return 'style-rose';
    if (n.includes('tugas')) return 'style-blue';
    if (n.includes('undangan')) return 'style-violet';
    if (n.includes('keterangan')) return 'style-indigo';
    if (n.includes('edaran')) return 'style-emerald';
    if (n.includes('memo')) return 'style-amber';
    return 'style-slate';
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

.tmpl-search-group:focus-within .tmpl-search-icon {
    color: var(--primary);
}

/* ===== GRID ===== */
.tmpl-grid {
    gap: 2rem;
}

/* ===== GROUP CARD ===== */
.tmpl-group-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.tmpl-group-header {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.tmpl-group-icon {
    transition: all 0.2s;
}

.tmpl-group-icon-text {
    font-size: 2rem;
    font-weight: 300;
}

.tmpl-group-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
}

.tmpl-group-count {
    color: var(--slate-500);
    font-size: 0.875rem;
    font-weight: 500;
}

/* ===== TYPES LIST ===== */
.tmpl-types-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tmpl-type-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-radius: 1.5rem;
    background: rgba(248, 250, 252, 0.5);
    border: 1px solid transparent;
    transition: all 0.2s;
}

.tmpl-type-item:hover {
    background: white;
}

.tmpl-type-info {
    display: flex;
    flex-direction: column;
}

.tmpl-type-name {
    font-weight: 700;
    color: var(--slate-800);
}

.tmpl-type-code {
    font-size: 0.75rem;
    color: var(--slate-500);
}

/* ===== DOWNLOAD BUTTON ===== */
.tmpl-download-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background: white;
    border: 1px solid var(--slate-200);
    color: var(--slate-700);
    font-size: 0.875rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
}

.tmpl-download-btn .material-symbols-outlined {
    font-size: 1.125rem;
}

/* ===== DISABLED BUTTON ===== */
.tmpl-disabled-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background: var(--slate-100);
    border: 1px solid var(--slate-200);
    color: var(--slate-400);
    font-size: 0.875rem;
    font-weight: 700;
    cursor: not-allowed;
}

.tmpl-disabled-btn .material-symbols-outlined {
    font-size: 1.125rem;
}

/* ===== COLOR VARIANTS - ICON ===== */
.style-rose { background: var(--rose-50); color: var(--rose-600); border: 1px solid var(--rose-100); }
.style-blue { background: var(--blue-50); color: var(--blue-600); border: 1px solid var(--blue-100); }
.style-violet { background: var(--violet-50); color: var(--violet-600); border: 1px solid var(--violet-100); }
.style-indigo { background: var(--indigo-50); color: var(--indigo-600); border: 1px solid var(--indigo-100); }
.style-emerald { background: var(--emerald-50); color: var(--emerald-600); border: 1px solid var(--emerald-100); }
.style-amber { background: var(--amber-50); color: var(--amber-600); border: 1px solid var(--amber-100); }
.style-slate { background: var(--slate-50); color: var(--slate-600); border: 1px solid var(--slate-100); }

/* ===== COLOR VARIANTS - HOVER BORDER ===== */
.hover-rose:hover { border-color: var(--rose-100); }
.hover-blue:hover { border-color: var(--blue-100); }
.hover-violet:hover { border-color: var(--violet-100); }
.hover-indigo:hover { border-color: var(--indigo-100); }
.hover-emerald:hover { border-color: var(--emerald-100); }
.hover-amber:hover { border-color: var(--amber-100); }
.hover-slate:hover { border-color: var(--slate-100); }

/* ===== COLOR VARIANTS - BUTTON HOVER ===== */
.btn-rose:hover { background: var(--rose-600); color: white; border-color: var(--rose-600); }
.btn-blue:hover { background: var(--blue-600); color: white; border-color: var(--blue-600); }
.btn-violet:hover { background: var(--violet-600); color: white; border-color: var(--violet-600); }
.btn-indigo:hover { background: var(--indigo-600); color: white; border-color: var(--indigo-600); }
.btn-emerald:hover { background: var(--emerald-600); color: white; border-color: var(--emerald-600); }
.btn-amber:hover { background: var(--amber-600); color: white; border-color: var(--amber-600); }
.btn-slate:hover { background: var(--slate-800); color: white; border-color: var(--slate-800); }

/* ===== EMPTY STATE ===== */
.tmpl-empty-state {
    background: white;
    border-radius: 1.5rem;
    border: 2px dashed var(--slate-200);
    padding: 1.5rem 4rem;
    text-align: center;
}

.tmpl-empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.tmpl-empty-icon {
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

.tmpl-empty-icon .material-symbols-outlined {
    font-size: 2.5rem;
}

.tmpl-empty-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-600);
    margin-bottom: 0.25rem;
}

.tmpl-empty-desc {
    font-size: 0.75rem;
    color: var(--slate-400);
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

