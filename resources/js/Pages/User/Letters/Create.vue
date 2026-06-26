<template>
    <AppLayout>
        <div class="create-page">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <router-link
                    to="/dashboard"
                    class="breadcrumb-back"
                >
                    <span class="material-symbols-outlined icon-size-md">arrow_back</span>
                </router-link>
                <div>
                    <div class="breadcrumb-path">
                        <span>Dashboard</span>
                        <span class="material-symbols-outlined icon-size-xs">chevron_right</span>
                        <span class="breadcrumb-current">Buat Surat</span>
                    </div>
                    <h1 class="breadcrumb-title">
                        Buat Surat Baru
                    </h1>
                </div>
            </div>

            <!-- Header Card -->
            <div
                v-if="letterType"
                class="card header-card"
            >
                <div class="header-glow"></div>
                <div class="header-content">
                    <div class="header-icon-box">
                        <span class="material-symbols-outlined icon-size-2xl">post_add</span>
                    </div>
                    <div class="header-left">
                        <div class="header-title-row">
                            <h2 class="header-title">
                                {{ letterType?.name }}
                            </h2>
                        </div>
                        <p class="header-desc">
                            Silakan isi form di bawah ini untuk mencatat surat baru.
                        </p>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <span class="material-symbols-outlined section-icon">list_alt</span>
                <h3 class="section-title">Formulir Pembuatan Surat</h3>
            </div>



            <!-- Step 1: Penandatangan -->
            <div class="card step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4 class="step-subtitle">
                        Penandatangan
                    </h4>
                    <p class="step-desc">
                        Masukkan nama pihak yang akan menandatangani surat ini.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Nama Penandatangan</label>
                        <input
                            type="text"
                            v-model="signatoryName"
                            list="user-list"
                            class="form-input"
                            placeholder="Ketik nama penandatangan..."
                        />
                        <datalist id="user-list">
                            <option v-for="t in targets" :key="t.id" :value="t.name"></option>
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- Step 2: Tujuan -->
            <div class="card step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4 class="step-subtitle">Tujuan</h4>
                    <p class="step-desc">Pilih jabatan untuk menentukan alur tujuan surat.</p>

                    <div class="form-group">
                        <label class="form-label">Jabatan Tujuan</label>
                        <select v-model="targetRoleSelection" class="form-select">
                            <option value="">-- Pilih Jabatan --</option>
                            <option value="direktur">Direktur</option>
                            <option value="wadir">Wadir</option>
                            <option value="kaprodi">Kaprodi</option>
                            <option value="staf">Staf TU</option>
                            <option value="dosen">Dosen</option>
                            <option value="lainnya">Lainnya (Ketik Manual)...</option>
                        </select>
                    </div>

                    <!-- Filter Wadir: 1, 2, 3 -->
                    <template v-if="targetRoleSelection === 'wadir'">
                        <div class="form-group">
                            <label class="form-label">Pilih Wadir (1, 2, atau 3)</label>
                            <select v-model="targetWadirLevelSelection" class="form-select">
                                <option value="">-- Pilih Level --</option>
                                <option value="1">Wadir 1</option>
                                <option value="2">Wadir 2</option>
                                <option value="3">Wadir 3</option>
                            </select>
                        </div>
                    </template>

                    <!-- Filter Dosen & Kaprodi: Pilih Prodi -->
                    <template v-if="targetRoleSelection === 'dosen' || targetRoleSelection === 'kaprodi'">
                        <div class="form-group">
                            <label class="form-label">Pilih Prodi {{ targetRoleSelection === 'dosen' ? 'Dosen' : 'Kaprodi' }}</label>
                            <select v-model="targetProdiSelection" class="form-select">
                                <option value="">-- Pilih Prodi --</option>
                                <option v-for="p in prodiList" :key="p" :value="p">{{ p }}</option>
                            </select>
                        </div>
                    </template>

                    <template v-if="showNameDropdown">
                        <div class="form-group">
                            <label class="form-label">Nama Tujuan</label>
                            
                            <!-- Direct Mode (Otomatis jika cuma 1, kecuali Staf) -->
                            <div v-if="isDirectMode" class="direct-name-display">
                                <span class="material-symbols-outlined">person</span>
                                <div>
                                    <div class="direct-name">{{ filteredTargets[0].name }}</div>
                                    <div class="direct-detail">{{ filteredTargets[0].jurusan || targetRoleSelection.toUpperCase() }}</div>
                                </div>
                            </div>

                            <!-- Jika jabatan lain atau lebih dari 1 orang, tampilkan dropdown -->
                            <select v-else v-model="targetSelection" class="form-select">
                                <option value="">-- Pilih Nama --</option>
                                <option v-for="t in filteredTargets" :key="t.id" :value="t.id">
                                    {{ t.name }} {{ t.jurusan ? ' - ' + t.jurusan : '' }}
                                </option>
                            </select>
                        </div>
                    </template>


                    <template v-if="targetRoleSelection === 'lainnya'">
                        <div class="form-group">
                            <label class="form-label">Nama Tujuan (Instansi/Pihak)</label>
                            <input
                                type="text"
                                v-model="targetName"
                                class="form-input"
                                placeholder="Contoh: LLDIKTI Wilayah IV"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jurusan/Prodi/Detail <span class="form-hint">(Opsional)</span></label>
                            <input
                                type="text"
                                v-model="targetJurusan"
                                class="form-input"
                                placeholder="Contoh: Administrasi Perkantoran"
                            />
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 3: Keterangan -->
            <div class="card step-card">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4 class="step-subtitle">
                        Keterangan
                    </h4>
                    <p class="step-desc">
                        Tambahkan catatan atau keterangan pengajuan surat ini (opsional).
                    </p>
                    <div class="form-group">
                        <label class="form-label">Keterangan / Catatan</label>
                        <textarea
                            v-model="notes"
                            class="form-input"
                            rows="3"
                            placeholder="Contoh: Pengajuan PKL Mahasiswa, Izin Kegiatan, dll..."
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 4: Submit -->
            <div class="card step-card submit-card">
                <div class="submit-glow"></div>
                <div class="step-number submit-step-number">4</div>
                <div class="step-content submit-content">
                    <h4 class="step-subtitle">
                        Ajukan Surat
                    </h4>
                    <button
                        @click="submitLetter"
                        :disabled="
                            !signatoryName ||
                            !targetRoleSelection ||
                            (targetRoleSelection !== 'lainnya' && !targetSelection) ||
                            (targetRoleSelection === 'lainnya' && !targetName) ||
                            submitting
                        "
                        class="submit-btn"
                    >
                        <span
                            v-if="submitting"
                            class="material-symbols-outlined animate-spin"
                        >progress_activity</span>
                        <span
                            v-else
                            class="material-symbols-outlined icon-size-md"
                        >send</span>
                        {{ submitting ? "Menyimpan..." : "Ajukan" }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject, computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import AppLayout from "../../../Layouts/AppLayout.vue";

const route = useRoute();
const router = useRouter();
const showFlash = inject("showFlash");

const letterType = ref(null);
const targets = ref([]);
const prodis = ref([]);

const signatoryName = ref("");
const targetRoleSelection = ref("");
const targetProdiSelection = ref(""); 
const targetWadirLevelSelection = ref(""); // Khusus filter Wadir
const targetSelection = ref("");
const targetName = ref("");
const targetJurusan = ref("");
const notes = ref("");

const needsProdiSelection = computed(() => {
    // Cek apakah di format nomor surat ada tag {prodi}/{Prodi} atau {extra}
    const format = (letterType.value?.code_format || "").toLowerCase();
    return format.includes('{prodi}') || format.includes('{extra}');
});

const prodiList = computed(() => prodis.value.map(p => p.name));

const filteredTargets = computed(() => {
    if (!targetRoleSelection.value) return [];
    const roleSearch = targetRoleSelection.value.toLowerCase();
    let result = targets.value.filter(t => t.role && t.role.toLowerCase() === roleSearch);
    
    // Filter Wadir: 1, 2, 3
    if (roleSearch === 'wadir' && targetWadirLevelSelection.value) {
        result = result.filter(t => t.wadir_level == targetWadirLevelSelection.value);
    }

    // Filter Dosen & Kaprodi: Prodi
    if ((roleSearch === 'dosen' || roleSearch === 'kaprodi') && targetProdiSelection.value) {
        result = result.filter(t => t.jurusan === targetProdiSelection.value);
    }
    
    return result;
});

const showNameDropdown = computed(() => {
    if (!targetRoleSelection.value || targetRoleSelection.value === 'lainnya') return false;
    
    // Jika Wadir, harus pilih level dulu
    if (targetRoleSelection.value === 'wadir') return !!targetWadirLevelSelection.value;
    
    // Jika Dosen atau Kaprodi, harus pilih prodi dulu
    if (targetRoleSelection.value === 'dosen' || targetRoleSelection.value === 'kaprodi') {
        return !!targetProdiSelection.value;
    }
    
    // Selain itu (Direktur, Staf), langsung muncul
    return true;
});

const isDirectMode = computed(() => {
    if (filteredTargets.value.length !== 1) return false;
    if (!targetRoleSelection.value) return false;
    const role = targetRoleSelection.value.toLowerCase();
    
    // Staf TU harus pilih manual meskipun cuma 1
    if (role === 'staf') return false;
    
    // Direktur, Wadir, Kaprodi, Dosen otomatis jika cuma 1
    return true;
});

// Auto-select menggunakan isDirectMode (pantau filteredTargets agar reaktif saat isi array berubah)
watch(filteredTargets, (newTargets) => {
    // 1. Reset selection jika yang dipilih saat ini tidak ada di daftar target yang baru
    if (targetSelection.value && !newTargets.some(t => t.id === targetSelection.value)) {
        targetSelection.value = "";
    }

    // 2. Auto-select jika masuk mode Direct
    if (isDirectMode.value && newTargets.length === 1) {
        targetSelection.value = newTargets[0].id;
    }
}, { immediate: true });

watch(targetRoleSelection, (newRole) => {
    // Reset filters saat jabatan berubah
    targetProdiSelection.value = "";
    targetWadirLevelSelection.value = "";
    targetName.value = "";
    targetJurusan.value = "";
});

// Watcher auto-select targetSelection dihapus karena sudah ada di atas

// Auto-select if only 1 person in role
watch(filteredTargets, (newTargets) => {
    if (newTargets.length === 1) {
        targetSelection.value = newTargets[0].id;
    } else {
        targetSelection.value = "";
    }
});

const submitting = ref(false);

const activeLetterType = computed(() => letterType.value);

const submitLetter = async () => {
    if (!letterType.value) return;

    if (!signatoryName.value.trim()) {
        showFlash("Nama penandatangan wajib diisi.", "error");
        return;
    }

    if (targetRoleSelection.value !== 'lainnya' && !targetSelection.value) {
        showFlash("Pilih tujuan surat terlebih dahulu.", "error");
        return;
    }

    if (targetRoleSelection.value === "lainnya" && !targetName.value.trim()) {
        showFlash("Nama tujuan wajib diisi jika memilih opsi Lainnya.", "error");
        return;
    }

    // Validasi input prodi tambahan dihapus

    submitting.value = true;
    try {
        const payload = {
            letter_type_id: activeLetterType.value?.id || letterType.value.id,
            signatory_name: signatoryName.value,
            notes: notes.value,
        };

        // Otomatis tentukan target_jurusan untuk nomor surat
        if (targetRoleSelection.value === 'lainnya') {
            payload.target_name = targetName.value;
            payload.target_jurusan = targetJurusan.value;
        } else {
            payload.target_user_id = targetSelection.value;
            // Ambil jurusan dari user yang dipilih untuk nomor surat
            const selectedUser = targets.value.find(t => t.id === targetSelection.value);
            if (selectedUser && selectedUser.jurusan) {
                payload.target_jurusan = selectedUser.jurusan;
            }
        }

        await axios.post("/api/letters", payload);
        showFlash("Surat berhasil dicatat! Nomor surat telah di-generate.");
        router.push("/letters");
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal menyimpan surat.", "error");
    } finally {
        submitting.value = false;
    }
};

onMounted(async () => {
    const typeId = route.query.type_id;
    if (typeId) {
        const res = await axios.get("/api/letters/create-data", {
            params: { type_id: typeId },
        });
        letterType.value = res.data.letterType;
        targets.value = res.data.targets || [];
        prodis.value = res.data.prodis || [];
    }
});
</script>

<style scoped>
.create-page {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.breadcrumb-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: var(--slate-100);
    color: var(--slate-500);
    text-decoration: none;
    transition: background 0.2s;
}

.breadcrumb-back:hover {
    background: var(--slate-200);
}

.breadcrumb-path {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    margin-bottom: 0.25rem;
}

.breadcrumb-current {
    color: var(--primary);
}

.breadcrumb-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
}

