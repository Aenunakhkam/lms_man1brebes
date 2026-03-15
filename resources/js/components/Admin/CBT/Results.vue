<template>
    <v-dialog :model-value="modelValue" fullscreen transition="fade-transition" persistent>
        <v-card class="grey-lighten-4">
            <v-toolbar color="primary" density="comfortable">
                <v-btn icon color="white" @click="$emit('close')">
                    <v-icon>mdi-arrow-left</v-icon>
                </v-btn>
                <v-toolbar-title class="text-white font-weight-bold">
                    Hasil Ujian: {{ quiz?.title }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
            </v-toolbar>

            <v-container fluid class="pa-4 pa-md-8">
                <v-row class="mb-4">
                    <v-col cols="12" md="8">
                        <v-card class="pa-4 rounded-lg elevation-2">
                            <v-row align="center">
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        v-model="search"
                                        prepend-inner-icon="mdi-magnify"
                                        label="Cari Nama Siswa"
                                        variant="outlined"
                                        density="comfortable"
                                        hide-details
                                        clearable
                                        @update:model-value="debounceSearch"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select
                                        v-model="selectedClass"
                                        :items="quizClasses"
                                        item-title="name"
                                        item-value="id"
                                        label="Filter Kelas"
                                        variant="outlined"
                                        density="comfortable"
                                        hide-details
                                        clearable
                                        @update:model-value="fetchResults"
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-col>
                    
                    <v-col cols="12" md="4">
                        <v-card class="pa-4 rounded-lg elevation-2 fill-height d-flex align-center">
                            <v-row no-gutters justify="space-around" class="w-100">
                                <v-btn
                                    color="error"
                                    prepend-icon="mdi-file-pdf-box"
                                    variant="flat"
                                    class="text-none font-weight-bold"
                                    @click="exportData('pdf')"
                                    :loading="exportingPdf"
                                >
                                    Eksport PDF
                                </v-btn>
                                <v-btn
                                    color="success"
                                    prepend-icon="mdi-file-excel"
                                    variant="flat"
                                    class="text-none font-weight-bold"
                                    @click="exportData('excel')"
                                    :loading="exportingExcel"
                                >
                                    Eksport Excel
                                </v-btn>
                            </v-row>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Data Table -->
                <v-card class="rounded-lg elevation-2">
                    <v-data-table
                        :headers="headers"
                        :items="results"
                        :loading="loading"
                        class="elevation-0"
                    >
                        <template v-slot:item.index="{ index }">
                            {{ index + 1 }}
                        </template>
                        
                        <template v-slot:item.score="{ item }">
                            <v-chip
                                :color="getScoreColor(item.score)"
                                size="small"
                                class="font-weight-bold"
                                variant="flat"
                            >
                                {{ item.score }}
                            </v-chip>
                        </template>

                        <template v-slot:item.details="{ item }">
                            <div class="text-caption">
                                <span class="text-success font-weight-bold">{{ item.total_points }} B</span> / 
                                <span class="text-error font-weight-bold">{{ totalQuestions - item.total_points }} S</span>
                            </div>
                        </template>

                        <template v-slot:item.status="{ item }">
                            <v-chip
                                :color="getStatusColor(item.status)"
                                size="small"
                                class="text-uppercase font-weight-black"
                                label
                            >
                                {{ 
                                    item.status === 'completed' ? 'Selesai' : 
                                    (item.status === 'blocked' ? 'Diblokir' : 
                                    (item.status === 'not_started' ? 'Belum Mulai' : 'Pengerjaan')) 
                                }}
                            </v-chip>
                        </template>

                        <template v-slot:item.actions="{ item }">
                            <div class="d-flex justify-center gap-1">
                                <v-btn
                                    v-if="item.status !== 'not_started'"
                                    icon
                                    size="x-small"
                                    color="info"
                                    variant="tonal"
                                    @click="viewHistory(item)"
                                    title="Lihat Riwayat Jawaban"
                                >
                                    <v-icon size="18">mdi-eye</v-icon>
                                </v-btn>
                                <v-btn
                                    icon
                                    size="x-small"
                                    :color="item.status === 'blocked' ? 'success' : 'warning'"
                                    variant="tonal"
                                    @click="toggleBlock(item)"
                                    :title="item.status === 'blocked' ? 'Buka Blokir' : 'Blokir Hasil'"
                                >
                                    <v-icon size="18">{{ item.status === 'blocked' ? 'mdi-lock-open-variant' : 'mdi-lock' }}</v-icon>
                                </v-btn>
                                <v-btn
                                    icon
                                    size="x-small"
                                    color="error"
                                    variant="tonal"
                                    @click="confirmDelete(item)"
                                    title="Hapus Hasil"
                                >
                                    <v-icon size="18">mdi-delete</v-icon>
                                </v-btn>
                            </div>
                        </template>

                        <template v-slot:item.finished_at="{ item }">
                            <span v-if="item.finished_at">
                                {{ formatDate(item.finished_at) }}
                            </span>
                            <span v-else class="text-grey-lighten-1">---</span>
                        </template>

                        <template v-slot:footer.prepend>
                            <div class="d-flex align-center text-caption text-grey ml-4">
                                <v-progress-circular
                                    v-if="loading"
                                    indeterminate
                                    size="14"
                                    width="2"
                                    class="mr-2"
                                ></v-progress-circular>
                                <span v-if="loading">Memperbarui data...</span>
                                <span v-else>Update otomatis aktif (10s)</span>
                            </div>
                        </template>
                    </v-data-table>
                </v-card>
            </v-container>
        </v-card>
    </v-dialog>

    <!-- Dialog Riwayat Jawaban -->
    <v-dialog v-model="historyDialog" max-width="800px" scrollable>
        <v-card v-if="historyData" class="rounded-xl overflow-hidden">
            <v-toolbar color="primary" dark class="px-4">
                <v-toolbar-title class="font-weight-bold">
                    Riwayat Jawaban: {{ historyData.student.name }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click="historyDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>

            <v-card-text class="pa-4 bg-grey-lighten-4" style="height: 70vh;">
                <div class="d-flex justify-space-between align-center mb-4 pa-4 bg-white rounded-lg elevation-1">
                    <div>
                        <div class="text-caption text-grey">Mata Pelajaran</div>
                        <div class="text-subtitle-1 font-weight-bold">{{ historyData.quiz_title }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-caption text-grey">Skor Akhir</div>
                        <div class="text-h4 font-weight-black text-primary">{{ historyData.score }}</div>
                    </div>
                </div>

                <div v-for="(q, idx) in historyData.history" :key="q.id" class="mb-4 pa-4 bg-white rounded-lg elevation-1 border-left-indicator" :class="q.is_correct ? 'border-success' : 'border-error'">
                    <div class="d-flex align-start mb-3">
                        <v-avatar :color="q.is_correct ? 'success' : 'error'" size="32" class="mr-3 text-white font-weight-bold">
                            {{ idx + 1 }}
                        </v-avatar>
                        <div class="text-body-1 font-weight-medium pt-1" v-html="q.question_text"></div>
                    </div>

                    <div class="options-container ml-11">
                        <div 
                            v-for="(text, key) in q.options" 
                            :key="key"
                            class="pa-2 px-3 mb-2 rounded border d-flex align-center"
                            :class="[
                                q.correct_answer === key ? 'bg-success-lighten-5 border-success text-success' : '',
                                q.student_answer === key && !q.is_correct ? 'bg-error-lighten-5 border-error text-error' : '',
                                q.student_answer === key && q.is_correct ? 'font-weight-bold' : ''
                            ]"
                        >
                            <span class="text-uppercase mr-3 font-weight-black">{{ key }}.</span>
                            <span>{{ text }}</span>
                            <v-spacer></v-spacer>
                            <v-icon v-if="q.correct_answer === key" color="success" size="20">mdi-check-circle</v-icon>
                            <v-icon v-if="q.student_answer === key && !q.is_correct" color="error" size="20">mdi-close-circle</v-icon>
                        </div>
                    </div>

                    <div class="ml-11 mt-2 text-caption d-flex align-center">
                        <span v-if="q.student_answer" :class="q.is_correct ? 'text-success' : 'text-error'">
                            Jawaban Siswa: <b>{{ q.student_answer.toUpperCase() }}</b> 
                            ({{ q.is_correct ? 'Benar' : 'Salah' }})
                        </span>
                        <span v-else class="text-grey italic">Siswa tidak menjawab</span>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    modelValue: Boolean,
    quiz: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'close']);

