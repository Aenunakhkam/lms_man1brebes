<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Tugas Saya</h1>
            <p class="text-subtitle-1 text-grey">Lihat dan kerjakan tugas dari bapak/ibu guru</p>
        </div>

        <v-row v-if="assignments.length">
            <v-col v-for="assignment in assignments" :key="assignment.id" cols="12" md="6">
                <v-card rounded="xl" elevation="2" class="h-100 d-flex flex-column">
                    <v-card-item class="pa-4 flex-grow-1">
                        <template v-slot:prepend>
                            <v-avatar color="warning-lighten-4" rounded="lg" size="48">
                                <v-icon color="warning">mdi-clipboard-text</v-icon>
                            </v-avatar>
                        </template>
                        <v-card-title class="text-h6 font-weight-bold mb-1">{{ assignment.title }}</v-card-title>
                        <v-card-subtitle class="text-caption text-primary font-weight-bold">
                            {{ assignment.subject?.name }}
                        </v-card-subtitle>

                        <div class="mt-4 pa-3 bg-grey-lighten-4 rounded-lg text-body-2 line-clamp-2">
                            {{ assignment.description }}
                        </div>

                        <div class="mt-4 d-flex flex-column gap-2">
                            <div class="d-flex align-center justify-space-between">
                                <div class="d-flex align-center">
                                    <v-icon size="16" color="success" class="mr-1">mdi-calendar-check</v-icon>
                                    <span class="text-caption font-weight-medium">Buka: {{ formatDate(assignment.start_time) }}</span>
                                </div>
                                <v-chip :color="getAssignmentStatus(assignment).color" size="small" rounded="lg">
                                    {{ getAssignmentStatus(assignment).text }}
                                </v-chip>
                            </div>
                            <div class="d-flex align-center">
                                <v-icon size="16" color="error" class="mr-1">mdi-clock-outline</v-icon>
                                <span class="text-caption font-weight-bold text-error">Tutup: {{ formatDate(assignment.deadline) }}</span>
                            </div>
                        </div>
                    </v-card-item>
                    <v-divider></v-divider>
                    <v-card-actions class="pa-4 d-flex flex-column">
                        <v-btn
                            v-if="assignment.attachment"
                            color="info"
                            variant="tonal"
                            rounded="lg"
                            block
                            prepend-icon="mdi-file-download"
                            :href="`/storage/${assignment.attachment}`"
                            target="_blank"
                            class="mb-3"
                        >
                            Unduh File Tugas
                        </v-btn>
                        
                        <v-btn
                            :color="assignment.is_submitted ? 'success' : 'warning'"
                            variant="flat"
                            rounded="lg"
                            block
                            :prepend-icon="assignment.is_submitted ? 'mdi-check-circle' : 'mdi-pencil'"
                            :to="`/siswa/assignments/${assignment.id}`"
                            class="ma-0"
                        >
                            {{ assignment.is_submitted ? 'Lihat Tugas' : 'Kerjakan Tugas' }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
        <v-card v-else rounded="xl" class="pa-12 text-center text-grey">
            <v-icon size="64" class="mb-4">mdi-clipboard-off-outline</v-icon>
            <div class="text-h6">Tidak ada tugas baru</div>
            <p>Bersantai sejenak atau pelajari materi yang lain!</p>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const assignments = ref([]);
const loading = ref(false);

const fetchAssignments = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/siswa/assignments');
        if (response.data.success) {
            assignments.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching assignments:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getAssignmentStatus = (assignment) => {
    if (assignment.is_submitted) return { text: 'Sudah Dikerjakan', color: 'success' };
    
    const now = new Date();
    if (assignment.start_time && now < new Date(assignment.start_time)) {
        return { text: 'Belum Dibuka', color: 'grey' };
    }
    if (assignment.deadline && now > new Date(assignment.deadline)) {
        return { text: 'Ditutup', color: 'error' };
    }
    return { text: 'Belum Dikerjakan', color: 'warning' };
};

onMounted(fetchAssignments);
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    overflow: hidden;
}
</style>
