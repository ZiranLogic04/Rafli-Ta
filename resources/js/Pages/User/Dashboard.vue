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
                    v-for="type in letterTypes"
                    :key="type.id"
                    class="card-shimmer letter-type-card"
                >
                    <div
                        class="letter-type-icon"
                        :class="getIconConfig(type.name).iconClass"
                    >
                        <span class="material-symbols-outlined icon-text-xl">
                            {{ getIconConfig(type.name).icon }}
                        </span>
                    </div>
                    <h3 class="letter-type-name">
                        {{ type.name }}
                    </h3>
                    <p class="letter-type-desc">
                        {{ type.description || `Pengajuan ${type.name} dapat langsung dibuat dari sini.` }}
                    </p>
                    <router-link
                        :to="{ path: '/letters/create', query: { type_id: type.id } }"
                        class="letter-type-btn"
                        :class="getIconConfig(type.name).btnClass"
                        :style="{ 
                            backgroundColor: getIconConfig(type.name).color,
                            color: 'white',
                            border: 'none'
                        }"
                    >
                        <span class="material-symbols-outlined icon-text-sm">
                            add_circle
                        </span>
                        {{ getIconConfig(type.name).btnText }}
                    </router-link>
                </div>
            </div>

            <div
                v-if="letterTypes.length === 0"
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
    if (!name) return { icon: "description", iconClass: "icon-indigo", btnClass: "btn-indigo", btnText: "Buat Surat", color: "#4F46E5" };
    const n = name.toLowerCase();

    if (n.includes("tugas") || n.includes("dinas")) {
        return {
            icon: "travel_explore",
            iconClass: "icon-indigo",
            btnClass: "btn-indigo",
            btnText: "Buat Surat Baru",
            color: "#4F46E5"
        };
    }
    if (n.includes("rekomendasi") || n.includes("prestasi")) {
        return {
            icon: "verified",
            iconClass: "icon-amber",
            btnClass: "btn-amber",
            btnText: "Ajukan Rekomendasi",
            color: "#D97706"
        };
    }
    if (n.includes("izin") || n.includes("cuti") || n.includes("sakit")) {
        return {
            icon: "event_busy",
            iconClass: "icon-emerald",
            btnClass: "btn-emerald",
            btnText: "Ajukan Perizinan",
            color: "#059669"
        };
    }
    if (n.includes("keterangan") || n.includes("aktif") || n.includes("mahasiswa")) {
        return {
            icon: "school",
            iconClass: "icon-blue",
            btnClass: "btn-blue",
            btnText: "Buat Surat",
            color: "#2563EB"
        };
    }
    if (n.includes("undangan") || n.includes("pertemuan") || n.includes("rapat")) {
        return {
            icon: "mail",
            iconClass: "icon-indigo",
            btnClass: "btn-indigo",
            btnText: "Buat Surat",
            color: "#4F46E5"
        };
    }
    if (n.includes("keputusan") || n.includes("edaran") || n === "sk" || n.includes("penetapan")) {
        return {
            icon: "gavel",
            iconClass: "icon-rose",
            btnClass: "btn-rose",
            btnText: "Buat Surat",
            color: "#E11D48"
        };
    }
    if (n.includes("perjanjian") || n.includes("kerjasama") || n.includes("ia") || n.includes("mou")) {
        return {
            icon: "handshake",
            iconClass: "icon-cyan",
            btnClass: "btn-cyan",
            btnText: "Buat Perjanjian",
            color: "#0891B2"
        };
    }

    return {
        icon: "description",
        iconClass: "icon-indigo",
        btnClass: "btn-indigo",
        btnText: "Buat Surat",
        color: "#4F46E5"
    };
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

.icon-cyan {
    background: #ecfeff;
    color: #0891b2;
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
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

/* Hover Logic: Efek tambahan saat tombol itu sendiri di-hover */
.letter-type-btn:hover {
    transform: translateY(-5px) !important;
    filter: brightness(1.1);
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.3) !important;
}

.btn-indigo { background: var(--indigo-600); border: none; color: white !important; }
.btn-amber { background: var(--amber-600); border: none; color: white !important; }
.btn-emerald { background: var(--emerald-600); border: none; color: white !important; }
.btn-blue { background: var(--blue-600); border: none; color: white !important; }
.btn-violet { background: var(--violet-600); border: none; color: white !important; }
.btn-rose { background: var(--rose-600); border: none; color: white !important; }
.btn-cyan { background: #0891b2; border: none; color: white !important; }
.btn-slate { background: var(--slate-600); border: none; color: white !important; }

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
