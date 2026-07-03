<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-title class="d-flex align-center py-4 px-6">
                <span class="text-h5 font-weight-bold">Jadwal Pelajaran</span>
                <v-spacer></v-spacer>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog()">
                    Tambah Jadwal
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="schedules"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.start_time="{ item }">
                    {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
                </template>
                <template v-slot:item.class.name="{ item }">
                    {{ item.class?.name }}
                </template>
                 <template v-slot:item.subject.name="{ item }">
                    {{ item.subject?.name }}
                </template>
                 <template v-slot:item.teacher.name="{ item }">
                    {{ item.teacher?.name }}
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
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ editedIndex === -1 ? 'Tambah Jadwal' : 'Edit Jadwal' }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.day"
                                    :items="['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']"
                                    label="Hari"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-select>
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field
                                    v-model="editedItem.start_time"
                                    label="Jam Mulai"
                                    type="time"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field
                                    v-model="editedItem.end_time"
                                    label="Jam Selesai"
                                    type="time"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            
                            <v-col cols="12">
                                <v-select
                                    v-model="editedItem.class_id"
                                    :items="classes"
                                    item-title="name"
                                    item-value="id"
                                    label="Kelas"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-select>
                            </v-col>
                            
                             <v-col cols="12">
                                <v-select
                                    v-model="editedItem.subject_id"
                                    :items="subjects"
                                    item-title="name"
                                    item-value="id"
                                    label="Mata Pelajaran"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-select>
                            </v-col>
                            
                            <v-col cols="12">
                                <v-select
                                    v-model="editedItem.teacher_id"
                                    :items="teachers"
                                    item-title="name"
                                    item-value="id"
                                    label="Guru Pengampu"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Wajib diisi']"
                                ></v-select>
                            </v-col>

                             <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.room"
                                    label="Ruangan (Opsional)"
                                    variant="outlined"
                                    class="mb-2"
                                ></v-text-field>
                            </v-col>

                             <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.online_link"
                                    label="Link Online (Zoom/Meet) - Opsional"
                                    placeholder="https://zoom.us/j/..."
                                    variant="outlined"
                                    prepend-inner-icon="mdi-video"
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
const schedules = ref([]);
const classes = ref([]);
const subjects = ref([]);
const teachers = ref([]);
const editedIndex = ref(-1);

const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

const editedItem = ref({
    class_id: null,
    subject_id: null,
    teacher_id: null,
    day: 'Senin',
    start_time: '',
    end_time: '',
    room: '',
    online_link: '',
    id: null
});

const defaultItem = {
    class_id: null,
    subject_id: null,
    teacher_id: null,
    day: 'Senin',
    start_time: '',
    end_time: '',
    room: '',
    online_link: '',
    id: null
};

// ... headers ...
const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Hari', align: 'start', key: 'day' },
    { title: 'Jam (WIB)', key: 'start_time' },
    { title: 'Kelas', key: 'class.name' },
    { title: 'Mata Pelajaran', key: 'subject.name' },
    { title: 'Guru', key: 'teacher.name' },
    { title: 'Ruangan', key: 'room' },
    { title: 'Aksi', key: 'actions', sortable: false },
];

const formTitle = computed(() => {
    return editedIndex.value === -1 ? 'Tambah Jadwal Baru' : 'Edit Jadwal';
});

const loadSchedules = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/schedules');
        if (response.data.success) {
            schedules.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat jadwal');
    } finally {
        loading.value = false;
    }
};

const loadDependencies = async () => {
    try {
        const [classesRes, subjectsRes, teachersRes] = await Promise.all([
            axios.get('/api/admin/classes'),
            axios.get('/api/admin/subjects'),
            axios.get('/api/admin/users?role=guru')
        ]);
        
        if (classesRes.data.success) classes.value = classesRes.data.data;
        if (subjectsRes.data.success) subjects.value = subjectsRes.data.data;
        if (teachersRes.data.success) teachers.value = teachersRes.data.data;

    } catch (error) {
        console.error('Error loading dependencies:', error);
    }
};

const openDialog = (item = null) => {
    if (item) {
        editedIndex.value = schedules.value.indexOf(item);
        
        const formatTimeForInput = (timeStr) => {
            if (!timeStr) return '';
            return timeStr.substring(0, 5);
        };

        editedItem.value = Object.assign({}, item);
        editedItem.value.start_time = formatTimeForInput(item.start_time);
        editedItem.value.end_time = formatTimeForInput(item.end_time);
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

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
};

const deleteItem = async (item) => {
    const confirmed = await showConfirm(
        'Hapus Jadwal?',
        `Apakah Anda yakin ingin menghapus jadwal ${item.subject?.name} di hari ${item.day}?`
    );
    
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/schedules/${item.id}`);
            await showSuccess('Terhapus!', 'Jadwal berhasil dihapus');
            await loadSchedules();
        } catch (error) {
            console.error('Error deleting schedule:', error);
            showError('Gagal!', 'Terjadi kesalahan saat menghapus jadwal');
        }
    }
};

const save = async () => {
    try {
        if (editedIndex.value > -1) {
            const response = await axios.put(`/api/admin/schedules/${editedItem.value.id}`, editedItem.value);
            Object.assign(schedules.value[editedIndex.value], response.data.data);
            showSuccess('Jadwal berhasil diperbarui');
        } else {
            const response = await axios.post('/api/admin/schedules', editedItem.value);
            schedules.value.push(response.data.data);
            showSuccess('Jadwal berhasil ditambahkan');
        }
        await loadSchedules();
        closeDialog();
    } catch (error) {
        if (error.response && error.response.status === 422) {
            showError('Gagal!', error.response.data.message);
        } else {
            showError('Gagal menyimpan jadwal');
        }
    }
};

onMounted(() => {
    loadSchedules();
    loadDependencies();
});
</script>
