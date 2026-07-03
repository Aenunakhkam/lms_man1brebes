<template>
    <v-container fluid class="pa-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Jadwal Mengajar</h1>
            <p class="text-subtitle-1 text-grey">Lihat jadwal mengajar mingguan Anda</p>
        </div>

        <v-card rounded="xl" elevation="2">
            <v-tabs v-model="tab" color="success" align-tabs="center">
                <v-tab v-for="day in days" :key="day" :value="day" class="text-none font-weight-bold">
                    {{ day }}
                </v-tab>
            </v-tabs>

            <v-window v-model="tab">
                <v-window-item v-for="day in days" :key="day" :value="day">
                    <v-data-table
                        :headers="headers"
                        :items="filteredSchedules(day)"
                        :loading="loading"
                        class="elevation-0"
                        hide-default-footer
                    >
                        <template v-slot:item.start_time="{ item }">
                            <v-chip color="success" variant="outlined" size="small" class="font-weight-bold">
                                {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
                            </v-chip>
                        </template>
                        <template v-slot:item.class.name="{ item }">
                            <div class="font-weight-bold">{{ item.class?.name }}</div>
                            <div class="text-caption text-grey">{{ item.class?.grade_level }} {{ item.class?.department?.name }}</div>
                        </template>
                        <template v-slot:item.room="{ item }">
                            <v-chip size="small" rounded="lg">{{ item.room || 'R. Kelas' }}</v-chip>
                        </template>
                        <template v-slot:item.on_class="{ item }">
                            <div class="d-flex align-center justify-center">
                                <v-btn
                                    v-if="item.online_link"
                                    :href="item.online_link"
                                    target="_blank"
                                    icon
                                    size="x-small"
                                    color="primary"
                                    variant="tonal"
                                    class="me-1"
                                >
                                    <v-icon size="18">mdi-video</v-icon>
                                    <v-tooltip activator="parent" location="top">Join Meeting</v-tooltip>
                                </v-btn>
                                <span v-else class="text-caption text-grey me-1">Offline</span>
                                
                                <v-btn
                                    icon
                                    size="x-small"
                                    variant="text"
                                    color="grey"
                                    @click="editOnlineLink(item)"
                                >
                                    <v-icon size="16">mdi-pencil</v-icon>
                                    <v-tooltip activator="parent" location="top">Update Link Kelas</v-tooltip>
                                </v-btn>
                            </div>
                        </template>
                        <template v-slot:no-data>
                            <div class="pa-12 text-center text-grey">
                                <v-icon size="48" class="mb-2">mdi-calendar-blank</v-icon>
                                <div>Tidak ada jadwal mengajar di hari {{ day }}</div>
                            </div>
                        </template>
                    </v-data-table>
                </v-window-item>
            </v-window>
        </v-card>

        <!-- Dialog Edit Link -->
        <v-dialog v-model="linkDialog" max-width="500px">
            <v-card rounded="xl">
                <v-card-title class="pa-6 pb-2">
                    <span class="text-h5 font-weight-bold">Update Link Kelas</span>
                </v-card-title>
                <v-card-text class="pa-6 pt-2">
                    <p class="text-subtitle-2 text-grey mb-4">
                        Masukkan link Zoom, Google Meet, atau platform lainnya untuk kelas ini.
                    </p>
                    <v-text-field
                        v-model="editedLink"
                        label="Link Meeting"
                        placeholder="https://meet.google.com/..."
                        variant="outlined"
                        prepend-inner-icon="mdi-link"
                        :loading="saving"
                        hide-details
                    ></v-text-field>
                </v-card-text>
                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="text"
                        color="grey"
                        @click="linkDialog = false"
                        class="text-none font-weight-bold"
                    >
                        Batal
                    </v-btn>
                    <v-btn
                        color="success"
                        @click="saveLink"
                        :loading="saving"
                        class="text-none font-weight-bold px-6"
                        rounded="lg"
                    >
                        Simpan
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Notification -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" rounded="pill">
            {{ snackbar.text }}
        </v-snackbar>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const schedules = ref([]);
const loading = ref(false);
const tab = ref('Senin');
const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// Link Management
const linkDialog = ref(false);
const editedSchedule = ref(null);
const editedLink = ref('');
const saving = ref(false);
const snackbar = ref({
    show: false,
    text: '',
    color: 'success'
});

const headers = [
    { title: 'Jam Ke', key: 'period', align: 'center', width: '100px' },
    { title: 'Waktu', key: 'start_time', sortable: true },
    { title: 'Kelas / Rombel', key: 'class.name' },
    { title: 'Mata Pelajaran', key: 'subject.name' },
    { title: 'Ruangan', key: 'room' },
    { title: 'On Class', key: 'on_class', align: 'center', sortable: false },
];

const fetchSchedules = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/guru/schedules');
        if (response.data.success) {
            schedules.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching schedules:', error);
    } finally {
        loading.value = false;
    }
};

const editOnlineLink = (item) => {
    editedSchedule.value = item;
    editedLink.value = item.online_link || '';
    linkDialog.value = true;
};

const saveLink = async () => {
    if (!editedSchedule.value) return;
    
    saving.value = true;
    try {
        const response = await axios.put(`/api/guru/schedules/${editedSchedule.value.id}/online-link`, {
            online_link: editedLink.value
        });
        
        if (response.data.success) {
            // Update local state
            const index = schedules.value.findIndex(s => s.id === editedSchedule.value.id);
            if (index !== -1) {
                schedules.value[index].online_link = editedLink.value;
            }
            
            snackbar.value = {
                show: true,
                text: 'Link kelas online berhasil diperbarui',
                color: 'success'
            };
            linkDialog.value = false;
        }
    } catch (error) {
        console.error('Error saving link:', error);
        snackbar.value = {
            show: true,
            text: error.response?.data?.message || 'Gagal memperbarui link',
            color: 'error'
        };
    } finally {
        saving.value = false;
    }
};

const filteredSchedules = (day) => {
    return schedules.value.filter(s => s.day === day).sort((a, b) => a.start_time.localeCompare(b.start_time));
};

const formatTime = (time) => {
    if (!time) return '';
    return time.substring(0, 5);
};

onMounted(() => {
    fetchSchedules();
    const today = new Date().toLocaleDateString('id-ID', { weekday: 'long' });
    if (days.includes(today)) {
        tab.value = today;
    }
});
</script>

<style scoped>
.v-tab {
    min-width: 120px;
}
</style>
