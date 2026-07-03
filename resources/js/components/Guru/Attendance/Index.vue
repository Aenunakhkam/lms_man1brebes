<template>
    <v-container fluid class="pa-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Presensi Siswa</h1>
            <p class="text-subtitle-1 text-grey">Catat kehadiran siswa di kelas Anda</p>
        </div>

        <v-row>
            <v-col cols="12" md="4">
                <v-card rounded="xl" class="pa-6 mb-6 h-100">
                    <h3 class="text-h6 font-weight-bold mb-4">Pilih Kelas & Tanggal</h3>
                    <v-form v-model="filterValid">
                        <v-select
                            v-model="filters.class_id"
                            :items="classes"
                            item-title="name"
                            item-value="id"
                            label="Kelas"
                            variant="outlined"
                            rounded="lg"
                            required
                            @update:model-value="fetchStudents"
                        ></v-select>

                        <v-text-field
                            v-model="filters.date"
                            label="Tanggal"
                            type="date"
                            variant="outlined"
                            rounded="lg"
                            required
                        ></v-text-field>

                        <v-btn
                            color="success"
                            block
                            size="large"
                            rounded="lg"
                            prepend-icon="mdi-magnify"
                            @click="loadAttendance"
                            :disabled="!filters.class_id || !filters.date"
                        >
                            Tampilkan Siswa
                        </v-btn>
                    </v-form>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card rounded="xl" elevation="2" v-if="students.length">
                    <v-card-title class="pa-6 d-flex align-center justify-space-between">
                        <span class="text-h6 font-weight-bold">Daftar Siswa</span>
                        <v-btn
                            color="success"
                            variant="flat"
                            rounded="lg"
                            @click="saveAttendance"
                            :loading="saving"
                        >
                            Simpan Presensi
                        </v-btn>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-table hover>
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 50px;">No.</th>
                                <th class="text-left">Nama Siswa</th>
                                <th class="text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in students" :key="student.id">
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ student.name }}</div>
                                    <div class="text-caption text-grey">NIS: {{ student.nis }}</div>
                                </td>
                                <td>
                                    <v-radio-group
                                        v-model="attendanceData[student.id]"
                                        inline
                                        hide-details
                                        class="d-flex justify-center"
                                    >
                                        <v-radio label="H" value="hadir" color="success" class="mr-2"></v-radio>
                                        <v-radio label="I" value="izin" color="info" class="mr-2"></v-radio>
                                        <v-radio label="S" value="sakit" color="warning" class="mr-2"></v-radio>
                                        <v-radio label="A" value="alpa" color="error"></v-radio>
                                    </v-radio-group>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>
                <v-card v-else rounded="xl" class="pa-12 text-center text-grey">
                    <v-icon size="64" class="mb-4">mdi-account-search-outline</v-icon>
                    <div>Silakan pilih kelas dan tanggal terlebih dahulu</div>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const classes = ref([]);
const students = ref([]);
const attendanceData = ref({});
const filters = ref({
    class_id: null,
    date: new Date().toISOString().substr(0, 10)
});
const loading = ref(false);
const saving = ref(false);
const filterValid = ref(false);

const fetchClasses = async () => {
    try {
        const response = await axios.get('/api/guru/classes');
        classes.value = response.data.data;
    } catch (error) {
        console.error('Error fetching classes:', error);
    }
};

const fetchStudents = async () => {
    if (!filters.value.class_id) return;
    loading.value = true;
    try {
        const response = await axios.get('/api/guru/attendance/students', {
            params: { class_id: filters.value.class_id }
        });
        students.value = response.data.data;
        // Reset attendance data
        students.value.forEach(s => {
            if (!attendanceData.value[s.id]) {
                attendanceData.value[s.id] = 'hadir';
            }
        });
    } catch (error) {
        console.error('Error fetching students:', error);
    } finally {
        loading.value = false;
    }
};

const loadAttendance = async () => {
    await fetchStudents();
    try {
        const response = await axios.get('/api/guru/attendance', { params: filters.value });
        if (response.data.success && response.data.data.length) {
            response.data.data.forEach(item => {
                attendanceData.value[item.student_id] = item.status;
            });
        }
    } catch (error) {
        console.error('Error loading attendance:', error);
    }
};

const saveAttendance = async () => {
    saving.value = true;
    try {
        const payload = {
            class_id: filters.value.class_id,
            date: filters.value.date,
            attendance: Object.keys(attendanceData.value).map(studentId => ({
                student_id: studentId,
                status: attendanceData.value[studentId]
            }))
        };
        const response = await axios.post('/api/guru/attendance', payload);
        if (response.data.success) {
            alert('Presensi berhasil disimpan!');
        }
    } catch (error) {
        console.error('Error saving attendance:', error);
        alert('Gagal menyimpan presensi.');
    } finally {
        saving.value = false;
    }
};

onMounted(fetchClasses);
</script>

<style scoped>
th {
    font-weight: bold !important;
    text-transform: uppercase;
    font-size: 0.75rem !important;
    letter-spacing: 1px;
}
</style>
