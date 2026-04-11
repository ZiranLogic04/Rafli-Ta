<template>
    <AppLayout>
        <div class="dashboard-page">
            <div class="welcome-card">
                <div class="welcome-glow"></div>
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h2 class="welcome-title">
                            Halo,
                            <span class="gradient-text">
                                {{ user?.name || "Pengguna" }}
                            </span>
                        </h2>
                        <p class="welcome-subtitle">
                            Pilih kategori surat yang ingin diajukan, lalu lanjutkan
                            ke form pengajuan untuk memilih jenis detailnya.
                        </p>
                    </div>
                    <div class="welcome-meta">
                        <div class="meta-info">
                            <span class="semester-badge">
                                Semester Aktif
                            </span>
                            <p class="role-text">
                                Peran: {{ user?.role }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="letter-types-grid">
                <div
                    v-for="group in groupedLetterTypes"
                    :key="group.id"
                    class="card-shimmer letter-type-card"
                >
                    <div
                        class="letter-type-icon"
                        :class="getIconConfig(group.name).iconClass"
                    >
                        <span class="material-symbols-outlined icon-text-xl">
                            {{ getIconConfig(group.name).icon }}
                        </span>
                    </div>
                    <h3 class="letter-type-name">
                        {{ group.name }}
                    </h3>
                    <p class="letter-type-desc">
                        {{ groupDescription(group) }}
                    </p>
                    <p
                        v-if="group.children.length > 0"
                        class="letter-type-count"
                    >
                        {{ group.children.length }} jenis tersedia
                    </p>
                    <router-link
                        :to="{ path: '/letters/create', query: { type_id: group.id } }"
                        class="letter-type-btn"
                        :class="getIconConfig(group.name).btnClass"
                    >
                        <span class="material-symbols-outlined icon-text-sm">
                            add_circle
                        </span>
                        {{
                            group.children.length > 0
                                ? 'Buat Surat'
                                : getIconConfig(group.name).btnText
                        }}
                    </router-link>
                </div>
            </div>

            <div
                v-if="groupedLetterTypes.length === 0"
                class="empty-state"
            >
                <p class="empty-text">
                    Anda belum memiliki izin akses untuk jenis surat apapun.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, inject, onMounted, ref } from "vue";
import axios from "axios";
import AppLayout from "../../Layouts/AppLayout.vue";

const user = inject("user");
const letterTypes = ref([]);

const getIconConfig = (name) => {
    const n = name.toLowerCase();

    if (n.includes("tugas")) {
        return {
            icon: "travel_explore",
            iconClass: "icon-indigo",
            btnClass: "btn-indigo",
            btnText: "Buat Surat Baru",
        };
    }

    if (n.includes("rekomendasi")) {
        return {
            icon: "verified",
            iconClass: "icon-amber",
            btnClass: "btn-amber",
            btnText: "Ajukan Rekomendasi",
        };
    }

    if (n.includes("izin") || n.includes("cuti")) {
        return {
            icon: "event_busy",
            iconClass: "icon-emerald",
            btnClass: "btn-emerald",
            btnText: "Ajukan Perizinan",
        };
    }

    if (n.includes("keterangan")) {
        return {
            icon: "school",
            iconClass: "icon-blue",
            btnClass: "btn-blue",
            btnText: "Buat Surat",
        };
    }

    if (n.includes("undangan")) {
        return {
            icon: "mail",
            iconClass: "icon-violet",
            btnClass: "btn-violet",
            btnText: "Buat Surat",
        };
    }

    if (n.includes("keputusan") || n.includes("edaran") || n === "sk") {
        return {
            icon: "gavel",
            iconClass: "icon-rose",
            btnClass: "btn-rose",
            btnText: "Buat Surat",
        };
    }

    return {
        icon: "description",
        iconClass: "icon-slate",
        btnClass: "btn-slate",
        btnText: "Buat Surat",
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
        return `Kategori ${group.name} memiliki beberapa jenis surat turunan yang akan dipilih di langkah berikutnya.`;
    }

    return `Pengajuan ${group.name} dapat langsung dibuat dari kategori ini.`;
};

onMounted(async () => {
    const res = await axios.get("/api/dashboard");
    letterTypes.value = res.data.letterTypes || [];
});
</script>

<style scoped>
.dashboard-page {
    max-width: 72rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    padding: 0.5rem;
}

