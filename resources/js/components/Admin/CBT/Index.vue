<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center py-4 px-6">
                <v-icon color="primary" class="mr-3">mdi-laptop-account</v-icon>
                <span class="text-h5">Manajemen CBT / Ujian ({{ quizzes.length }})</span>
                <v-spacer></v-spacer>
                <v-btn icon color="grey" class="mr-2" @click="loadQuizzes" :loading="loading" title="Refresh">
                    <v-icon>mdi-refresh</v-icon>
                </v-btn>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog">
                    Tambah Ujian
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="quizzes"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.classes="{ item }">
                    <div class="d-flex flex-wrap gap-1 py-1">
                        <v-chip
                            v-for="c in item.classes"
                            :key="c.id"
                            size="x-small"
                            color="info"
                            variant="flat"
                            class="mr-1 mb-1 font-weight-bold"
                        >
                            {{ c.name }}
                        </v-chip>
                    </div>
                </template>
                <template v-slot:item.start_time="{ item }">
                    {{ formatDate(item.start_time) }}
                </template>
                <template v-slot:item.end_time="{ item }">
                    {{ formatDate(item.end_time) }}
                </template>
                <template v-slot:item.is_active="{ item }">
                    <v-chip
                        :color="item.is_active ? 'success' : 'grey'"
                        size="small"
                        variant="flat"
                    >
                        {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
                    </v-chip>
                </template>
                <template v-slot:item.questions_count="{ item }">
                    <v-chip size="small" variant="tonal" color="primary">
                        {{ item.questions_count || 0 }} Soal
                    </v-chip>
                </template>
                <template v-slot:item.total_points="{ item }">
                    <v-chip size="small" variant="flat" color="orange-darken-3">
                        {{ item.total_points || 0 }} Poin
                    </v-chip>
                </template>
                <template v-slot:item.duration_minutes="{ item }">
                    {{ item.duration_minutes }} mnt
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn
                        icon
                        size="small"
                        color="success"
                        class="mr-2"
                        @click="openPreview(item)"
                        title="Pratinjau Ujian"
                    >
                        <v-icon>mdi-eye</v-icon>
                    </v-btn>
                    <v-btn
                        icon
                        size="small"
                        color="info"
                        class="mr-2"
                        @click="manageQuestions(item)"
                        title="Kelola Soal"
                    >
                        <v-icon>mdi-format-list-checks</v-icon>
                    </v-btn>
                    <v-btn
                        icon
                        size="small"
                        color="warning"
                        class="mr-2"
                        @click="editItem(item)"
                    >
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn
                        icon
                        size="small"
                        color="primary"
                        class="mr-2"
                        @click="viewResults(item)"
                        title="Lihat Hasil Peserta"
                    >
                        <v-icon>mdi-chart-bar</v-icon>
                    </v-btn>
                    <v-btn
                        icon
                        size="small"
                        color="error"
                        @click="deleteItem(item)"
                    >
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Quiz Dialog -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ formTitle }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="editedItem.class_ids"
                                    :items="classes"
                                    item-title="name"
                                    item-value="id"
                                    label="Kelas (Bisa Pilih Banyak)"
                                    variant="outlined"
                                    multiple
                                    chips
                                    closable-chips
                                    required
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="editedItem.subject_id"
                                    :items="subjects"
                                    item-title="name"
                                    item-value="id"
                                    label="Mata Pelajaran"
                                    variant="outlined"
                                    required
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.title"
                                    label="Judul Ujian"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="editedItem.description"
                                    label="Deskripsi"
                                    variant="outlined"
                                    rows="3"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="editedItem.duration_minutes"
                                    label="Durasi (Menit)"
                                    type="number"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-switch
                                    v-model="editedItem.is_active"
                                    label="Status Aktif"
                                    color="success"
                                ></v-switch>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="editedItem.start_time"
                                    label="Waktu Mulai"
                                    type="datetime-local"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="editedItem.end_time"
                                    label="Waktu Selesai"
                                    type="datetime-local"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.max_attempts_type"
                                    :items="[
                                        { title: '1 Kali', value: 1 },
                                        { title: '3 Kali', value: 3 },
                                        { title: 'Manual', value: 'manual' },
                                        { title: 'Unlimited', value: 'unlimited' }
                                    ]"
                                    label="Batas Pengerjaan"
                                    variant="outlined"
                                ></v-select>
                                <v-text-field
                                    v-if="editedItem.max_attempts_type === 'manual'"
                                    v-model="editedItem.max_attempts"
                                    label="Jumlah Percobaan"
                                    type="number"
                                    variant="outlined"
                                    min="1"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-switch
                                    v-model="editedItem.prevent_copy_paste"
                                    label="Cegah Copy-Paste (Anti-Copas)"
                                    color="primary"
                                    hide-details
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue-darken-1" variant="text" @click="close">
                        Batal
                    </v-btn>
                    <v-btn color="blue-darken-1" variant="text" @click="save" :loading="saving">
                        Simpan
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Questions Drawer/Dialog placeholder -->
        <question-manager
            v-if="selectedQuiz"
            v-model="questionsDialog"
            :quiz="selectedQuiz"
            @close="closeQuestions"
        ></question-manager>

        <!-- Preview Drawer/Dialog -->
        <preview-dialog
            v-if="selectedPreviewQuiz"
            v-model="previewDialog"
            :quiz="selectedPreviewQuiz"
            @close="closePreview"
        ></preview-dialog>

        <!-- Results Dialog -->
        <quiz-results
            v-if="selectedResultsQuiz"
            v-model="resultsDialog"
            :quiz="selectedResultsQuiz"
            @close="closeResults"
        ></quiz-results>

    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const user = JSON.parse(localStorage.getItem('user') || '{}');
