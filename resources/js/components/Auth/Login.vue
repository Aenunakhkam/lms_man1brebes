<template>
    <v-app theme="light">
        <v-main class="login-page">
            <v-container fluid class="fill-height pa-0">
                <v-row class="fill-height ma-0">
                    <!-- Left Side - School Image & Abstract Typography -->
                    <v-col cols="12" md="7" class="pa-0 d-none d-md-block position-relative overflow-hidden">
                        <div class="image-section h-100">
                            <div class="overlay"></div>
                            <img :src="Laravel?.assetUrl + 'images/school-building.png'" alt="MAN 1 Brebes" class="school-image">
                            
                            <!-- Abstract Typography Layer -->
                            <div class="abstract-bg-text">LMS</div>
                            <div class="abstract-bg-text secondary">MAN 1</div>
                            
                            <div class="image-content-editorial">
                                <div class="accent-line mb-6"></div>
                                <h2 class="text-h4 font-weight-thin text-white mb-2 opacity-80" style="letter-spacing: 2px;">Selamat Datang di</h2>
                                <h1 class="text-h1 font-weight-black text-white mb-6 uppercase" style="line-height: 1;">
                                    {{ appSettings.app_name?.split(' ').slice(0, 1).join(' ') || 'LMS' }}<br>{{ appSettings.school_name || 'MAN 1 BREBES' }}
                                </h1>
                                <p class="text-h6 text-white font-weight-light opacity-70" style="max-width: 450px; line-height: 1.6;">
                                    Transformasi digital pendidikan di {{ appSettings.school_name || 'MAN 1 Brebes' }} untuk masa depan yang lebih gemilang.
                                </p>
                            </div>
                        </div>
                    </v-col>

                    <!-- Right Side - Proportional Login Form -->
                    <v-col cols="12" md="5" class="d-flex align-center justify-center pa-12 pa-lg-16 bg-white">
                        <div class="login-form-container-pro" style="width: 100%; max-width: 420px;">
                            <div class="text-left mb-12">
                                <div class="d-flex align-center mb-8">
                                     <v-avatar size="48" rounded="0" class="mr-4">
                                        <v-img v-if="appSettings.school_logo" :src="Laravel?.assetUrl + 'storage/' + appSettings.school_logo" contain></v-img>
                                        <v-img v-else :src="Laravel?.assetUrl + 'images/logo.png'" contain></v-img>
                                    </v-avatar>
                                    <div class="brand-divider"></div>
                                    <div class="ml-4 overflow-hidden">
                                        <div class="text-h6 font-weight-black uppercase truncate" style="line-height: 1; max-width: 250px;">{{ appSettings.school_name || 'MAN 1 BREBES' }}</div>
                                        <div class="text-caption font-weight-bold text-grey uppercase" style="letter-spacing: 1px;">{{ appSettings.app_name || 'E-LEARNING' }}</div>
                                    </div>
                                </div>
                                <h2 class="text-h4 font-weight-black text-black mb-2">LOGIN</h2>
                                <p class="text-body-1 text-grey-darken-1">Masuk ke dashboard akademik Anda</p>
                            </div>

                            <v-card class="elevation-0" rounded="0">
                                <v-form @submit.prevent="handleLogin" ref="form">
                                    <div class="mb-8">
                                        <div class="text-caption font-weight-black text-black mb-3 text-uppercase" style="letter-spacing: 2px;">Identity / Email</div>
                                        <v-text-field
                                            v-model="email"
                                            placeholder="Masukkan Email"
                                            prepend-inner-icon="mdi-email-outline"
                                            variant="outlined"
                                            color="black"
                                            base-color="#333"
                                            :rules="[rules.required, rules.email]"
                                            :error-messages="errors.email"
                                            density="comfortable"
                                            class="sharp-input"
                                            rounded="0"
                                            persistent-placeholder
                                        ></v-text-field>
                                    </div>

                                    <div class="mb-10">
                                        <div class="d-flex justify-space-between align-center mb-3">
                                            <div class="text-caption font-weight-black text-black text-uppercase" style="letter-spacing: 2px;">Kata Sandi</div>
                                            <a href="#" class="text-caption text-black font-weight-black text-decoration-underline">Bantuan?</a>
                                        </div>
                                        <v-text-field
                                            v-model="password"
                                            placeholder="Masukkan Kata Sandi"
                                            prepend-inner-icon="mdi-lock-outline"
                                            :type="showPassword ? 'text' : 'password'"
                                            variant="outlined"
                                            color="black"
                                            base-color="#333"
                                            :rules="[rules.required]"
                                            :error-messages="errors.password"
                                            density="comfortable"
                                            class="sharp-input"
                                            rounded="0"
                                            persistent-placeholder
                                        >
                                            <template v-slot:append-inner>
                                                <v-btn icon variant="text" size="small" @click="showPassword = !showPassword">
                                                    <v-icon>{{ showPassword ? 'mdi-eye-off' : 'mdi-eye' }}</v-icon>
                                                </v-btn>
                                            </template>
                                        </v-text-field>
                                    </div>

                                    <v-btn
                                        type="submit"
                                        color="black"
                                        size="x-large"
                                        block
                                        :loading="loading"
                                        class="mb-8 text-none font-weight-black sharp-btn"
                                        rounded="0"
                                        height="64"
                                    >
                                        MASUK KE SISTEM
                                        <v-icon right size="18" class="ml-3">mdi-arrow-right</v-icon>
                                    </v-btn>

                                    <v-alert
                                        v-if="errorMessage"
                                        type="error"
                                        variant="flat"
                                        rounded="0"
                                        class="mb-0 font-weight-bold"
                                        style="border: 1px solid #ff5252;"
                                    >
                                        {{ errorMessage }}
                                    </v-alert>
                                </v-form>
                            </v-card>
                            
                            <div class="mt-12 pt-8 border-t text-center">
                                <v-btn variant="text" color="grey-darken-3" class="text-none font-weight-bold" to="/" size="small">
                                    <v-icon left size="small" class="mr-2">mdi-chevron-left</v-icon>
                                    Kembali ke Portal Utama
                                </v-btn>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from 'vuetify';
