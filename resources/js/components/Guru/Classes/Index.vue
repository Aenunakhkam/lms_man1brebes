<template>
    <v-container fluid class="pa-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Kelas Saya</h1>
            <p class="text-subtitle-1 text-grey">Daftar kelas yang Anda ampu</p>
        </div>

        <v-row v-if="loading">
            <v-col cols="12" md="4" v-for="i in 3" :key="i">
                <v-skeleton-loader type="card" class="rounded-xl"></v-skeleton-loader>
            </v-col>
        </v-row>

        <v-row v-else-if="classes.length">
            <v-col cols="12" md="4" v-for="cls in classes" :key="cls.id">
                <v-card rounded="xl" elevation="2" class="overflow-hidden h-100">
                    <div class="gradient-bg pa-6 text-white">
                        <div class="d-flex justify-space-between align-center mb-4">
                            <v-avatar color="white" size="48">
                                <v-icon color="success">mdi-google-classroom</v-icon>
                            </v-avatar>
                            <v-chip color="white" variant="flat" class="text-success font-weight-bold">
                                {{ cls.grade_level }} {{ cls.department?.name }}
                            </v-chip>
                        </div>
                        <h3 class="text-h5 font-weight-bold mb-1">{{ cls.name }}</h3>
                        <div class="text-subtitle-2 opacity-80">{{ cls.department?.name || 'Umum' }}</div>
                    </div>
                    
                    <v-card-text class="pa-6">
                        <div class="d-flex align-center mb-4">
                            <v-icon color="grey" class="mr-3">mdi-account-group</v-icon>
                            <div>
                                <div class="text-body-2 font-weight-bold">{{ cls.students_count || 0 }} Siswa</div>
                                <div class="text-caption text-grey">Terdaftar di kelas ini</div>
                            </div>
                        </div>

                        <v-divider class="mb-4"></v-divider>

                        <v-btn
                            color="success"
                            variant="flat"
                            block
                            rounded="lg"
                            class="text-none"
                            :to="`/guru/attendance?class_id=${cls.id}`"
                        >
                            Input Presensi
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-card v-else rounded="xl" class="pa-12 text-center text-grey">
            <v-icon size="64" class="mb-4">mdi-google-classroom</v-icon>
            <div class="text-h6">Belum ada kelas yang diampu</div>
            <p>Silakan hubungi Admin jika jadwal Anda belum muncul</p>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const classes = ref([]);
const loading = ref(false);

const fetchClasses = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/guru/classes');
        if (response.data.success) {
            classes.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching classes:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchClasses);
</script>

<style scoped>
.gradient-bg {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
</style>