const results = ref([]);
const loading = ref(false);
const exportingPdf = ref(false);
const exportingExcel = ref(false);
const search = ref('');
const selectedClass = ref(null);
const totalQuestions = ref(0);
const historyDialog = ref(false);
const historyData = ref(null);
const loadingHistory = ref(false);
let searchTimeout = null;
let pollInterval = null;

const headers = [
    { title: 'No', key: 'index', width: '60px' },
    { title: 'Nama Siswa', key: 'student.name', align: 'start' },
    { title: 'NIS', key: 'student.nis', width: '120px' },
    { title: 'Kelas', key: 'student_class_name' },
	{ title: 'Hasil (B/S)', key: 'details', align: 'center', sortable: false },
    { title: 'Skor', key: 'score', align: 'center' },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Waktu Selesai', key: 'finished_at', align: 'end' },
    { title: 'Aksi', key: 'actions', align: 'center', sortable: false },
];

const quizClasses = computed(() => {
    return props.quiz?.classes || [];
});

const fetchResults = async (silent = false) => {
    if (!props.quiz?.id) return;
    if (!silent) loading.value = true;
    try {
        const params = {};
        if (search.value) params.search = search.value;
        if (selectedClass.value) params.class_id = selectedClass.value;

        const response = await axios.get(`api/admin/quizzes/${props.quiz.id}/results`, { params });
        if (response.data.success) {
            results.value = response.data.data.results;
            totalQuestions.value = response.data.data.quiz.questions_count;
        }
    } catch (error) {
        console.error('Error fetching quiz results:', error);
    } finally {
        if (!silent) loading.value = false;
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchResults();
    }, 500);
};

