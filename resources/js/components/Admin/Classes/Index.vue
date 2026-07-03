<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-title class="d-flex align-center py-4 px-6">
                <span class="text-h5 font-weight-bold">Data Kelas</span>
                <v-spacer></v-spacer>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog()">
                    Tambah Kelas
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="classes"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.department.name="{ item }">
                    {{ item.department?.name }}
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-tooltip text="Lihat Anggota Rombel" location="top">
                        <template v-slot:activator="{ props }">
                            <v-btn icon size="small" variant="text" color="info" class="me-1" v-bind="props" @click="openRombel(item)">
                                <v-icon>mdi-account-group</v-icon>
                            </v-btn>
                        </template>
                    </v-tooltip>
                    <v-icon size="small" class="me-2" color="primary" @click="openDialog(item)">
                        mdi-pencil
                    </v-icon>
                    <v-icon size="small" color="error" @click="deleteItem(item)">
                        mdi-delete
                    </v-icon>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Form -->
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ editedIndex === -1 ? 'Tambah Kelas' : 'Edit Kelas' }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.grade_level"
                                    :items="['10', '11', '12']"
                                    label="Tingkat"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.department_id"
                                    :items="departments"
                                    item-title="name"
                                    item-value="id"
                                    label="Jurusan"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.name"
                                    label="Nama Kelas Lengkap (Contoh: 10 IPA 1)"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                    hint="Direkomendasikan format: [Tingkat] [Jurusan] [Nomor]"
                                    persistent-hint
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="grey-darken-1" variant="text" @click="closeDialog">Batal</v-btn>
                    <v-btn color="primary" variant="text" @click="save">Simpan</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Rombel Dialog -->
        <v-dialog v-model="rombelDialog" max-width="800px">
            <v-card v-if="selectedClass">
                <v-card-title class="d-flex align-center">
                    <span class="text-h5">Anggota Kelas {{ selectedClass.name }}</span>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" variant="text" @click="closeRombel">Tutup</v-btn>
                </v-card-title>
                <v-card-text>
                    <!-- Add Student Form -->
                    <div class="d-flex gap-2 mb-4 align-center">
                        <v-select
                            v-model="newStudentId"
                            :items="availableStudents"
                            item-title="name"
                            item-value="id"
                            label="Pilih Siswa"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="flex-grow-1"
                            :loading="loadingStudents"
                        ></v-select>
                        <v-btn color="primary" @click="addStudentToClass" :disabled="!newStudentId">
                            <v-icon left>mdi-plus</v-icon> Tambah
                        </v-btn>
                    </div>

                    <!-- Students Table -->
                    <v-data-table
                        :headers="rombelHeaders"
                        :items="classStudents"
                        :loading="loadingRombel"
                        density="compact"
                    >
                        <template v-slot:item.index="{ index }">
                            {{ index + 1 }}
                        </template>
                        <template v-slot:item.actions="{ item }">
                            <v-icon size="small" color="error" @click="removeStudentFromClass(item)">
                                mdi-logout
                            </v-icon>
                        </template>
                    </v-data-table>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const { showSuccess, showError, showConfirm } = useAlert();

const search = ref('');
const dialog = ref(false);
const loading = ref(false);
const classes = ref([]);
const departments = ref([]);
const editedIndex = ref(-1);

// Rombel State
const rombelDialog = ref(false);
const selectedClass = ref(null);
const classStudents = ref([]);
const availableStudents = ref([]);
const newStudentId = ref(null);
const loadingRombel = ref(false);
const loadingStudents = ref(false);

const editedItem = ref({
    name: '',
    grade_level: '10',
    department_id: null,
    id: null
});

const defaultItem = {
    name: '',
    grade_level: '10',
    department_id: null,
    id: null
};

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Nama Kelas', align: 'start', key: 'name' },
    { title: 'Tingkat', key: 'grade_level' },
    { title: 'Jurusan', key: 'department.name' },
    { title: 'Aksi', key: 'actions', sortable: false },
];

const rombelHeaders = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Nama Siswa', key: 'name' },
    { title: 'NIS', key: 'nis' },
    { title: 'Email', key: 'email' },
    { title: 'Aksi', key: 'actions', sortable: false },
];

