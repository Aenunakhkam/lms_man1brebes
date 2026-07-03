<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Riwayat Presensi</h1>
            <p class="text-subtitle-1 text-grey">Data kehadiran Anda selama satu semester</p>
        </div>

        <v-row>
            <v-col cols="12" md="4">
                <v-card rounded="xl" elevation="2" class="pa-6 mb-6">
                    <h3 class="text-h6 font-weight-bold mb-4">Ringkasan Kehadiran</h3>
                    <v-row dense>
                        <v-col cols="6">
                            <v-card flat color="success-lighten-5" class="pa-4 text-center" rounded="lg">
                                <div class="text-h4 font-weight-bold text-success">{{ summary.hadir || 0 }}</div>
                                <div class="text-caption font-weight-bold text-success">HADIR</div>
                            </v-card>
                        </v-col>
                        <v-col cols="6">
                            <v-card flat color="info-lighten-5" class="pa-4 text-center" rounded="lg">
                                <div class="text-h4 font-weight-bold text-info">{{ summary.izin || 0 }}</div>
                                <div class="text-caption font-weight-bold text-info">IZIN</div>
                            </v-card>
                        </v-col>
                        <v-col cols="6">
                            <v-card flat color="warning-lighten-5" class="pa-4 text-center" rounded="lg">
                                <div class="text-h4 font-weight-bold text-warning">{{ summary.sakit || 0 }}</div>
                                <div class="text-caption font-weight-bold text-warning">SAKIT</div>
                            </v-card>
                        </v-col>
                        <v-col cols="6">
                            <v-card flat color="error-lighten-5" class="pa-4 text-center" rounded="lg">
                                <div class="text-h4 font-weight-bold text-error">{{ summary.alpa || 0 }}</div>
                                <div class="text-caption font-weight-bold text-error">ALPA</div>
                            </v-card>
                        </v-col>
                    </v-row>
                    <v-progress-linear
                        :model-value="attendancePercentage"
                        color="success"
                        height="10"
                        rounded
                        class="mt-6"
                    ></v-progress-linear>
                    <div class="d-flex justify-space-between mt-2">
                        <span class="text-caption text-grey">Presentase Kehadiran</span>
                        <span class="text-caption font-weight-bold">{{ attendancePercentage }}%</span>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card rounded="xl" elevation="2">
                    <v-card-title class="pa-6 d-flex justify-space-between align-center">
                        <span class="text-h6 font-weight-bold">Log Kehadiran</span>
                        <v-btn-toggle v-model="filterMonth" density="compact" rounded="lg" color="primary" mandatory>
                            <v-btn value="all">Semua</v-btn>
                            <v-btn value="this_month">Bulan Ini</v-btn>
                        </v-btn-toggle>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-data-table
                        :headers="headers"
                        :items="filteredAttendance"
                        :loading="loading"
                        class="elevation-0"
                    >
                        <template v-slot:item.index="{ index }">
                            {{ index + 1 }}
                        </template>
                        <template v-slot:item.date="{ item }">
                            {{ formatDate(item.date) }}
                        </template>
                        <template v-slot:item.status="{ item }">
                            <v-chip
                                :color="getStatusColor(item.status)"
                                size="small"
                                rounded="lg"
                                class="text-uppercase font-weight-bold"
                            >
                                {{ item.status }}
                            </v-chip>
                        </template>
                        <template v-slot:no-data>
                            <div class="pa-12 text-center text-grey">
                                <v-icon size="64" class="mb-4">mdi-calendar-remove</v-icon>
                                <div>Belum ada histori presensi</div>
                            </div>
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const attendance = ref([]);
const summary = ref({});
const loading = ref(false);
const filterMonth = ref('all');

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Tanggal', key: 'date' },
    { title: 'Keterangan', key: 'subject.name' },
    { title: 'Status', key: 'status', align: 'center' },
];

const fetchAttendance = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/siswa/attendance');
        if (response.data.success) {
            attendance.value = response.data.data;
            summary.value = response.data.summary;
        }
    } catch (error) {
        console.error('Error fetching attendance:', error);
    } finally {
        loading.value = false;
    }
};

const attendancePercentage = computed(() => {
    if (!summary.value || !summary.value.total) return 0;
    return Math.round((summary.value.hadir / summary.value.total) * 100);
});

const filteredAttendance = computed(() => {
    if (filterMonth.value === 'all') return attendance.value;
    const now = new Date();
    return attendance.value.filter(item => {
        const itemDate = new Date(item.date);
        return itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear();
    });
});

const getStatusColor = (status) => {
    switch (status.toLowerCase()) {
        case 'hadir': return 'success';
        case 'izin': return 'info';
        case 'sakit': return 'warning';
        case 'alpa': return 'error';
        default: return 'grey';
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

onMounted(fetchAttendance);
</script>
