<template>
    <v-app :theme="theme.global.name.value" class="anbk-bg">
        <!-- ANBK Style Header -->
        <v-app-bar elevation="0" color="#2c6fb7" height="100" class="px-0 relative overflow-visible">
            <v-container fluid class="pa-0 fill-height d-flex align-center">
                <!-- Logo & App Name -->
                <div class="d-flex align-center ml-4 ml-md-10">
                    <v-img 
                        :src="settings.school_logo ? `/storage/${settings.school_logo}` : 'https://upload.wikimedia.org/wikipedia/commons/9/9c/Logo_of_Ministry_of_Education_and_Culture_of_Republic_of_Indonesia.svg'" 
                        width="50" 
                        height="50" 
                        contain 
                        class="mr-4"
                    ></v-img>
                    <div class="header-text">
                        <h1 class="text-h5 font-weight-black text-white line-height-1 mb-0">{{ settings.app_name || 'LMS MAN 1 BREBES' }}</h1>
                        <div class="text-subtitle-2 text-white font-weight-medium opacity-80">CBT Application</div>
                    </div>
                </div>

                <v-spacer></v-spacer>

                <!-- User Profile Info -->
                <div class="d-flex align-center mr-4 mr-md-10" v-if="user">
                    <div class="text-right mr-4 d-none d-sm-block">
                        <div class="text-body-2 font-weight-bold text-white mb-1">{{ user.name }}</div>
                        <v-btn size="x-small" color="white" variant="flat" class="text-none font-weight-bold logout-btn px-4" rounded="pill" @click="confirmLogout">
                            Logout
                        </v-btn>
                    </div>
                    <v-avatar color="white" size="48" class="elevation-2 border-white">
                        <v-img v-if="user.photo" :src="`/storage/${user.photo}`"></v-img>
                        <v-icon v-else color="#2c6fb7">mdi-account</v-icon>
                    </v-avatar>
                </div>
            </v-container>
            
            <div class="header-accent"></div>
        </v-app-bar>

        <v-main class="anbk-main">
            <v-container class="pa-0 fill-height d-flex flex-column align-center" fluid>
                
                <div class="content-wrapper mt-n4 relative z-index-2 w-full max-w-1000 px-4 px-md-0">
                    <v-row v-if="loading" justify="center">
                         <v-col cols="12" class="text-center pa-12">
                             <v-progress-circular indeterminate color="#2c6fb7" size="64"></v-progress-circular>
                             <div class="mt-4 text-subtitle-1 text-grey-darken-1">Menyiapkan Lembar Ujian...</div>
                         </v-col>
                    </v-row>
                    
                    <v-row v-else-if="questions.length === 0" justify="center">
                        <v-col cols="12" class="text-center pa-12">
                            <v-card class="pa-10 rounded-xl elevation-4">
                                <div class="text-h5 text-grey">Data ujian tidak ditemukan.</div>
                                <v-btn color="#2c6fb7" class="mt-6 px-10" rounded="pill" @click="router.push('/siswa/cbt')">Kembali</v-btn>
                            </v-card>
                        </v-col>
                    </v-row>

                    <template v-else>
                        <!-- Main Question Card -->
                        <v-card class="question-card mx-auto mb-10 elevation-10" min-height="600">
                            <!-- Card Header -->
                            <v-toolbar color="white" class="px-6 border-bottom" flat height="70">
                                <div class="d-flex align-center">
                                    <div class="soal-label mr-3">SOAL NOMOR</div>
                                    <div class="soal-number">{{ currentQuestionIndex + 1 }}</div>
                                </div>
                                
                                <v-spacer></v-spacer>

                                <!-- Timer Pill -->
                                <div class="timer-pill px-6 py-2 mr-4 d-none d-sm-flex align-center">
                                    <span class="text-caption font-weight-bold text-grey-darken-1 mr-4">Sisa Waktu</span>
                                    <span class="timer-value" :class="{ 'text-error': timeLeft < 300 }">{{ formattedTime }}</span>
                                </div>

                                <v-btn color="#1a73e8" class="text-none font-weight-bold daftar-btn" rounded="lg" @click="gridDialog = true">
                                    Daftar Soal
                                    <v-icon end icon="mdi-view-grid-outline" class="ml-2"></v-icon>
                                </v-btn>
                            </v-toolbar>

                            <!-- Tool Bar Text Size -->
                            <div class="bg-grey-lighten-4 px-6 py-2 d-flex align-center border-bottom">
                                <span class="text-caption text-grey-darken-1 mr-4 font-weight-bold">Ukuran Soal :</span>
                                <span class="text-caption mr-2 font-weight-bold opacity-30 cursor-pointer hover-opacity-1" @click="textSize = 'small'">A</span>
                                <span class="text-caption mr-2 font-weight-bold opacity-60 cursor-pointer hover-opacity-1" @click="textSize = 'medium'">A</span>
                                <span class="text-caption font-weight-bold cursor-pointer hover-opacity-1" @click="textSize = 'large'">A</span>
                            </div>

                            <!-- Question Content -->
                            <v-card-text class="pa-6 pa-md-10" :class="`size-${textSize}`">
                                <div class="text-body-1 mb-8 text-black html-content quest-text" v-html="currentQuestion.question_text"></div>

                                <div v-if="currentQuestion.question_image" class="mb-8 text-center text-md-left">
                                    <v-img :src="`/storage/${currentQuestion.question_image}`" max-height="400" contain class="rounded-lg shadow-sm border mx-auto mx-md-0"></v-img>
                                </div>

                                <!-- Options -->
                                <div class="options-list pb-12">
                                    <div 
                                        v-for="opt in ['a', 'b', 'c', 'd', 'e']" 
                                        :key="opt"
                                        v-show="currentQuestion['option_' + opt]"
                                        class="option-item d-flex align-center mb-4 cursor-pointer"
                                        @click="selectOption(opt)"
                                    >
                                        <div class="option-circle mr-4" :class="{ 'active': currentQuestion.my_answer === opt }">
                                            {{ opt.toUpperCase() }}
                                        </div>
                                        <div class="option-label text-body-1" :class="{ 'font-weight-bold': currentQuestion.my_answer === opt }">
                                            {{ currentQuestion['option_' + opt] }}
                                        </div>
                                    </div>
                                </div>
                            </v-card-text>

                            <!-- Navigation Bottom -->
                            <v-card-actions class="pa-6 bg-grey-lighten-4 border-top">
                                <v-btn
                                    color="#1a73e8"
                                    variant="flat"
                                    class="nav-btn px-4 px-md-6 text-none font-weight-bold rounded-pill"
                                    height="50"
                                    prepend-icon="mdi-arrow-left-circle"
                                    :disabled="currentQuestionIndex === 0"
                                    @click="currentQuestionIndex--"
                                >
                                    Soal Sebelumnya
                                </v-btn>

                                <v-spacer></v-spacer>

                                <v-btn
                                    color="#fbbc05"
                                    variant="flat"
                                    class="nav-btn px-6 px-md-10 text-none font-weight-bold text-white rounded-pill ragu-btn"
                                    height="50"
                                    @click="toggleRagu"
                                >
                                    <v-icon start icon="mdi-square" v-if="!currentQuestion.ragu"></v-icon>
                                    <v-icon start icon="mdi-checkbox-marked" v-else></v-icon>
                                    Ragu-Ragu
                                </v-btn>

                                <v-spacer></v-spacer>

                                <v-btn
                                    color="#1a73e8"
                                    variant="flat"
                                    class="nav-btn px-4 px-md-6 text-none font-weight-bold rounded-pill"
                                    height="50"
                                    @click="nextQuestion"
                                >
                                    {{ currentQuestionIndex < questions.length - 1 ? 'Soal Selanjutnya' : 'Selesai' }}
                                    <v-icon end :icon="currentQuestionIndex < questions.length - 1 ? 'mdi-arrow-right-circle' : 'mdi-check-all'"></v-icon>
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </template>
                </div>
            </v-container>
        </v-main>

        <!-- Grid Dialog (Daftar Soal) -->
        <v-dialog v-model="gridDialog" max-width="600">
            <v-card class="rounded-xl pa-4">
                <v-card-title class="d-flex justify-space-between align-center">
                    <span class="font-weight-bold">Daftar Soal</span>
                    <v-btn icon="mdi-close" variant="text" @click="gridDialog = false"></v-btn>
                </v-card-title>
                <v-card-text class="pa-4">
                    <div class="grid-container">
                        <div 
                            v-for="(q, index) in questions" 
                            :key="q.id"
                            class="grid-item d-flex align-center justify-center cursor-pointer font-weight-bold"
                            :class="getGridClass(index)"
                            @click="goToQuestion(index)"
                        >
                            {{ index + 1 }}
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="d-flex justify-center flex-wrap mt-8 pt-4 border-top">
                        <div class="d-flex align-center mr-6 mb-2">
                            <div class="legend-dot bg-blue-primary mr-2"></div>
                            <span class="text-caption font-weight-bold text-grey-darken-1">Terjawab</span>
                        </div>
                        <div class="d-flex align-center mr-6 mb-2">
                            <div class="legend-dot bg-white border mr-2"></div>
                            <span class="text-caption font-weight-bold text-grey-darken-1">Kosong</span>
                        </div>
                        <div class="d-flex align-center mb-2">
                            <div class="legend-dot bg-yellow-ragu mr-2"></div>
                            <span class="text-caption font-weight-bold text-grey-darken-1">Ragu-Ragu</span>
                        </div>
                    </div>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Finish Confirm Dialog -->
        <v-dialog v-model="finishDialog" max-width="500" persistent>
            <v-card rounded="xl" class="overflow-hidden">
                <div class="pa-6 text-center bg-blue-lighten-5">
                    <v-icon color="#2c6fb7" size="64" class="mb-4">mdi-information-outline</v-icon>
                    <h2 class="text-h5 font-weight-bold text-blue-darken-4">Selesaikan Ujian?</h2>
                </div>
                <v-card-text class="pa-8 text-center text-body-1">
                    Anda telah menjawab <strong>{{ answeredCount }}</strong> dari {{ questions.length }} soal.<br><br>
                    Pastikan semua soal telah terjawab dengan benar sebelum menekan tombol Selesai.
                </v-card-text>
                <v-card-actions class="pa-6 pt-0">
                    <v-row>
                        <v-col cols="6">
                            <v-btn block color="grey-darken-1" variant="outlined" class="text-none font-weight-bold rounded-pill" height="50" @click="finishDialog = false">Batal</v-btn>
                        </v-col>
                        <v-col cols="6">
                            <v-btn block color="#2c6fb7" variant="flat" class="text-none font-weight-bold text-white rounded-pill" height="50" @click="submitQuiz" :loading="submitting">Ya, Selesai</v-btn>
                        </v-col>
                    </v-row>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useTheme } from 'vuetify';

