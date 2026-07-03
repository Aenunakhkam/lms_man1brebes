<template>
    <v-container fluid class="pa-6">
        <v-row>
            <v-col cols="12">
                <v-card class="pa-6 mb-6" rounded="xl" elevation="2">
                    <div class="d-flex align-center mb-6">
                        <v-icon color="primary" size="large" class="mr-3">mdi-cog-box</v-icon>
                        <div>
                            <h2 class="text-h5 font-weight-bold">Pengaturan Aplikasi & Sekolah</h2>
                            <p class="text-subtitle-2 text-grey">Kelola identitas instansi dan informasi sistem</p>
                        </div>
                    </div>

                    <v-form @submit.prevent="saveSettings">
                        <v-row>
                            <!-- Identitas Sekolah Section -->
                            <v-col cols="12">
                                <h3 class="text-h6 font-weight-bold mb-4 border-b pb-2">Identitas Sekolah</h3>
                            </v-col>
                            
                            <v-col cols="12" md="4" class="text-center">
                                <div class="text-subtitle-1 mb-3">Logo Sekolah</div>
                                <div class="position-relative d-inline-block mb-4">
                                    <v-avatar size="150" color="grey-lighten-4" rounded="lg" class="elevation-1 border">
                                        <v-img v-if="logoPreview" :src="logoPreview" contain></v-img>
                                        <v-img v-else-if="settings.school_logo" :src="Laravel.assetUrl + 'storage/' + settings.school_logo" contain></v-img>
                                        <v-icon v-else size="80" color="grey">mdi-image-plus</v-icon>
                                    </v-avatar>
                                    <v-btn
                                        icon="mdi-camera"
                                        color="primary"
                                        size="small"
                                        class="position-absolute bottom-0 right-0 elevation-4"
                                        @click="$refs.logoInput.click()"
                                    ></v-btn>
                                    <input type="file" ref="logoInput" hidden accept="image/*" @change="onLogoSelected">
                                </div>
                                <div v-if="settings.school_logo" class="mt-2">
                                    <v-btn color="error" variant="text" size="small" @click="removeLogo" prepend-icon="mdi-delete" class="text-none">
                                        Hapus Logo
                                    </v-btn>
                                </div>
                            </v-col>

                            <v-col cols="12" md="8">
                                <v-row>
                                    <v-col cols="12">
                                        <v-text-field
                                            v-model="settings.school_name"
                                            label="Nama Sekolah"
                                            variant="outlined"
                                            prepend-inner-icon="mdi-school"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field
                                            v-model="settings.school_phone"
                                            label="Nomor HP Sekolah"
                                            variant="outlined"
                                            prepend-inner-icon="mdi-phone"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field
                                            v-model="settings.school_email"
                                            label="Email Sekolah"
                                            variant="outlined"
                                            prepend-inner-icon="mdi-email"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-textarea
                                            v-model="settings.school_address"
                                            label="Alamat Sekolah"
                                            variant="outlined"
                                            prepend-inner-icon="mdi-map-marker"
                                            rows="3"
                                        ></v-textarea>
                                    </v-col>
                                </v-row>
                            </v-col>

                            <!-- Informasi Aplikasi Section -->
                            <v-col cols="12" class="mt-6">
                                <h3 class="text-h6 font-weight-bold mb-4 border-b pb-2">Informasi Aplikasi</h3>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="settings.app_name"
                                    label="Nama Aplikasi"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-apps"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="settings.app_developer"
                                    label="Nama Pengembang"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-code-tags"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="settings.app_version"
                                    label="Versi Aplikasi"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-information-outline"
                                ></v-text-field>
                            </v-col>
                        </v-row>

                        <div class="mt-10 d-flex justify-end gap-3">
                            <v-btn color="grey-lighten-3" size="large" class="text-none">Batal</v-btn>
                            <v-btn color="primary" size="large" type="submit" :loading="loading" class="text-none px-10">
                                Simpan Pengaturan
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
const loading = ref(false);
const logoInput = ref(null);
const logoFile = ref(null);
const logoPreview = ref(null);

const settings = ref({
    school_name: '',
    school_logo: '',
    school_address: '',
    school_phone: '',
    school_email: '',
    app_name: '',
    app_developer: '',
    app_version: ''
});

const loadSettings = async () => {
    try {
        const response = await axios.get('/api/admin/settings');
        if (response.data.success) {
            settings.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat pengaturan');
    }
};

const onLogoSelected = (event) => {
    logoFile.value = event.target.files[0];
    if (logoFile.value) {
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(logoFile.value);
    }
};

const removeLogo = async () => {
    try {
        await axios.delete('/api/admin/settings/logo');
        settings.value.school_logo = null;
        logoFile.value = null;
        logoPreview.value = null;
        showSuccess('Berhasil!', 'Logo berhasil dihapus');
    } catch (error) {
        showError('Gagal menghapus logo');
    }
};

const saveSettings = async () => {
    loading.value = true;
    try {
        const formData = new FormData();
        Object.keys(settings.value).forEach(key => {
            if (key !== 'school_logo' && settings.value[key] !== null) {
                formData.append(key, settings.value[key]);
            }
        });

        if (logoFile.value) {
            formData.append('school_logo', logoFile.value);
        }

        const response = await axios.post('/api/admin/settings', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        showSuccess('Berhasil!', response.data.message);
        settings.value = response.data.data;
        logoFile.value = null;
        logoPreview.value = null;
    } catch (error) {
        showError(error.response?.data?.message || 'Gagal menyimpan pengaturan');
    } finally {
        loading.value = false;
    }
};

onMounted(loadSettings);
</script>

<style scoped>
.right-0 { right: 0; }
.bottom-0 { bottom: 0; }
.border-b { border-bottom: 1px solid #eee; }
</style>
