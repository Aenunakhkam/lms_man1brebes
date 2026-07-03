<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="d-flex align-center mb-6">
            <v-btn icon="mdi-arrow-left" variant="text" to="/siswa/assignments" class="mr-4"></v-btn>
            <div>
                <h1 class="text-h4 font-weight-bold mb-1">Detail Tugas</h1>
                <p class="text-subtitle-1 text-grey" v-if="assignment">{{ assignment.title }}</p>
            </div>
        </div>

        <v-row v-if="assignment">
            <!-- Informasi Tugas -->
            <v-col cols="12" md="8">
                <v-card rounded="xl" elevation="2" class="h-100">
                    <v-card-text class="pa-6">
                        <div class="d-flex align-center justify-space-between mb-4">
                            <div>
                                <div class="text-h6 font-weight-bold">{{ assignment.title }}</div>
                                <div class="text-subtitle-2 text-primary font-weight-bold">{{ assignment.subject?.name }}</div>
                            </div>
                            <v-chip :color="assignment.is_submitted ? 'success' : (isClosed ? 'error' : (isOpen ? 'warning' : 'grey'))" rounded="lg">
                                {{ assignment.is_submitted ? 'Sudah Dikumpulkan' : (isClosed ? 'Ditutup' : (isOpen ? 'Sedang Berlangsung' : 'Belum Dibuka')) }}
                            </v-chip>
                        </div>

                        <v-divider class="mb-4"></v-divider>

                        <div class="mb-6">
                            <div class="text-subtitle-2 text-grey mb-2">Instruksi Tugas:</div>
                            <div class="text-body-1 bg-grey-lighten-4 pa-4 rounded-lg" style="white-space: pre-wrap;">
                                {{ assignment.description }}
                            </div>
                        </div>

                        <!-- Hasil Penilaian Jika Ada -->
                        <div v-if="assignment.is_submitted && assignment.submission?.status === 'graded'" class="bg-success-lighten-5 pa-4 rounded-lg border-success border">
                            <h3 class="text-success-darken-2 d-flex align-center mb-2">
                                <v-icon class="mr-2">mdi-check-decagram</v-icon> Hasil Penilaian
                            </h3>
                            <v-row>
                                <v-col cols="12" sm="4">
                                    <div class="text-subtitle-2 text-grey">Nilai:</div>
                                    <div class="text-h5 font-weight-bold text-success-darken-2">
                                        {{ assignment.submission.score }} / {{ assignment.max_points || 100 }}
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="8" v-if="assignment.submission.feedback">
                                    <div class="text-subtitle-2 text-grey">Komentar Guru:</div>
                                    <div class="text-body-2 font-italic">"{{ assignment.submission.feedback }}"</div>
                                </v-col>
                            </v-row>
                        </div>

                    </v-card-text>
                </v-card>
            </v-col>

            <!-- Form Pengumpulan / Info Waktu -->
            <v-col cols="12" md="4">
                <v-card rounded="xl" elevation="2" class="mb-6">
                    <v-card-text class="pa-5">
                        <div class="d-flex align-center mb-3">
                            <v-icon color="primary" class="mr-2">mdi-calendar-clock</v-icon>
                            <span class="font-weight-bold">Waktu Pengerjaan</span>
                        </div>
                        <div class="mb-2">
                            <div class="text-caption text-grey">Buka:</div>
                            <div class="text-body-2">{{ formatDate(assignment.start_time) }}</div>
                        </div>
                        <div>
                            <div class="text-caption text-grey">Tutup (Deadline):</div>
                            <div class="text-body-2 text-error font-weight-bold">{{ formatDate(assignment.deadline) }}</div>
                        </div>
                    </v-card-text>
                </v-card>

                <!-- Box Kumpulkan Tugas -->
                <v-card rounded="xl" elevation="2" v-if="!assignment.is_submitted">
                    <v-card-text class="pa-5">
                        <h3 class="font-weight-bold mb-4">Pengumpulan Jawaban</h3>
                        
                        <div v-if="!isOpen" class="text-center pa-4 text-grey">
                            <v-icon size="40" class="mb-2">mdi-lock-clock</v-icon>
                            <div>Tugas ini belum dibuka. Silakan tunggu hingga waktu buka tiba.</div>
                        </div>
                        <div v-else-if="isClosed" class="text-center pa-4 text-error">
                            <v-icon size="40" class="mb-2">mdi-close-circle-outline</v-icon>
                            <div>Tugas ini sudah ditutup. Waktu pengerjaan telah habis.</div>
                        </div>
                        <div v-else>
                            <v-form ref="form" v-model="valid">
                                <v-textarea
                                    v-model="submissionData.content"
                                    label="Ketik jawaban di sini (opsional)"
                                    variant="outlined"
                                    rounded="lg"
                                    rows="4"
                                    class="mb-3"
                                ></v-textarea>
                                
                                <v-file-input
                                    v-model="submissionData.file"
                                    label="Upload File Jawaban (opsional)"
                                    variant="outlined"
                                    rounded="lg"
                                    prepend-icon="mdi-paperclip"
                                    hint="Maks. 10MB"
                                    persistent-hint
                                    class="mb-4"
                                ></v-file-input>

                                <v-btn 
                                    color="primary" 
                                    block 
                                    size="large" 
                                    rounded="lg" 
                                    :loading="submitting"
                                    :disabled="!isValidSubmission"
                                    @click="submitAssignment"
                                >
                                    Kumpulkan Tugas
                                </v-btn>
                            </v-form>
                        </div>
                    </v-card-text>
                </v-card>
                
                <!-- Info Sudah Dikumpulkan -->
                <v-card rounded="xl" elevation="2" v-else>
                    <v-card-text class="pa-5">
                        <!-- Status Dinilai -->
                        <div v-if="assignment.submission.status === 'graded'" class="text-center">
                            <v-icon color="success" size="48" class="mb-2">mdi-check-decagram</v-icon>
                            <h3 class="font-weight-bold text-success mb-1">Sudah Dinilai</h3>
                            <div class="text-caption text-grey mb-3">
                                Dikumpulkan pada: {{ formatDate(assignment.submission.submitted_at) }}
                            </div>
                            <div v-if="assignment.submission.file_path" class="mt-2">
                                <v-btn color="primary" variant="tonal" size="small"
                                    :href="`/storage/${assignment.submission.file_path}`" target="_blank"
                                    prepend-icon="mdi-file-download">
                                    Lihat File Tersimpan
                                </v-btn>
                            </div>
                        </div>

                        <!-- Status Belum Dinilai - Bisa Dibatalkan -->
                        <div v-else class="text-center">
                            <v-icon color="primary" size="48" class="mb-2">mdi-check-circle</v-icon>
                            <h3 class="font-weight-bold text-primary mb-1">Sudah Terkumpul</h3>
                            <v-chip color="warning" size="small" rounded="lg" class="mb-3">Menunggu Penilaian</v-chip>
                            <div class="text-caption text-grey mb-4">
                                Dikumpulkan pada: {{ formatDate(assignment.submission.submitted_at) }}
                            </div>

                            <div v-if="assignment.submission.file_path" class="mb-4">
                                <v-btn color="primary" variant="tonal" size="small"
                                    :href="`/storage/${assignment.submission.file_path}`" target="_blank"
                                    prepend-icon="mdi-file-download" class="mr-2">
                                    Lihat File
                                </v-btn>
                            </div>

                            <v-divider class="mb-4"></v-divider>

                            <div class="text-caption text-grey mb-3">
                                Jika ada kesalahan, Anda masih dapat membatalkan pengiriman selama tugas belum dinilai.
                            </div>
                            <v-btn
                                color="error"
                                variant="tonal"
                                rounded="lg"
                                block
                                prepend-icon="mdi-close-circle"
                                :loading="cancelling"
                                @click="cancelSubmission"
                            >
                                Batalkan Pengiriman
                            </v-btn>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
        
        <!-- Loading -->
        <v-row v-else-if="loading" justify="center" class="mt-12">
            <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const route = useRoute();