const apiPrefix = (user.role === 'guru' || (user.role && user.role.name === 'guru')) ? '/api/guru' : '/api/admin';
import { useAlert } from '../../../composables/useAlert';
import QuestionManager from './Questions.vue';
import PreviewDialog from './Preview.vue';
import QuizResults from './Results.vue';

const { showSuccess, showError, showConfirm } = useAlert();

const quizzes = ref([]);
const classes = ref([]);
const subjects = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialog = ref(false);
const questionsDialog = ref(false);
const previewDialog = ref(false);
const resultsDialog = ref(false);
const selectedQuiz = ref(null);
const selectedPreviewQuiz = ref(null);
const selectedResultsQuiz = ref(null);
const editedIndex = ref(-1);

const editedItem = ref({
    class_ids: [],
    subject_id: null,
    title: '',
    description: '',
    duration_minutes: 60,
    start_time: '',
    end_time: '',
    is_active: true
});

const defaultItem = {
    title: '',
    description: '',
    duration_minutes: 60,
    start_time: '',
    end_time: '',
    is_active: true,
    class_ids: [],
    subject_id: null,
    max_attempts: 1,
    max_attempts_type: 1,
    prevent_copy_paste: false
};

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Judul Ujian', align: 'start', key: 'title' },
    { title: 'Kelas Tersedia', key: 'classes', sortable: false },
    { title: 'Mata Pelajaran', key: 'subject.name' },
    { title: 'Soal', key: 'questions_count', align: 'center' },
    { title: 'Skor', key: 'total_points', align: 'center' },
    { title: 'Durasi', key: 'duration_minutes', align: 'center' },
    { title: 'Status', key: 'is_active', align: 'center' },
    { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
];

const formTitle = computed(() => {
    return editedIndex.value === -1 ? 'Tambah Ujian Baru' : 'Edit Ujian';
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID');
};

const loadQuizzes = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`${apiPrefix}/quizzes`);
        if (response.data.success) {
            quizzes.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat data ujian');
    } finally {
        loading.value = false;
    }
};

const openDialog = () => {
    editedIndex.value = -1;
    editedItem.value = Object.assign({}, defaultItem);
    dialog.value = true;
};

