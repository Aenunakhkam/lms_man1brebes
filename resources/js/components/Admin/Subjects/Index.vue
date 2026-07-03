<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-title class="d-flex align-center py-4 px-6">
                <span class="text-h5 font-weight-bold">Data Mata Pelajaran</span>
                <v-spacer></v-spacer>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog()">
                    Tambah Mapel
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="subjects"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.actions="{ item }">
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
                    <span class="text-h5">{{ editedIndex === -1 ? 'Tambah Mapel' : 'Edit Mapel' }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.name"
                                    label="Nama Mata Pelajaran"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Nama wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.code"
                                    label="Kode Mapel"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Kode wajib diisi']"
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
const subjects = ref([]);
const editedIndex = ref(-1);
const editedItem = ref({
    name: '',
    code: '',
    description: '',
    id: null
});
const defaultItem = {
    name: '',
    code: '',
    description: '',
    id: null
};

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Kode Mapel', key: 'code' },
    { title: 'Nama Mata Pelajaran', key: 'name' },
    { title: 'Deskripsi', key: 'description' },
    { title: 'Aksi', key: 'actions', sortable: false },
];

const formTitle = computed(() => {
    return editedIndex.value === -1 ? 'Tambah Mapel Baru' : 'Edit Mapel';
});

const loadSubjects = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/subjects');
        if (response.data.success) {
            subjects.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat data mata pelajaran');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadSubjects();
});

const openDialog = (item = null) => {
    if (item) {
        editedIndex.value = subjects.value.indexOf(item);
        editedItem.value = Object.assign({}, item);
    } else {
        editedIndex.value = -1;
        editedItem.value = Object.assign({}, defaultItem);
    }
    dialog.value = true;
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
            const response = await axios.put(`/api/admin/subjects/${editedItem.value.id}`, editedItem.value);
            Object.assign(subjects.value[editedIndex.value], response.data.data);
            showSuccess('Mata pelajaran berhasil diperbarui');
        } else {
            const response = await axios.post('/api/admin/subjects', editedItem.value);
            subjects.value.push(response.data.data);
            showSuccess('Mata pelajaran berhasil ditambahkan');
        }
        await loadSubjects();
        closeDialog();
    } catch (error) {
        if (error.response && error.response.data.errors) {
             showError('Validasi Gagal', Object.values(error.response.data.errors).flat().join('\n'));
        } else {
             showError('Gagal menyimpan data');
        }
    }
};

const deleteItem = async (item) => {
    const confirmed = await showConfirm(
        'Hapus Mata Pelajaran?',
        `Apakah Anda yakin ingin menghapus ${item.name}?`
    );
    
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/subjects/${item.id}`);
            await showSuccess('Terhapus!', 'Mata pelajaran berhasil dihapus');
            await loadSubjects();
        } catch (error) {
            console.error('Error deleting item:', error);
            showError('Gagal!', 'Terjadi kesalahan saat menghapus data');
        }
    }
};
</script>