const router = useRouter();
const { showSuccess, showError, showConfirm } = useAlert();

const assignment = ref(null);
const loading = ref(false);
const submitting = ref(false);
const cancelling = ref(false);
const valid = ref(false);

const submissionData = ref({
    content: '',
    file: null
});

const isValidSubmission = computed(() => {
    return submissionData.value.content.trim() !== '' || (submissionData.value.file && submissionData.value.file.length > 0);
});

const isOpen = computed(() => {
    if (!assignment.value) return false;
    if (!assignment.value.start_time) return true;
    return new Date() >= new Date(assignment.value.start_time);
});

const isClosed = computed(() => {
    if (!assignment.value) return false;
    if (!assignment.value.deadline) return false;
    return new Date() > new Date(assignment.value.deadline);
});

const fetchAssignment = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/siswa/assignments/${route.params.id}`);
        if (response.data.success) {
            assignment.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching assignment:', error);
        showError('Gagal memuat tugas', 'Tugas mungkin tidak tersedia');
        router.push('/siswa/assignments');
    } finally {
        loading.value = false;
    }
};

const submitAssignment = async () => {
    if (!isValidSubmission.value) return;
    
    submitting.value = true;
    const formData = new FormData();
    if (submissionData.value.content) formData.append('content', submissionData.value.content);
    if (submissionData.value.file && submissionData.value.file[0]) formData.append('file', submissionData.value.file[0]);

    try {
        const response = await axios.post(`/api/siswa/assignments/${assignment.value.id}/submit`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        if (response.data.success) {
            showSuccess(response.data.message);
            fetchAssignment(); // reload data
        }
    } catch (error) {
        console.error('Error submitting assignment:', error);
        showError('Gagal mengumpulkan tugas', error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        submitting.value = false;
    }
};

const cancelSubmission = async () => {
    const confirmed = await showConfirm(
        'Batalkan pengiriman tugas?',
        'Pengiriman tugas Anda akan dihapus. File yang sudah diupload juga akan ikut dihapus. Anda masih bisa mengumpulkan kembali sebelum deadline.'
    );
    if (!confirmed) return;

    cancelling.value = true;
    try {
        const response = await axios.delete(`/api/siswa/assignments/${assignment.value.id}/cancel`);
        if (response.data.success) {
            showSuccess(response.data.message);
            // Reset form
            submissionData.value.content = '';
            submissionData.value.file = null;
            fetchAssignment();
        }
    } catch (error) {
        console.error('Error cancelling submission:', error);
        showError('Gagal membatalkan pengiriman', error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        cancelling.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    fetchAssignment();
});
</script>
