<template>
    <AppLayout>
        <div class="users-page">
            <!-- Header Card -->
            <div class="page-header">
                <div class="page-header__left">
                    <div class="page-header__icon">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <div>
                        <h1 class="page-header__title">Data Pengguna</h1>
                        <p class="page-header__subtitle">
                            Kelola pengguna dan atur izin akses jenis surat per role.
                        </p>
                    </div>
                </div>
                <div class="page-header__actions">
                    <button @click="showPermissionModal = true" class="btn-secondary">
                        <span class="material-symbols-outlined">lock_open</span>
                        Kelola Izin Surat
                    </button>
                    <button @click="showDeptModal = true" class="btn-secondary">
                        <span class="material-symbols-outlined">school</span>
                        Jurusan
                    </button>
                    <button @click="openDrawer()" class="btn-primary-action">
                        <span class="material-symbols-outlined">person_add</span>
                        Tambah Pengguna
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-num">#</th>
                                <th>Pengguna</th>
                                <th>Email</th>
                                <th>Kode</th>
                                <th>Peran</th>
                                <th>Detail</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(u, i) in users" :key="u.id">
                                <td class="text-muted font-bold">{{ i + 1 }}</td>
                                <td>
                                    <div class="user-avatar">
                                        <div class="user-avatar__initials">
                                            {{ u.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="user-avatar__name">{{ u.name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted font-medium text-sm">{{ u.email }}</td>
                                <td>
                                    <span v-if="u.code" class="code-badge">{{ u.code }}</span>
                                    <span v-else class="text-muted text-xs italic">-</span>
                                </td>
                                <td>
                                    <span class="role-badge" :class="roleClass(u.role)">{{ u.role }}</span>
                                </td>
                                <td class="text-muted text-sm font-medium">{{ roleMeta(u) }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button @click="openDrawer(u)" class="btn-icon btn-icon--edit">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="openDeleteModal(u)" :disabled="u.id === currentUser?.id" class="btn-icon btn-icon--delete">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="7" class="empty-row">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">
                                            <span class="material-symbols-outlined">group</span>
                                        </div>
                                        <h3 class="empty-state__title">Belum ada pengguna</h3>
                                        <p class="empty-state__desc">Tambahkan pengguna pertama Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Drawer -->
            <div v-if="showDrawer" class="drawer-overlay" @click.self="showDrawer = false">
                <div class="drawer-backdrop" @click="showDrawer = false"></div>
                <div class="drawer">
                    <div class="drawer__header">
                        <div>
                            <h3 class="drawer__title">{{ editingUser ? "Edit Pengguna" : "Tambah Pengguna" }}</h3>
                            <p class="drawer__desc">{{ editingUser ? "Perbarui data pengguna" : "Isi data pengguna baru" }}</p>
                        </div>
                        <button @click="showDrawer = false" class="modal-close">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <form @submit.prevent="saveUser" class="drawer__body">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input v-model="form.name" required class="form-input-lg" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kode Identitas <span v-if="editingUser" class="text-disabled">(tidak dapat diubah)</span></label>
                            <input v-model="form.code" :disabled="editingUser" placeholder="Misal: KPD-TIM, DOS-TIK" class="form-input-lg" type="text" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input v-model="form.email" type="email" required class="form-input-lg" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password <span v-if="editingUser" class="text-optional">(kosongkan jika tidak diubah)</span></label>
                            <input v-model="form.password" type="password" :required="!editingUser" class="form-input-lg" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Peran</label>
                            <select v-model="form.role" required class="form-select-lg">
                                <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div v-if="form.role === 'wadir'" class="form-group">
                            <label class="form-label">Pilihan Wadir</label>
                            <select v-model="form.wadir_level" class="form-select-lg">
                                <option :value="null">-- Pilih Wadir 1-3 --</option>
                                <option :value="1">Wadir 1</option>
                                <option :value="2">Wadir 2</option>
                                <option :value="3">Wadir 3</option>
                            </select>
                        </div>
                        <div v-if="['kaprodi', 'dosen'].includes(form.role)" class="form-group">
                            <label class="form-label">Jurusan</label>
                            <select v-model="form.jurusan" required class="form-select-lg">
                                <option :value="null">-- Pilih Jurusan --</option>
                                <option v-for="d in departments" :key="d.id" :value="d.name">{{ d.name }}</option>
                            </select>
                        </div>
                        <div class="form-group pt-4">
                            <button type="submit" :disabled="saving" class="btn-submit-full">
                                <span class="material-symbols-outlined">{{ editingUser ? "save" : "person_add" }}</span>
                                {{ saving ? "Menyimpan..." : (editingUser ? "Simpan Perubahan" : "Tambah Pengguna") }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Modal -->
            <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
                <div class="modal-card modal-card--sm">
                    <div class="confirm-modal">
                        <div class="confirm-modal__icon confirm-modal__icon--danger">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <h3 class="confirm-modal__title">Hapus Pengguna?</h3>
                        <p class="confirm-modal__desc">Pengguna <b>{{ deletingUser?.name }}</b> akan dihapus secara permanen.</p>
                        <div class="confirm-modal__actions">
                            <button @click="showDeleteModal = false" class="btn-cancel">Batal</button>
                            <button @click="deleteUser" class="btn-danger">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Modal -->
            <div v-if="showDeptModal" class="modal-overlay" @click.self="showDeptModal = false">
                <div class="modal-card modal-card--dept">
                    <div class="modal-card__header">
                        <div>
                            <h3 class="modal-card__title">Kelola Jurusan</h3>
                            <p class="modal-card__desc">Tambah atau hapus jurusan untuk penempatan Kaprodi dan Dosen.</p>
                        </div>
                        <button type="button" @click="showDeptModal = false" class="modal-close">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="modal-card__body">
                        <div class="dept-form">
                            <input v-model="deptForm.name" placeholder="Nama Jurusan" class="dept-form__input" />
                            <input v-model="deptForm.code" placeholder="Kode" class="dept-form__input dept-form__input--code" />
                            <button @click="addDepartment" :disabled="!deptForm.name || !deptForm.code" class="btn-add">Tambah</button>
                        </div>
                        <div class="dept-list">
                            <div v-for="dept in departments" :key="dept.id" class="dept-item">
                                <div class="dept-item__info">
                                    <div class="dept-item__icon">
                                        <span class="material-symbols-outlined">school</span>
                                    </div>
                                    <div>
                                        <span class="dept-item__name">{{ dept.name }}</span>
                                        <span class="dept-item__code">{{ dept.code }}</span>
                                    </div>
                                </div>
                                <button @click="deleteDepartment(dept)" class="btn-icon btn-icon--delete btn-icon--sm">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                            <div v-if="departments.length === 0" class="dept-empty">Belum ada jurusan.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Modal -->
            <div v-if="showPermissionModal" class="modal-overlay" @click.self="showPermissionModal = false">
                <div class="permission-modal">
                    <div class="modal-card__header">
                        <div>
                            <h3 class="modal-card__title">Kelola Izin Surat per Role</h3>
                            <p class="modal-card__desc">Atur jenis surat yang bisa diakses setiap role.</p>
                        </div>
                        <button @click="showPermissionModal = false" class="modal-close">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="permission-modal__body">
                        <div class="role-tabs">
                            <button
                                v-for="role in roles"
                                :key="role"
                                @click="selectedPermissionRole = role"
                                class="role-tab"
                                :class="{ active: selectedPermissionRole === role }"
                            >
                                {{ role }}
                            </button>
                        </div>

                        <div class="permission-groups">
                            <div v-for="group in permissionGroups" :key="group.id" class="permission-group">
                                <div
                                    @click="toggleParent(group)"
                                    class="permission-group__parent"
                                    :class="{ checked: allChildrenChecked(group) }"
                                >
                                    <span class="material-symbols-outlined" :class="allChildrenChecked(group) ? 'text-primary' : 'text-muted'">
                                        {{ allChildrenChecked(group) ? "check_box" : "check_box_outline_blank" }}
                                    </span>
                                    <span class="permission-group__name">{{ group.name }}</span>
                                    <span v-if="group.children.length > 0" class="permission-group__count">{{ group.children.length }} jenis</span>
                                </div>
                                <div v-if="group.children.length > 0" class="permission-group__children">
                                    <label
                                        v-for="child in group.children"
                                        :key="child.id"
                                        class="permission-child"
                                        :class="{ checked: rolePermissions.includes(child.id) }"
                                    >
                                        <input type="checkbox" :value="child.id" v-model="rolePermissions" class="hidden" />
                                        <span class="material-symbols-outlined" :class="rolePermissions.includes(child.id) ? 'text-primary' : 'text-light'">
                                            {{ rolePermissions.includes(child.id) ? "check_box" : "check_box_outline_blank" }}
                                        </span>
                                        <span class="permission-child__name">{{ child.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button @click="savePermissions" :disabled="savingPermissions" class="btn-submit">
                            {{ savingPermissions ? "Menyimpan..." : "Simpan Izin" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject, computed, watch } from "vue";
import axios from "axios";
import AppLayout from "../../../Layouts/AppLayout.vue";

const showFlash = inject("showFlash");
const currentUser = inject("user");
const users = ref([]);
const allTypes = ref([]);
const departments = ref([]);
const showDrawer = ref(false);
const showDeleteModal = ref(false);
const showDeptModal = ref(false);
const showPermissionModal = ref(false);
const deptForm = ref({ name: "", code: "" });
const editingUser = ref(null);
const deletingUser = ref(null);
const form = ref({ name: "", email: "", password: "", role: "staf", code: "", wadir_level: null, jurusan: "" });
const saving = ref(false);
const roles = ["admin", "direktur", "kaprodi", "wadir", "staf", "dosen"];

const selectedPermissionRole = ref("admin");
const rolePermissions = ref([]);
const savingPermissions = ref(false);

const rootTypes = computed(() => allTypes.value.filter((t) => !t.parent_id));

const permissionGroups = computed(() => {
    return rootTypes.value.map((root) => ({
        id: root.id,
        name: root.name,
        children: allTypes.value.filter((t) => t.parent_id === root.id),
    }));
});

const allChildrenChecked = (group) => {
    if (group.children.length === 0) return rolePermissions.value.includes(group.id);
    return group.children.every((c) => rolePermissions.value.includes(c.id));
};

const toggleParent = (group) => {
    if (group.children.length === 0) {
        const idx = rolePermissions.value.indexOf(group.id);
        if (idx !== -1) rolePermissions.value.splice(idx, 1);
        else rolePermissions.value.push(group.id);
    } else if (allChildrenChecked(group)) {
        group.children.forEach((c) => {
            const idx = rolePermissions.value.indexOf(c.id);
            if (idx !== -1) rolePermissions.value.splice(idx, 1);
        });
    } else {
        group.children.forEach((c) => {
            if (!rolePermissions.value.includes(c.id)) rolePermissions.value.push(c.id);
        });
    }
};

const roleClass = (r) => {
    const map = {
        admin: "role-badge--admin",
        direktur: "role-badge--direktur",
        kaprodi: "role-badge--kaprodi",
        wadir: "role-badge--wadir",
        staf: "role-badge--staf",
        dosen: "role-badge--dosen",
    };
    return map[r] || "role-badge--default";
};

const fetchData = async () => {
    const res = await axios.get("/api/admin/users");
    users.value = res.data.users || [];
    allTypes.value = res.data.letterTypes || [];
    const deptRes = await axios.get("/api/admin/departments");
    departments.value = deptRes.data || [];
};

const fetchRolePermissions = async () => {
    const res = await axios.get(`/api/admin/permissions/${selectedPermissionRole.value}`);
    rolePermissions.value = res.data.map((p) => p.letter_type_id);
};

watch(selectedPermissionRole, fetchRolePermissions);

const roleMeta = (u) => {
    if (u.role === "wadir" && u.wadir_level) return `Wadir ${u.wadir_level}`;
    if (["kaprodi", "dosen"].includes(u.role) && u.jurusan) return u.jurusan;
    return "-";
};

const openDrawer = (user = null) => {
    editingUser.value = user;
    form.value = user
        ? { name: user.name, email: user.email, password: "", role: user.role, code: user.code, wadir_level: user.wadir_level ?? null, jurusan: user.jurusan ?? "" }
        : { name: "", email: "", password: "", role: "staf", code: "", wadir_level: null, jurusan: "" };
    showDrawer.value = true;
};

const openDeleteModal = (user) => {
    deletingUser.value = user;
    showDeleteModal.value = true;
};

const saveUser = async () => {
    saving.value = true;
    try {
        if (editingUser.value) {
            await axios.put(`/api/admin/users/${editingUser.value.id}`, form.value);
        } else {
            await axios.post("/api/admin/users", form.value);
        }
        showFlash(editingUser.value ? "Pengguna diperbarui." : "Pengguna ditambahkan.");
        editingUser.value = null;
        showDrawer.value = false;
        fetchData();
    } catch (e) {
        let msg = "Gagal menyimpan.";
        if (e.response?.data?.errors) msg = Object.values(e.response.data.errors).flat()[0];
        else if (e.response?.data?.message) msg = e.response.data.message;
        showFlash(msg, "error");
    } finally {
        saving.value = false;
    }
};

const deleteUser = async () => {
    try {
        await axios.delete(`/api/admin/users/${deletingUser.value.id}`);
        showFlash("Pengguna dihapus.");
        showDeleteModal.value = false;
        fetchData();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const addDepartment = async () => {
    try {
        await axios.post("/api/admin/departments", deptForm.value);
        showFlash("Jurusan berhasil ditambahkan.");
        deptForm.value = { name: "", code: "" };
        fetchData();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const deleteDepartment = async (dept) => {
    try {
        await axios.delete(`/api/admin/departments/${dept.id}`);
        showFlash("Jurusan berhasil dihapus.");
        fetchData();
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    }
};

const savePermissions = async () => {
    savingPermissions.value = true;
    try {
        await axios.post(`/api/admin/permissions/${selectedPermissionRole.value}`, { letter_type_ids: rolePermissions.value });
        showFlash("Izin surat berhasil diperbarui.");
    } catch (e) {
        showFlash(e.response?.data?.message || "Gagal.", "error");
    } finally {
        savingPermissions.value = false;
    }
};

onMounted(() => {
    fetchData();
    fetchRolePermissions();
});
</script>

<style scoped>
.users-page {
    max-width: 80rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 2.5rem;
}

/* Page Header */
.page-header {
    background: white;
    border-radius: 1.5rem;
    padding: 1.5rem;
    box-shadow: 0 20px 25px -5px rgba(203, 213, 225, 0.3);
    border: 1px solid var(--slate-100);
}

@media (min-width: 768px) {
    .page-header {
        padding: 2rem;
        display: flex;
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.page-header__left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-header__icon {
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #4F46E5, #6366F1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.page-header__icon .material-symbols-outlined {
    font-size: 1.75rem;
}

.page-header__title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
}

.page-header__subtitle {
    color: var(--slate-500);
    font-size: 0.875rem;
    font-weight: 500;
}

.page-header__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: white;
    color: var(--indigo-600);
    border: 1px solid var(--indigo-100);
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-secondary:hover {
    background: var(--indigo-50);
}

.btn-secondary .material-symbols-outlined {
    font-size: 1.125rem;
}

.btn-primary-action {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: var(--primary);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.btn-primary-action:hover {
    background: var(--primary-dark);
}

.btn-primary-action:active {
    transform: scale(0.95);
}

.btn-primary-action .material-symbols-outlined {
    font-size: 1.125rem;
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid var(--slate-100);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.data-table thead tr {
    background: var(--primary);
}

.data-table thead th {
    padding: 1.25rem 2rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    white-space: nowrap;
}

.data-table thead th.text-center {
    text-align: center;
}

.data-table thead th.col-num {
    width: 3rem;
}

.data-table tbody tr {
    border-bottom: 1px solid var(--slate-50);
    transition: background 0.15s;
}

.data-table tbody tr:hover {
    background: rgba(248, 250, 252, 0.5);
}

.data-table tbody td {
    padding: 1.5rem 1.5rem;
    font-size: 0.875rem;
    white-space: nowrap;
}

.data-table tbody td.text-center {
    text-align: center;
}

.data-table tbody td.text-muted {
    color: var(--slate-400);
}

.data-table tbody td.font-bold {
    font-weight: 700;
    color: var(--slate-400);
}

.data-table tbody td.font-medium {
    font-weight: 500;
    color: var(--slate-500);
}

.data-table tbody td.text-sm {
    font-size: 0.875rem;
}

.data-table tbody td.text-xs {
    font-size: 0.75rem;
}

.data-table tbody td.italic {
    font-style: italic;
}

/* User Avatar */
.user-avatar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar__initials {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #4F46E5, #6366F1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.user-avatar__name {
    font-weight: 700;
    color: var(--slate-800);
    font-size: 0.875rem;
}

/* Code Badge */
.code-badge {
    padding: 0.375rem 0.75rem;
    background: var(--slate-100);
    color: var(--slate-600);
    border-radius: 0.5rem;
    font-family: monospace;
    font-size: 0.75rem;
    font-weight: 700;
}

/* Role Badge */
.role-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.role-badge--admin {
    background: var(--rose-50);
    color: var(--rose-700);
    border: 1px solid var(--rose-100);
}

.role-badge--direktur {
    background: var(--violet-50);
    color: var(--violet-600);
    border: 1px solid var(--violet-100);
}

.role-badge--kaprodi {
    background: var(--indigo-50);
    color: var(--indigo-700);
    border: 1px solid var(--indigo-100);
}

.role-badge--wadir {
    background: var(--blue-50);
    color: var(--blue-600);
    border: 1px solid var(--blue-100);
}

.role-badge--staf {
    background: var(--emerald-50);
    color: var(--emerald-700);
    border: 1px solid var(--emerald-100);
}

.role-badge--dosen {
    background: var(--amber-50);
    color: var(--amber-700);
    border: 1px solid var(--amber-100);
}

.role-badge--default {
    background: var(--slate-50);
    color: var(--slate-700);
    border: 1px solid var(--slate-100);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon .material-symbols-outlined {
    font-size: 1.125rem;
}

.btn-icon--edit {
    background: var(--amber-50);
    color: var(--amber-600);
}

.btn-icon--edit:hover {
    background: var(--amber-100);
}

.btn-icon--delete {
    background: var(--rose-50);
    color: var(--rose-600);
}

.btn-icon--delete:hover {
    background: var(--rose-100);
}

.btn-icon--delete:disabled {
    opacity: 0.3;
}

.btn-icon--sm {
    width: 2rem;
    height: 2rem;
}

.btn-icon--sm .material-symbols-outlined {
    font-size: 1rem;
}

/* Empty State */
.empty-row {
    padding: 4rem !important;
    text-align: center;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-state__icon {
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

.empty-state__icon .material-symbols-outlined {
    font-size: 2.5rem;
}

.empty-state__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-600);
    margin-bottom: 0.25rem;
}

.empty-state__desc {
    font-size: 0.75rem;
    color: var(--slate-400);
}

/* Drawer */
.drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    justify-content: flex-end;
}

.drawer-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(4px);
}

.drawer {
    position: relative;
    width: 100%;
    max-width: 32rem;
    background: white;
    height: 100%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-overlay .drawer {
    transform: translateX(0);
}

.drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--slate-100);
}

.drawer__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
}

.drawer__desc {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-weight: 500;
}

.modal-close {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    border: none;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.modal-close:hover {
    background: var(--slate-100);
}

.modal-close .material-symbols-outlined {
    color: var(--slate-400);
}

.drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}

.form-input-lg {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-900);
    transition: all 0.2s;
}

.form-input-lg:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.form-input-lg:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.form-select-lg {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--slate-700);
    transition: all 0.2s;
}

.form-select-lg:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.text-disabled {
    font-size: 0.5625rem;
    font-weight: 400;
    opacity: 0.5;
    text-transform: lowercase;
}

.text-optional {
    color: var(--amber-500);
}

.pt-4 {
    padding-top: 1rem;
}

.btn-submit-full {
    width: 100%;
    padding: 1rem;
    background: var(--primary);
    color: white;
    font-weight: 700;
    border-radius: 1rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.btn-submit-full:hover {
    background: var(--primary-dark);
}

.btn-submit-full:disabled {
    opacity: 0.6;
}

.btn-submit-full .material-symbols-outlined {
    font-size: 1.25rem;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
}

.modal-card {
    background: white;
    width: 100%;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-card--sm {
    max-width: 28rem;
}

.modal-card--dept {
    max-width: 42rem;
}

.modal-card__header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--slate-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-card__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--slate-900);
}

.modal-card__desc {
    font-size: 0.75rem;
    color: var(--slate-400);
    font-weight: 500;
}

.modal-card__body {
    padding: 2rem;
}

/* Confirm Modal */
.confirm-modal {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2rem;
}

.confirm-modal__icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.confirm-modal__icon .material-symbols-outlined {
    font-size: 2rem;
}

.confirm-modal__icon--danger {
    background: var(--rose-50);
    color: var(--rose-500);
}

.confirm-modal__title {
    font-size: 1.125rem;
    font-weight: 800;
    color: var(--slate-900);
    margin-bottom: 0.5rem;
}

.confirm-modal__desc {
    font-size: 0.875rem;
    color: var(--slate-500);
    margin-bottom: 1.5rem;
}

.confirm-modal__actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    width: 100%;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-700);
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: var(--slate-50);
}

.btn-danger {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background: var(--rose-500);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background: var(--rose-600);
}

/* Department Form */
.dept-form {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.dept-form__input {
    flex: 1;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.dept-form__input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.dept-form__input--code {
    width: 7rem;
    flex: none;
    font-family: monospace;
}

.btn-add {
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    background: var(--primary);
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-add:hover {
    background: var(--primary-dark);
}

.btn-add:disabled {
    opacity: 0.5;
}

/* Department List */
.dept-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 16rem;
    overflow-y: auto;
}

.dept-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-100);
}

.dept-item__info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dept-item__icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    background: var(--indigo-50);
    color: var(--indigo-600);
    display: flex;
    align-items: center;
    justify-content: center;
}

.dept-item__icon .material-symbols-outlined {
    font-size: 1rem;
}

.dept-item__name {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-800);
}

.dept-item__code {
    margin-left: 0.5rem;
    padding: 0.125rem 0.5rem;
    background: var(--slate-200);
    color: var(--slate-600);
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.625rem;
    font-weight: 700;
}

.dept-empty {
    text-align: center;
    padding: 2rem;
    color: var(--slate-400);
    font-size: 0.875rem;
}

/* Permission Modal */
.permission-modal {
    background: white;
    width: 100%;
    max-width: 48rem;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.permission-modal__body {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

/* Role Tabs */
.role-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.role-tab {
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 700;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    background: var(--slate-100);
    color: var(--slate-600);
}

.role-tab:hover {
    background: var(--slate-200);
}

.role-tab.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

/* Permission Groups */
.permission-groups {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.permission-group {
    border-radius: 0.75rem;
    border: 1px solid var(--slate-200);
    overflow: hidden;
}

.permission-group__parent {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
    background: var(--slate-50);
}

.permission-group__parent.checked {
    background: var(--indigo-50);
}

.permission-group__parent .material-symbols-outlined {
    font-size: 1.25rem;
}

.permission-group__name {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-800);
}

.permission-group__count {
    margin-left: auto;
    font-size: 0.6875rem;
    color: var(--slate-400);
    font-weight: 500;
}

.permission-group__children {
    border-top: 1px solid var(--slate-100);
}

.permission-child {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    padding-left: 3.5rem;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.permission-child:hover {
    background: var(--slate-50);
}

.permission-child.checked {
    background: rgba(238, 242, 255, 0.5);
}

.permission-child .material-symbols-outlined {
    font-size: 1.125rem;
}

.text-primary {
    color: var(--indigo-600);
}

.text-muted {
    color: var(--slate-400);
}

.text-light {
    color: var(--slate-300);
}

.permission-child__name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--slate-700);
}

.hidden {
    display: none;
}

.btn-submit {
    margin-top: 1.5rem;
    width: 100%;
    padding: 0.75rem;
    background: var(--primary);
    color: white;
    font-weight: 700;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn-submit:hover {
    background: var(--primary-dark);
}

.btn-submit:disabled {
    opacity: 0.6;
}
</style>