/* Header Card */
.header-card {
    position: relative;
    background: white;
    padding: 1.25rem 1.75rem;
    border-radius: 1.25rem;
    overflow: hidden;
    border: 1px solid var(--slate-100);
    box-shadow: 0 10px 15px -3px rgba(203, 213, 225, 0.2);
}

.header-glow {
    position: absolute;
    top: 0;
    right: 0;
    width: 20rem;
    height: 20rem;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(124, 58, 237, 0.08) 100%);
    border-radius: 50%;
    margin-right: -8rem;
    margin-top: -8rem;
    filter: blur(60px);
}

.header-content {
    position: relative;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.header-icon-box {
    width: 3.5rem;
    height: 3.5rem;
    background: var(--primary-light);
    color: var(--primary);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1);
}

.header-left {
    display: flex;
    flex-direction: column;
}

.header-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.01em;
}

.header-desc {
    color: var(--slate-500);
    font-size: 0.875rem;
    font-weight: 500;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-left: 0.5rem;
}

.section-icon {
    color: var(--primary);
    font-variation-settings: 'FILL' 1;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.01em;
}

/* Steps Layout */
.step-card {
    display: flex;
    gap: 2rem;
    padding: 2.5rem;
    border-radius: 2rem;
}

/* Step Cards */
.step-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    transition: border-color 0.2s;
    padding: 1.5rem;
}

