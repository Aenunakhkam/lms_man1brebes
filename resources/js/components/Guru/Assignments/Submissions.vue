<template>
    <v-container fluid class="pa-6">
        <div class="d-flex align-center mb-6">
            <v-btn icon="mdi-arrow-left" variant="text" to="/guru/assignments" class="mr-4"></v-btn>
            <div>
                <h1 class="text-h4 font-weight-bold mb-1">Pengumpulan Tugas</h1>
                <p class="text-subtitle-1 text-grey" v-if="assignment">{{ assignment.title }} - Kelas {{ assignment.class?.name }}</p>
            </div>
        </div>

        <v-card rounded="xl" elevation="2">
            <v-data-table
                :headers="headers"
                :items="students"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                
                <template v-slot:item.status="{ item }">
                    <v-chip
                        :color="getSubmissionStatus(item).color"
                        size="small"
                        rounded="lg"
                    >
                        {{ getSubmissionStatus(item).text }}
                    </v-chip>
                </template>

                <template v-slot:item.score="{ item }">
                    <span v-if="getSubmission(item)?.score !== null" class="font-weight-bold">
                        {{ getSubmission(item).score }} / {{ assignment?.max_score || 100 }}
                    </span>
                    <span v-else class="text-grey">-</span>
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-btn 
                        v-if="getSubmission(item)"
                        color="primary" 
                        variant="text" 
                        size="small" 
                        @click="openGradeDialog(item)"
                    >
                        Nilai Tugas
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Penilaian -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
            <v-card rounded="xl">
                <v-card-title class="pa-6 d-flex justify-space-between align-center">
                    <span class="text-h5 font-weight-bold">Penilaian Tugas</span>
                    <v-btn icon variant="text" @click="closeDialog">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-divider></v-divider>

                <v-card-text class="pa-6" v-if="activeStudent && activeSubmission">
                    <div class="mb-4">
                        <div class="text-subtitle-2 text-grey">Siswa</div>
                        <div class="text-h6">{{ activeStudent.name }}</div>
                    </div>
                    
                    <div class="mb-4" v-if="activeSubmission.content">
                        <div class="text-subtitle-2 text-grey mb-1">Teks Jawaban</div>
                        <v-card variant="outlined" class="pa-3 bg-grey-lighten-4">
                            {{ activeSubmission.content }}
                        </v-card>
                    </div>

                    <div class="mb-6" v-if="activeSubmission.file_path">
                        <div class="text-subtitle-2 text-grey mb-1">File Lampiran</div>
                        <v-btn 
                            color="info" 
                            variant="tonal" 
                            prepend-icon="mdi-download"
                            :href="`/storage/${activeSubmission.file_path}`"
                            target="_blank"
                            rounded="lg"
                        >
                            Unduh File Jawaban
                        </v-btn>
                    </div>

                    <v-form ref="form" v-model="valid">
                        <v-text-field
                            v-model.number="gradeForm.score"
                            label="Nilai"
                            type="number"
                            :max="assignment?.max_score || 100"
                            min="0"
                            variant="outlined"
                            rounded="lg"
                            required
                            class="mb-2"
                            :rules="[
                                v => v !== null || 'Nilai wajib diisi',
                                v => v >= 0 || 'Minimal 0',
                                v => v <= (assignment?.max_score || 100) || `Maksimal ${assignment?.max_score || 100}`
                            ]"
                        ></v-text-field>

                        <v-textarea
                            v-model="gradeForm.feedback"
                            label="Komentar / Feedback (Opsional)"
                            variant="outlined"
                            rounded="lg"
                            rows="3"
                        ></v-textarea>
                    </v-form>
                </v-card-text>

                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn variant="text" rounded="lg" @click="closeDialog" :disabled="saving">Batal</v-btn>
                    <v-btn color="success" variant="flat" rounded="lg" @click="saveGrade" :loading="saving" :disabled="!valid">Simpan Nilai</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const route = useRoute();
const { showSuccess, showError } = useAlert();

const assignment = ref(null);
const students = ref([]);
const submissions = ref([]);
const loading = ref(false);

const dialog = ref(false);
const saving = ref(false);
const valid = ref(false);
const activeStudent = ref(null);
const activeSubmission = ref(null);

const gradeForm = ref({
    score: null,
    feedback: ''
});

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'NIS', key: 'nis' },
    { title: 'Nama Siswa', key: 'name' },
    { title: 'Status', key: 'status', sortable: false },
    { title: 'Nilai', key: 'score' },
    { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
];

const fetchSubmissions = async () => {
    loading.value = true;
    try {
        const assignmentId = route.params.id;
        const response = await axios.get(`/api/guru/assignments/${assignmentId}/submissions`);
        if (response.data.success) {
            assignment.value = response.data.data.assignment;
            submissions.value = response.data.data.submissions;
            students.value = response.data.data.students;
        }
    } catch (error) {
        console.error('Error fetching submissions:', error);
        showError('Gagal memuat data pengumpulan tugas');
    } finally {
        loading.value = false;
    }
};

const getSubmission = (student) => {
    return submissions.value.find(sub => sub.student_id === student.id);
};

const getSubmissionStatus = (student) => {
    const sub = getSubmission(student);
    if (!sub) return { text: 'Belum Mengumpulkan', color: 'error' };
    if (sub.status === 'graded') return { text: 'Sudah Dinilai', color: 'success' };
    if (sub.status === 'late') return { text: 'Terlambat', color: 'warning' };
    return { text: 'Menunggu Penilaian', color: 'info' };
};

const openGradeDialog = (student) => {
    activeStudent.value = student;
    activeSubmission.value = getSubmission(student);
    gradeForm.value.score = activeSubmission.value.score;
    gradeForm.value.feedback = activeSubmission.value.feedback || '';
    dialog.value = true;
};

const closeDialog = () => {
    dialog.value = false;
    activeStudent.value = null;
    activeSubmission.value = null;
    gradeForm.value.score = null;
    gradeForm.value.feedback = '';
};

const saveGrade = async () => {
    if (!valid.value) return;
    saving.value = true;
    try {
        const response = await axios.post(`/api/guru/assignments/submissions/${activeSubmission.value.id}/grade`, gradeForm.value);
        if (response.data.success) {
            showSuccess(response.data.message);
            fetchSubmissions();
            closeDialog();
        }
    } catch (error) {
        console.error('Error saving grade:', error);
        showError('Gagal menyimpan nilai');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchSubmissions();
});
</script>
