<template>
    <AppLayout>
        <div class="create-page">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <router-link
                    to="/dashboard"
                    class="breadcrumb-back"
                >
                    <span class="material-symbols-outlined icon-size-md"
                        >arrow_back</span
                    >
                </router-link>
                <div>
                    <div
                        class="breadcrumb-path"
                    >
                        <span>Dashboard</span>
                        <span class="material-symbols-outlined icon-size-xs"
                            >chevron_right</span
                        >
                        <span class="breadcrumb-current">Buat Surat</span>
                    </div>
                    <h1 class="breadcrumb-title">
                        Pengajuan Dokumen
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
                    <div class="header-left">
                        <div class="header-title-row">
                            <h2 class="header-title">
                                {{ activeLetterType?.name || letterType.name }}
                            </h2>
                            <span
                                v-if="activeParentLabel"
                                class="parent-label"
                            >
                                {{ activeParentLabel }}
                            </span>
                            <span class="draft-label">Draf Pengajuan</span>
                        </div>
                        <p class="header-desc">
                            Silakan lengkapi isian surat sesuai format
                            {{ activeLetterType?.name || letterType.name }}, lalu unggah untuk diajukan.
                        </p>
                    </div>
                    <div class="header-icon-box">
                        <span class="material-symbols-outlined icon-size-2xl"
                            >description</span
                        >
                    </div>
                </div>
            </div>

            <div class="section-header">
                <span class="material-symbols-outlined section-icon">analytics</span>
                <h3 class="section-title">Alur Pengajuan</h3>
            </div>

            <!-- Step 1: Download -->
            <div class="card step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="form-group">
                        <label class="form-label">
                            Jenis Surat
                        </label>
                        <select
                            v-if="childTypes.length > 0"
                            v-model="selectedChildTypeId"
                            class="form-select"
                        >
                            <option :value="null">
                                -- Pilih jenis surat {{ letterType.name }} --
                            </option>
                            <option
                                v-for="child in childTypes"
                                :key="child.id"
                                :value="child.id"
                            >
                                {{ child.name }}
                            </option>
                        </select>
                        <div
                            v-else
                            class="type-display"
                        >
                            {{ letterType.name }}
                        </div>
                        <p class="form-hint">
                            {{ childTypes.length > 0 ? 'Pilih jenis surat yang paling sesuai sebelum mengunduh template dan mengirim pengajuan.' : 'Jenis surat ini tidak memiliki sub-jenis.' }}
                        </p>
                    </div>

                    <h4 class="step-subtitle">
                        Unduh Template
                    </h4>
                    <p class="step-desc">
                        Silakan unduh template dokumen (.docx) yang disediakan.
                    </p>
                    <a
                        v-if="templateTypeId"
                        :href="`/api/letters/template/${templateTypeId}`"
                        class="template-link"
                    >
                        <span class="material-symbols-outlined icon-size-md">download</span>
                        Template_{{
                            (activeLetterType?.name || letterType.name)?.replace(
                                / /g,
                                "_",
                            )
                        }}.docx
                    </a>
                    <p
                        v-else-if="childTypes.length > 0"
                        class="form-hint"
                    >
                        Pilih jenis surat terlebih dahulu untuk melihat template
                        yang sesuai.
                    </p>
                </div>
            </div>

            <!-- Step 2: Upload -->
            <div class="card step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4 class="step-subtitle">
                        Unggah Dokumen
                    </h4>
                    <p class="step-desc">
                        Gunakan template sebagai acuan bila diperlukan, lalu
                        unggah surat final Anda ke sistem.
                    </p>
                    <div
                        class="upload-zone"
                        :class="selectedFile ? 'upload-zone-active' : ''"
                        @click="$refs.fileInput.click()"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".docx,.doc,.pdf"
                            class="file-input-hidden"
                            @change="onFileSelect"
                        />
                        <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                        <div class="upload-text">
                            <p class="upload-main">Klik atau seret file</p>
                            <p class="upload-hint">
                                Format: .DOCX, .DOC, .PDF (Maks 5MB)
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="selectedFile"
                        class="file-preview"
                    >
                        <span class="material-symbols-outlined">description</span>
                        <span class="file-name">{{ selectedFile.name }}</span>
                        <span
                            class="material-symbols-outlined file-remove"
                            @click.stop="selectedFile = null"
                            >close</span
                        >
                    </div>
                </div>
            </div>

            <!-- Step 3: Target Approver -->
            <div class="card">
                <div class="step-header">
                    <div class="step-number">3</div>
                    <div>
                        <h4 class="step-subtitle">
                            Pilih Tujuan
                        </h4>
                        <p class="step-desc">
                            Pilih kepada siapa surat ini ditujukan.
                        </p>
                    </div>
                </div>
                <div class="form-stack">
                    <div class="form-group">
                        <label class="form-label">Kepada</label>
                        <select
                            v-model="targetRole"
                            class="form-select"
                        >
                            <option value="">-- Pilih tujuan --</option>
                            <option
                                v-for="r in targetRoles"
                                :key="r.value"
                                :value="r.value"
                            >
                                {{ r.label }}
                            </option>
                        </select>
                    </div>

                    <div v-if="targetRole === 'wadir'" class="form-group">
                        <label class="form-label">Pilihan Wadir</label>
                        <select
                            v-model="targetWadirLevel"
                            class="form-select"
                        >
                            <option :value="null">-- Pilih Wadir 1-3 --</option>
                            <option
                                v-for="w in wadirLevels"
                                :key="w"
                                :value="w"
                            >
                                Wadir {{ w }}
                            </option>
                        </select>
                    </div>

                    <div v-if="['kaprodi', 'dosen'].includes(targetRole)" class="form-group">
                        <label class="form-label">Jurusan</label>
                        <select
                            v-model="targetJurusan"
                            class="form-select"
                        >
                            <option value="">-- Pilih jurusan --</option>
                            <option
                                v-for="j in jurusanOptions"
                                :key="j"
                                :value="j"
                            >
                                {{ j }}
                            </option>
                        </select>
                    </div>

                    <div v-if="targetRole" class="form-group">
                        <label class="form-label">Nama Tujuan</label>
                        <select
                            v-model="targetUserId"
                            class="form-select"
                        >
                            <option :value="null">
                                -- Pilih nama tujuan --
                            </option>
                            <option
                                v-for="t in filteredTargets"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ targetLabel(t) }}
                            </option>
                        </select>
                        <p
                            v-if="filteredTargets.length === 1"
                            class="hint-success"
                        >
                            Sistem otomatis memilih tujuan karena hanya ada
                            satu akun yang cocok.
                        </p>
                        <p
                            v-else-if="targetRole && filteredTargets.length > 1"
                            class="form-hint"
                        >
                            Terdapat lebih dari satu akun yang cocok. Pilih
                            nama tujuan spesifik.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Submit -->
            <div class="card step-card submit-card">
                <div class="submit-glow"></div>
                <div class="step-number submit-step-number">4</div>
                <div class="step-content submit-content">
                    <h4 class="step-subtitle">
                        Ajukan Persetujuan
                    </h4>
                    <p class="step-desc">
                        Pastikan semua data sudah benar.
                    </p>
                    <button
                        @click="submitLetter"
                        :disabled="
                            !selectedFile ||
                            !targetRole ||
                            (childTypes.length > 0 && !selectedChildTypeId) ||
                            submitting
                        "
                        class="submit-btn"
                    >
                        <span
                            v-if="submitting"
                            class="material-symbols-outlined animate-spin"
                            >progress_activity</span
                        >
                        <span
                            class="material-symbols-outlined icon-size-md"
                            v-else
                            >send</span
                        >
                        {{
                            submitting
                                ? "Mengirim..."
                                : "Kirim Pengajuan Sekarang"
                        }}
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
const childTypes = ref([]);
const selectedChildTypeId = ref(null);
const selectedFile = ref(null);
const submitting = ref(false);
const targets = ref([]);
const targetRoles = ref([]);
const wadirLevels = ref([]);
const jurusanOptions = ref([]);
const targetRole = ref("");
const targetUserId = ref(null);
const targetWadirLevel = ref(null);
const targetJurusan = ref("");

