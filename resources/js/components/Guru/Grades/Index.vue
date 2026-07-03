<template>
    <v-container fluid class="pa-6">
        <div class="mb-6 d-flex align-center justify-space-between">
            <div>
                <h1 class="text-h4 font-weight-bold mb-1">Manajemen Nilai</h1>
                <p class="text-subtitle-1 text-grey">Input data nilai dan lihat rekapitulasi penilaian</p>
            </div>
            <v-tabs v-model="activeTab" bg-color="transparent" color="primary" rounded="lg">
                <v-tab value="input" prepend-icon="mdi-pencil-plus">Input Nilai</v-tab>
                <v-tab value="recap" prepend-icon="mdi-table-large">Rekapitulasi</v-tab>
            </v-tabs>
        </div>

        <v-window v-model="activeTab">
            <!-- TAB INPUT NILAI -->
            <v-window-item value="input">
                <v-row>
                    <v-col cols="12" md="4">
                        <v-card rounded="xl" elevation="2" class="pa-6 mb-6 border-card">
                            <div class="d-flex align-center mb-6">
                                <v-avatar color="primary-lighten-4" class="mr-3" size="40">
                                    <v-icon color="primary">mdi-filter-variant</v-icon>
                                </v-avatar>
                                <h3 class="text-h6 font-weight-bold">Filter Input</h3>
                            </div>

                            <v-form v-model="filterValid">
                                <div class="mb-4">
                                    <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Pilih Kelas</label>
                                    <v-select
                                        v-model="filters.class_id"
                                        :items="classes"
                                        item-title="name"
                                        item-value="id"
                                        placeholder="Pilih Kelas"
                                        variant="outlined"
                                        rounded="lg"
                                        density="comfortable"
                                        required
                                        @update:model-value="fetchStudents"
                                        prepend-inner-icon="mdi-google-classroom"
                                    ></v-select>
                                </div>

                                <div class="mb-4">
                                    <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Mata Pelajaran</label>
                                    <v-select
                                        v-model="filters.subject_id"
                                        :items="subjects"
                                        item-title="name"
                                        item-value="id"
                                        placeholder="Pilih Mata Pelajaran"
                                        variant="outlined"
                                        rounded="lg"
                                        density="comfortable"
                                        required
                                        prepend-inner-icon="mdi-book-open-variant"
                                    ></v-select>
                                </div>

                                <div class="mb-6">
                                    <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Kategori / Jenis Nilai</label>
                                    <v-combobox
                                        v-model="filters.category"
                                        :items="categories"
                                        placeholder="Ketik atau pilih jenis nilai..."
                                        variant="outlined"
                                        rounded="lg"
                                        density="comfortable"
                                        required
                                        hint="Contoh: UTS, UAS, Harian 1"
                                        persistent-hint
                                        prepend-inner-icon="mdi-tag-outline"
                                    ></v-combobox>
                                </div>

                                <v-btn
                                    color="primary"
                                    block
                                    size="large"
                                    rounded="lg"
                                    variant="flat"
                                    prepend-icon="mdi-magnify"
                                    @click="loadGrades"
                                    :loading="loading"
                                    :disabled="!filters.class_id || !filters.subject_id || !filters.category"
                                    class="text-none"
                                >
                                    Tampilkan Daftar
                                </v-btn>
                            </v-form>
                        </v-card>
                    </v-col>

                    <v-col cols="12" md="8">
                        <v-card rounded="xl" elevation="2" v-if="students.length" class="border-card">
                            <v-card-title class="pa-6 d-flex align-center justify-space-between">
                                <div>
                                    <span class="text-h6 font-weight-bold d-block">Daftar Nilai Siswa</span>
                                    <v-chip size="small" color="primary" variant="flat" class="mt-1">
                                        Kategori: {{ filters.category }}
                                    </v-chip>
                                </div>
                                <div class="d-flex gap-2">
                                    <v-btn
                                        color="error"
                                        variant="tonal"
                                        rounded="lg"
                                        prepend-icon="mdi-delete"
                                        @click="confirmDeleteGrade"
                                        class="text-none mr-2"
                                        title="Hapus semua nilai di kategori ini"
                                    >
                                        Hapus
                                    </v-btn>
                                    <v-btn
                                        color="success"
                                        variant="flat"
                                        rounded="lg"
                                        prepend-icon="mdi-content-save"
                                        @click="saveGrades"
                                        :loading="saving"
                                        class="text-none px-6"
                                    >
                                        Simpan Nilai
                                    </v-btn>
                                </div>
                            </v-card-title>
                            <v-divider></v-divider>
                            <v-table hover>
                                <thead>
                                    <tr>
                                        <th class="text-left" style="width: 60px;">NO.</th>
                                        <th class="text-left">NAMA SISWA</th>
                                        <th class="text-center" style="width: 140px;">NILAI (0-100)</th>
                                        <th class="text-left">CATATAN</th>
                                        <th class="text-center" style="width: 120px;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(student, index) in students" :key="student.id">
                                        <td class="text-grey font-weight-medium">{{ index + 1 }}</td>
                                        <td class="py-4">
                                            <div class="d-flex align-center">
                                                <v-avatar size="32" color="primary-lighten-4" class="mr-3">
                                                    <v-img v-if="student.photo" :src="Laravel.assetUrl + 'storage/' + student.photo"></v-img>
                                                    <span v-else class="text-caption font-weight-bold text-primary">{{ student.name.charAt(0) }}</span>
                                                </v-avatar>
                                                <div>
                                                    <div class="font-weight-bold">{{ student.name }}</div>
                                                    <div class="text-caption text-grey">NIS: {{ student.nis }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <v-text-field
                                                v-model="gradeData[student.id]"
                                                type="number"
                                                min="0"
                                                max="100"
                                                density="compact"
                                                variant="outlined"
                                                hide-details
                                                rounded="lg"
                                                class="grade-input"
                                            ></v-text-field>
                                        </td>
                                        <td>
                                            <v-text-field
                                                v-model="noteData[student.id]"
                                                density="compact"
                                                variant="outlined"
                                                placeholder="..."
                                                hide-details
                                                rounded="lg"
                                            ></v-text-field>
                                        </td>
                                        <td class="text-center">
                                            <!-- Tampil jika nilai sudah ada (ada gradeIdMap) -->
                                            <template v-if="gradeIdMap[student.id]">
                                                <v-btn
                                                    icon
                                                    color="info"
                                                    variant="text"
                                                    size="small"
                                                    @click="openEditDialog(student)"
                                                >
                                                    <v-icon>mdi-pencil</v-icon>
                                                    <v-tooltip activator="parent" location="top">Edit Nilai</v-tooltip>
                                                </v-btn>
                                                <v-btn
                                                    icon
                                                    color="error"
                                                    variant="text"
                                                    size="small"
                                                    @click="confirmDeleteSingleGrade(student)"
                                                >
                                                    <v-icon>mdi-delete</v-icon>
                                                    <v-tooltip activator="parent" location="top">Hapus Nilai</v-tooltip>
                                                </v-btn>
                                            </template>
                                            <span v-else class="text-caption text-grey">Belum ada</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </v-table>

                        </v-card>
                        <v-card v-else rounded="xl" class="pa-12 text-center text-grey border-dashed" elevation="0">
                            <v-icon size="80" color="grey-lighten-2" class="mb-4">mdi-clipboard-edit-outline</v-icon>
                            <div class="text-h6 font-weight-medium text-grey-darken-1">Siap untuk Menginput Nilai?</div>
                            <div class="mt-1">Pilih kelas, mata pelajaran, dan kategori di samping untuk memulai.</div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-window-item>

            <!-- TAB REKAP NILAI -->
            <v-window-item value="recap">
                <v-card rounded="xl" elevation="2" class="border-card pa-6 mb-6">
                    <v-row align="end">
                        <v-col cols="12" md="3">
                            <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Pilih Kelas</label>
                            <v-select
                                v-model="recapFilters.class_id"
                                :items="classes"
                                item-title="name"
                                item-value="id"
                                placeholder="Pilih Kelas"
                                variant="outlined"
                                rounded="lg"
                                density="comfortable"
                                hide-details
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="4">
                            <label class="text-subtitle-2 font-weight-bold mb-2 d-block">Mata Pelajaran</label>
                            <v-select
                                v-model="recapFilters.subject_id"
                                :items="subjects"
                                item-title="name"
                                item-value="id"
                                placeholder="Pilih Mata Pelajaran"
                                variant="outlined"
                                rounded="lg"
                                density="comfortable"
                                hide-details
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="2">
                            <v-btn
                                color="primary"
                                block
                                size="large"
                                rounded="lg"
                                prepend-icon="mdi-magnify"
                                @click="loadRecap"
                                :loading="loadingRecap"
                                :disabled="!recapFilters.class_id || !recapFilters.subject_id"
                            >
                                Cari
                            </v-btn>
                        </v-col>
                        <v-spacer></v-spacer>
                        <v-col cols="12" md="3" v-if="recapData.students.length" class="text-right">
                            <v-btn
                                color="success"
                                variant="flat"
                                rounded="lg"
                                prepend-icon="mdi-file-excel"
                                @click="exportExcel"
                                class="mr-2 text-none"
                            >Excel</v-btn>
                            <v-btn
                                color="error"
                                variant="flat"
                                rounded="lg"
                                prepend-icon="mdi-file-pdf-box"
                                @click="exportPdf"
                                class="text-none"
                            >PDF</v-btn>
                        </v-col>
                    </v-row>
                </v-card>

                <v-card v-if="recapData.students.length" rounded="xl" elevation="2" class="border-card overflow-hidden">
                    <div class="pa-6 border-b">
                        <h3 class="text-h6 font-weight-bold">Rekapitulasi Nilai Akhir</h3>
                        <p class="text-caption text-grey">Menampilkan semua kategori nilai yang telah diinputkan</p>
                    </div>
                    <v-table hover>
                        <thead>
                            <tr>
                                <th class="text-left" style="min-width: 50px;">NO</th>
                                <th class="text-left" style="min-width: 200px;">NAMA SISWA</th>
                                <th v-for="cat in recapData.categories" :key="cat" class="text-center font-weight-bold" style="min-width: 80px;">
                                    {{ cat }}
                                </th>
                                <th class="text-center success white--text font-weight-black" style="min-width: 100px; background-color: #4CAF50 !important;">
                                    NILAI AKHIR
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in recapData.students" :key="student.id">
                                <td>{{ index + 1 }}</td>
                                <td class="font-weight-bold">{{ student.name }}</td>
                                <td v-for="cat in recapData.categories" :key="cat" class="text-center">
                                    {{ getGrade(student.id, cat) }}
                                </td>
                                <td class="text-center font-weight-black text-primary bg-grey-lighten-4">
                                    {{ calculateFinalGrade(student.id) }}
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>
                <div v-else class="text-center pa-12 text-grey">
                    <v-icon size="80" color="grey-lighten-3">mdi-table-search</v-icon>
                    <p class="mt-4">Pilih kelas dan mata pelajaran untuk melihat rekap nilai.</p>
                </div>
            </v-window-item>
        </v-window>

    <!-- Dialog Edit Nilai Siswa -->
    <v-dialog v-model="editDialog" max-width="420px" persistent>
        <v-card rounded="xl">
            <v-card-title class="pa-6 d-flex justify-space-between align-center">
                <span class="text-h6 font-weight-bold">Edit Nilai Siswa</span>
                <v-btn icon variant="text" @click="editDialog = false"><v-icon>mdi-close</v-icon></v-btn>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-6" v-if="editTarget">
                <div class="mb-4">
                    <div class="text-subtitle-2 text-grey">Siswa:</div>
                    <div class="font-weight-bold">{{ editTarget.name }}</div>
                </div>
                <v-text-field
                    v-model.number="editForm.score"
                    label="Nilai (0–100)"
                    type="number"
                    min="0"
                    max="100"
                    variant="outlined"
                    rounded="lg"
                    class="mb-3"
                    :rules="[v => v >= 0 && v <= 100 || 'Nilai 0-100']"
                ></v-text-field>
                <v-textarea
                    v-model="editForm.notes"
                    label="Catatan (opsional)"
                    variant="outlined"
                    rounded="lg"
                    rows="2"
                    hide-details
                ></v-textarea>
            </v-card-text>
            <v-card-actions class="pa-6 pt-0">
                <v-spacer></v-spacer>
                <v-btn variant="text" rounded="lg" @click="editDialog = false">Batal</v-btn>
                <v-btn color="info" variant="flat" rounded="lg" :loading="editSaving" @click="saveEditGrade">Simpan Perubahan</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Dialog Hapus Semua -->
        <v-dialog v-model="deleteDialog" max-width="400" persistent>
            <v-card rounded="xl" class="pa-6">
                <div class="text-center mb-4">
                    <v-avatar color="error-lighten-4" size="64">
                        <v-icon color="error" size="32">mdi-alert-octagon</v-icon>
                    </v-avatar>
                </div>
                <h3 class="text-h6 font-weight-bold text-center mb-2">Hapus Nilai Kategori?</h3>
                <p class="text-center text-grey mb-6">
                    Tindakan ini akan menghapus semua nilai siswa pada kategori <strong>{{ filters.category }}</strong>. Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="d-flex gap-4 w-100">
                    <v-btn
                        variant="tonal"
                        rounded="lg"
                        @click="deleteDialog = false"
                        class="flex-grow-1"
                    >Batal</v-btn>
                    <v-btn
                        color="error"
                        rounded="lg"
                        variant="flat"
                        @click="deleteGrades"
                        :loading="deleting"
                        class="flex-grow-1"
                    >Ya, Hapus</v-btn>
                </div>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" rounded="lg">
            {{ snackbar.message }}
        </v-snackbar>
    </v-container>
</template>

<script setup>
const Laravel = window.Laravel;
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';
const { showSuccess, showError, showConfirm } = useAlert();

const activeTab = ref('input');
const classes = ref([]);
const subjects = ref([]);
const students = ref([]);
const gradeData = ref({});
const noteData = ref({});
const filters = ref({
    class_id: null,
    subject_id: null,
    category: 'Harian 1'
});
const recapFilters = ref({
    class_id: null,
    subject_id: null
});
const categories = ref(['Tugas 1', 'Tugas 2', 'Tugas 3', 'UH 1', 'UH 2', 'UTS', 'UAS', 'Harian 1', 'Harian 2']);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const filterValid = ref(false);
const deleteDialog = ref(false);
const editDialog = ref(false);
const editSaving = ref(false);
const editTarget = ref(null);
const editForm = ref({ score: 0, notes: '' });
const gradeIdMap = ref({}); // map: student_id -> grade id

const recapData = ref({
    students: [],
    categories: [],
    grades: []
});
const loadingRecap = ref(false);

const snackbar = ref({
    show: false,
    message: '',
    color: 'success'
});

const showSnackbar = (message, color = 'success') => {
    snackbar.value = { show: true, message, color };
};

const fetchClasses = async () => {
    try {
        const response = await axios.get('/api/guru/classes');
        classes.value = response.data.data;
    } catch (error) {
        console.error('Error fetching classes:', error);
    }
};

const fetchSubjects = async () => {
    try {
        const response = await axios.get('/api/guru/subjects');
        subjects.value = response.data.data;
    } catch (error) {
        console.error('Error fetching subjects:', error);
    }
};

const fetchStudents = async () => {
    if (!filters.value.class_id) return;
    loading.value = true;
    try {
        const response = await axios.get(`/api/guru/classes/${filters.value.class_id}/students`);
        students.value = response.data.data;
        students.value.forEach(s => {
            if (gradeData.value[s.id] === undefined) gradeData.value[s.id] = 0;
            if (noteData.value[s.id] === undefined) noteData.value[s.id] = '';
        });
    } catch (error) {
        console.error('Error fetching students:', error);
    } finally {
        loading.value = false;
    }
};

const loadGrades = async () => {
    loading.value = true;
    gradeIdMap.value = {};
    await fetchStudents();
    try {
        const response = await axios.get('/api/guru/grades', { params: filters.value });
        if (response.data.success && response.data.data.length) {
            response.data.data.forEach(item => {
                gradeData.value[item.student_id] = item.score;
                noteData.value[item.student_id] = item.notes;
                gradeIdMap.value[item.student_id] = item.id; // simpan ID grade per siswa
            });
            showSnackbar('Data nilai ditemukan dan dimuat');
        } else {
            students.value.forEach(s => {
                gradeData.value[s.id] = 0;
                noteData.value[s.id] = '';
            });
            showSnackbar('Belum ada nilai untuk kategori ini', 'info');
        }
    } catch (error) {
        console.error('Error loading grades:', error);
    } finally {
        loading.value = false;
    }
};

const saveGrades = async () => {
    saving.value = true;
    try {
        const payload = {
            class_id: filters.value.class_id,
            subject_id: filters.value.subject_id,
            category: filters.value.category,
            grades: Object.keys(gradeData.value)
                .filter(id => students.value.some(s => s.id == id))
                .map(studentId => ({
                    student_id: studentId,
                    score: gradeData.value[studentId],
                    note: noteData.value[studentId] || ''
                }))
        };
        const response = await axios.post('/api/guru/grades', payload);
        if (response.data.success) {
            if (!categories.value.includes(filters.value.category)) {
                categories.value.push(filters.value.category);
            }
            showSnackbar('Nilai berhasil disimpan!');
        }
    } catch (error) {
        console.error('Error saving grades:', error);
        showSnackbar('Gagal menyimpan nilai.', 'error');
    } finally {
        saving.value = false;
    }
};

const confirmDeleteGrade = () => {
    if (!students.value.length) return;
    deleteDialog.value = true;
};

const deleteGrades = async () => {
    deleting.value = true;
    try {
        const response = await axios.delete('/api/guru/grades', { data: filters.value });
        if (response.data.success) {
            showSnackbar(response.data.message);
            deleteDialog.value = false;
            loadGrades();
        }
    } catch (error) {
        console.error('Error deleting grades:', error);
        showSnackbar('Gagal menghapus nilai.', 'error');
    } finally {
        deleting.value = false;
    }
};

// Edit & Delete Per Siswa
const openEditDialog = (student) => {
    editTarget.value = student;
    editForm.value = {
        score: gradeData.value[student.id] ?? 0,
        notes: noteData.value[student.id] ?? ''
    };
    editDialog.value = true;
};

const saveEditGrade = async () => {
    const gradeId = gradeIdMap.value[editTarget.value.id];
    if (!gradeId) return;
    editSaving.value = true;
    try {
        const response = await axios.put(`/api/guru/grades/${gradeId}`, editForm.value);
        if (response.data.success) {
            showSuccess(response.data.message);
            editDialog.value = false;
            loadGrades();
        }
    } catch (error) {
        showError('Gagal memperbarui nilai');
    } finally {
        editSaving.value = false;
    }
};

const confirmDeleteSingleGrade = async (student) => {
    const gradeId = gradeIdMap.value[student.id];
    if (!gradeId) return;
    const confirmed = await showConfirm(
        `Hapus nilai ${student.name}?`,
        'Nilai siswa ini akan dihapus secara permanen dari kategori ini.'
    );
    if (!confirmed) return;
    try {
        const response = await axios.delete(`/api/guru/grades/${gradeId}`);
        if (response.data.success) {
            showSuccess(response.data.message);
            loadGrades();
        }
    } catch (error) {
        showError('Gagal menghapus nilai siswa');
    }
};

// Functions for Recap
const loadRecap = async () => {
    loadingRecap.value = true;
    try {
        const response = await axios.get('/api/guru/grades/summary', { params: recapFilters.value });
        if (response.data.success) {
            recapData.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading recap:', error);
        showSnackbar('Gagal memuat rekap nilai.', 'error');
    } finally {
        loadingRecap.value = false;
    }
};

const getGrade = (studentId, category) => {
    const entry = recapData.value.grades.find(g => g.student_id == studentId && g.grade_type == category);
    return entry ? entry.score : '-';
};

const calculateFinalGrade = (studentId) => {
    const studentGrades = recapData.value.grades.filter(g => g.student_id == studentId);
    if (!studentGrades.length) return 0;
    const sum = studentGrades.reduce((acc, curr) => acc + parseFloat(curr.score), 0);
    return (sum / studentGrades.length).toFixed(1);
};

const exportExcel = () => {
    const url = `/api/guru/grades/export/excel?class_id=${recapFilters.value.class_id}&subject_id=${recapFilters.value.subject_id}&token=${localStorage.getItem('token')}`;
    window.open(url, '_blank');
};

const exportPdf = () => {
    const url = `/api/guru/grades/export/pdf?class_id=${recapFilters.value.class_id}&subject_id=${recapFilters.value.subject_id}&token=${localStorage.getItem('token')}`;
    window.open(url, '_blank');
};

onMounted(() => {
    fetchClasses();
    fetchSubjects();
});
</script>

<style scoped>
.border-card {
    border: 1px solid rgba(0,0,0,0.05);
}

.border-dashed {
    border: 2px dashed #e0e0e0 !important;
}

.border-b {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

th {
    font-weight: bold !important;
    text-transform: uppercase;
    font-size: 0.75rem !important;
    letter-spacing: 1px;
    color: #64748b !important;
    padding-top: 16px !important;
    padding-bottom: 16px !important;
    white-space: nowrap;
}

.grade-input :deep(input) {
    text-align: center;
    font-weight: bold;
    color: #10b981;
}

.gap-2 { gap: 8px; }
.gap-4 { gap: 16px; }

.text-success {
    color: #10b981 !important;
}

.bg-success-lighten-4 {
    background-color: #ecfdf5 !important;
}
</style>
