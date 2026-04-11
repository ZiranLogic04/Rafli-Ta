<template>
    <div class="mesh-gradient login-page">
        <div class="login-container">
            <!-- Left Side - Branding -->
            <div class="login-left">
                <div class="branding-content">
                    <div class="logo-wrapper">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <h1 class="brand-title">Pusat Layanan Administrasi Surat</h1>
                    <p class="brand-subtitle">Politeknik Pajajaran</p>
                    <p class="brand-description">
                        Sistem pengelolaan surat menyurat digital untuk lingkungan Politeknik Pajajaran.
                    </p>
                </div>
                <p class="brand-copyright">&copy; 2026 Politeknik Pajajaran</p>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-right">
                <div class="form-wrapper">
                    <div class="form-header">
                        <h2>Selamat Datang</h2>
                        <p>Silakan masuk ke akun Anda</p>
                    </div>

                    <form @submit.prevent="handleLogin" class="login-form">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <div class="input-wrapper">
                                <span class="input-icon material-symbols-outlined">mail</span>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-input"
                                    placeholder="nama@sisurat.com"
                                    required
                                    autofocus
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kata Sandi</label>
                            <div class="input-wrapper">
                                <span class="input-icon material-symbols-outlined">lock</span>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="form-input"
                                    placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                                    required
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-primary login-btn"
                            :disabled="loading"
                        >
                            <span
                                v-if="loading"
                                class="material-symbols-outlined animate-spin"
                                >progress_activity</span
                            >
                            <span>{{ loading ? "Memproses..." : "Masuk Sekarang" }}</span>
                            <span v-if="!loading" class="material-symbols-outlined">login</span>
                        </button>
                    </form>

                    <div class="divider">
                        <span>Akses Cepat</span>
                    </div>

                    <router-link to="/templates" class="btn-outline template-link">
                        <span class="template-link-left">
                            <span class="material-symbols-outlined">description</span>
                            Lihat Template Surat
                        </span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from "vue";
const showFlash = inject("showFlash");
import { useRouter } from "vue-router";
import axios from "axios";
import { resetUserCache } from "../../router";

const router = useRouter();
const updateUser = inject("updateUser");
const form = ref({ email: "", password: "" });
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post("/api/login", {
            email: form.value.email,
            password: form.value.password,
        });

        resetUserCache();
        await updateUser();
        showFlash("Login berhasil!");
        setTimeout(() => {
            router.push(response.data.redirect || "/dashboard");
        }, 500);
    } catch (e) {
        showFlash(e.response?.data?.message || "Terjadi kesalahan.", "error");
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.login-container {
    display: flex;
    width: 100%;
    min-height: 100vh;
}

/* Left Side - Branding */
.login-left {
    width: 45%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent-indigo) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.login-left::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 30% 50%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
    pointer-events: none;
}

.branding-content {
    text-align: center;
    z-index: 1;
    max-width: 28rem;
}

.logo-wrapper {
    width: 5rem;
    height: 5rem;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    color: white;
    font-size: 2.5rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.brand-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: white;
    line-height: 1.3;
    margin-bottom: 0.75rem;
    letter-spacing: -0.02em;
}

.brand-subtitle {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 1.5rem;
}

.brand-description {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.75);
    line-height: 1.7;
    font-weight: 400;
}

.brand-copyright {
    position: absolute;
    bottom: 2rem;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.5);
    font-weight: 500;
    z-index: 1;
}

/* Right Side - Form */
.login-right {
    width: 55%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
}

.form-wrapper {
    width: 100%;
    max-width: 28rem;
}

.form-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.form-header h2 {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.02em;
    margin-bottom: 0.5rem;
}

.form-header p {
    color: var(--slate-500);
    font-weight: 500;
    font-size: 0.95rem;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--slate-700);
    margin-left: 0.25rem;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--slate-400);
    pointer-events: none;
}

.login-btn {
    width: 100%;
    margin-top: 0.5rem;
}

/* Divider */
.divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 2rem 0;
}

.divider::before,
.divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: var(--slate-200);
}

.divider span {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Template Link */
.template-link {
    text-decoration: none;
}

.template-link-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .login-container {
        flex-direction: column !important;
    }

    .login-left {
        width: 100% !important;
        padding: 2.5rem 1.5rem;
        min-height: auto;
    }

    .brand-copyright {
        position: static;
        margin-top: 1.5rem;
    }

    .login-right {
        width: 100% !important;
        padding: 2rem 1.5rem;
    }

    .brand-title {
        font-size: 1.5rem;
    }
}
</style>