const route = useRoute();
const router = useRouter();
const theme = useTheme();

const attemptId = route.params.attemptId;
const quizTitle = ref('Memuat Ujian...');
const questions = ref([]);
const currentQuestionIndex = ref(0);
const timeLeft = ref(0);
const timerInterval = ref(null);
const loading = ref(true);
const submitting = ref(false);
const finishDialog = ref(false);
const gridDialog = ref(false);
const settings = ref({});
const quizSettings = ref({
    prevent_copy_paste: false
});
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));
const textSize = ref('medium');

const currentQuestion = computed(() => {
    return questions.value[currentQuestionIndex.value] || null;
});

const formattedTime = computed(() => {
    const hours = Math.floor(timeLeft.value / 3600);
    const minutes = Math.floor((timeLeft.value % 3600) / 60);
    const seconds = timeLeft.value % 60;
    
    return [
        String(hours).padStart(2, '0'),
        String(minutes).padStart(2, '0'),
        String(seconds).padStart(2, '0')
    ].join(':');
});

const answeredCount = computed(() => {
    return questions.value.filter(q => q.my_answer !== null).length;
});

const fetchSettings = async () => {
    try {
        const response = await axios.get('/api/settings');
        if (response.data.success) {
            settings.value = response.data.data;
        }
    } catch (e) {
        console.error(e);
    }
};