const activeLetterType = computed(() => {
    if (childTypes.value.length > 0 && selectedChildTypeId.value) {
        return childTypes.value.find((child) => child.id === selectedChildTypeId.value) || null;
    }

    return letterType.value;
});

const activeParentLabel = computed(() => {
    if (childTypes.value.length > 0 && letterType.value) {
        return letterType.value.name;
    }

    return activeLetterType.value?.parent?.name || letterType.value?.parent?.name || "";
});

const templateTypeId = computed(() => activeLetterType.value?.id || null);

const filteredTargets = computed(() => {
    let list = targets.value;

    if (targetRole.value) {
        list = list.filter((t) => t.role === targetRole.value);
    }

    if (targetRole.value === "wadir" && targetWadirLevel.value) {
        list = list.filter(
            (t) => Number(t.wadir_level) === Number(targetWadirLevel.value),
        );
    }

    if (
        ["kaprodi", "dosen"].includes(targetRole.value) &&
        targetJurusan.value
    ) {
        list = list.filter(
            (t) =>
                (t.jurusan || "").toLowerCase() ===
                targetJurusan.value.toLowerCase(),
        );
    }

    return list;
});

watch(targetRole, (role) => {
    targetUserId.value = null;
    if (role !== "wadir") targetWadirLevel.value = null;
    if (!["kaprodi", "dosen"].includes(role)) targetJurusan.value = "";
});