const startPolling = () => {
    stopPolling(); // Clear any existing interval
    pollInterval = setInterval(() => {
        fetchResults(true); // Fetch silently
    }, 10000); // Poll every 10 seconds
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

const viewHistory = async (item) => {
    loadingHistory.value = true;
    try {
        const response = await axios.get(`api/admin/quizzes/attempts/${item.id}/history`);
        if (response.data.success) {
            historyData.value = response.data.data;
            historyDialog.value = true;
        }
    } catch (error) {
        console.error('Error fetching history:', error);
        Swal.fire({
            title: 'Gagal',
            text: 'Terjadi kesalahan saat mengambil riwayat pengerjaan.',
            icon: 'error'
        });
    } finally {
        loadingHistory.value = false;
    }
};

const exportData = async (type) => {
    if (!props.quiz?.id) return;
    
    if (type === 'pdf') exportingPdf.value = true;
    else exportingExcel.value = true;

    try {
        const params = new URLSearchParams();
        if (search.value) params.append('search', search.value);
        if (selectedClass.value) params.append('class_id', selectedClass.value);

        const url = `api/admin/quizzes/${props.quiz.id}/export/${type}?${params.toString()}`;
        
        // Trigger download
        const response = await axios({
            url: url,
            method: 'GET',
            responseType: 'blob',
        });

        const blob = new Blob([response.data], { 
            type: type === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
        });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.setAttribute('download', `Hasil_Ujian_${props.quiz.title.replace(/\s+/g, '_')}.${type === 'pdf' ? 'pdf' : 'xlsx'}`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

    } catch (error) {
        console.error(`Error exporting ${type}:`, error);
    } finally {
        if (type === 'pdf') exportingPdf.value = false;
        else exportingExcel.value = false;
    }
};

const getScoreColor = (score) => {
    if (score >= 80) return 'success';
    if (score >= 60) return 'warning';
    return 'error';
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'success';
        case 'in_progress': return 'info';
        case 'blocked': return 'error';
        case 'not_started': return 'grey';
        default: return 'grey';
    }
};

const confirmDelete = async (item) => {
    const result = await Swal.fire({
        title: '<span class="text-error">Hapus Hasil?</span>',
        html: `Apakah Anda yakin ingin menghapus hasil pengerjaan <b>${item.student.name}</b>?<br><small class="text-grey">Data jawaban dan poin akan hilang permanen.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff5252',
        cancelButtonColor: '#9e9e9e',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        background: '#ffffff',
        customClass: {
            title: 'text-h5 font-weight-bold',
            confirmButton: 'v-btn v-btn--variant-flat v-theme--light v-btn--density-default v-btn--size-default v-btn--rounded',
            cancelButton: 'v-btn v-btn--variant-tonal v-theme--light v-btn--density-default v-btn--size-default v-btn--rounded'
        }
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.delete(`api/admin/quizzes/attempts/${item.id}`);
            if (response.data.success) {
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'Data pengerjaan telah dibersihkan.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchResults();
            }
        } catch (error) {
            console.error('Error deleting attempt:', error);
            const status = error.response ? error.response.status : 'Unknown';
            Swal.fire({
                title: 'Data Tidak Ditemukan',
                html: `Gagal menghapus (Error ${status}).<br><br>Hal ini biasanya terjadi karena:<br>1. Data sudah dihapus di tab lain/oleh admin lain.<br>2. Halaman belum di-<b>Refresh</b> setelah data dihapus.<br><br>Silakan lakukan <b>Refresh (F5)</b> browser Anda.`,
                icon: 'error'
            });
        }
    }
};

const toggleBlock = async (item) => {
    const isBlocking = item.status !== 'blocked';
    const msg = isBlocking ? 'Blokir' : 'Buka Blokir';
    
    const result = await Swal.fire({
        title: `Konfirmasi ${msg}`,
        html: `Anda ingin <b>${msg.toLowerCase()}</b> hasil pengerjaan milik <b>${item.student.name}</b>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: isBlocking ? '#fb8c00' : '#4caf50',
        cancelButtonColor: '#9e9e9e',
        confirmButtonText: `Ya, ${msg}!`,
        cancelButtonText: 'Batal',
        background: '#ffffff'
    });

    if (result.isConfirmed) {
        try {
            const url = isBlocking 
                ? `api/admin/quizzes/attempts/${item.id}/block`
                : `api/admin/quizzes/attempts/${item.id}/unblock`;
            
            const response = await axios.post(url);
            if (response.data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: `Status berhasil diubah menjadi ${isBlocking ? 'Diblokir' : 'Selesai'}.`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchResults();
            }
        } catch (error) {
            console.error(`Error ${msg} attempt:`, error);
            Swal.fire({
                title: 'Gagal!',
                text: `Terjadi kesalahan saat mencoba ${msg.toLowerCase()}.`,
                icon: 'error'
            });
        }
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

watch(() => props.modelValue, (val) => {
    if (val) {
        fetchResults();
        startPolling();
    } else {
        stopPolling();
    }
});

onMounted(() => {
    if (props.modelValue) {
        fetchResults();
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<style scoped>
.gap-2 { gap: 8px; }
.w-full { width: 100%; }
.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
}

.border-left-indicator {
    border-left: 4px solid transparent;
}
.border-success { border-left-color: #4CAF50 !important; }
.border-error { border-left-color: #FF5252 !important; }

.bg-success-lighten-5 { background-color: #E8F5E9 !important; }
.bg-error-lighten-5 { background-color: #FFEBEE !important; }
</style>

<style>
/* Ensure SweetAlert2 appears above Vuetify's fullscreen dialog */
.swal2-container {
    z-index: 9999 !important;
}
</style>
