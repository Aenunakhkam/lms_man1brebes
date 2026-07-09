<template>
    <v-container fluid class="pa-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Presensi Siswa</h1>
            <p class="text-subtitle-1 text-grey">Catat kehadiran siswa di kelas Anda</p>
        </div>

        <v-row>
            <v-col cols="12" md="4">
                <v-card rounded="xl" class="pa-6 mb-6">
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

                <!-- Export Section -->
                <v-card rounded="xl" class="pa-6">
                    <h3 class="text-h6 font-weight-bold mb-4">Export Rekapitulasi</h3>
                    <v-form>
                        <v-select
                            v-model="exportFilters.class_id"
                            :items="classes"
                            item-title="name"
                            item-value="id"
                            label="Kelas"
                            variant="outlined"
                            rounded="lg"
                            required
                        ></v-select>

                        <v-text-field
                            v-model="exportFilters.month"
                            label="Bulan (Opsional)"
                            type="month"
                            variant="outlined"
                            rounded="lg"
                        ></v-text-field>

                        <v-btn
                            color="error"
                            block
                            class="mb-3"
                            size="large"
                            rounded="lg"
                            prepend-icon="mdi-file-pdf-box"
                            @click="exportPdf"
                            :disabled="!exportFilters.class_id"
                        >
                            Export PDF
                        </v-btn>
                        
                        <v-btn
                            color="success"
                            block
                            size="large"
                            rounded="lg"
                            prepend-icon="mdi-file-excel-box"
                            @click="exportExcel"
                            :disabled="!exportFilters.class_id"
                        >
                            Export Excel
                        </v-btn>
                    </v-form>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card rounded="xl" elevation="2" v-if="students.length">
                    <v-card-title class="pa-6 d-flex align-center justify-space-between">
                        <span class="text-h6 font-weight-bold">Daftar Siswa</span>
                        <div>
                            <v-btn
                                color="primary"
                                variant="outlined"
                                rounded="lg"
                                @click="exportDailyPdf"
                                class="mr-2"
                                prepend-icon="mdi-file-pdf-box"
                            >
                                Cetak Harian
                            </v-btn>
                            <v-btn
                                color="error"
                                variant="outlined"
                                rounded="lg"
                                @click="deleteAttendance"
                                :loading="deleting"
                                class="mr-2"
                            >
                                Hapus
                            </v-btn>
                            <v-btn
                                color="success"
                                variant="flat"
                                rounded="lg"
                                @click="saveAttendance"
                                :loading="saving"
                            >
                                Simpan Presensi
                            </v-btn>
                        </div>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-table hover>
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 50px;">No.</th>
                                <th class="text-left">Nama Siswa</th>
                                <th class="text-center">Status Kehadiran</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
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
                                        <v-radio label="H" value="Hadir" color="success" class="mr-2"></v-radio>
                                        <v-radio label="I" value="Izin" color="info" class="mr-2"></v-radio>
                                        <v-radio label="S" value="Sakit" color="warning" class="mr-2"></v-radio>
                                        <v-radio label="A" value="Alpa" color="error"></v-radio>
                                    </v-radio-group>
                                </td>
                                <td class="text-center">
                                    <v-btn
                                        icon
                                        color="error"
                                        variant="text"
                                        size="small"
                                        @click="deleteStudentAttendance(student)"
                                        :loading="deletingStudent === student.id"
                                    >
                                        <v-icon>mdi-delete</v-icon>
                                        <v-tooltip activator="parent" location="top">Hapus Presensi Siswa</v-tooltip>
                                    </v-btn>
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
import { useAlert } from '../../../composables/useAlert';

const { showSuccess, showError, showConfirm } = useAlert();

const classes = ref([]);
const students = ref([]);
const attendanceData = ref({});
const filters = ref({
    class_id: null,
    date: new Date().toISOString().substr(0, 10)
});
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const deletingStudent = ref(null);
const filterValid = ref(false);

