<template>
    <v-container fluid class="pa-6">
        <!-- Welcome Section -->
        <v-row class="mb-6">
            <v-col cols="12">
                <v-card 
                    class="pa-6" 
                    :class="theme.global.current.value.dark ? 'bg-slate-800 text-white border-card-dark' : 'gradient-card-guru text-white'" 
                    rounded="xl" 
                    elevation="2"
                >
                    <v-row align="center">
                        <v-col cols="12">
                            <h1 class="text-h4 font-weight-bold mb-2">
                                Selamat Datang, {{ user?.name }}! 📚
                            </h1>
                            <p class="text-h6 mb-0 opacity-90">
                                Kelola pembelajaran dengan mudah dan efektif
                            </p>
                        </v-col>
                    </v-row>
                </v-card>
            </v-col>
        </v-row>

        <!-- Stats Cards -->
        <v-row>
            <v-col cols="12" md="3">
                <v-card class="pa-6" rounded="xl" elevation="2" :loading="statsLoading">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Kelas Diampu</div>
                            <div class="text-h4 font-weight-bold text-success">{{ stats.classes }}</div>
                        </div>
                        <v-avatar size="60" color="success-lighten-4">
                            <v-icon size="30" color="success">mdi-google-classroom</v-icon>
                        </v-avatar>
                    </div>
                    <div class="d-flex align-center">
                        <v-icon size="small" color="success" class="mr-1">mdi-check-circle</v-icon>
                        <span class="text-caption text-success">Aktif mengajar</span>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="3">
                <v-card class="pa-6" rounded="xl" elevation="2" :loading="statsLoading">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Total Siswa</div>
                            <div class="text-h4 font-weight-bold text-primary">{{ stats.students }}</div>
                        </div>
                        <v-avatar size="60" color="primary-lighten-4">
                            <v-icon size="30" color="primary">mdi-account-group</v-icon>
                        </v-avatar>
                    </div>
                    <div class="d-flex align-center">
                        <v-icon size="small" color="grey" class="mr-1">mdi-account-multiple</v-icon>
                        <span class="text-caption text-grey">Di semua kelas</span>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="3">
                <v-card class="pa-6" rounded="xl" elevation="2" :loading="statsLoading">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Tugas Dibuat</div>
                            <div class="text-h4 font-weight-bold text-warning">{{ stats.assignments }}</div>
                        </div>
                        <v-avatar size="60" color="warning-lighten-4">
                            <v-icon size="30" color="warning">mdi-clipboard-text</v-icon>
                        </v-avatar>
                    </div>
                    <div class="d-flex align-center">
                        <v-icon size="small" color="warning" class="mr-1">mdi-clock-outline</v-icon>
                        <span class="text-caption text-warning">Sedang berlangsung</span>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="3">
                <v-card class="pa-6" rounded="xl" elevation="2" :loading="statsLoading">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <div class="text-caption text-grey mb-1">Materi Upload</div>
                            <div class="text-h4 font-weight-bold text-info">{{ stats.materials }}</div>
                        </div>
                        <v-avatar size="60" color="info-lighten-4">
                            <v-icon size="30" color="info">mdi-book-open-page-variant</v-icon>
                        </v-avatar>
                    </div>
                    <div class="d-flex align-center">
                        <v-icon size="small" color="info" class="mr-1">mdi-file-document</v-icon>
                        <span class="text-caption text-info">Total materi</span>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Quick Actions & Recent Activity -->
        <v-row class="mt-4">
            <v-col cols="12" md="8">
                <v-card class="pa-6" rounded="xl" elevation="2" :loading="classesLoading">
                    <div class="d-flex align-center justify-space-between mb-6">
                        <h3 class="text-h6 font-weight-bold">Kelas Yang Diampu</h3>
                        <v-btn to="/guru/classes" color="success" variant="text" size="small" class="text-none">
                            Lihat Semua
                        </v-btn>
                    </div>
                    
                    <v-row v-if="classes.length">
                        <v-col cols="12" md="6" v-for="cls in filteredClasses" :key="cls.id">
                            <v-card rounded="xl" elevation="1" class="classroom-card overflow-hidden">
                                <div class="pa-4 d-flex align-center">
                                    <v-avatar color="success-lighten-4" rounded="lg" size="48">
                                        <v-icon color="success">mdi-google-classroom</v-icon>
                                    </v-avatar>
                                    <div class="ml-4">
                                        <div class="text-subtitle-1 font-weight-bold truncate" style="max-width: 200px;">{{ cls.name }}</div>
                                        <div class="text-caption text-grey d-flex align-center">
                                            <v-icon size="12" class="mr-1">mdi-account-group</v-icon>
                                            {{ cls.students_count || 0 }} Siswa
                                            <v-divider vertical class="mx-2 my-1"></v-divider>
                                            {{ cls.department?.name || 'Umum' }}
                                        </div>
                                    </div>
                                </div>
                                <v-divider class="border-opacity-25"></v-divider>
                                <v-card-actions class="pa-2">
                                    <v-btn 
                                        variant="text" 
                                        color="success" 
                                        block 
                                        rounded="lg"
                                        class="text-none font-weight-bold"
                                        prepend-icon="mdi-calendar-check"
                                        :to="`/guru/attendance?class_id=${cls.id}`"
                                    >
                                        Input Presensi
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-col>
                    </v-row>
                    <div v-else-if="!classesLoading" class="text-center py-8 text-grey text-caption">
                        Belum ada kelas yang diampu
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="4">
                <v-card class="pa-6 border-card mb-6" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-6 d-flex align-center">
                        <v-icon color="primary" class="mr-3">mdi-account-multiple</v-icon>
                        Siswa Terbaru
                    </h3>
                    <v-list v-if="stats.recent_students && stats.recent_students.length">
                        <v-list-item
                            v-for="student in stats.recent_students"
                            :key="student.id"
                            class="px-0 mb-2"
                        >
                            <template v-slot:prepend>
                                <v-avatar color="primary-lighten-4" class="mr-3" size="36">
                                    <v-img v-if="student.photo" :src="Laravel.assetUrl + 'storage/' + student.photo"></v-img>
                                    <v-icon v-else color="primary" size="18">mdi-account</v-icon>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="font-weight-bold text-body-2">
                                {{ student.name }}
                            </v-list-item-title>
                            <v-list-item-subtitle class="text-caption">
                                NIS: {{ student.nis || '-' }}
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                    <div v-else class="text-center py-8 text-grey text-caption border-dashed rounded-lg">
                        Belum ada data siswa
                    </div>
                </v-card>

                <v-card class="pa-6 border-card" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-6 d-flex align-center">
                        <v-icon color="warning" class="mr-3">mdi-bullhorn</v-icon>
                        Pengumuman
                    </h3>
                    
                    <v-list v-if="loadingAnnouncements">
                        <v-list-item v-for="i in 3" :key="i" class="px-0 mb-3">
                            <v-skeleton-loader type="list-item-two-line"></v-skeleton-loader>
                        </v-list-item>
                    </v-list>

                    <v-list v-else-if="announcements && announcements.length">
                        <v-list-item
                            v-for="announcement in announcements"
                            :key="announcement.id"
                            class="px-0 mb-3 border-b pb-3"
                        >
                            <template v-slot:prepend>
                                <v-avatar color="amber-lighten-4" class="mr-3" size="32">
                                    <v-icon color="warning" size="16">mdi-information</v-icon>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="font-weight-bold text-body-2">
                                {{ announcement.title }}
                            </v-list-item-title>
                            <v-list-item-subtitle class="text-caption mt-1">
                                {{ formatDate(announcement.created_at) }}
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                    <div v-else class="text-center py-8 text-grey text-caption">
                        <v-icon size="40" class="mb-2 opacity-20">mdi-bullhorn-variant-outline</v-icon>
                        <div>Belum ada pengumuman baru</div>
                    </div>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
