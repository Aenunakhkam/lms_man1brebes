<template>
    <v-dialog :model-value="modelValue" fullscreen transition="fade-transition" persistent>
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

                    <!-- User Profile Mock -->
                    <div class="d-flex align-center mr-4 mr-md-10">
                        <div class="text-right mr-4 d-none d-sm-block">
                            <div class="text-body-2 font-weight-bold text-white mb-1">Administrator</div>
                            <v-btn size="x-small" color="white" variant="flat" class="text-none font-weight-bold logout-btn px-4" rounded="pill" @click="$emit('close')">
                                Logout
                            </v-btn>
                        </div>
                        <v-avatar color="white" size="48" class="elevation-2 border-white">
                            <v-icon color="#2c6fb7">mdi-account-circle</v-icon>
                        </v-avatar>
                    </div>
                </v-container>
                
                <!-- Decorative wave/diag (css) -->
                <div class="header-accent"></div>
            </v-app-bar>

            <v-main class="anbk-main">
                <v-container class="pa-0 fill-height d-flex flex-column align-center" fluid>
                    
                    <div class="content-wrapper mt-n4 relative z-index-2 w-full max-w-1000">
                        <v-row v-if="loading" justify="center">
                             <v-col cols="12" class="text-center pa-12">
                                 <v-progress-circular indeterminate color="#2c6fb7" size="64"></v-progress-circular>
                                 <div class="mt-4 text-subtitle-1 text-grey-darken-1">Memuat Pratinjau...</div>
                             </v-col>
                        </v-row>
                        
                        <v-row v-else-if="questions.length === 0" justify="center">
                            <v-col cols="12" class="text-center pa-12">
                                <v-card class="pa-10 rounded-xl elevation-4">
                                    <div class="text-h5 text-grey">Ujian ini belum memiliki soal.</div>
                                    <v-btn color="#2c6fb7" class="mt-6 px-10" rounded="pill" @click="$emit('close')">Tutup Pratinjau</v-btn>
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
                                        <span class="timer-value">{{ formattedTime }}</span>
                                    </div>

                                    <v-btn color="#1a73e8" class="text-none font-weight-bold daftar-btn" rounded="lg" @click="gridDialog = true">
                                        Daftar Soal
                                        <v-icon end icon="mdi-view-grid-outline" class="ml-2"></v-icon>
                                    </v-btn>
                                </v-toolbar>

                                <!-- Text Size Adjuster (Decorative) -->
                                <div class="bg-grey-lighten-4 px-6 py-2 d-flex align-center border-bottom">
                                    <span class="text-caption text-grey-darken-1 mr-4 font-weight-bold">Ukuran Soal :</span>
                                    <span class="text-caption mr-2 font-weight-bold opacity-30">A</span>
                                    <span class="text-caption mr-2 font-weight-bold opacity-60">A</span>
                                    <span class="text-caption font-weight-bold">A</span>
                                </div>

                                <!-- Question Content -->
                                <v-card-text class="pa-10">
                                    <div class="text-body-1 mb-8 text-black html-content quest-text" v-html="currentQuestion.question_text"></div>

                                    <div v-if="currentQuestion.question_image" class="mb-8">
                                        <v-img :src="`/storage/${currentQuestion.question_image}`" max-height="400" contain class="rounded-lg shadow-sm border"></v-img>
                                    </div>

                                    <!-- Options -->
                                    <div class="options-list pb-12">
                                        <div 
                                            v-for="opt in availableOptions" 
                                            :key="opt"
                                            class="option-item d-flex align-center mb-4 cursor-pointer"
                                            @click="currentQuestion.preview_answer = opt"
                                        >
                                            <div class="option-circle mr-4" :class="{ 'active': currentQuestion.preview_answer === opt }">
                                                {{ opt.toUpperCase() }}
                                            </div>
                                            <div class="option-label text-body-1" :class="{ 'font-weight-bold': currentQuestion.preview_answer === opt }">
                                                {{ currentQuestion['option_' + opt] }}
                                            </div>

                                            <v-chip v-if="currentQuestion.preview_answer === opt && currentQuestion.correct_answer === opt" color="success" size="x-small" class="ml-4">Benar</v-chip>
                                            <v-chip v-if="currentQuestion.preview_answer === opt && currentQuestion.correct_answer !== opt" color="error" size="x-small" class="ml-4">Kunci: {{ currentQuestion.correct_answer.toUpperCase() }}</v-chip>
                                        </div>
                                    </div>
                                </v-card-text>

                                <!-- Navigation Bottom -->
                                <v-card-actions class="pa-6 bg-grey-lighten-4 border-top">
                                    <v-btn
                                        color="#1a73e8"
                                        variant="flat"
                                        class="nav-btn px-6 text-none font-weight-bold rounded-pill"
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
                                        class="nav-btn px-10 text-none font-weight-bold text-white rounded-pill ragu-btn"
                                        height="50"
                                        prepend-icon="mdi-square"
                                        @click="toggleRagu"
                                    >
                                        Ragu-Ragu
                                    </v-btn>

                                    <v-spacer></v-spacer>

                                    <v-btn
                                        color="#1a73e8"
                                        variant="flat"
                                        class="nav-btn px-6 text-none font-weight-bold rounded-pill"
                                        height="50"
                                        append-icon="mdi-arrow-right-circle"
                                        @click="nextQuestion"
                                    >
                                        {{ currentQuestionIndex < questions.length - 1 ? 'Soal Selanjutnya' : 'Kembali' }}
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </div>

                    <!-- Close Button Floating -->
                    <v-btn
                        icon="mdi-close"
                        position="fixed"
                        location="top right"
                        class="mt-16 mr-6 elevation-8 z-index-10"
                        color="white"
                        @click="$emit('close')"
                    ></v-btn>
                </v-container>
            </v-main>

            <!-- Grid Dialog (Daftar Soal) -->
            <v-dialog v-model="gridDialog" max-width="600">
                <v-card class="rounded-xl pa-4">
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span class="font-weight-bold">Daftar Soal (Matriks)</span>
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
        </v-app>
    </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onUnmounted, onMounted } from 'vue';