const exportFilters = ref({
    class_id: null,
    month: new Date().toISOString().substr(0, 7)
});

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
        // Reset attendance data to null (unselected)
        students.value.forEach(s => {
            if (attendanceData.value[s.id] === undefined) {
                attendanceData.value[s.id] = null;
            }
        });
    } catch (error) {
        console.error('Error fetching students:', error);
    } finally {
        loading.value = false;
    }
};

const loadAttendance = async () => {
    attendanceData.value = {};
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
    const missing = students.value.find(s => !attendanceData.value[s.id]);
    if (missing) {
        showError('Gagal', 'Masih ada siswa yang belum dipresensi (belum dipilih statusnya).');
        return;
    }

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
            showSuccess('Presensi berhasil disimpan!');
        }
    } catch (error) {
        console.error('Error saving attendance:', error);
        showError('Gagal menyimpan presensi', error.response?.data?.message || 'Terjadi kesalahan pada server');
    } finally {
        saving.value = false;
    }
};

const deleteAttendance = async () => {
    const isConfirmed = await showConfirm(
        'Apakah Anda yakin?',
        'Data presensi untuk kelas dan tanggal ini akan dihapus permanen.'
    );
    if (!isConfirmed) return;
    
    deleting.value = true;
    try {
        const response = await axios.delete('/api/guru/attendance', {
            data: {
                class_id: filters.value.class_id,
                date: filters.value.date
            }
        });
        
        if (response.data.success) {
            showSuccess('Data presensi berhasil dihapus!');
            attendanceData.value = {};
            await loadAttendance();
        }
    } catch (error) {
        console.error('Error deleting attendance:', error);
        showError('Gagal menghapus presensi', error.response?.data?.message || 'Terjadi kesalahan pada server');
    } finally {
        deleting.value = false;
    }
};

const deleteStudentAttendance = async (student) => {
    const isConfirmed = await showConfirm(
        'Hapus Presensi Siswa?',
        `Apakah Anda yakin ingin menghapus data presensi untuk ${student.name}?`
    );
    if (!isConfirmed) return;

    deletingStudent.value = student.id;
    try {
        const response = await axios.delete('/api/guru/attendance', {
            data: {
                class_id: filters.value.class_id,
                date: filters.value.date,
                student_id: student.id
            }
        });
        
        if (response.data.success) {
            showSuccess('Berhasil!', `Presensi ${student.name} berhasil dihapus.`);
            attendanceData.value[student.id] = null;
        }
    } catch (error) {
        console.error('Error deleting student attendance:', error);
        showError('Gagal menghapus', error.response?.data?.message || 'Terjadi kesalahan pada server');
    } finally {
        deletingStudent.value = null;
    }
};

const exportPdf = async () => {
    let url = `/api/guru/attendance/export/pdf?class_id=${exportFilters.value.class_id}`;
    if (exportFilters.value.month) {
        url += `&month=${exportFilters.value.month}`;
    }
    
    try {
        const response = await axios.get(url, { responseType: 'blob' });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `Presensi_PDF_${new Date().getTime()}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
        console.error('Error downloading PDF:', error);
        showError('Gagal', 'Tidak dapat mengunduh PDF. Pastikan data tersedia.');
    }
};

const exportDailyPdf = async () => {
    let url = `/api/guru/attendance/export/pdf?class_id=${filters.value.class_id}&date=${filters.value.date}`;
    
    try {
        const response = await axios.get(url, { responseType: 'blob' });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `Presensi_Harian_PDF_${new Date().getTime()}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
        console.error('Error downloading daily PDF:', error);
        showError('Gagal', 'Tidak dapat mengunduh PDF. Pastikan data tersedia.');
    }
};

const exportExcel = async () => {
    let url = `/api/guru/attendance/export/excel?class_id=${exportFilters.value.class_id}`;
    if (exportFilters.value.month) {
        url += `&month=${exportFilters.value.month}`;
    }
    
    try {
        const response = await axios.get(url, { responseType: 'blob' });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `Presensi_Excel_${new Date().getTime()}.xlsx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
        console.error('Error downloading Excel:', error);
        showError('Gagal', 'Tidak dapat mengunduh Excel. Pastikan data tersedia.');
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