const formTitle = computed(() => {
    return editedIndex.value === -1 ? 'Tambah Kelas Baru' : 'Edit Kelas';
});

const loadClasses = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/classes');
        if (response.data.success) {
            classes.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat data kelas');
    } finally {
        loading.value = false;
    }
};

const loadDepartments = async () => {
    try {
        const response = await axios.get('/api/admin/departments');
        if (response.data.success) {
            departments.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
};

onMounted(() => {
    loadClasses();
    loadDepartments();
});

const openDialog = (item = null) => {
    if (item) {
        editedIndex.value = classes.value.indexOf(item);
        editedItem.value = Object.assign({}, item);
    } else {
        editedIndex.value = -1;
        editedItem.value = Object.assign({}, defaultItem);
    }
    dialog.value = true;
};

const deleteItem = async (item) => {
    const confirmed = await showConfirm('Hapus Kelas?', 'Data yang dihapus tidak dapat dikembalikan.');
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/classes/${item.id}`);
            classes.value = classes.value.filter(i => i.id !== item.id);
            showSuccess('Kelas berhasil dihapus');
        } catch (error) {
            showError('Gagal menghapus kelas');
        }
    }
};

const closeDialog = () => {
    dialog.value = false;
    setTimeout(() => {
        editedItem.value = Object.assign({}, defaultItem);
        editedIndex.value = -1;
    }, 300);
};

const save = async () => {
    try {
        if (editedIndex.value > -1) {
            const response = await axios.put(`/api/admin/classes/${editedItem.value.id}`, editedItem.value);
            Object.assign(classes.value[editedIndex.value], response.data.data);
            showSuccess('Kelas berhasil diperbarui');
        } else {
            const response = await axios.post('/api/admin/classes', editedItem.value);
            classes.value.push(response.data.data);
            showSuccess('Kelas berhasil ditambahkan');
        }
        closeDialog();
    } catch (error) {
        if (error.response && error.response.data.errors) {
            showError('Validasi Gagal', Object.values(error.response.data.errors).flat().join('\n'));
        } else {
            showError('Gagal menyimpan kelas');
        }
    }
};

// --- Rombel Logic ---

const openRombel = async (item) => {
    selectedClass.value = item;
    rombelDialog.value = true;
    await loadClassStudents(item.id);
    await loadAvailableStudents();
};

const closeRombel = () => {
    rombelDialog.value = false;
    selectedClass.value = null;
    classStudents.value = [];
    newStudentId.value = null;
};

const loadClassStudents = async (classId) => {
    loadingRombel.value = true;
    try {
        const response = await axios.get(`/api/admin/classes/${classId}/students`);
        if (response.data.success) {
            classStudents.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat anggota kelas');
    } finally {
        loadingRombel.value = false;
    }
};

const loadAvailableStudents = async () => {
    loadingStudents.value = true;
    try {
         // Assuming we want all students to pick from. Ideally this endpoint should filter out those already in a class if logic requires.
        const response = await axios.get('/api/admin/users?role=siswa');
        if (response.data.success) {
            availableStudents.value = response.data.data;
        }
    } catch (error) {
        console.error(error);
    } finally {
        loadingStudents.value = false;
    }
};

const addStudentToClass = async () => {
    if (!newStudentId.value || !selectedClass.value) return;

    try {
        const response = await axios.post(`/api/admin/classes/${selectedClass.value.id}/students`, {
            student_id: newStudentId.value
        });
        
        showSuccess('Siswa berhasil ditambahkan');
        await loadClassStudents(selectedClass.value.id);
        newStudentId.value = null;
    } catch (error) {
        const msg = error.response?.data?.message || 'Gagal menambahkan siswa';
        showError(msg);
    }
};

const removeStudentFromClass = async (student) => {
    const confirmed = await showConfirm('Keluarkan Siswa?', `Keluarkan ${student.name} dari kelas?`);
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/classes/${selectedClass.value.id}/students/${student.id}`);
            showSuccess('Siswa berhasil dikeluarkan');
            await loadClassStudents(selectedClass.value.id);
        } catch (error) {
            showError('Gagal mengeluarkan siswa');
        }
    }
};

</script>