watch(filteredTargets, (list) => {
    if (list.length === 1) {
        targetUserId.value = list[0].id;
        return;
    }

    if (!list.some((t) => t.id === targetUserId.value)) {
        targetUserId.value = null;
    }
});

const onFileSelect = (e) => {
    selectedFile.value = e.target.files[0] || null;
};

const targetLabel = (t) => {
    if (t.role === "wadir" && t.wadir_level)
        return `${t.name} (Wadir ${t.wadir_level})`;
    if (["kaprodi", "dosen"].includes(t.role) && t.jurusan)
        return `${t.name} (${t.jurusan})`;
    return `${t.name} (${t.role})`;
};

const submitLetter = async () => {
    if (!selectedFile.value || !letterType.value) return;

    if (childTypes.value.length > 0 && !selectedChildTypeId.value) {
        showFlash("Pilih jenis surat terlebih dahulu.", "error");
        return;
    }

    if (!targetRole.value) {
        showFlash("Pilih role tujuan terlebih dahulu.", "error");
        return;
    }

    if (targetRole.value === "wadir" && !targetWadirLevel.value) {
        showFlash("Pilih Wadir 1-3.", "error");
        return;
    }

    if (["kaprodi", "dosen"].includes(targetRole.value)) {
        if (!targetJurusan.value) {
            showFlash("Pilih jurusan tujuan.", "error");
            return;
        }
        if (!targetUserId.value) {
            showFlash("Pilih nama tujuan spesifik.", "error");
            return;
        }
    }

    if (!targetUserId.value && filteredTargets.value.length > 1) {
        showFlash("Pilih nama tujuan spesifik.", "error");
        return;
    }

    submitting.value = true;
    try {
        const fd = new FormData();
        fd.append("letter_type_id", activeLetterType.value?.id || letterType.value.id);
        fd.append("target_role", targetRole.value);
        if (targetUserId.value) fd.append("target_user_id", targetUserId.value);
        if (targetWadirLevel.value)
            fd.append("target_wadir_level", String(targetWadirLevel.value));
        if (targetJurusan.value)
            fd.append("target_jurusan", targetJurusan.value);
        fd.append("file", selectedFile.value);
        await axios.post("/api/letters", fd);
        showFlash("Surat berhasil diajukan!");
        router.push("/letters");
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal mengirim.", "error");
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
        childTypes.value = res.data.childTypes || [];
        selectedChildTypeId.value =
            childTypes.value.length === 1 ? childTypes.value[0].id : null;
        targets.value = res.data.targets || [];
        targetRoles.value = res.data.targetRoles || [];
        wadirLevels.value = res.data.wadirLevels || [1, 2, 3];
        jurusanOptions.value = res.data.jurusanOptions || [];
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
    padding: 2rem;
    border: 1px solid var(--slate-100);
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.3s;
}

.header-card:hover {
    box-shadow: 0 25px 50px -12px rgba(203, 213, 225, 0.5);
}

@media (min-width: 768px) {
    .header-card {
        padding: 2rem;
        flex-direction: row;
    }
}

.header-glow {
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

.header-card:hover .header-glow {
    background: rgba(79, 70, 229, 0.1);
}

.header-content {
    position: relative;
    z-index: 10;
    flex: 1;
}

.header-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.header-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
}

@media (min-width: 768px) {
    .header-title {
        font-size: 1.875rem;
    }
}

.parent-label {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    background: var(--indigo-50);
    color: var(--indigo-600);
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    border: 1px solid var(--indigo-100);
}

.draft-label {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    background: var(--slate-100);
    color: var(--slate-500);
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    border: 1px solid var(--slate-200);
}

.header-desc {
    color: var(--slate-500);
    font-weight: 500;
}

.header-icon-box {
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
    background: var(--indigo-50);
    color: var(--indigo-600);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid var(--indigo-100);
    position: relative;
    z-index: 10;
}

/* Section Header */
.section-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-left: 0.5rem;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.section-icon {
    color: var(--primary);
    font-size: 1.25rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--slate-900);
}