const editItem = (item) => {
    editedIndex.value = quizzes.value.indexOf(item);
    
    // Format dates for datetime-local input (YYYY-MM-DDTHH:mm)
    const formatForInput = (dateStr) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    };

    editedItem.value = Object.assign({}, item);
    // Ekstrak array ID dari objek relasi classes untuk Multi-Select
    editedItem.value.class_ids = item.classes ? item.classes.map(c => c.id) : [];
    
    editedItem.value.start_time = formatForInput(item.start_time);
    editedItem.value.end_time = formatForInput(item.end_time);
    editedItem.value.is_active = Boolean(item.is_active);
    editedItem.value.prevent_copy_paste = Boolean(item.prevent_copy_paste);
    
    // Set max_attempts_type
    if (item.max_attempts === null) {
        editedItem.value.max_attempts_type = 'unlimited';
    } else if (item.max_attempts === 1 || item.max_attempts === 3) {
        editedItem.value.max_attempts_type = item.max_attempts;
    } else {
        editedItem.value.max_attempts_type = 'manual';
    }
    
    dialog.value = true;
};

const deleteItem = async (item) => {
    const confirmed = await showConfirm('Apakah Anda yakin ingin menghapus ujian ini?');
    if (confirmed) {
        try {
            await axios.delete(`${apiPrefix}/quizzes/${item.id}`);
            quizzes.value = quizzes.value.filter(q => q.id !== item.id);
            showSuccess('Ujian berhasil dihapus');
        } catch (error) {
            if (error.response && error.response.status === 404) {
                showError('Ujian tidak ditemukan', 'Data mungkin sudah dihapus sebelumnya.');
                loadQuizzes();
            } else {
                showError('Gagal menghapus ujian');
            }
        }
    }
};

const loadClasses = async () => {
    try {
        const response = await axios.get(`${apiPrefix}/classes`);
        if (response.data.success) {
            classes.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
};

const loadSubjects = async () => {
    try {
        const response = await axios.get(`${apiPrefix}/subjects`);
        if (response.data.success) {
            subjects.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading subjects:', error);
    }
};

const save = async () => {
    try {
        saving.value = true;
        
        // Prepare max_attempts based on type
        const payload = { ...editedItem.value };
        if (payload.max_attempts_type === 'unlimited') {
            payload.max_attempts = null;
        } else if (payload.max_attempts_type === 'manual') {
            payload.max_attempts = parseInt(payload.max_attempts);
        } else {
            payload.max_attempts = payload.max_attempts_type;
        }

        if (editedIndex.value > -1) {
            const response = await axios.put(`${apiPrefix}/quizzes/${editedItem.value.id}`, payload);
            Object.assign(quizzes.value[editedIndex.value], response.data.data);
            showSuccess('Ujian berhasil diperbarui');
        } else {
            const response = await axios.post(`${apiPrefix}/quizzes`, payload);
            quizzes.value.push(response.data.data);
            showSuccess('Ujian berhasil ditambahkan');
        }
        close();
    } catch (error) {
        showError(error.response?.data?.message || 'Gagal menyimpan data ujian');
        console.error(error);
    } finally {
        saving.value = false;
    }
};

const close = () => {
    dialog.value = false;
    setTimeout(() => {
        editedItem.value = Object.assign({}, defaultItem);
        editedIndex.value = -1;
    }, 300);
};

const manageQuestions = (item) => {
    selectedQuiz.value = item;
    questionsDialog.value = true;
};

const closeQuestions = () => {
    questionsDialog.value = false;
    selectedQuiz.value = null;
};

const openPreview = (item) => {
    selectedPreviewQuiz.value = item;
    previewDialog.value = true;
};

const closePreview = () => {
    previewDialog.value = false;
    selectedPreviewQuiz.value = null;
};

const viewResults = (item) => {
    selectedResultsQuiz.value = item;
    resultsDialog.value = true;
};

const closeResults = () => {
    resultsDialog.value = false;
    selectedResultsQuiz.value = null;
};

onMounted(() => {
    loadQuizzes();
    loadClasses();
    loadSubjects();
});
</script>
