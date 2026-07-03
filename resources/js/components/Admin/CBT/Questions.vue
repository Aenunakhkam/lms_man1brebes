<template>
    <v-dialog :model-value="modelValue" fullscreen transition="dialog-bottom-transition" persistent>
        <v-card color="grey-lighten-4">
            <!-- Main Toolbar -->
            <v-toolbar color="primary" elevation="4">
                <v-btn icon @click="$emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <v-toolbar-title class="font-weight-bold">
                    Kelola Soal: {{ quiz.title }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-chip class="mr-4" color="white" variant="flat" label>
                    <v-icon start icon="mdi-star" color="warning"></v-icon>
                    <span class="text-primary font-weight-black">TOTAL SKOR: {{ totalPoints }}</span>
                </v-chip>
                <v-btn variant="elevated" color="success" class="mr-2" prepend-icon="mdi-plus" @click="openAddDialog">
                    Tambah Soal
                </v-btn>
                <v-btn variant="text" @click="$emit('close')" prepend-icon="mdi-check-circle">
                    Selesai
                </v-btn>
            </v-toolbar>

            <v-container fluid class="pa-4">
                <!-- Stats Summary Row -->
                <v-row v-if="questions.length > 0">
                    <v-col cols="12" sm="4">
                        <v-card border elevation="1" class="text-center pa-4 rounded-lg">
                            <div class="text-caption text-grey text-uppercase font-weight-bold">Jumlah Soal</div>
                            <div class="text-h4 font-weight-black text-primary">{{ questions.length }}</div>
                        </v-card>
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card border elevation="1" class="text-center pa-4 rounded-lg">
                            <div class="text-caption text-grey text-uppercase font-weight-bold">Total Poin</div>
                            <div class="text-h4 font-weight-black" color="primary">{{ totalPoints }}</div>
                        </v-card>
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card border elevation="1" class="text-center pa-4 rounded-lg">
                            <div class="text-caption text-grey text-uppercase font-weight-bold">Rata-rata Poin</div>
                            <div class="text-h4 font-weight-black text-success">{{ Math.round(totalPoints / questions.length) || 0 }}</div>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Data Table for Questions -->
                <v-card border elevation="2" class="mt-4 overflow-hidden rounded-lg">
                    <v-table hover>
                        <thead class="bg-grey-lighten-3">
                            <tr>
                                <th class="text-center font-weight-black" width="60">No</th>
                                <th class="text-left font-weight-black">Pertanyaan & Gambar</th>
                                <th class="text-center font-weight-black" width="100">Opsi</th>
                                <th class="text-center font-weight-black" width="80">Kunci</th>
                                <th class="text-center font-weight-black" width="80">Poin</th>
                                <th class="text-center font-weight-black" width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="6" class="text-center pa-8">
                                    <v-progress-circular indeterminate color="primary" size="50"></v-progress-circular>
                                    <div class="mt-2 text-primary font-weight-bold">Sinkronisasi data...</div>
                                </td>
                            </tr>
                            <tr v-else-if="questions.length === 0">
                                <td colspan="6" class="text-center pa-12 text-grey-darken-1">
                                    <v-icon size="80" color="grey-lighten-2" icon="mdi-text-box-search-outline"></v-icon>
                                    <div class="mt-2 text-h5 font-weight-bold">Belum Ada Soal</div>
                                    <div class="text-subtitle-1">Mulai buat soal pertama Anda untuk instansi resmi ini.</div>
                                    <v-btn color="primary" class="mt-4" prepend-icon="mdi-plus" @click="openAddDialog">Buat Soal Sekarang</v-btn>
                                </td>
                            </tr>
                            <tr v-for="(q, index) in questions" :key="q.id">
                                <td class="text-center">
                                    <v-avatar color="primary" size="28" class="text-white font-weight-bold text-caption">{{ index + 1 }}</v-avatar>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-start gap-4">
                                        <v-img 
                                            v-if="q.question_image" 
                                            :src="'/storage/' + q.question_image" 
                                            width="80" 
                                            height="60" 
                                            cover 
                                            class="rounded border mr-3"
                                        ></v-img>
                                        <div class="flex-grow-1">
                                            <div class="question-text-preview" v-html="q.question_text"></div>
                                            <div class="text-caption text-grey mt-1">
                                                <v-chip v-if="q.question_image" size="x-small" color="info" variant="tonal" class="mr-1">Ada Gambar</v-chip>
                                                <span>A: {{ truncateOption(q.option_a) }} | B: {{ truncateOption(q.option_b) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <v-badge :content="countOptions(q)" color="grey-darken-1" inline></v-badge>
                                </td>
                                <td class="text-center">
                                    <v-chip color="success" size="small" variant="flat" class="font-weight-black">
                                        {{ q.correct_answer.toUpperCase() }}
                                    </v-chip>
                                </td>
                                <td class="text-center">
                                    <span class="font-weight-black text-orange-darken-4">{{ q.points || 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <v-btn icon size="small" variant="text" color="primary" @click="editQuestion(q)">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                    <v-btn icon size="small" variant="text" color="error" @click="deleteQuestion(q)" :disabled="saving">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>
            </v-container>
        </v-card>

        <!-- EDITOR DIALOG (TAMPILAN PROFESIONAL SEPERTI WORD) -->
        <v-dialog v-model="editorOpen" max-width="1000px" persistent scrollable>
            <v-card :loading="saving" class="rounded-lg">
                <v-card-title class="bg-primary text-white py-4 d-flex align-center">
                    <v-icon start large>{{ isEditing ? 'mdi-pencil-box' : 'mdi-plus-box' }}</v-icon>
                    <span class="text-h6 font-weight-black">{{ isEditing ? 'Edit Soal (Mode Professional)' : 'Tambah Soal Baru (Standard Industri)' }}</span>
                    <v-spacer></v-spacer>
                    <v-btn icon variant="text" @click="closeEditor" :disabled="saving">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                
                <v-card-text class="pa-6 bg-white">
                    <v-form ref="form" @submit.prevent="saveQuestion">
                        <v-row>
                            <!-- LEFT SIDE: QUESTION TEXT -->
                            <v-col cols="12" md="8">
                                <label class="text-subtitle-2 font-weight-black mb-1 d-block">NARASI PERTANYAAN (WORD STYLE)</label>
                                <div id="editor-container" class="mb-6" style="height: 300px;"></div>
                                
                                <v-divider class="my-4"></v-divider>
                                
                                <label class="text-subtitle-2 font-weight-black mb-1 d-block">UNGGAH GAMBAR PENDUKUNG (OPSIONAL)</label>
                                <v-file-input
                                    v-model="questionImage"
                                    accept="image/*"
                                    label="Pilih Berkas Gambar"
                                    variant="outlined"
                                    density="comfortable"
                                    prepend-inner-icon="mdi-camera"
                                    prepend-icon=""
                                    show-size
                                    hint="Format: JPG, PNG, GIF. Maks: 2MB"
                                    persistent-hint
                                    @update:model-value="handleImagePreview"
                                ></v-file-input>
                                
                                <v-expand-transition>
                                    <div v-if="imagePreview" class="mt-2 text-center border rounded-lg pa-2 bg-grey-lighten-4">
                                        <v-img :src="imagePreview" max-height="200" contain class="rounded"></v-img>
                                        <div class="text-caption mt-1 text-grey">Pratinjau Gambar Baru</div>
                                    </div>
                                    <div v-else-if="existingImage" class="mt-2 text-center border rounded-lg pa-2 bg-grey-lighten-4">
                                        <v-img :src="'/storage/' + existingImage" max-height="200" contain class="rounded"></v-img>
                                        <div class="text-caption mt-1 text-primary italic">Gambar Saat Ini (Di Database)</div>
                                    </div>
                                </v-expand-transition>
                            </v-col>

                            <!-- RIGHT SIDE: SETTINGS & OPTIONS -->
                            <v-col cols="12" md="4" class="bg-grey-lighten-5 rounded-lg pa-4">
                                <div class="mb-4">
                                    <label class="text-subtitle-2 font-weight-black mb-1 d-block">PENGATURAN SKOR</label>
                                    <v-text-field
                                        v-model="questionForm.points"
                                        label="Poin Bobot Soal"
                                        variant="solo"
                                        type="number"
                                        min="0"
                                        prepend-inner-icon="mdi-star-circle"
                                        bg-color="white"
                                        class="elevation-1"
                                    ></v-text-field>
                                </div>

                                <div class="mb-4">
                                    <label class="text-subtitle-2 font-weight-black mb-1 d-block">JUMLAH OPSI</label>
                                    <v-select
                                        v-model="jumlahOpsi"
                                        :items="pilihanJumlahOpsi"
                                        variant="solo"
                                        bg-color="white"
                                        class="elevation-1"
                                        @update:model-value="onJumlahOpsiChange"
                                    ></v-select>
                                </div>

                                <v-divider class="my-4"></v-divider>

                                <label class="text-subtitle-2 font-weight-black mb-1 d-block">PILIHAN JAWABAN</label>
                                <div v-for="opt in currentOptionsConfig" :key="opt.key" class="mb-2">
                                    <v-text-field
                                        v-model="questionForm['option_' + opt.key]"
                                        :label="`Isi Opsi ${opt.key.toUpperCase()}`"
                                        variant="outlined"
                                        density="compact"
                                        bg-color="white"
                                        hide-details
                                        :rules="[v => !!v || 'Wajib diisi']"
                                    >
                                        <template v-slot:prepend-inner>
                                            <v-icon :color="questionForm.correct_answer === opt.key ? 'success' : 'grey'" size="small">
                                                {{ `mdi-alpha-${opt.key}-circle` }}
                                            </v-icon>
                                        </template>
                                    </v-text-field>
                                </div>

                                <div class="mt-4">
                                    <label class="text-subtitle-2 font-weight-black mb-1 d-block text-success">KUNCI JAWABAN BENAR</label>
                                    <v-select
                                        v-model="questionForm.correct_answer"
                                        :items="kunciJawabanOptions"
                                        variant="solo"
                                        bg-color="green-lighten-5"
                                        class="elevation-2 text-success"
                                        item-title="text"
                                        item-value="value"
                                        :rules="[v => !!v || 'Wajib pilih kunci!']"
                                    ></v-select>
                                </div>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>

                <v-divider></v-divider>
                
                <v-card-actions class="pa-4 bg-grey-lighten-3">
                    <v-btn variant="text" color="grey-darken-1" size="large" @click="closeEditor" :disabled="saving">
                        Batal
                    </v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" variant="elevated" size="x-large" @click="saveQuestion" :loading="saving" prepend-icon="mdi-content-save-check">
                        {{ isEditing ? 'Update & Sinkronkan' : 'Simpan ke Database' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';

const user = JSON.parse(localStorage.getItem('user') || '{}');
const apiPrefix = (user.role === 'guru' || (user.role && user.role.name === 'guru')) ? '/api/guru' : '/api/admin';
import { useAlert } from '../../../composables/useAlert';

const props = defineProps({
    modelValue: Boolean,
    quiz: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'close']);

const { showSuccess, showError, showConfirm } = useAlert();

// --- States ---
const questions = ref([]);
const loading = ref(false);
const saving = ref(false);
const editorOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = ref(null);

// Quill Instance
let quill = null;

const jumlahOpsi = ref(4);
const questionImage = ref(null);
const imagePreview = ref(null);
const existingImage = ref(null);

const questionForm = ref({
    question_text: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    option_e: '',
    correct_answer: null,
    points: 10,
    type: 'multiple_choice'
});

// --- Constants ---
const pilihanJumlahOpsi = [
    { title: 'A - B (2 Opsi)', value: 2 },
    { title: 'A - C (3 Opsi)', value: 3 },
    { title: 'A - D (4 Opsi)', value: 4 },
    { title: 'A - E (5 Opsi)', value: 5 },
];

const totalPoints = computed(() => {
    return questions.value.reduce((sum, q) => sum + (parseInt(q.points) || 0), 0);
});

const kunciJawabanOptions = computed(() => {
    return [
        { text: 'OPSI A', value: 'a' },
        { text: 'OPSI B', value: 'b' },
        { text: 'OPSI C', value: 'c' },
        { text: 'OPSI D', value: 'd' },
        { text: 'OPSI E', value: 'e' },
    ].slice(0, jumlahOpsi.value);
});

const currentOptionsConfig = computed(() => {
    return [
        { key: 'a' },
        { key: 'b' },
        { key: 'c' },
        { key: 'd' },
        { key: 'e' },
    ].slice(0, jumlahOpsi.value);
});

// --- Methods ---
const initQuill = () => {
    nextTick(() => {
        const container = document.getElementById('editor-container');
        if (container && window.Quill) {
            quill = new window.Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['clean']
                    ]
                },
                placeholder: 'Tuliskan pertanyaan ujian di sini seperti di MS Word...'
            });
            
            // Sync content from form
            if (questionForm.value.question_text) {
                quill.root.innerHTML = questionForm.value.question_text;
            }
        }
    });
};

const countOptions = (q) => {
    let count = 0;
    if (q.option_a) count++;
    if (q.option_b) count++;
    if (q.option_c) count++;
    if (q.option_d) count++;
    if (q.option_e) count++;
    return count;
};

const truncateOption = (text) => {
    if (!text) return '';
    return text.length > 30 ? text.substring(0, 30) + '...' : text;
};

const handleImagePreview = (file) => {
    if (file) {
        if (typeof file === 'object' && file[0]) file = file[0]; // Vuetify 3 file input quirk
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
};

const openAddDialog = () => {
    isEditing.value = false;
    editingId.value = null;
    resetFormValues();
    editorOpen.value = true;
    initQuill();
};

const editQuestion = (q) => {
    isEditing.value = true;
    editingId.value = q.id;
    existingImage.value = q.question_image;
    
    // Auto detect jumlah opsi
    if (q.option_e) jumlahOpsi.value = 5;
    else if (q.option_d) jumlahOpsi.value = 4;
    else if (q.option_c) jumlahOpsi.value = 3;
    else jumlahOpsi.value = 2;

    questionForm.value = {
        question_text: q.question_text || '',
        option_a: q.option_a || '',
        option_b: q.option_b || '',
        option_c: q.option_c || '',
        option_d: q.option_d || '',
        option_e: q.option_e || '',
        correct_answer: q.correct_answer || null,
        points: q.points || 10,
        type: q.type || 'multiple_choice'
    };
    
    editorOpen.value = true;
    initQuill();
};

const closeEditor = () => {
    editorOpen.value = false;
    if (quill) quill = null;
};

const onJumlahOpsiChange = (val) => {
    if (val < 5) questionForm.value.option_e = '';
    if (val < 4) questionForm.value.option_d = '';
    if (val < 3) questionForm.value.option_c = '';
    const range = ['a', 'b', 'c', 'd', 'e'].slice(0, val);
    if (!range.includes(questionForm.value.correct_answer)) {
        questionForm.value.correct_answer = null;
    }
};

const loadQuestions = async () => {
    if (!props.quiz?.id) return;
    loading.value = true;
    try {
        const response = await axios.get(`${apiPrefix}/quizzes/${props.quiz.id}/questions`);
        questions.value = response.data.data;
    } catch (error) {
        if (error.response && error.response.status === 404) {
            showError('Ujian Tidak Ditemukan', 'Data ujian ini mungkin sudah dihapus. Halaman akan ditutup.');
            emit('close');
        } else {
            showError('Kesalahan Sinkronisasi', 'Gagal memuat daftar soal.');
        }
    } finally {
        loading.value = false;
    }
};

const saveQuestion = async () => {
    const { valid } = await form.value.validate();
    if (!valid) return;

    saving.value = true;
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('question_text', quill ? quill.root.innerHTML : questionForm.value.question_text);
    formData.append('option_a', questionForm.value.option_a);
    formData.append('option_b', questionForm.value.option_b);
    formData.append('option_c', jumlahOpsi.value >= 3 ? (questionForm.value.option_c || '') : '');
    formData.append('option_d', jumlahOpsi.value >= 4 ? (questionForm.value.option_d || '') : '');
    formData.append('option_e', jumlahOpsi.value >= 5 ? (questionForm.value.option_e || '') : '');
    formData.append('correct_answer', questionForm.value.correct_answer);
    formData.append('points', questionForm.value.points);
    formData.append('type', questionForm.value.type);
    
    if (questionImage.value) {
        // Handle Vuetify 3 file array or single file
        const file = Array.isArray(questionImage.value) ? questionImage.value[0] : questionImage.value;
        formData.append('question_image', file);
    }

    try {
        // Laravel PUT doesn't support FormData out of the box, use POST with _method spoofing
        if (isEditing.value) {
            formData.append('_method', 'PUT');
            await axios.post(`${apiPrefix}/quizzes/${props.quiz.id}/questions/${editingId.value}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            showSuccess('Sinkronisasi Berhasil', 'Soal diperbarui di database.');
        } else {
            await axios.post(`${apiPrefix}/quizzes/${props.quiz.id}/questions`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            showSuccess('Berhasil Disimpan', 'Soal baru telah masuk kedalam sistem.');
        }

        await loadQuestions();
        closeEditor();
    } catch (error) {
        console.error(error);
        if (error.response && error.response.status === 404) {
            showError('Data Tidak Ditemukan', 'Ujian atau soal ini sudah tidak tersedia di database.');
            emit('close');
        } else {
            showError('Gagal Menyimpan', error.response?.data?.message || 'Database menolak permintaan penyimpanan.');
        }
    } finally {
        saving.value = false;
    }
};

const deleteQuestion = async (q) => {
    const confirmed = await showConfirm('Konfirmasi Hapus', 'Data akan dihapus permanen dari storage & database.');
    if (confirmed) {
        saving.value = true;
        try {
            await axios.delete(`${apiPrefix}/quizzes/${props.quiz.id}/questions/${q.id}`);
            await loadQuestions();
            showSuccess('Data Terhapus', 'Soal berhasil dibersihkan.');
        } catch (error) {
            showError('Gagal Menghapus');
        } finally {
            saving.value = false;
        }
    }
};

const resetFormValues = () => {
    jumlahOpsi.value = 4;
    questionImage.value = null;
    imagePreview.value = null;
    existingImage.value = null;
    questionForm.value = {
        question_text: '',
        option_a: '',
        option_b: '',
        option_c: '',
        option_d: '',
        option_e: '',
        correct_answer: null,
        points: 10,
        type: 'multiple_choice'
    };
    if (form.value) form.value.resetValidation();
};

watch(() => props.modelValue, (val) => {
    if (val) {
        loadQuestions();
        resetFormValues();
    }
}, { immediate: true });
</script>

<style>
/* Quill Overrides for Professional Look */
.ql-container {
    font-family: 'Inter', sans-serif !important;
    font-size: 16px !important;
}
.ql-editor {
    min-height: 200px;
}
.question-text-preview p {
    margin: 0;
}
.question-text-view img {
    max-width: 100%;
}
</style>
