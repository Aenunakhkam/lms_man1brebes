<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-title class="d-flex align-center py-4 px-6">
                <span class="text-h5 font-weight-bold">Data Jurusan</span>
                <v-spacer></v-spacer>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog()">
                    Tambah Jurusan
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="items"
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
                    <span class="text-h5">{{ editedIndex === -1 ? 'Tambah Jurusan' : 'Edit Jurusan' }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.name"
                                    label="Nama Jurusan"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Nama wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.code"
                                    label="Kode Jurusan"
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const { showSuccess, showError, showConfirm } = useAlert();

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Kode', align: 'start', key: 'code' },
    { title: 'Nama Jurusan', key: 'name' },
    { title: 'Deskripsi', key: 'description' },
    { title: 'Aksi', key: 'actions', sortable: false },
];

const items = ref([]);
const loading = ref(false);
const dialog = ref(false);
const dialogDelete = ref(false);
const editedIndex = ref(-1);
const editedItem = ref({
    name: '',
    code: '',
    description: ''
});
const defaultItem = {
    name: '',
    code: '',
    description: ''
};
const itemToDelete = ref(null);

const loadData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/departments');
        items.value = response.data.data;
    } catch (error) {
        console.error('Error loading data:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadData();
});

const openDialog = (item = null) => {
    if (item) {
        editedIndex.value = items.value.indexOf(item);
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
            await axios.put(`/api/admin/departments/${editedItem.value.id}`, editedItem.value);
            showSuccess('Berhasil!', 'Jurusan berhasil diperbarui');
        } else {
            await axios.post('/api/admin/departments', editedItem.value);
            showSuccess('Berhasil!', 'Jurusan berhasil ditambahkan');
        }
        await loadData();
        closeDialog();
    } catch (error) {
        console.error('Error saving data:', error);
        showError('Gagal!', error.response?.data?.message || 'Gagal menyimpan data');
    }
};

const deleteItem = async (item) => {
    const confirmed = await showConfirm(
        'Hapus Jurusan?',
        `Apakah Anda yakin ingin menghapus jurusan ${item.name}?`
    );
    
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/departments/${item.id}`);
            await showSuccess('Terhapus!', 'Jurusan berhasil dihapus');
            await loadData();
        } catch (error) {
            console.error('Error deleting item:', error);
            showError('Gagal!', 'Terjadi kesalahan saat menghapus data');
        }
    }
};
</script>