import axios from 'axios';

const theme = useTheme();

const router = useRouter();
const form = ref(null);
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const appSettings = ref({});
const Laravel = window.Laravel;

const fetchSettings = async () => {
    try {
        const response = await axios.get('/api/settings');
        if (response.data.success) {
            appSettings.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching settings:', error);
    }
};

onMounted(() => {
    // Force light theme on login page
    theme.global.name.value = 'light';
    fetchSettings();
});
const errors = ref({
    email: [],
    password: []
});

const rules = {
    required: value => !!value || 'Wajib diisi',
    email: value => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(value) || 'Format email tidak valid';
    }
};

const handleLogin = async () => {
    const { valid } = await form.value.validate();
    if (!valid) return;

    loading.value = true;
    errorMessage.value = '';
    errors.value = { email: [], password: [] };

    try {
        const response = await axios.post('/api/login', {
            email: email.value,
            password: password.value
        });

        if (response.data.success) {
            localStorage.setItem('token', response.data.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.data.user));
            localStorage.setItem('role', response.data.data.user.role);

            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`;

            const role = response.data.data.user.role;
            if (role === 'admin') router.push('/admin');
            else if (role === 'guru') router.push('/guru');
            else if (role === 'siswa') router.push('/siswa');
        }
    } catch (error) {
        if (error.response && error.response.data) {
            if (error.response.data.errors) errors.value = error.response.data.errors;
            else errorMessage.value = error.response.data.message || 'Login gagal.';
        } else {
            errorMessage.value = 'Gangguan jaringan. Coba lagi nanti.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-page {
    background: white;
}

.image-section {
    position: relative;
    overflow: hidden;
}

.school-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1.1);
    filter: brightness(0.7) contrast(1.1);
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 100%),
                linear-gradient(to top, rgba(15, 77, 25, 0.4) 0%, transparent 100%);
    z-index: 1;
}

/* Abstract Typography */
.abstract-bg-text {
    position: absolute;
    top: 10%;
    left: -5%;
    font-size: 25vw;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    line-height: 0.8;
    user-select: none;
    pointer-events: none;
    z-index: 1;
}

.abstract-bg-text.secondary {
    top: auto;
    bottom: 5%;
    right: -5%;
    left: auto;
    font-size: 15vw;
    color: rgba(255, 255, 255, 0.02);
}

.image-content-editorial {
    position: absolute;
    bottom: 80px;
    left: 80px;
    z-index: 2;
    padding-right: 40px;
}

.accent-line {
    width: 60px;
    height: 4px;
    background: #4caf50;
}

.brand-divider {
    width: 2px;
    height: 32px;
    background: #e0e0e0;
}

/* Form Styles */
.sharp-input :deep(.v-field__outline) {
    --v-field-border-width: 1px !important;
    --v-field-border-opacity: 1 !important;
}

.sharp-input :deep(.v-field--focused .v-field__outline) {
    --v-field-border-width: 2px !important;
}

.sharp-btn {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.sharp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
}

.border-t {
    border-top: 1px solid #f0f0f0;
}

@media (max-width: 1264px) {
    .image-content-editorial {
        left: 40px;
        bottom: 40px;
    }
    .text-h1 {
        font-size: 4rem !important;
    }
}
</style>
