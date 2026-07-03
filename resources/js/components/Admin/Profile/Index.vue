<template>
    <v-container fluid class="pa-6">
        <v-row>
            <!-- Left Side: Profile Photo & Basic Info -->
            <v-col cols="12" md="4">
                <v-card class="text-center pa-6" rounded="xl" elevation="2">
                    <div class="position-relative d-inline-block mb-4">
                        <v-avatar size="150" color="primary" class="elevation-3">
                            <v-img v-if="user?.photo" :src="Laravel.assetUrl + 'storage/' + user.photo" cover></v-img>
                            <span v-else class="text-white text-uppercase font-weight-bold" style="font-size: 64px;">{{ user?.name ? user.name.charAt(0) : 'A' }}</span>
                        </v-avatar>
                        <v-btn
                            icon="mdi-camera"
                            color="primary"
                            size="small"
                            class="position-absolute bottom-0 right-0 elevation-4"
                            @click="$refs.photoInput.click()"
                            :loading="photoLoading"
                        ></v-btn>
                        <input type="file" ref="photoInput" hidden accept="image/*" @change="handlePhotoUpload">
                    </div>

                    <h2 class="text-h5 font-weight-bold mb-1">{{ user?.name }}</h2>
                    <p class="text-subtitle-1 text-grey mb-4">{{ user?.role?.display_name || user?.role?.name }}</p>
                    
                    <v-divider class="mb-4"></v-divider>
                    
                    <div class="d-flex justify-center gap-2">
                        <v-btn
                            v-if="user?.photo"
                            color="error"
                            variant="outlined"
                            prepend-icon="mdi-delete"
                            size="small"
                            class="text-none"
                            @click="removePhoto"
                        >
                            Hapus Foto
                        </v-btn>
                    </div>
                </v-card>
            </v-col>

            <!-- Right Side: Forms -->
            <v-col cols="12" md="8">
                <!-- Biodata Form -->
                <v-card class="pa-6 mb-6" rounded="xl" elevation="2">
                    <div class="d-flex align-center mb-6">
                        <v-icon color="primary" class="mr-3">mdi-account-details</v-icon>
                        <h3 class="text-h6 font-weight-bold">Informasi Pribadi</h3>
                    </div>

                    <v-form @submit.prevent="updateProfile">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profileForm.name"
                                    label="Nama Lengkap"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-account"
                                    placeholder="Masukkan nama lengkap"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profileForm.email"
                                    label="Email"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-email"
                                    placeholder="Masukkan alamat email"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profileForm.phone"
                                    label="Nomor HP"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-phone"
                                    placeholder="Contoh: 08123456789"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6" v-if="user?.role?.name === 'siswa'">
                                <v-text-field
                                    v-model="profileForm.nis"
                                    label="NIS"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-card-account-details"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6" v-if="user?.role?.name === 'guru'">
                                <v-text-field
                                    v-model="profileForm.nip"
                                    label="NIP"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-card-account-details"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="profileForm.address"
                                    label="Alamat"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-map-marker"
                                    rows="3"
                                ></v-textarea>
                            </v-col>
                        </v-row>
                        <div class="text-right">
                            <v-btn color="primary" size="large" type="submit" :loading="profileLoading" class="text-none">
                                Simpan Perubahan
                            </v-btn>
                        </div>
                    </v-form>
                </v-card>

                <!-- Change Password Form -->
                <v-card class="pa-6" rounded="xl" elevation="2">
                    <div class="d-flex align-center mb-6">
                        <v-icon color="warning" class="mr-3">mdi-lock-reset</v-icon>
                        <h3 class="text-h6 font-weight-bold">Ubah Kata Sandi</h3>
                    </div>

                    <v-form @submit.prevent="updatePassword">
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="passwordForm.current_password"
                                    label="Kata Sandi Sekarang"
                                    :type="showPass1 ? 'text' : 'password'"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-lock-outline"
                                    :append-inner-icon="showPass1 ? 'mdi-eye' : 'mdi-eye-off'"
                                    @click:append-inner="showPass1 = !showPass1"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="passwordForm.password"
                                    label="Kata Sandi Baru"
                                    :type="showPass2 ? 'text' : 'password'"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-lock-plus-outline"
                                    :append-inner-icon="showPass2 ? 'mdi-eye' : 'mdi-eye-off'"
                                    @click:append-inner="showPass2 = !showPass2"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="passwordForm.password_confirmation"
                                    label="Konfirmasi Kata Sandi Baru"
                                    :type="showPass3 ? 'text' : 'password'"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-lock-check-outline"
                                    :append-inner-icon="showPass3 ? 'mdi-eye' : 'mdi-eye-off'"
                                    @click:append-inner="showPass3 = !showPass3"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <div class="text-right">
                            <v-btn color="warning" size="large" type="submit" :loading="passwordLoading" class="text-none">
                                Ganti Password
                            </v-btn>
                        </div>
                    </v-form>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
const Laravel = window.Laravel;
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const { showSuccess, showError } = useAlert();

const user = ref(null);
const photoInput = ref(null);
const photoLoading = ref(false);
const profileLoading = ref(false);
const passwordLoading = ref(false);

const showPass1 = ref(false);
const showPass2 = ref(false);
const showPass3 = ref(false);

const profileForm = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    nis: '',
    nip: ''
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: ''
});

const loadProfile = async () => {
    try {
        const response = await axios.get('/api/profile');
        user.value = response.data.data;
        Object.keys(profileForm.value).forEach(key => {
            profileForm.value[key] = user.value[key] || '';
        });
    } catch (error) {
        showError('Gagal memuat data profil');
    }
};

const handlePhotoUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('photo', file);

    photoLoading.value = true;
    try {
        const response = await axios.post('/api/profile/photo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showSuccess('Berhasil!', 'Foto profil diperbarui');
        await loadProfile();
        // Update localStorage
        const storedUser = JSON.parse(localStorage.getItem('user'));
        storedUser.photo = response.data.photo_url;
        localStorage.setItem('user', JSON.stringify(storedUser));
    } catch (error) {
        showError(error.response?.data?.message || 'Gagal mengunggah foto');
    } finally {
        photoLoading.value = false;
        event.target.value = '';
    }
};

const removePhoto = async () => {
    try {
        await axios.delete('/api/profile/photo');
        showSuccess('Berhasil!', 'Foto profil dihapus');
        await loadProfile();
        const storedUser = JSON.parse(localStorage.getItem('user'));
        storedUser.photo = null;
        localStorage.setItem('user', JSON.stringify(storedUser));
    } catch (error) {
        showError('Gagal menghapus foto');
    }
};

const updateProfile = async () => {
    profileLoading.value = true;
    try {
        const response = await axios.post('/api/profile', profileForm.value);
        showSuccess('Berhasil!', 'Profil diperbarui');
        user.value = response.data.data;
        localStorage.setItem('user', JSON.stringify(user.value));
    } catch (error) {
        showError(error.response?.data?.message || 'Gagal memperbarui profil');
    } finally {
        profileLoading.value = false;
    }
};

const updatePassword = async () => {
    passwordLoading.value = true;
    try {
        await axios.post('/api/profile/password', passwordForm.value);
        showSuccess('Berhasil!', 'Password berhasil diubah');
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: ''
        };
    } catch (error) {
        showError(error.response?.data?.message || 'Gagal mengubah password');
    } finally {
        passwordLoading.value = false;
    }
};

onMounted(loadProfile);
</script>

<style scoped>
.right-0 { right: 0; }
.bottom-0 { bottom: 0; }
</style>
