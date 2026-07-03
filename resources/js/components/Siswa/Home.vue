<template>
    <v-container fluid class="pa-4 pa-md-6">
        <!-- Welcome Section -->
        <v-row class="mb-4">
            <v-col cols="12">
                <v-card 
                    class="pa-4 pa-md-6" 
                    :class="theme.global.current.value.dark ? 'bg-slate-800 text-white border-card-dark' : 'gradient-card-siswa text-white'" 
                    rounded="xl" 
                    elevation="2"
                >
                    <h1 class="text-h5 text-md-h4 font-weight-bold mb-2">
                        Halo, {{ user?.name }}! 🎓
                    </h1>
                    <p class="text-body-1 text-md-h6 mb-0 opacity-90">
                        Semangat belajar hari ini!
                    </p>
                </v-card>
            </v-col>
        </v-row>

        <!-- Stats Cards -->
        <v-row v-if="loadingStats">
            <v-col v-for="i in 4" :key="i" cols="6" md="3">
                <v-skeleton-loader type="card" rounded="xl"></v-skeleton-loader>
            </v-col>
        </v-row>
        <v-row v-else>
            <v-col cols="6" md="3">
                <v-card class="pa-4" rounded="xl" elevation="2">
                    <div class="text-center">
                        <v-avatar size="50" color="info-lighten-4" class="mb-2">
                            <v-icon size="25" color="info">mdi-google-classroom</v-icon>
                        </v-avatar>
                        <div class="text-caption text-grey mb-1">Mata Pelajaran</div>
                        <div class="text-h5 font-weight-bold text-info">{{ stats.subjects_count }}</div>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="6" md="3">
                <v-card class="pa-4" rounded="xl" elevation="2">
                    <div class="text-center">
                        <v-avatar size="50" color="warning-lighten-4" class="mb-2">
                            <v-icon size="25" color="warning">mdi-clipboard-text</v-icon>
                        </v-avatar>
                        <div class="text-caption text-grey mb-1">Tugas Pending</div>
                        <div class="text-h5 font-weight-bold text-warning">{{ stats.pending_assignments }}</div>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="6" md="3">
                <v-card class="pa-4" rounded="xl" elevation="2">
                    <div class="text-center">
                        <v-avatar size="50" color="success-lighten-4" class="mb-2">
                            <v-icon size="25" color="success">mdi-check-circle</v-icon>
                        </v-avatar>
                        <div class="text-caption text-grey mb-1">Tugas Selesai</div>
                        <div class="text-h5 font-weight-bold text-success">{{ stats.completed_assignments }}</div>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="6" md="3">
                <v-card class="pa-4" rounded="xl" elevation="2">
                    <div class="text-center">
                        <v-avatar size="50" color="primary-lighten-4" class="mb-2">
                            <v-icon size="25" color="primary">mdi-chart-line</v-icon>
                        </v-avatar>
                        <div class="text-caption text-grey mb-1">Rata-rata Nilai</div>
                        <div class="text-h5 font-weight-bold text-primary">{{ stats.average_grade }}</div>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Upcoming Assignments & Recent Grades -->
        <v-row class="mt-4">
            <v-col cols="12" md="7">
                <v-card class="pa-4 pa-md-6" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-4">Tugas Mendatang</h3>
                    <v-list v-if="stats.upcoming_assignments && stats.upcoming_assignments.length" density="compact">
                        <v-list-item
                            v-for="assignment in stats.upcoming_assignments"
                            :key="assignment.id"
                            class="px-0 mb-3"
                        >
                            <template v-slot:prepend>
                                <v-avatar size="50" color="warning-lighten-4">
                                    <v-icon size="25" color="warning">mdi-clipboard-text</v-icon>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="text-body-2 font-weight-medium mb-1">
                                {{ assignment.title }} ({{ assignment.subject?.name }})
                            </v-list-item-title>
                            <v-list-item-subtitle class="text-caption">
                                Deadline: {{ assignment.days_remaining > 0 ? assignment.days_remaining + ' hari lagi' : 'Hari ini' }}
                            </v-list-item-subtitle>
                            <template v-slot:append>
                                <v-btn 
                                    size="small" 
                                    :color="assignment.is_submitted ? 'success' : 'warning'" 
                                    variant="outlined" 
                                    class="text-none"
                                    :to="`/siswa/assignments`"
                                >
                                    {{ assignment.is_submitted ? 'Selesai' : 'Kerjakan' }}
                                </v-btn>
                            </template>
                        </v-list-item>
                    </v-list>
                    <div v-else class="text-center py-8 text-grey">
                        <v-icon size="50" class="mb-2 opacity-20">mdi-clipboard-check-outline</v-icon>
                        <div>Tidak ada tugas mendatang</div>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="5">
                <v-card class="pa-4 pa-md-6" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-4">Nilai Terbaru</h3>
                    <v-list v-if="stats.recent_grades && stats.recent_grades.length" density="compact">
                        <v-list-item
                            v-for="grade in stats.recent_grades"
                            :key="grade.id"
                            class="px-0 mb-1"
                        >
                            <template v-slot:prepend>
                                <v-avatar size="40" :color="grade.score >= 75 ? 'success-lighten-4' : 'primary-lighten-4'">
                                    <v-icon size="20" :color="grade.score >= 75 ? 'success' : 'primary'">mdi-chart-box</v-icon>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="text-body-2 font-weight-medium">
                                {{ grade.subject?.name }}
                            </v-list-item-title>
                            <v-list-item-subtitle class="text-caption">
                                {{ grade.grade_type || 'Tugas' }}
                            </v-list-item-subtitle>
                            <template v-slot:append>
                                <v-chip size="small" :color="grade.score >= 75 ? 'success' : 'primary'">
                                    {{ grade.score }}
                                </v-chip>
                            </template>
                        </v-list-item>
                    </v-list>
                    <div v-else class="text-center py-8 text-grey">
                        <v-icon size="50" class="mb-2 opacity-20">mdi-chart-box-outline</v-icon>
                        <div>Belum ada nilai</div>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Announcements Section -->
        <v-row class="mt-4">
            <v-col cols="12">
                <v-card class="pa-4 pa-md-6 border-card" rounded="xl" elevation="2">
                    <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
                        <v-icon color="warning" class="mr-3">mdi-bullhorn</v-icon>
                        Pengumuman Sekolah
                    </h3>
                    
                    <v-row v-if="loadingAnnouncements">
                        <v-col v-for="i in 2" :key="i" cols="12" md="6">
                            <v-skeleton-loader type="list-item-two-line" rounded="lg"></v-skeleton-loader>
                        </v-col>
                    </v-row>

                    <v-row v-else-if="announcements && announcements.length">
                        <v-col v-for="announcement in announcements" :key="announcement.id" cols="12" md="6">
                            <v-alert
                                border="start"
                                color="warning-lighten-5"
                                :theme="theme.global.current.value.dark ? 'dark' : 'light'"
                                class="mb-0 h-100"
                            >
                                <template v-slot:title>
                                    <div class="text-body-1 font-weight-bold text-warning-darken-2">{{ announcement.title }}</div>
                                </template>
                                <div class="text-body-2 mb-2">{{ announcement.content }}</div>
                                <div class="text-caption text-grey">{{ formatDate(announcement.created_at) }}</div>
                            </v-alert>
                        </v-col>
                    </v-row>

                    <div v-else class="text-center py-6 text-grey">
                        <v-icon size="40" class="mb-2 opacity-20">mdi-bullhorn-variant-outline</v-icon>
                        <div class="text-body-2">Belum ada pengumuman terbaru</div>
                    </div>
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
const user = ref(null);
const announcements = ref([]);
const loadingStats = ref(true);
const loadingAnnouncements = ref(true);
const stats = ref({
    subjects_count: 0,
    pending_assignments: 0,
    completed_assignments: 0,
    average_grade: 0,
    upcoming_assignments: [],
    recent_grades: []
});

const fetchDashboardStats = async () => {
    try {
        loadingStats.value = true;
        const response = await axios.get('/api/siswa/dashboard-stats');
        if (response.data.success) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    } finally {
        loadingStats.value = false;
    }
};

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

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

onMounted(() => {
    fetchDashboardStats();
    fetchAnnouncements();
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

.gradient-card-siswa {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.border-b {
    border-bottom: 1px solid #e0e0e0;
}
</style>
