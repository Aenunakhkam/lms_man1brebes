<template>
    <v-container fluid class="pa-6">
        <div class="mb-6 d-flex align-center justify-space-between">
            <div>
                <h1 class="text-h4 font-weight-bold mb-1">Presensi</h1>
                <p class="text-subtitle-1 text-grey">Ekspor data presensi siswa berdasarkan kelas</p>
            </div>
        </div>

        <v-row>
            <v-col cols="12" md="6" lg="4">
                <v-card rounded="xl" elevation="2" class="pa-6 border-card">
                    <div class="d-flex align-center mb-6">
                        <v-avatar color="primary-lighten-4" class="mr-3" size="40">
                            <v-icon color="primary">mdi-filter-variant</v-icon>
                        </v-avatar>
                        <h3 class="text-h6 font-weight-bold">Filter Ekspor</h3>
                    </div>

                    <v-form v-model="filterValid">
                        <div class="mb-4">
                            <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Pilih Kelas</label>
                            <v-select
                                v-model="filters.class_id"
                                :items="classes"
                                item-title="name"
                                item-value="id"
                                placeholder="Semua Kelas (Opsional)"
                                variant="outlined"
                                rounded="lg"
                                density="comfortable"
                                prepend-inner-icon="mdi-google-classroom"
                                clearable
                            ></v-select>
                        </div>

                        <div class="mb-6">
                            <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Pilih Bulan (Opsional)</label>
                            <v-text-field
                                v-model="filters.month"
                                type="month"
                                variant="outlined"
                                rounded="lg"
                                density="comfortable"
                                prepend-inner-icon="mdi-calendar"
                                clearable
                            ></v-text-field>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <v-btn 
                                color="success" 
                                size="large" 
                                prepend-icon="mdi-file-excel" 
                                rounded="lg"
                                @click="exportData('excel')"
                                :loading="exportingExcel"
                                class="w-100 font-weight-bold shadow-1"
                            >
                                Ekspor Excel
                            </v-btn>
                            <v-btn 
                                color="error" 
                                size="large" 
                                prepend-icon="mdi-file-pdf-box" 
                                rounded="lg"
                                @click="exportData('pdf')"
                                :loading="exportingPdf"
                                class="w-100 font-weight-bold mt-3 shadow-1"
                            >
                                Ekspor PDF Resmi
                            </v-btn>
                        </div>
                    </v-form>
                </v-card>
            </v-col>
            <v-col cols="12" md="6" lg="8">
                 <v-card rounded="xl" elevation="0" class="pa-10 bg-grey-lighten-4 h-100 d-flex flex-column align-center justify-center border-dashed">
                    <v-icon size="80" color="grey" class="mb-4">mdi-printer-outline</v-icon>
                    <h3 class="text-h5 font-weight-bold mb-2">Cetak Dokumen Resmi</h3>
                    <p class="text-grey text-center max-w-sm">
                        Laporan presensi yang diekspor dilengkapi dengan format resmi instansi. Semua data ditarik secara langsung dari pengisian harian para guru.
                    </p>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const { showError } = useAlert();

const classes = ref([]);
const filterValid = ref(false);
const exportingExcel = ref(false);
const exportingPdf = ref(false);

const filters = ref({
    class_id: null,
    month: null
});

const loadDropdownData = async () => {
    try {
        const res = await axios.get('/api/admin/classes');
        if (res.data.success) classes.value = res.data.data;
    } catch (error) {
        showError('Gagal memuat data kelas');
    }
};

onMounted(() => {
    loadDropdownData();
});

const exportData = (format) => {
    let url = `/api/admin/attendance/export/${format}?token=${localStorage.getItem('token')}`;
    if (filters.value.class_id) url += `&class_id=${filters.value.class_id}`;
    if (filters.value.month) url += `&month=${filters.value.month}`;

    if (format === 'excel') exportingExcel.value = true;
    else exportingPdf.value = true;

    // Trigger download using window.location
    window.location.href = url;

    setTimeout(() => {
        exportingExcel.value = false;
        exportingPdf.value = false;
    }, 2000);
};
</script>

<style scoped>
.border-card {
    border: 1px solid #e0e0e0;
}
.border-dashed {
    border: 2px dashed #bdbdbd !important;
}
.max-w-sm {
    max-width: 400px;
}
.shadow-1 {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}
</style>
