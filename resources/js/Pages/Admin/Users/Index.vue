<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Data Pengguna</h1>
                    <p class="text-slate-500 mt-1 font-medium">Kelola pengguna dan hak akses jenis surat.</p>
                </div>
                <button @click="openDrawer()" class="flex items-center gap-2 px-6 py-3.5 bg-[#4F46E5] text-white text-sm font-bold rounded-2xl shadow-lg shadow-[#4F46E5]/20 hover:bg-[#4338CA] transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                    Tambah Pengguna
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/30 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Pengguna</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Peran</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <span class="font-bold text-slate-800">{{ u.name }}</span>
                                </td>
                                <td class="px-6 py-5 text-slate-500 font-medium text-sm">{{ u.email }}</td>
                                <td class="px-6 py-5"><span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg font-mono text-xs font-bold" v-if="u.code">{{ u.code }}</span><span v-else class="text-slate-300 italic text-[10px]">None</span></td>
                                <td class="px-6 py-5"><span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="roleClass(u.role)">{{ u.role }}</span></td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openDrawer(u)" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-all"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                        <button @click="openDeleteModal(u)" :disabled="u.id === currentUser?.id" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-all disabled:opacity-30"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0"><td colspan="5" class="px-8 py-12 text-center text-slate-500">Tidak ada data.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Drawer -->
            <div v-if="showDrawer" class="fixed inset-0 z-50 flex justify-end" @click.self="showDrawer = false">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showDrawer = false"></div>
                <div class="side-drawer open relative w-full max-w-lg bg-white h-full shadow-xl flex flex-col">
                    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-900">{{ editingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h3>
                            <p class="text-xs text-slate-400 font-medium">{{ editingUser ? 'Perbarui data' : 'Isi data pengguna baru' }}</p>
                        </div>
                        <button @click="showDrawer = false" class="w-10 h-10 rounded-xl hover:bg-slate-100 flex items-center justify-center"><span class="material-symbols-outlined text-slate-400">close</span></button>
                    </div>
                    <form @submit.prevent="saveUser" class="flex-1 overflow-y-auto p-8 space-y-6">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input v-model="form.name" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kode Identitas <span v-if="editingUser" class="text-[9px] lowercase font-normal opacity-50">(tidak dapat diubah)</span></label>
                            <input v-model="form.code" :disabled="editingUser" placeholder="Misal: KPD-TIM, DOS-TIK" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed" type="text"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Email</label>
                            <input v-model="form.email" type="email" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Password <span v-if="editingUser" class="text-amber-500">(kosongkan jika tidak diubah)</span></label>
                            <input v-model="form.password" type="password" :required="!editingUser" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Peran</label>
                            <select v-model="form.role" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] text-sm font-medium transition-all">
                                <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Akses Jenis Surat</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="lt in allTypes" :key="lt.id" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-all" :class="form.letter_types.includes(lt.id) ? 'bg-indigo-50 border-[#4F46E5] text-[#4F46E5]' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100'">
                                    <input type="checkbox" :value="lt.id" v-model="form.letter_types" class="hidden"/>
                                    <span class="material-symbols-outlined text-[16px]">{{ form.letter_types.includes(lt.id) ? 'check_box' : 'check_box_outline_blank' }}</span>
                                    <span class="text-xs font-bold">{{ lt.name }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="submit" :disabled="saving" class="w-full py-4 bg-[#4F46E5] text-white font-bold rounded-2xl shadow-lg shadow-[#4F46E5]/20 hover:bg-[#4338CA] transition-all disabled:opacity-60 flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[20px]">{{ editingUser ? 'save' : 'person_add' }}</span>
                                {{ saving ? 'Menyimpan...' : (editingUser ? 'Simpan Perubahan' : 'Tambah Pengguna') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Modal -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm" @click.self="showDeleteModal = false">
                <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-8">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-4"><span class="material-symbols-outlined text-3xl">warning</span></div>
                        <h3 class="text-lg font-extrabold text-slate-900 mb-2">Hapus Pengguna?</h3>
                        <p class="text-sm text-slate-500 mb-6">Pengguna <b>{{ deletingUser?.name }}</b> akan dihapus secara permanen.</p>
                        <div class="grid grid-cols-2 gap-4 w-full">
                            <button @click="showDeleteModal = false" class="py-3 px-6 rounded-2xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition-all">Batal</button>
                            <button @click="deleteUser" class="py-3 px-6 rounded-2xl bg-rose-500 text-white font-bold text-sm hover:bg-rose-600 transition-all">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import AppLayout from '../../../Layouts/AppLayout.vue';

const showFlash = inject('showFlash');
const currentUser = inject('user');
const users = ref([]);
const allTypes = ref([]);
const showDrawer = ref(false);
const showDeleteModal = ref(false);
const editingUser = ref(null);
const deletingUser = ref(null);
const form = ref({ name: '', email: '', password: '', role: 'staf', code: '', letter_types: [] });
const saving = ref(false);
const roles = ['admin', 'direktur', 'kaprodi', 'wadir', 'staf', 'dosen'];

const roleClass = (r) => {
    const map = {
        admin: 'bg-rose-50 text-rose-700 border border-rose-100',
        direktur: 'bg-purple-50 text-purple-700 border border-purple-100',
        kaprodi: 'bg-indigo-50 text-indigo-700 border border-indigo-100',
        wadir: 'bg-sky-50 text-sky-700 border border-sky-100',
        staf: 'bg-emerald-50 text-emerald-700 border border-emerald-100',
        dosen: 'bg-amber-50 text-amber-700 border border-amber-100',
    };
    return map[r] || 'bg-slate-50 text-slate-700 border border-slate-100';
};

const fetchData = async () => {
    const res = await axios.get('/api/admin/users');
    users.value = res.data.users || [];
    allTypes.value = res.data.letterTypes || [];
};

const openDrawer = (user = null) => {
    editingUser.value = user;
    form.value = user
        ? { name: user.name, email: user.email, password: '', role: user.role, code: user.code, letter_types: (user.letter_types || []).map(t => t.id) }
        : { name: '', email: '', password: '', role: 'staf', code: '', letter_types: [] };
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
            await axios.post('/api/admin/users', form.value);
        }
        showFlash(editingUser.value ? 'Pengguna diperbarui.' : 'Pengguna ditambahkan.');
        editingUser.value = null;
        showDrawer.value = false;
        fetchData();
    } catch (e) {
        let msg = 'Gagal menyimpan.';
        if (e.response?.data?.errors) {
            msg = Object.values(e.response.data.errors).flat()[0];
        } else if (e.response?.data?.message) {
            msg = e.response.data.message;
        }
        showFlash(msg, 'error');
    } finally { 
        saving.value = false;
    }
};

const deleteUser = async () => {
    try {
        await axios.delete(`/api/admin/users/${deletingUser.value.id}`);
        showFlash('Pengguna dihapus.');
        showDeleteModal.value = false;
        fetchData();
    } catch (e) { showFlash(e.response?.data?.message || 'Gagal.', 'error'); }
};

onMounted(fetchData);
</script>