import axios from 'axios';
import { useTheme } from 'vuetify';

const props = defineProps({
    modelValue: Boolean,
    quiz: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'close']);
const theme = useTheme();

const questions = ref([]);
const loading = ref(false);
const currentQuestionIndex = ref(0);
const settings = ref({});
const gridDialog = ref(false);

// Timer Mockup
const timeLeft = ref(0);
let timerInterval = null;

const currentQuestion = computed(() => {
    return questions.value[currentQuestionIndex.value] || null;
});

const availableOptions = computed(() => {
    if (!currentQuestion.value) return [];
    const opts = ['a', 'b'];
    if (currentQuestion.value.option_c) opts.push('c');
    if (currentQuestion.value.option_d) opts.push('d');
    if (currentQuestion.value.option_e) opts.push('e');
    return opts;
});

const formattedTime = computed(() => {
    const hours = Math.floor(timeLeft.value / 3600);
    const minutes = Math.floor((timeLeft.value % 3600) / 60);
    const seconds = timeLeft.value % 60;
    
    const hStr = String(hours).padStart(2, '0');
    const mStr = String(minutes).padStart(2, '0');
    const sStr = String(seconds).padStart(2, '0');
    
    return `${hStr}:${mStr}:${sStr}`;
});

const getGridClass = (index) => {
    const q = questions.value[index];
    if (q.ragu) return 'bg-yellow-ragu text-white';
    if (q.preview_answer) return 'bg-blue-primary text-white';
    return 'bg-white text-grey-darken-1 border';
};

const goToQuestion = (index) => {
    currentQuestionIndex.value = index;
    gridDialog.value = false;
};

const toggleRagu = () => {
    if (currentQuestion.value) {
        currentQuestion.value.ragu = !currentQuestion.value.ragu;
    }
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++;
    } else {
        emit('close');
    }
};

const fetchSettings = async () => {
    try {
        const response = await axios.get('/api/settings');
        if (response.data.success) {
            settings.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching settings:', error);
    }
};

const loadQuestions = async () => {
    if (!props.quiz?.id) return;
    loading.value = true;
    try {
        const response = await axios.get(`api/admin/quizzes/${props.quiz.id}/questions`);
        questions.value = response.data.data.map(q => ({
            ...q,
            preview_answer: null,
            ragu: false
        }));
        currentQuestionIndex.value = 0;
        
        timeLeft.value = (props.quiz.duration_minutes || 60) * 60;
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (timeLeft.value > 0) timeLeft.value--;
        }, 1000);
        
    } catch (error) {
        console.error('Error fetching preview questions:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchSettings);

watch(() => props.modelValue, (val) => {
    if (val) {
        loadQuestions();
    } else {
        clearInterval(timerInterval);
    }
}, { immediate: true });

onUnmounted(() => {
    clearInterval(timerInterval);
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
    border: 1px solid #ef4444;
    border-radius: 50px;
    background-color: #fff1f2;
}

.timer-value {
    color: #b91c1c;
    font-weight: 900;
    font-size: 18px;
    font-family: monospace;
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
    font-size: 1.1rem;
}

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

.opacity-80 {
    opacity: 0.8;
}

.opacity-30 { opacity: 0.3; }
.opacity-60 { opacity: 0.6; }

.z-index-2 { z-index: 2; }
.z-index-10 { z-index: 10; }

.w-full { width: 100%; }

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