const fetchQuestions = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/siswa/cbt/attempts/${attemptId}/questions`);
        if (response.data.success) {
            questions.value = response.data.data.questions.map(q => ({
                ...q,
                ragu: q.ragu || false 
            }));
            timeLeft.value = response.data.data.time_left_seconds;
            quizTitle.value = response.data.data.quiz_title;
            if (response.data.data.quiz_settings) {
                quizSettings.value = response.data.data.quiz_settings;
                if (quizSettings.value.prevent_copy_paste) {
                    enableProctoring();
                }
            }
            startTimer();
        }
    } catch (error) {
        console.error('Error fetching questions:', error);
        router.push('/siswa/cbt');
    } finally {
        loading.value = false;
    }
};

const startTimer = () => {
    if (timerInterval.value) clearInterval(timerInterval.value);
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
            autoSubmit();
        }
    }, 1000);
};

const selectOption = (opt) => {
    if (!currentQuestion.value) return;
    currentQuestion.value.my_answer = opt;
    saveAnswer();
};

const toggleRagu = () => {
    if (currentQuestion.value) {
        currentQuestion.value.ragu = !currentQuestion.value.ragu;
        saveAnswer();
    }
};

const saveAnswer = async () => {
    if (!currentQuestion.value) return;
    try {
        await axios.post(`/api/siswa/cbt/attempts/${attemptId}/answer`, {
            question_id: currentQuestion.value.id,
            answer: currentQuestion.value.my_answer,
            ragu: currentQuestion.value.ragu
        });
    } catch (error) {
        console.error('Error saving answer:', error);
    }
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++;
    } else {
        finishDialog.value = true;
    }
};

const goToQuestion = (index) => {
    currentQuestionIndex.value = index;
    gridDialog.value = false;
};

const getGridClass = (index) => {
    const q = questions.value[index];
    if (q.ragu) return 'bg-yellow-ragu text-white';
    if (q.my_answer) return 'bg-blue-primary text-white';
    return 'bg-white text-grey-darken-1 border';
};

const getRadioClass = (opt) => {
    if (currentQuestion.value.my_answer === opt) return 'border-active';
    return 'border-inactive';
};

const submitQuiz = async () => {
    try {
        submitting.value = true;
        const response = await axios.post(`/api/siswa/cbt/attempts/${attemptId}/finish`);
        if (response.data.success) {
            router.push('/siswa/cbt');
        }
    } catch (error) {
        console.error('Error submitting quiz:', error);
    } finally {
        submitting.value = false;
        finishDialog.value = false;
    }
};

const autoSubmit = () => {
    submitQuiz();
};

const confirmLogout = () => {
    // Logic for logout if necessary, but usually session based
};

const handleProctoringEvent = (e) => {
    e.preventDefault();
    return false;
};

const enableProctoring = () => {
    document.addEventListener('contextmenu', handleProctoringEvent);
    document.addEventListener('copy', handleProctoringEvent);
    document.addEventListener('paste', handleProctoringEvent);
    document.addEventListener('cut', handleProctoringEvent);
    // Optional: Mencegah tombol print screen atau inspect element bisa lebih kompleks, 
    // tapi ini dasar yang diminta user (anti-copas)
};

const disableProctoring = () => {
    document.removeEventListener('contextmenu', handleProctoringEvent);
    document.removeEventListener('copy', handleProctoringEvent);
    document.removeEventListener('paste', handleProctoringEvent);
    document.removeEventListener('cut', handleProctoringEvent);
};

onMounted(() => {
    fetchSettings();
    fetchQuestions();
});

onUnmounted(() => {
    if (timerInterval.value) clearInterval(timerInterval.value);
    disableProctoring();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');

.anbk-bg {
    background-color: #e9ecef !important;
}

.font-inter {
    font-family: 'Inter', sans-serif !important;
}

.anbk-main {
    background-color: #e9ecef;
}

.line-height-1 {
    line-height: 1.1;
}

.header-accent {
    position: absolute;
    bottom: -30px;
    left: 0;
    width: 100%;
    height: 100px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    clip-path: polygon(0 0, 100% 0, 100% 10%, 0 70%);
    z-index: 1;
}

.max-w-1000 {
    max-width: 1000px;
}

.question-card {
    border-radius: 12px !important;
    overflow: hidden;
    margin-top: 20px;
    width: 100%;
}

.soal-label {
    font-size: 15px;
    font-weight: 700;
    color: #4b5563;
}

.soal-number {
    background-color: #3b82f6;
    color: white;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-weight: 900;
    font-size: 18px;
}

.timer-pill {
    border: 1px solid #71717a;
    border-radius: 50px;
    background-color: #ffffff;
}

.timer-value {
    color: #18181b;
    font-weight: 900;
    font-size: 18px;
    font-family: monospace;
}

.timer-value.text-error {
    color: #ef4444 !important;
}

.daftar-btn {
    letter-spacing: 0.5px;
}

.border-bottom {
    border-bottom: 1px solid #e5e7eb !important;
}

.border-top {
    border-top: 1px solid #e5e7eb !important;
}

.quest-text {
    line-height: 1.8;
    color: #1f2937;
}

.size-small .quest-text { font-size: 0.95rem; }
.size-medium .quest-text { font-size: 1.1rem; }
.size-large .quest-text { font-size: 1.3rem; }

/* Options */
.option-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    color: #6b7280;
    transition: all 0.2s;
    flex-shrink: 0;
}

.option-circle.active {
    background-color: #1a73e8;
    color: white;
    border-color: #1a73e8;
    transform: scale(1.1);
}

.option-item:hover .option-circle:not(.active) {
    border-color: #1a73e8;
    color: #1a73e8;
}

.nav-btn {
    letter-spacing: 0.5px;
    text-transform: none;
}

.ragu-btn {
    background-color: #f59e0b !important;
}

.logout-btn {
    border: 1px solid #e5e7eb;
}

.opacity-80 { opacity: 0.8; }
.opacity-30 { opacity: 0.3; }
.opacity-60 { opacity: 0.6; }

.hover-opacity-1:hover { opacity: 1 !important; }

.z-index-2 { z-index: 2; }

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
    gap: 10px;
}

.grid-item {
    aspect-ratio: 1;
    border-radius: 6px;
    font-size: 16px;
    transition: all 0.2s;
}

.grid-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.bg-blue-primary { background-color: #2c6fb7 !important; }
.bg-yellow-ragu { background-color: #fbbc05 !important; }

.html-content :deep(p) {
    margin-bottom: 1rem;
}

.html-content :deep(img) {
    max-width: 100%;
    height: auto;
}
</style>
