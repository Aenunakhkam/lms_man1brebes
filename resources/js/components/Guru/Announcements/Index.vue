<template>
    <v-container fluid class="pa-6">
        <v-row class="mb-6" align="center">
            <v-col>
                <h1 class="text-h4 font-weight-bold d-flex align-center">
                    <v-icon color="primary" class="mr-3">mdi-bullhorn</v-icon>
                    Manajemen Pengumuman
                </h1>
                <p class="text-subtitle-1 text-grey-darken-1">Kelola informasi dan pengumuman untuk seluruh pengguna</p>
            </v-col>
            <v-col cols="auto">
                <v-btn
                    color="primary"
                    prepend-icon="mdi-plus"
                    size="large"
                    rounded="lg"
                    elevation="2"
                    @click="openDialog()"
                >
                    Tambah Pengumuman
                </v-btn>
            </v-col>
        </v-row>

        <!-- Table View -->
        <v-card rounded="xl" elevation="2">
            <v-data-table
                :headers="headers"
                :items="announcements"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.content="{ item }">
                    <div class="text-truncate" style="max-width: 300px;">{{ item.content }}</div>
                </template>
                <template v-slot:item.target_role="{ item }">
                    <v-chip
                        :color="getRoleColor(item.target_role)"
                        size="small"
                        label
                        class="text-uppercase font-weight-bold"
                    >
                        {{ item.target_role === 'all' ? 'Semua' : (item.target_role === 'guru_classes' ? 'Kelas Saya' : item.target_role) }}
                    </v-chip>
                </template>
                <template v-slot:item.is_active="{ item }">
                    <v-chip
                        :color="item.is_active ? 'success' : 'error'"
                        size="small"
                        variant="flat"
                    >
                        {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                    </v-chip>
                </template>
                <template v-slot:item.created_at="{ item }">
                    {{ formatDate(item.created_at) }}
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon color="info" variant="text" size="small" @click="openDialog(item)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon color="error" variant="text" size="small" @click="confirmDelete(item)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Add/Edit -->
        <v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card rounded="xl">
                <v-card-title class="pa-6">
                    <span class="text-h5 font-weight-bold">{{ editedItem.id ? 'Edit' : 'Tambah' }} Pengumuman</span>
                </v-card-title>

                <v-card-text class="pa-6 pt-0">
                    <v-form ref="form">
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.title"
                                    label="Judul Pengumuman"
                                    placeholder="Contoh: Libur Sekolah"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="editedItem.content"
                                    label="Isi Pengumuman"
                                    placeholder="Tuliskan detail pengumuman di sini..."
                                    variant="outlined"
                                    rounded="lg"
                                    rows="5"
                                    required
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.target_role"
                                    :items="roleOptions"
                                    label="Target Penerima"
                                    variant="outlined"
                                    rounded="lg"
                                    required
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-switch
                                    v-model="editedItem.is_active"
                                    label="Status Aktif"
                                    color="success"
                                    inset
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>

                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn color="grey-darken-1" variant="text" @click="closeDialog" rounded="lg">Batal</v-btn>
                    <v-btn color="primary" @click="save" rounded="lg" :loading="saving" size="large" class="px-6">Simpan</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const { showSuccess: alertSuccess, showError: alertError, showConfirm: alertConfirm } = useAlert();

const announcements = ref([]);
const loading = ref(false);
const dialog = ref(false);
const saving = ref(false);
const form = ref(null);

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Judul', key: 'title', sortable: true },
    { title: 'Konten', key: 'content', sortable: false },
    { title: 'Target', key: 'target_role', sortable: true },
    { title: 'Status', key: 'is_active', sortable: true },
    { title: 'Tanggal', key: 'created_at', sortable: true },
    { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
];

const roleOptions = [
    { title: 'Seluruh Pengguna LMS', value: 'all' },
    { title: 'Kelas Yang Diampu', value: 'guru_classes' },
    { title: 'Seluruh Siswa', value: 'siswa' },
];

const editedItem = ref({
    id: null,
    title: '',
    content: '',
    target_role: 'siswa',
    is_active: true,
});

const defaultItem = {
    id: null,
    title: '',
    content: '',
    target_role: 'siswa',
    is_active: true,
};

const fetchAnnouncements = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/guru/announcements');
        if (response.data.success) {
            announcements.value = response.data.data;
        }
    } catch (error) {
        alertError('Gagal mengambil data pengumuman');
    } finally {
        loading.value = false;
    }
};

const openDialog = (item = null) => {
    if (item) {
        editedItem.value = { ...item };
    } else {
        editedItem.value = { ...defaultItem };
    }
    dialog.value = true;
};

const closeDialog = () => {
    dialog.value = false;
    editedItem.value = { ...defaultItem };
};

const save = async () => {
    saving.value = true;
    try {
        if (editedItem.value.id) {
            await axios.put(`/api/guru/announcements/${editedItem.value.id}`, editedItem.value);
            alertSuccess('Pengumuman berhasil diperbarui');
        } else {
            await axios.post('/api/guru/announcements', editedItem.value);
            alertSuccess('Pengumuman berhasil ditambahkan');
        }
        fetchAnnouncements();
        closeDialog();
    } catch (error) {
        alertError('Gagal menyimpan pengumuman');
    } finally {
        saving.value = false;
    }
};

const confirmDelete = async (item) => {
    const confirmed = await alertConfirm(
        'Hapus Pengumuman',
        `Apakah Anda yakin ingin menghapus pengumuman "${item.title}"?`
    );
    if (confirmed) {
        try {
            await axios.delete(`/api/guru/announcements/${item.id}`);
            alertSuccess('Pengumuman berhasil dihapus');
            fetchAnnouncements();
        } catch (error) {
            alertError('Gagal menghapus pengumuman');
        }
    }
};

const getRoleColor = (role) => {
    switch (role) {
        case 'guru_classes': return 'warning';
        case 'siswa': return 'info';
        case 'all': return 'primary';
        default: return 'grey';
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

onMounted(() => {
    fetchAnnouncements();
});
</script>
