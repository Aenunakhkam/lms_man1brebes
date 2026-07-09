<template>
    <v-container fluid class="pa-6">
        <div class="d-flex align-center justify-space-between mb-6">
            <div>
                <h1 class="text-h4 font-weight-bold mb-1">Tugas Siswa</h1>
                <p class="text-subtitle-1 text-grey">Kelola tugas dan evaluasi siswa</p>
            </div>
            <v-btn
                color="warning"
                prepend-icon="mdi-plus"
                size="large"
                rounded="lg"
                elevation="2"
                @click="openDialog()"
            >
                Tambah Tugas
            </v-btn>
        </div>

        <v-card rounded="xl" elevation="2">
            <v-data-table
                :headers="headers"
                :items="assignments"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.deadline="{ item }">
                    {{ formatDate(item.deadline) }}
                </template>
                <template v-slot:item.attachment="{ item }">
                    <v-btn 
                        v-if="item.attachment"
                        color="info" 
                        variant="tonal" 
                        size="small" 
                        prepend-icon="mdi-download"
                        :href="`/storage/${item.attachment}`"
                        target="_blank"
                        rounded="lg"
                    >
                        Unduh
                    </v-btn>
                    <span v-else class="text-grey text-caption">Tidak ada</span>
                </template>
                <template v-slot:item.submissions_count="{ item }">
                    <v-chip color="primary" size="small" rounded="lg">
                        {{ item.submissions_count }} Terkumpul
                    </v-chip>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon color="primary" variant="text" size="small" :to="`/guru/assignments/${item.id}/submissions`">
                        <v-icon>mdi-eye</v-icon>
                        <v-tooltip activator="parent" location="top">Lihat Pengumpulan</v-tooltip>
                    </v-btn>
                    <v-btn icon color="info" variant="text" size="small" @click="openDialog(item)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon color="error" variant="text" size="small" @click="confirmDelete(item)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Formulir -->
        <v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card rounded="xl">
                <v-card-title class="pa-6 d-flex justify-space-between align-center">
                    <span class="text-h5 font-weight-bold">
                        {{ editedItem.id ? 'Edit Tugas' : 'Tambah Tugas' }}
                    </span>
                    <v-btn icon variant="text" @click="closeDialog">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-divider></v-divider>

                <v-card-text class="pa-6">
                    <v-form ref="form" v-model="valid">
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.title"
                                    label="Judul Tugas"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                    :rules="[v => !!v || 'Judul wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.class_id"
                                    :items="classes"
                                    item-title="name"
                                    item-value="id"
                                    label="Kelas"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                    :rules="[v => !!v || 'Kelas wajib diisi']"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.subject_id"
                                    :items="subjects"
                                    item-title="name"
                                    item-value="id"
                                    label="Mata Pelajaran"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                    :rules="[v => !!v || 'Mapel wajib diisi']"
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="editedItem.description"
                                    label="Instruksi Tugas"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                    rows="3"
                                    :rules="[v => !!v || 'Instruksi tugas wajib diisi']"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12">
                                <v-file-input
                                    v-model="editedItem.attachment_file"
                                    label="File Lampiran Tugas (Opsional)"
                                    variant="outlined"
                                    rounded="lg"
                                    prepend-icon="mdi-paperclip"
                                    hint="Maks. 10MB (Bisa berupa PDF, Word, PPT, Excel, dll)"
                                    persistent-hint
                                ></v-file-input>
                                
                                <div v-if="editedItem.id && editedItem.attachment && !editedItem.remove_attachment" class="mt-2 d-flex align-center">
                                    <v-icon color="info" class="mr-2">mdi-file-document</v-icon>
                                    <span class="text-body-2 mr-4">File lampiran saat ini tersimpan</span>
                                    <v-btn size="small" color="error" variant="text" @click="editedItem.remove_attachment = true">
                                        Hapus File Ini
                                    </v-btn>
                                </div>
                                <div v-if="editedItem.id && editedItem.remove_attachment" class="mt-2">
                                    <span class="text-error text-caption">File lama akan dihapus saat disimpan</span>
                                    <v-btn size="small" color="primary" variant="text" @click="editedItem.remove_attachment = false">
                                        Batal Hapus
                                    </v-btn>
                                </div>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.start_time"
                                    label="Waktu Mulai (Open)"
                                    type="datetime-local"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                    :rules="[v => !!v || 'Waktu mulai wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.deadline"
                                    label="Waktu Selesai (Closed)"
                                    type="datetime-local"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                    :rules="[v => !!v || 'Waktu selesai wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.max_score"
                                    label="Poin Maksimal"
                                    type="number"
                                    variant="outlined"
                                    rounded="lg"
                                    min="1"
                                    max="100"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>

                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn variant="text" rounded="lg" @click="closeDialog" :disabled="saving">Batal</v-btn>
                    <v-btn color="warning" variant="flat" rounded="lg" @click="save" :loading="saving" :disabled="!valid">Simpan</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const { showSuccess, showError, showConfirm } = useAlert();