const Laravel = window.Laravel;
import { ref, onMounted, computed } from 'vue';
import { useTheme } from 'vuetify';
import axios from 'axios';

const theme = useTheme();
const user = ref(null);
const announcements = ref([]);
const statsLoading = ref(false);
const classesLoading = ref(false);
const loadingAnnouncements = ref(true);
const classes = ref([]);
const stats = ref({
    classes: 0,
    students: 0,
    assignments: 0,
    materials: 0
});

const filteredClasses = computed(() => {
    return classes.value.slice(0, 4); // Show only top 4 classes in dashboard
});

const fetchAnnouncements = async () => {
    try {
        loadingAnnouncements.value = true;
        const response = await axios.get('/api/announcements');
        if (response.data.success) {
            announcements.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching announcements:', error);
    } finally {
        loadingAnnouncements.value = false;
    }
};

const fetchStats = async () => {
    statsLoading.value = true;
    try {
        const response = await axios.get('/api/guru/dashboard-stats');
        if (response.data.success) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching stats:', error);
    } finally {
        statsLoading.value = false;
    }
};

const fetchClasses = async () => {
    classesLoading.value = true;
    try {
        const response = await axios.get('/api/guru/classes');
        if (response.data.success) {
            classes.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching classes:', error);
    } finally {
        classesLoading.value = false;
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
    fetchAnnouncements();
    fetchStats();
    fetchClasses();
    const userData = localStorage.getItem('user');
    if (userData) {
        user.value = JSON.parse(userData);
    }
});
</script>

<style scoped>
.bg-slate-800 {
    background-color: #1e293b !important;
}

.border-card-dark {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.gradient-card-guru {
    background: linear-gradient(135deg, #065f46 0%, #10b981 100%);
    box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.4) !important;
}

.text-success {
    color: #10b981 !important;
}

.bg-success-lighten-4 {
    background-color: #ecfdf5 !important;
}

.border-b {
    border-bottom: 1px solid #e0e0e0;
}

.classroom-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.classroom-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    border-color: #10b981;
}
</style>
