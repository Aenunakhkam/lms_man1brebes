<template>
    <v-container fluid class="pa-4 pa-md-6">
        <v-row class="mb-4 align-center">
            <v-col>
                <h1 class="text-h5 text-md-h4 font-weight-bold mb-2">
                    Pengumuman
                </h1>
                <p class="text-body-1 text-grey-darken-1 mb-0">
                    Informasi dan pengumuman terbaru dari sekolah
                </p>
            </v-col>
        </v-row>

        <!-- Skeleton Loader -->
        <v-row v-if="loading">
            <v-col v-for="i in 3" :key="i" cols="12" md="6">
                <v-skeleton-loader type="card" rounded="xl"></v-skeleton-loader>
            </v-col>
        </v-row>

        <!-- Announcements List -->
        <v-row v-else-if="announcements.length > 0">
            <v-col v-for="announcement in announcements" :key="announcement.id" cols="12">
                <v-card 
                    class="pa-4" 
                    rounded="xl" 
                    elevation="2"
                    :class="theme.global.current.value.dark ? 'bg-slate-800' : 'bg-white'"
                >
                    <div class="d-flex align-start mb-3">
                        <v-avatar color="primary-lighten-4" size="48" class="mr-4">
                            <v-icon color="primary" size="24">mdi-bullhorn</v-icon>
                        </v-avatar>
                        <div class="flex-grow-1">
                            <h3 class="text-h6 font-weight-bold mb-1">{{ announcement.title }}</h3>
                            <div class="d-flex align-center text-caption text-grey">
                                <v-icon size="14" class="mr-1">mdi-calendar</v-icon>
                                {{ formatDate(announcement.created_at) }}
                                <v-icon size="14" class="ml-3 mr-1">mdi-account</v-icon>
                                {{ announcement.user?.name || 'Admin' }}
                            </div>
                        </div>
                    </div>
                    <v-divider class="my-3"></v-divider>
                    <div class="text-body-1" v-html="announcement.content"></div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Empty State -->
        <v-row v-else>
            <v-col cols="12" class="text-center py-10">
                <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-bell-off-outline</v-icon>
                <h3 class="text-h6 font-weight-medium text-grey-darken-1">Belum Ada Pengumuman</h3>
                <p class="text-grey">Saat ini belum ada pengumuman terbaru dari pihak sekolah.</p>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTheme } from 'vuetify';
import axios from 'axios';

const theme = useTheme();
const announcements = ref([]);
const loading = ref(true);

const fetchAnnouncements = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/announcements');
        if (response.data.success) {
            announcements.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching announcements:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    fetchAnnouncements();
});
</script>

<style scoped>
.bg-slate-800 {
    background-color: #1e293b !important;
}
</style>
