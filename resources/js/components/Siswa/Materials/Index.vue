<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Materi Pelajaran</h1>
            <p class="text-subtitle-1 text-grey">Akses bahan ajar dari bapak/ibu guru</p>
        </div>

        <v-row v-if="materials.length">
            <v-col v-for="material in materials" :key="material.id" cols="12" md="6" lg="4">
                <v-card rounded="xl" elevation="2" class="h-100 d-flex flex-column">
                    <v-card-item class="pa-4 flex-grow-1">
                        <template v-slot:prepend>
                            <v-avatar color="info-lighten-4" rounded="lg" size="48">
                                <v-icon color="info">mdi-book-open-page-variant</v-icon>
                            </v-avatar>
                        </template>
                        <v-card-title class="text-h6 font-weight-bold mb-1">{{ material.title }}</v-card-title>
                        <v-card-subtitle class="text-caption text-primary font-weight-bold">
                            {{ material.subject?.name }}
                        </v-card-subtitle>
                        <div class="mt-4 text-body-2 text-grey-darken-1 line-clamp-3">
                            {{ material.description || 'Tidak ada deskripsi.' }}
                        </div>
                        <div class="mt-4 d-flex align-center">
                            <v-icon size="14" color="grey" class="mr-1">mdi-account</v-icon>
                            <span class="text-caption text-grey">{{ material.teacher?.name }}</span>
                        </div>
                    </v-card-item>
                    <v-divider></v-divider>
                    <v-card-actions class="pa-4 flex-column">
                        <v-btn
                            v-if="material.file_path"
                            color="primary"
                            variant="flat"
                            rounded="lg"
                            block
                            prepend-icon="mdi-download"
                            :href="`/storage/${material.file_path}`"
                            target="_blank"
                            class="mb-2"
                        >
                            Unduh File
                        </v-btn>
                        <v-btn
                            v-if="material.link"
                            color="info"
                            variant="tonal"
                            rounded="lg"
                            block
                            prepend-icon="mdi-link"
                            :href="material.link"
                            target="_blank"
                        >
                            Buka Tautan (Link)
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
        <v-card v-else rounded="xl" class="pa-12 text-center text-grey">
            <v-icon size="64" class="mb-4">mdi-book-off-outline</v-icon>
            <div class="text-h6">Belum ada materi pelajaran</div>
            <p>Materi akan muncul di sini setelah bapak/ibu guru menguploadnya.</p>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const materials = ref([]);
const loading = ref(false);

const fetchMaterials = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/siswa/materials');
        if (response.data.success) {
            materials.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching materials:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchMaterials);
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    line-clamp: 3;
    overflow: hidden;
}
</style>