@media (min-width: 768px) {
    .step-card {
        flex-direction: row;
        padding: 2rem;
    }
}

.step-card:hover {
    border-color: rgba(79, 70, 229, 0.3);
}

.step-number {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.step-content {
    flex: 1;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--slate-500);
    margin-bottom: 0.5rem;
}

.form-hint {
    font-size: 0.6875rem;
    color: var(--slate-400);
    font-weight: 400;
}

.form-input,
.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid var(--slate-200);
    background-color: var(--slate-50);
    color: var(--slate-900);
    font-size: 0.875rem;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--primary);
    background-color: white;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

textarea.form-input {
    resize: vertical;
    min-height: 80px;
}

.step-subtitle {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--slate-900);
    margin-bottom: 0.5rem;
}

.step-desc {
    color: var(--slate-500);
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
}

/* Submit Card */
.submit-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(to right, #ffffff, #F8FAFC);
    border: 1px solid var(--indigo-100);
    align-items: center;
}

.submit-glow {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4rem;
    background: linear-gradient(to top, rgba(79, 70, 229, 0.05), transparent);
    pointer-events: none;
}

.submit-step-number {
    background: linear-gradient(135deg, #4F46E5, #6366F1);
}

.ia-special-prodi {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px dashed var(--slate-100);
}

.highlight-select {
    border-color: var(--primary) !important;
    background-color: var(--indigo-50) !important;
    font-weight: 700;
}

.direct-name-display {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, var(--indigo-50), white);
    border: 2px solid var(--primary);
    border-radius: 1rem;
    color: var(--slate-900);
}

.direct-name-display .material-symbols-outlined {
    font-size: 2rem;
    color: var(--primary);
}

.direct-name {
    font-size: 1.125rem;
    font-weight: 800;
}

.direct-detail {
    font-size: 0.875rem;
    color: var(--slate-500);
}

.submit-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

@media (min-width: 768px) {
    .submit-content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 3.5rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white !important;
    border: none;
    border-radius: 1.25rem;
    font-size: 1.25rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.4);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: 1rem;
}

@media (min-width: 768px) {
    .submit-btn {
        margin-top: 0;
    }
}

.submit-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, var(--primary-hover), var(--primary-dark)) !important;
    transform: translateY(-3px);
    box-shadow: 0 20px 30px -5px rgba(79, 70, 229, 0.5);
}

.submit-btn:active:not(:disabled) {
    transform: translateY(0);
}

.submit-btn:disabled {
    background: var(--slate-300);
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.7;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