@media (min-width: 768px) {
    .dashboard-page {
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
    transition: box-shadow 0.3s;
}

.welcome-card:hover {
    box-shadow: 0 25px 50px -12px rgba(203, 213, 225, 0.5);
}

@media (min-width: 768px) {
    .welcome-card {
        padding: 2.5rem;
    }
}

.welcome-glow {
    position: absolute;
    top: 0;
    right: 0;
    width: 16rem;
    height: 16rem;
    background: rgba(79, 70, 229, 0.05);
    border-radius: 50%;
    margin-right: -5rem;
    margin-top: -5rem;
    filter: blur(40px);
    transition: all 0.3s;
}

.welcome-card:hover .welcome-glow {
    background: rgba(79, 70, 229, 0.1);
}

.welcome-content {
    position: relative;
    z-index: 10;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .welcome-content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.welcome-title {
    font-size: 1.875rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

@media (min-width: 768px) {
    .welcome-title {
        font-size: 2.25rem;
    }
}

.gradient-text {
    background: linear-gradient(to right, var(--primary), var(--accent-indigo));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.welcome-subtitle {
    color: var(--slate-500);
    font-size: 1.125rem;
    font-weight: 500;
    max-width: 36rem;
}

.welcome-meta {
    display: flex;
    flex-shrink: 0;
}

.meta-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
    text-align: right;
}

.semester-badge {
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    background: var(--amber-50);
    color: var(--amber-700);
    border: 1px solid var(--amber-100);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.role-text {
    font-size: 0.6875rem;
    font-weight: 500;
    color: var(--slate-400);
    font-style: italic;
    text-transform: capitalize;
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
    box-shadow: 0 10px 15px -3px rgba(203, 213, 225, 0.2);
    border: 1px solid var(--slate-100);
    padding: 2rem;
    transition: all 0.3s;
}

.letter-type-card:hover {
    transform: translateY(-0.5rem);
}

/* Icon Box */
.letter-type-icon {
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

.letter-type-card:hover .letter-type-icon {
    transform: scale(1.1);
}

/* Icon color variants */
.icon-indigo {
    background: var(--indigo-50);
    color: var(--indigo-600);
}

.icon-amber {
    background: var(--amber-50);
    color: var(--amber-600);
}

.icon-emerald {
    background: var(--emerald-50);
    color: var(--emerald-600);
}

.icon-blue {
    background: var(--blue-50);
    color: var(--blue-600);
}

.icon-violet {
    background: var(--violet-50);
    color: var(--violet-600);
}

.icon-rose {
    background: var(--rose-50);
    color: var(--rose-600);
}

.icon-slate {
    background: var(--slate-100);
    color: var(--slate-600);
}

.letter-type-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.75rem;
}

.letter-type-desc {
    color: var(--slate-500);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    line-height: 1.625;
    font-weight: 500;
}

.letter-type-count {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--slate-400);
    margin-bottom: 2rem;
}

/* Button variants */
.letter-type-btn {
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
}

.btn-indigo {
    background: white;
    border: 2px solid var(--indigo-100);
    color: var(--indigo-600);
}

.btn-indigo:hover {
    background: var(--indigo-600);
    color: white;
    border-color: var(--indigo-600);
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2);
}

.btn-amber {
    background: white;
    border: 2px solid var(--amber-100);
    color: var(--amber-600);
}

.btn-amber:hover {
    background: var(--amber-600);
    color: white;
    border-color: var(--amber-600);
    box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.2);
}

.btn-emerald {
    background: white;
    border: 2px solid var(--emerald-100);
    color: var(--emerald-600);
}

.btn-emerald:hover {
    background: var(--emerald-600);
    color: white;
    border-color: var(--emerald-600);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
}

.btn-blue {
    background: white;
    border: 2px solid var(--blue-100);
    color: var(--blue-600);
}

.btn-blue:hover {
    background: var(--blue-600);
    color: white;
    border-color: var(--blue-600);
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
}

.btn-violet {
    background: white;
    border: 2px solid var(--violet-100);
    color: var(--violet-600);
}

.btn-violet:hover {
    background: var(--violet-600);
    color: white;
    border-color: var(--violet-600);
    box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.2);
}

.btn-rose {
    background: white;
    border: 2px solid var(--rose-100);
    color: var(--rose-600);
}

.btn-rose:hover {
    background: var(--rose-600);
    color: white;
    border-color: var(--rose-600);
    box-shadow: 0 10px 15px -3px rgba(244, 63, 94, 0.2);
}

.btn-slate {
    background: white;
    border: 2px solid var(--slate-200);
    color: var(--slate-600);
}

.btn-slate:hover {
    background: var(--slate-800);
    color: white;
    border-color: var(--slate-800);
    box-shadow: 0 10px 15px -3px rgba(100, 116, 139, 0.2);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 5rem 0;
    background: white;
    border-radius: 2rem;
    border: 2px dashed var(--slate-200);
}

.empty-text {
    color: var(--slate-400);
    font-weight: 500;
}

/* Icon text sizes */
.icon-text-xl {
    font-size: 2.25rem;
}

.icon-text-sm {
    font-size: 20px;
}
</style>