/* Step Cards */
.step-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    transition: border-color 0.2s;
}

@media (min-width: 768px) {
    .step-card {
        flex-direction: row;
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
    color: var(--slate-500);
    margin-top: 0.5rem;
}

.hint-success {
    font-size: 0.6875rem;
    color: var(--emerald-600);
    margin-top: 0.5rem;
}

.type-display {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--slate-700);
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

/* Template Link */
.template-link {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    color: var(--slate-700);
    font-weight: 700;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s;
}

.template-link:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.template-link .material-symbols-outlined {
    color: var(--slate-400);
    transition: color 0.2s;
}

.template-link:hover .material-symbols-outlined {
    color: white;
}

/* Upload Zone */
.upload-zone {
    position: relative;
    width: 100%;
    border-radius: 1rem;
    border: 2px dashed var(--slate-200);
    background: var(--slate-50);
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.2s;
    cursor: pointer;
}

.upload-zone:hover {
    border-color: var(--primary);
    background: rgba(79, 70, 229, 0.05);
}

.upload-zone-active {
    border-color: var(--primary) !important;
    background: rgba(79, 70, 229, 0.05) !important;
}

.upload-icon {
    font-size: 2.5rem;
    color: var(--slate-300);
}

.upload-text {
    text-align: center;
}

.upload-main {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-700);
}

.upload-hint {
    font-size: 0.6875rem;
    font-weight: 500;
    color: var(--slate-400);
    margin-top: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

/* File Preview */
.file-preview {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 0.75rem;
    background: rgba(79, 70, 229, 0.1);
    border: 1px solid rgba(79, 70, 229, 0.2);
    color: var(--primary);
}

.file-name {
    font-weight: 700;
    font-size: 0.875rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-remove {
    margin-left: auto;
    font-size: 1.125rem;
    cursor: pointer;
    transition: color 0.2s;
}

.file-remove:hover {
    color: var(--rose-500);
}

/* Step Header (Step 3) */
.step-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.form-stack {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Submit Card */
.submit-card {
    position: relative;
    overflow: hidden;
}

.submit-glow {
    position: absolute;
    top: 0;
    right: 0;
    width: 8rem;
    height: 8rem;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 50%;
    margin-right: -2.5rem;
    margin-top: -2.5rem;
    filter: blur(20px);
}

.submit-step-number {
    position: relative;
    z-index: 10;
}

.submit-content {
    position: relative;
    z-index: 10;
}

.submit-btn {
    width: 100%;
    padding: 1rem 1.5rem;
    background: var(--primary);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 1rem;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
}

.submit-btn:hover:not(:disabled) {
    background: var(--primary-dark);
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Hidden file input */
.file-input-hidden {
    display: none;
}

/* Icon sizes */
.icon-size-md {
    font-size: 20px;
}

.icon-size-xs {
    font-size: 14px;
}

.icon-size-2xl {
    font-size: 32px;
}
</style>
