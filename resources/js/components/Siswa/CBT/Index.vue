<template>
    <v-container fluid class="pa-4 pa-md-6">
        <v-row class="mb-6">
            <v-col cols="12">
                <div class="d-flex align-center mb-2">
                    <v-btn icon variant="text" to="/siswa" class="mr-2">
                        <v-icon>mdi-arrow-left</v-icon>
                    </v-btn>
                    <h1 class="text-h4 font-weight-bold">Ujian / CBT</h1>
                </div>
                <p class="text-grey-darken-1 pl-12">Daftar ujian dan tes yang tersedia untuk Anda.</p>
            </v-col>
        </v-row>

        <v-row v-if="loading">
            <v-col v-for="i in 3" :key="i" cols="12" md="4">
                <v-skeleton-loader type="article, actions" rounded="xl"></v-skeleton-loader>
            </v-col>
        </v-row>

        <v-row v-else-if="quizzes.length === 0">
            <v-col cols="12" class="text-center py-12">
                <v-icon size="100" color="grey-lighten-2">mdi-laptop-account</v-icon>
                <h3 class="text-h5 text-grey mt-4">Belum ada jadwal ujian</h3>
            </v-col>
        </v-row>

        <v-row v-else>
            <v-col v-for="quiz in quizzes" :key="quiz.id" cols="12" md="4">
                <v-card rounded="xl" elevation="2" class="overflow-hidden h-100 d-flex flex-column">
                    <div :class="getStatusColor(quiz.status)" class="pa-4 text-white d-flex justify-space-between align-center">
                        <span class="text-overline font-weight-bold" style="letter-spacing: 1px !important;">
                            {{ quiz.status.toUpperCase() }}
                        </span>
                        <v-icon size="20">mdi-clock-outline</v-icon>
                    </div>
                    
                    <v-card-text class="pa-6 flex-grow-1">
                        <h3 class="text-h6 font-weight-bold mb-2">{{ quiz.title }}</h3>
                        <p class="text-body-2 text-grey-darken-1 mb-4 line-clamp-2">
                            {{ quiz.description || 'Tidak ada deskripsi' }}
                        </p>

                        <v-divider class="mb-4"></v-divider>

                        <div class="d-flex align-center mb-2 text-body-2">
                            <v-icon size="18" color="primary" class="mr-2">mdi-timer-outline</v-icon>
                            <span>Durasi: <strong>{{ quiz.duration_minutes }} Menit</strong></span>
                        </div>
                        <div class="d-flex align-center mb-2 text-body-2">
                            <v-icon size="18" color="primary" class="mr-2">mdi-calendar-range</v-icon>
                            <span>Mulai: {{ formatDateTime(quiz.start_time) }}</span>
                        </div>
                        <div class="d-flex align-center text-body-2">
                            <v-icon size="18" color="primary" class="mr-2">mdi-calendar-check</v-icon>
                            <span>Selesai: {{ formatDateTime(quiz.end_time) }}</span>
                        </div>

                        <!-- Info Percobaan -->
                        <div v-if="quiz.my_attempt" class="d-flex align-center mt-3 text-body-2">
                            <v-icon size="18" color="orange" class="mr-2">mdi-counter</v-icon>
                            <span>
                                Percobaan ke-<strong>{{ quiz.attempts_count || 1 }}</strong>
                                <span v-if="quiz.max_attempts !== null"> dari {{ quiz.max_attempts }}</span>
                                <span v-else> (Tidak Terbatas)</span>
                            </span>
                        </div>
                    </v-card-text>

                    <v-divider></v-divider>
                    
                    <v-card-actions class="pa-4">
                        <template v-if="quiz.status === 'finished'">
                            <div class="w-100 d-flex align-center justify-space-between px-2">
                                <div class="text-subtitle-1">Nilai: <strong class="text-primary">{{ quiz.my_attempt?.score }}</strong></div>
                                <v-chip color="success" size="small" variant="flat">Selesai</v-chip>
                            </div>
                        </template>
                        <template v-else-if="quiz.status === 'available' || quiz.status === 'in_progress'">
                            <v-btn 
                                block 
                                :color="quiz.status === 'in_progress' ? 'warning' : 'primary'" 
                                size="large" 
                                rounded="lg"
                                @click="startQuiz(quiz)"
                                :loading="startingQuizId === quiz.id"
                            >
                                <v-icon start>{{ quiz.status === 'in_progress' ? 'mdi-play-circle' : (quiz.my_attempt ? 'mdi-refresh' : 'mdi-play') }}</v-icon>
                                {{ quiz.status === 'in_progress' ? 'Lanjutkan Ujian' : (quiz.my_attempt ? 'Kerjakan Ulang' : 'Mulai Ujian') }}
                            </v-btn>
                        </template>
                        <template v-else-if="quiz.status === 'upcoming'">
                            <v-btn block disabled size="large" rounded="lg" variant="tonal">
                                Belum Dimulai
                            </v-btn>
                        </template>
                        <template v-else>
                            <v-btn block disabled size="large" rounded="lg" variant="tonal">
                                Berakhir
                            </v-btn>
                        </template>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useTheme } from 'vuetify';

const router = useRouter();
const theme = useTheme();
const loading = ref(true);
const quizzes = ref([]);
const startingQuizId = ref(null);

const fetchQuizzes = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/siswa/cbt');
        if (response.data.success) {
            quizzes.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching quizzes:', error);
    } finally {
        loading.value = false;
    }
};

const startQuiz = async (quiz) => {
    try {
        startingQuizId.value = quiz.id;
        const response = await axios.post(`/api/siswa/cbt/${quiz.id}/start`);
        if (response.data.success) {
            router.push({ 
                name: 'SiswaQuizRoom', 
                params: { attemptId: response.data.data.id } 
            });
        }
    } catch (error) {
        console.error('Error starting quiz:', error);
        alert(error.response?.data?.message || 'Gagal memulai ujian');
    } finally {
        startingQuizId.value = null;
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'available': return 'bg-info';
        case 'in_progress': return 'bg-warning';
        case 'finished': return 'bg-success';
        case 'upcoming': return 'bg-grey';
        default: return 'bg-error';
    }
};

const formatDateTime = (dateTimeString) => {
    const date = new Date(dateTimeString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(fetchQuizzes);
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
