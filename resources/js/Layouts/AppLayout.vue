<template>
    <div class="app-layout">
        <!-- SIDEBAR -->
        <aside
            class="sidebar"
            :class="sidebarOpen ? 'sidebar-open' : 'sidebar-collapsed'"
        >
            <!-- Logo area -->
            <div class="sidebar-logo-area" :class="sidebarOpen ? 'sidebar-logo-expanded' : 'sidebar-logo-collapsed'">
                <div class="sidebar-logo-icon">
                    <span class="material-symbols-outlined font-light">school</span>
                </div>
                <div class="sidebar-logo-text" :class="sidebarOpen ? 'sidebar-logo-text-visible' : 'sidebar-logo-text-hidden'">
                    <h1 class="sidebar-title">SISurat</h1>
                    <p class="sidebar-subtitle">Politeknik Pajajaran</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <router-link
                    to="/dashboard"
                    :title="!sidebarOpen ? 'Dashboard' : ''"
                    :class="[
                        'nav-link',
                        sidebarOpen ? 'nav-link-expanded' : 'nav-link-collapsed',
                        isActive('dashboard')
                            ? 'nav-link-active'
                            : 'nav-link-inactive'
                    ]"
                >
                    <span class="material-symbols-outlined nav-link-icon" :class="sidebarOpen ? '' : 'nav-link-icon-centered'">dashboard</span>
                    <span v-if="sidebarOpen" class="nav-link-text">Dashboard</span>
                </router-link>

                <template v-if="user">
                    <!-- Surat Saya -->
                    <router-link
                        to="/letters"
                        :title="!sidebarOpen ? 'Surat Saya' : ''"
                        :class="[
                            'nav-link',
                            sidebarOpen ? 'nav-link-expanded' : 'nav-link-collapsed',
                            isActive('user.letters')
                                ? 'nav-link-active'
                                : 'nav-link-inactive'
                        ]"
                    >
                        <span class="material-symbols-outlined nav-link-icon" :class="sidebarOpen ? '' : 'nav-link-icon-centered'">description</span>
                        <span v-if="sidebarOpen" class="nav-link-text">Surat Saya</span>
                    </router-link>

                    <!-- Perlu Persetujuan -->
                    <router-link
                        to="/approvals"
                        :title="!sidebarOpen ? 'Perlu Persetujuan' : ''"
                        :class="[
                            'nav-link',
                            sidebarOpen ? 'nav-link-expanded' : 'nav-link-collapsed',
                            isActive('approvals')
                                ? 'nav-link-active'
                                : 'nav-link-inactive',
                            'nav-link-relative'
                        ]"
                    >
                        <span class="material-symbols-outlined nav-link-icon nav-link-icon-relative" :class="sidebarOpen ? '' : 'nav-link-icon-centered'">
                            pending_actions
                            <span
                                v-if="approvalCount > 0 && !sidebarOpen"
                                class="nav-badge nav-badge-icon"
                            >{{ approvalCount }}</span>
                        </span>
                        <span v-if="sidebarOpen" class="nav-link-text nav-link-text-with-badge">
                            Perlu Persetujuan
                            <span
                                v-if="approvalCount > 0"
                                class="nav-badge nav-badge-text"
                            >{{ approvalCount }}</span>
                        </span>
                    </router-link>

                    <!-- Admin section -->
                    <template v-if="user.role === 'admin'">
                        <div class="nav-divider"></div>
                        <p v-if="sidebarOpen" class="nav-section-label">Admin</p>

                        <router-link
                            to="/admin/types"
                            :title="!sidebarOpen ? 'Jenis Surat' : ''"
                            :class="[
                                'nav-link',
                                sidebarOpen ? 'nav-link-expanded' : 'nav-link-collapsed',
                                isActive('admin.types')
                                    ? 'nav-link-active'
                                    : 'nav-link-inactive'
                            ]"
                        >
                            <span class="material-symbols-outlined nav-link-icon" :class="sidebarOpen ? '' : 'nav-link-icon-centered'">category</span>
                            <span v-if="sidebarOpen" class="nav-link-text">Jenis Surat</span>
                        </router-link>

                        <router-link
                            to="/admin/users"
                            :title="!sidebarOpen ? 'Data Pengguna' : ''"
                            :class="[
                                'nav-link',
                                sidebarOpen ? 'nav-link-expanded' : 'nav-link-collapsed',
                                isActive('admin.users')
                                    ? 'nav-link-active'
                                    : 'nav-link-inactive'
                            ]"
                        >
                            <span class="material-symbols-outlined nav-link-icon" :class="sidebarOpen ? '' : 'nav-link-icon-centered'">group</span>
                            <span v-if="sidebarOpen" class="nav-link-text">Data Pengguna</span>
                        </router-link>
                    </template>
                </template>
            </nav>
        </aside>

        <div class="main-wrapper">
            <!-- TOP HEADER -->
            <header class="top-header">
                <div class="header-left">
                    <button
                        @click="toggleSidebar"
                        class="sidebar-toggle-btn"
                    >
                        <span class="material-symbols-outlined sidebar-toggle-icon">menu</span>
                    </button>
                    <div>
                        <h2 class="header-greeting">Selamat Datang</h2>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="profile-dropdown" v-if="user">
                    <button
                        @click="showProfileMenu = !showProfileMenu"
                        class="profile-trigger"
                    >
                        <div class="profile-avatar">
                            <img :alt="user.name" class="profile-avatar-img" :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`" />
                        </div>
                        <div class="profile-info">
                            <p class="profile-name">{{ user.name }}</p>
                            <p class="profile-role">{{ user.role }}</p>
                        </div>
                        <span class="material-symbols-outlined profile-chevron">expand_more</span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        v-if="showProfileMenu"
                        class="profile-menu"
                    >
                        <div class="profile-menu-mobile-info">
                            <p class="profile-name">{{ user.name }}</p>
                            <p class="profile-role">{{ user.role }}</p>
                        </div>
                        <button
                            @click="showLogoutModal = true; showProfileMenu = false;"
                            class="profile-menu-logout"
                        >
                            <span class="material-symbols-outlined profile-logout-icon">logout</span>
                            Keluar
                        </button>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT AREA -->
            <main class="main-content">
                <slot></slot>
            </main>
        </div>

        <!-- Click outside to close profile menu -->
        <div
            v-if="showProfileMenu"
            class="menu-backdrop"
            @click="showProfileMenu = false"
        ></div>

        <!-- Logout Confirmation Modal -->
        <div v-if="showLogoutModal" class="modal-overlay" @click.self="showLogoutModal = false">
            <div class="modal-content logout-modal">
                <div class="modal-body">
                    <div class="modal-icon-wrapper">
                        <span class="material-symbols-outlined modal-icon">logout</span>
                    </div>
                    <h3 class="modal-title">Keluar dari Sistem?</h3>
                    <p class="modal-description">
                        Anda perlu login kembali untuk mengakses sistem.
                    </p>
                    <div class="modal-actions">
                        <button
                            @click="showLogoutModal = false"
                            class="btn-modal-cancel"
                        >
                            Batal
                        </button>
                        <button
                            @click="logout"
                            class="btn-modal-confirm"
                        >
                            Ya, Keluar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, inject, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { resetUserCache } from "../router";

const route = useRoute();
const router = useRouter();
const user = inject("user");
const approvalCount = ref(0);
const sidebarOpen = ref(sessionStorage.getItem('sidebarOpen') !== 'false');
const showLogoutModal = ref(false);
const showProfileMenu = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
    sessionStorage.setItem('sidebarOpen', sidebarOpen.value);
};

const isActive = (name) => {
    const path = route.path;
    if (name === "dashboard") return path === "/dashboard";
    if (name === "user.letters") return path.startsWith("/letters");
    if (name === "approvals") return path === "/approvals";
    if (name === "admin.types") return path === "/admin/types";
    if (name === "admin.users") return path === "/admin/users";
    return false;
};

const fetchApprovalCount = async () => {
    try {
        const res = await axios.get("/api/dashboard");
        approvalCount.value = res.data.stats?.approvalInboxCount || 0;
    } catch {
        approvalCount.value = 0;
    }
};

const logout = async () => {
    try {
        await axios.post("/api/logout");
    } catch {}
    resetUserCache();
    user.value = null;
    sessionStorage.removeItem('sidebarOpen');
    router.push("/login");
};

watch(() => route.path, () => {
    showProfileMenu.value = false;
    if (user.value && user.value.role !== 'admin') {
        fetchApprovalCount();
    }
});

onMounted(() => {
    if (user.value && user.value.role !== 'admin') {
        fetchApprovalCount();
    }
});
</script>

<style scoped>
.app-layout {
    display: flex;
    height: 100vh;
    width: 100%;
    overflow: hidden;
}

/* ===== SIDEBAR ===== */
.sidebar {
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
    border-right: 1px solid #F1F5F9;
    height: 100%;
    flex-shrink: 0;
    transition: width 0.3s;
    overflow-x: hidden;
}

.sidebar-open {
    width: 18rem;
}

.sidebar-collapsed {
    width: 5rem;
}

/* Sidebar Logo Area */
.sidebar-logo-area {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    transition: all 0.3s;
}

.sidebar-logo-expanded {
    gap: 0.75rem;
    padding: 2rem;
}

.sidebar-logo-collapsed {
    justify-content: center;
    padding: 1.5rem 1rem;
}

.sidebar-logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #4F46E5, #6366F1);
    color: #ffffff;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    flex-shrink: 0;
}

.sidebar-logo-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s;
}

.sidebar-logo-text-visible {
    opacity: 1;
    width: auto;
}

.sidebar-logo-text-hidden {
    opacity: 0;
    width: 0;
}

.sidebar-title {
    font-size: 1.125rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    color: #0F172A;
    line-height: 1;
    white-space: nowrap;
}

.sidebar-subtitle {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    font-weight: 700;
    color: #94A3B8;
    margin-top: 0.25rem;
    white-space: nowrap;
}

/* Sidebar Nav */
.sidebar-nav {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    overflow-y: auto;
}

/* Nav Links */
.nav-link {
    display: flex;
    align-items: center;
    border-radius: 0.75rem;
    transition: all 0.2s;
}

.nav-link-expanded {
    gap: 0.75rem;
    padding: 0.75rem 1rem;
}

.nav-link-collapsed {
    gap: 0;
    padding: 0.75rem 0;
    justify-content: center;
}

.nav-link-active {
    background-color: #4F46E5;
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}

.nav-link-inactive {
    color: #64748B;
}

.nav-link-inactive:hover {
    background-color: #F8FAFC;
    color: #4F46E5;
}

.nav-link-icon {
    flex-shrink: 0;
}

.nav-link-icon-centered {
    margin: 0 auto;
}

.nav-link-icon-relative {
    position: relative;
}

.nav-link-text {
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
}

.nav-link-text-with-badge {
    display: flex;
    align-items: center;
}

.nav-link-relative {
    position: relative;
}

/* Nav Divider */
.nav-divider {
    margin: 0.5rem 0;
    border-top: 1px solid #F1F5F9;
}

.nav-section-label {
    padding: 0 1rem;
    font-size: 0.625rem;
    font-weight: 700;
    color: #CBD5E1;
    text-transform: uppercase;
    letter-spacing: 0.25em;
}

/* Nav Badges */
.nav-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    border-radius: 9999px;
}

.nav-badge-icon {
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    width: 1rem;
    height: 1rem;
    background-color: #F43F5E;
    color: #ffffff;
    font-size: 0.5rem;
}

.nav-badge-text {
    margin-left: auto;
    background-color: #F43F5E;
    color: #ffffff;
    font-size: 0.625rem;
    padding: 0.125rem 0.5rem;
    min-width: 1.25rem;
    text-align: center;
}

/* ===== MAIN WRAPPER ===== */
.main-wrapper {
    display: flex;
    flex-direction: column;
    flex: 1;
    height: 100%;
    overflow: hidden;
    position: relative;
}

/* ===== TOP HEADER ===== */
.top-header {
    height: 5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #F1F5F9;
    flex-shrink: 0;
    z-index: 20;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sidebar-toggle-btn {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background-color: #F1F5F9;
    color: #475569;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.sidebar-toggle-btn:hover {
    background-color: #4F46E5;
    color: #ffffff;
}

.sidebar-toggle-icon {
    font-size: 1.375rem;
}

.header-greeting {
    font-size: 0.875rem;
    font-weight: 700;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: 0.25em;
}

/* ===== PROFILE DROPDOWN ===== */
.profile-dropdown {
    position: relative;
}

.profile-trigger {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.profile-trigger:hover {
    background-color: #F8FAFC;
}

.profile-avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.5rem;
    overflow: hidden;
    ring: 2px solid #F1F5F9;
}

.profile-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info {
    text-align: left;
}

.profile-name {
    font-size: 0.875rem;
    font-weight: 700;
    color: #1E293B;
    line-height: 1.25;
}

.profile-role {
    font-size: 0.6875rem;
    color: #94A3B8;
    text-transform: capitalize;
}

.profile-chevron {
    font-size: 1.125rem;
    color: #94A3B8;
}

/* Profile Menu */
.profile-menu {
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 0.5rem;
    width: 14rem;
    background-color: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    border: 1px solid #F1F5F9;
    overflow: hidden;
    z-index: 50;
}

.profile-menu-mobile-info {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #F1F5F9;
}

.profile-menu-logout {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #E11D48;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.profile-menu-logout:hover {
    background-color: #FFF1F2;
}

.profile-logout-icon {
    font-size: 1.25rem;
}

/* Menu Backdrop */
.menu-backdrop {
    position: fixed;
    inset: 0;
    z-index: 10;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}

@media (min-width: 768px) {
    .main-content {
        padding: 2.5rem;
    }
}

/* ===== MODAL ===== */
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 70;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(15, 23, 42, 0.6);
}

.logout-modal {
    background-color: #ffffff;
    border-radius: 1.5rem;
    width: 100%;
    max-width: 24rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

.modal-icon-wrapper {
    width: 4rem;
    height: 4rem;
    border-radius: 9999px;
    background-color: #FFF1F2;
    color: #F43F5E;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.modal-icon {
    font-size: 1.5rem;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 800;
    color: #0F172A;
    margin-bottom: 0.5rem;
}

.modal-description {
    font-size: 0.875rem;
    color: #64748B;
    text-align: center;
    margin-bottom: 1.5rem;
}

.modal-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    width: 100%;
}

.btn-modal-cancel {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    border: 1px solid #E2E8F0;
    background-color: #ffffff;
    color: #334155;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-modal-cancel:hover {
    background-color: #F8FAFC;
}

.btn-modal-confirm {
    padding: 0.75rem 1.5rem;
    border-radius: 1rem;
    background-color: #F43F5E;
    color: #ffffff;
    border: none;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-modal-confirm:hover {
    background-color: #E11D48;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
    .profile-info {
        display: none;
    }

    .profile-menu-mobile-info {
        display: block;
    }
}

@media (min-width: 641px) {
    .profile-menu-mobile-info {
        display: none;
    }
}
</style>
