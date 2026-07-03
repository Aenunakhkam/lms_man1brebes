<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Jadwal Pelajaran</h1>
            <p class="text-subtitle-1 text-grey">Jadwal belajar mingguan di kelas Anda</p>
        </div>

        <v-card rounded="xl" elevation="2">
            <v-tabs v-model="tab" color="primary" align-tabs="center" class="border-b">
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
                            <v-chip color="primary" variant="flat" size="small" class="font-weight-bold">
                                {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
                            </v-chip>
                        </template>
                        <template v-slot:item.subject.name="{ item }">
                            <div class="font-weight-bold">{{ item.subject?.name }}</div>
                            <div class="text-caption text-grey">{{ item.teacher?.name }}</div>
                        </template>
                        <template v-slot:item.room="{ item }">
                            <v-chip size="small" variant="outlined" rounded="lg">{{ item.room || 'Ruang Kelas' }}</v-chip>
                        </template>
                        <template v-slot:no-data>
                            <div class="pa-12 text-center text-grey">
                                <v-icon size="64" class="mb-4">mdi-calendar-clock</v-icon>
                                <div class="text-h6">Tidak ada jadwal hari ini</div>
                                <div class="text-caption">Hore! Gunakan waktu luangmu untuk belajar mandiri.</div>
                            </div>
                        </template>
                    </v-data-table>
                </v-window-item>
            </v-window>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const schedules = ref([]);
const loading = ref(false);
const tab = ref('Senin');
const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

const headers = [
    { title: 'Waktu', key: 'start_time', sortable: true, width: '150px' },
    { title: 'Mata Pelajaran / Guru', key: 'subject.name' },
    { title: 'Ruangan', key: 'room', align: 'center' },
];

const fetchSchedules = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/siswa/schedules');
        if (response.data.success) {
            schedules.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching schedules:', error);
    } finally {
        loading.value = false;
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