const assignments = ref([]);
const classes = ref([]);
const subjects = ref([]);
const loading = ref(false);
const dialog = ref(false);
const saving = ref(false);
const valid = ref(false);
const form = ref(null);

const editedItem = ref({
    id: null,
    title: '',
    class_id: null,
    subject_id: null,
    description: '',
    start_time: '',
    deadline: '',
    max_score: 100,
    attachment: null,
    attachment_file: null,
    remove_attachment: false
});

const defaultItem = {
    id: null,
    title: '',
    class_id: null,
    subject_id: null,
    description: '',
    start_time: '',
    deadline: '',
    max_score: 100,
    attachment: null,
    attachment_file: null,
    remove_attachment: false
};

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Judul', key: 'title' },
    { title: 'Kelas', key: 'class.name' },
    { title: 'Mapel', key: 'subject.name' },
    { title: 'File Tugas', key: 'attachment', sortable: false, align: 'center' },
    { title: 'Deadline', key: 'deadline' },
    { title: 'Status', key: 'submissions_count', sortable: false },
    { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
];

const fetchAssignments = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/guru/assignments');
        if (response.data.success) {
            assignments.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching assignments:', error);
    } finally {
        loading.value = false;
    }
};

const fetchInitialData = async () => {
    try {
        const [classRes, subjectRes] = await Promise.all([
            axios.get('/api/guru/classes'),
            axios.get('/api/guru/subjects')
        ]);
        classes.value = classRes.data.data;
        subjects.value = subjectRes.data.data;
    } catch (error) {
        console.error('Error fetching initial data:', error);
    }
};

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

const openDialog = (item = null) => {
    if (item) {
        editedItem.value = { 
            ...item,
            start_time: formatForInput(item.start_time),
            deadline: formatForInput(item.deadline),
            attachment_file: null,
            remove_attachment: false
        };
    } else {
        editedItem.value = { ...defaultItem };
    }
    dialog.value = true;
};

const closeDialog = () => {
    dialog.value = false;
};

const save = async () => {
    if (!valid.value) return;
    saving.value = true;
    try {
        const url = editedItem.value.id 
            ? `/api/guru/assignments/${editedItem.value.id}` 
            : '/api/guru/assignments';
            
        const formData = new FormData();
        formData.append('title', editedItem.value.title);
        formData.append('class_id', editedItem.value.class_id);
        formData.append('subject_id', editedItem.value.subject_id);
        formData.append('description', editedItem.value.description || '');
        formData.append('start_time', editedItem.value.start_time);
        formData.append('deadline', editedItem.value.deadline);
        if (editedItem.value.max_score) formData.append('max_score', editedItem.value.max_score);
        
        if (editedItem.value.attachment_file) {
            const file = Array.isArray(editedItem.value.attachment_file) ? editedItem.value.attachment_file[0] : editedItem.value.attachment_file;
            if (file) {
                formData.append('attachment', file);
            }
        }
        
        if (editedItem.value.id) {
            formData.append('_method', 'put');
            if (editedItem.value.remove_attachment) {
                formData.append('remove_attachment', '1');
            }
        }

        const response = await axios.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.success) {
            showSuccess(response.data.message);
            fetchAssignments();
            closeDialog();
        }
    } catch (error) {
        console.error('Error saving assignment:', error);
        showError('Gagal menyimpan tugas', error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        saving.value = false;
    }
};

const confirmDelete = async (item) => {
    const confirmed = await showConfirm('Apakah Anda yakin ingin menghapus tugas ini?');
    if (confirmed) {
        try {
            const response = await axios.delete(`/api/guru/assignments/${item.id}`);
            showSuccess(response.data.message);
            fetchAssignments();
        } catch (error) {
            console.error('Error deleting assignment:', error);
            showError('Gagal menghapus tugas');
        }
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    fetchAssignments();
    fetchInitialData();
});
</script>
