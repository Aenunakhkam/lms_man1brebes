<template>
    <v-container fluid class="pa-6">
        <!-- Welcome Section -->
        <v-row class="mb-6">
            <v-col cols="12">
                <v-card 
                    class="pa-6" 
                    :class="theme.global.current.value.dark ? 'bg-slate-800 text-white border-card-dark' : 'gradient-card text-white'" 
                    rounded="xl" 
                    elevation="2"
                >
                    <v-row align="center">
                        <v-col cols="12" md="8">
                            <h1 class="text-h4 font-weight-bold mb-2">
                                Selamat Datang, {{ user?.name }}! 👋
                            </h1>
                            <p class="text-h6 mb-0 opacity-90">
                                Kelola sistem LMS MAN 1 Brebes dengan mudah
                            </p>
                        </v-col>
                        <v-col cols="12" md="4" class="text-right">
                        </v-col>
                    </v-row>
                </v-card>
            </v-col>
        </v-row>

        <!-- Stats Cards -->
        <v-row v-if="loading">
            <v-col cols="12" md="3" v-for="i in 4" :key="i">
                <v-skeleton-loader type="card" class="rounded-xl"></v-skeleton-loader>
            </v-col>
        </v-row>
        <v-row v-else>
            <!-- Students Count -->
            <v-col cols="12" md="3">
                <v-card class="pa-6 elevation-1 border-card" rounded="xl" elevation="2">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Total Siswa</div>
                            <div class="text-h4 font-weight-black text-primary">{{ stats.students_count || 0 }}</div>
                        </div>
                        <v-avatar size="60" color="blue-lighten-5">
                            <v-icon size="30" color="primary">mdi-account-group</v-icon>
                        </v-avatar>
                    </div>
                    <div class="text-caption text-grey-darken-1">Peningkatan aktifitas belajar</div>
                </v-card>
            </v-col>

            <!-- Teachers Count -->
            <v-col cols="12" md="3">
                <v-card class="pa-6 elevation-1 border-card" rounded="xl" elevation="2">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Tenaga Pengajar</div>
                            <div class="text-h4 font-weight-black text-success">{{ stats.teachers_count || 0 }}</div>
                        </div>
                        <v-avatar size="60" color="green-lighten-5">
                            <v-icon size="30" color="success">mdi-account-tie</v-icon>
                        </v-avatar>
                    </div>
                    <div class="text-caption text-grey-darken-1">Guru profesional terdaftar</div>
                </v-card>
            </v-col>

            <!-- Subjects Count -->
            <v-col cols="12" md="3">
                <v-card class="pa-6 elevation-1 border-card" rounded="xl" elevation="2">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Mata Pelajaran</div>
                            <div class="text-h4 font-weight-black text-warning">{{ stats.subjects_count || 0 }}</div>
                        </div>
                        <v-avatar size="60" color="amber-lighten-5">
                            <v-icon size="30" color="warning">mdi-book-open-variant</v-icon>
                        </v-avatar>
                    </div>
                    <div class="text-caption text-grey-darken-1">Kurikulum aktif semester ini</div>
                </v-card>
            </v-col>

            <!-- Classes Count -->
            <v-col cols="12" md="3">
                <v-card class="pa-6 elevation-1 border-card" rounded="xl" elevation="2">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Total Kelas</div>
                            <div class="text-h4 font-weight-black text-info">{{ stats.classes_count || 0 }}</div>
                        </div>
                        <v-avatar size="60" color="cyan-lighten-5">
                            <v-icon size="30" color="info">mdi-google-classroom</v-icon>
                        </v-avatar>
                    </div>
                    <div class="text-caption text-grey-darken-1">Ruang kelas fisik & daring</div>
                </v-card>
            </v-col>
        </v-row>

        <!-- New Sections: Class Stats and Announcements -->
        <v-row v-if="!loading" class="mt-6">
            <!-- Left: Class Stats -->
            <v-col cols="12" md="7">
                <v-card class="pa-6 border-card h-100" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-6 d-flex align-center">
                        <v-icon color="primary" class="mr-3">mdi-chart-bar</v-icon>
                        Jumlah Siswa per Kelas
                    </h3>
                    <div v-if="stats.students_per_class && stats.students_per_class.length">
                        <div v-for="(item, index) in stats.students_per_class" :key="index" class="mb-4">
                            <div class="d-flex justify-space-between mb-1">
                                <span class="font-weight-medium">{{ item.name }}</span>
                                <span class="text-primary font-weight-bold">{{ item.count }} Siswa</span>
                            </div>
                            <v-progress-linear
                                :model-value="stats.students_count > 0 ? (item.count / stats.students_count) * 100 : 0"
                                color="primary"
                                height="8"
                                rounded
                            ></v-progress-linear>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-grey">
                        Tidak ada data statistik kelas
                    </div>
                </v-card>
            </v-col>

            <!-- Right: Announcements -->
            <v-col cols="12" md="5">
                <v-card class="pa-6 border-card h-100" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-6 d-flex align-center">
                        <v-icon color="warning" class="mr-3">mdi-bullhorn</v-icon>
                        Pengumuman Terbaru
                    </h3>
                    <v-list v-if="stats.latest_announcements && stats.latest_announcements.length">
                        <v-list-item
                            v-for="announcement in stats.latest_announcements"
                            :key="announcement.id"
                            class="px-0 mb-3 border-b pb-3"
                        >
                            <template v-slot:prepend>
                                <v-avatar color="amber-lighten-4" class="mr-3">
                                    <v-icon color="warning" size="small">mdi-information</v-icon>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="font-weight-bold text-body-1">
                                {{ announcement.title }}
                            </v-list-item-title>
                            <v-list-item-subtitle class="mt-1">
                                <span class="text-caption text-grey">
                                    {{ formatDate(announcement.created_at) }} • {{ announcement.target_role.toUpperCase() }}
                                </span>
                            </v-list-item-subtitle>
                            <div class="text-body-2 text-grey-darken-1 mt-2 text-truncate">{{ announcement.content }}</div>
                        </v-list-item>
                    </v-list>
                    <div v-else class="text-center py-12 text-grey">
                        Belum ada pengumuman hari ini
                    </div>
                    <v-btn
                        variant="text"
                        color="primary"
                        block
                        class="mt-4 text-none"
                        to="/admin/announcements"
                    >
                        Lihat Seluruh Pengumuman
                    </v-btn>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTheme } from 'vuetify';
import axios from 'axios';

const theme = useTheme();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const stats = ref({});
const loading = ref(true);

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/admin/dashboard-stats');
        if (response.data.success) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching admin stats:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

onMounted(() => {
    fetchStats();
});
</script>

<style scoped>
.gradient-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-slate-800 {
    background-color: #1e293b !important;
}

.border-card-dark {
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
