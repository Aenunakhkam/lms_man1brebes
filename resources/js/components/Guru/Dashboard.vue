<template>
    <v-app>
        <!-- Premium Sidebar -->
        <v-navigation-drawer
            v-model="drawer"
            app
            :color="theme.global.current.value.dark ? '#111827' : 'white'"
            width="280"
            class="sidebar-premium"
        >
            <div class="pa-6">
                <div class="d-flex align-center mb-6 px-2">
                    <v-avatar size="44" color="emerald-lighten-5" rounded="lg" class="elevation-1">
                        <v-img v-if="appSettings.school_logo" :src="Laravel.assetUrl + 'storage/' + appSettings.school_logo" contain></v-img>
                        <v-icon v-else size="26" color="emerald-darken-2">mdi-teach</v-icon>
                    </v-avatar>
                    <div class="ml-3 overflow-hidden">
                        <div class="text-subtitle-1 font-weight-black text-emerald-darken-4 truncate" style="max-width: 170px;">{{ appSettings.school_name || 'LMS MAN 1' }}</div>
                        <div class="text-caption font-weight-medium text-grey-darken-1 truncate">{{ appSettings.app_name || 'Portal Guru' }}</div>
                    </div>
                </div>

                <v-divider class="mb-4 border-opacity-25"></v-divider>

                <v-list density="compact" nav class="transparent px-0">
                    <!-- Dashboard Group -->
                    <v-list-item
                        prepend-icon="mdi-view-dashboard"
                        title="Dashboard"
                        to="/guru"
                        exact
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <!-- Akademik Group -->
                    <div class="sidebar-over-line px-4 mb-2 mt-4">AKADEMIK</div>
                    
                    <v-list-item
                        prepend-icon="mdi-google-classroom"
                        title="Kelas Saya"
                        to="/guru/classes"
                        exact
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-calendar-clock"
                        title="Jadwal Mengajar"
                        to="/guru/schedules"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-calendar-check"
                        title="Presensi Siswa"
                        to="/guru/attendance"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <!-- Pembelajaran Group -->
                    <div class="sidebar-over-line px-4 mb-2 mt-4">PEMBELAJARAN</div>
                    
                    <v-list-item
                        prepend-icon="mdi-book-open-page-variant"
                        title="Materi Pelajaran"
                        to="/guru/materials"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-clipboard-text"
                        title="Tugas Siswa"
                        to="/guru/assignments"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-laptop-account"
                        title="Ujian / CBT"
                        to="/guru/cbt"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <!-- Laporan Group -->
                    <div class="sidebar-over-line px-4 mb-2 mt-4">LAPORAN</div>

                    <v-list-item
                        prepend-icon="mdi-file-percent"
                        title="Input & Rekap Nilai"
                        to="/guru/grades"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <!-- Sistem Group -->
                    <div class="sidebar-over-line px-4 mb-2 mt-4">SISTEM</div>
                    
                    <v-list-item
                        prepend-icon="mdi-bullhorn"
                        title="Pengumuman"
                        to="/guru/announcements"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-account-circle"
                        title="Profil Saya"
                        to="/guru/profile"
                        rounded="lg"
                        active-class="sidebar-item-active"
                        class="sidebar-item mb-1"
                    ></v-list-item>
                </v-list>
            </div>
        </v-navigation-drawer>

        <!-- App Bar -->
        <v-app-bar elevation="0" :color="theme.global.current.value.dark ? '#1e293b' : 'white'" class="border-b px-4">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            
            <v-toolbar-title class="text-h6 font-weight-bold">
                Dashboard Guru
            </v-toolbar-title>

            <v-spacer></v-spacer>

            <!-- Theme Switcher -->
            <v-menu min-width="150px" rounded="lg">
                <template v-slot:activator="{ props }">
                    <v-btn icon variant="text" v-bind="props" class="mr-2">
                        <v-icon>{{ currentThemeIcon }}</v-icon>
                    </v-btn>
                </template>
                <v-list nav>
                    <v-list-item
                        prepend-icon="mdi-white-balance-sunny"
                        title="Terang"
                        @click="setTheme('light')"
                        :active="theme.global.name.value === 'light'"
                    ></v-list-item>
                    <v-list-item
                        prepend-icon="mdi-moon-waning-crescent"
                        title="Gelap"
                        @click="setTheme('dark')"
                        :active="theme.global.name.value === 'dark'"
                    ></v-list-item>
                </v-list>
            </v-menu>

            <!-- Notifications -->
            <v-menu min-width="300px" rounded="lg">
                <template v-slot:activator="{ props }">
                    <v-btn icon variant="text" class="mr-2" v-bind="props">
                        <v-badge color="error" content="5" dot>
                            <v-icon>mdi-bell-outline</v-icon>
                        </v-badge>
                    </v-btn>
                </template>
                <v-card>
                    <v-card-title class="text-subtitle-1 px-4 py-2 bg-primary text-white">
                        Notifikasi
                    </v-card-title>
                    <v-list lines="two">
                        <v-list-item
                            prepend-icon="mdi-information"
                            title="Selamat Datang"
                            subtitle="Selamat datang di LMS MAN 1 Brebes. Belum ada notifikasi baru."
                        ></v-list-item>
                    </v-list>
                </v-card>
            </v-menu>

            <!-- Profile Dropdown -->
            <v-menu min-width="200px" rounded="lg">
                <template v-slot:activator="{ props }">
                    <v-btn
                        variant="text"
                        class="px-2 py-1"
                        v-bind="props"
                        rounded="lg"
                    >
                        <div class="d-flex align-center">
                            <div class="text-right mr-3 d-none d-sm-block">
                                <div class="text-subtitle-2 font-weight-bold truncate" style="max-width: 150px;">{{ user?.name }}</div>
                                <div class="text-caption text-grey">{{ user?.role_display }}</div>
                            </div>
                            <v-avatar size="36" color="success">
                                <v-img v-if="user?.photo" :src="Laravel.assetUrl + 'storage/' + user.photo" cover></v-img>
                                <span v-else class="font-weight-bold" style="color: white !important; font-size: 1.2rem;">{{ user?.name ? String(user.name).charAt(0).toUpperCase() : 'G' }}</span>
                            </v-avatar>
                        </div>
                    </v-btn>
                </template>
                <v-list nav>
                    <v-list-item
                        prepend-icon="mdi-account-circle"
                        title="Profil Saya"
                        to="/guru/profile"
                    ></v-list-item>
                    <v-list-item
                        prepend-icon="mdi-cog"
                        title="Pengaturan"
                        to="/guru/settings"
                    ></v-list-item>
                    <v-divider class="my-2"></v-divider>
                    <v-list-item
                        prepend-icon="mdi-logout"
                        title="Keluar"
                        color="error"
                        @click="logout"
                    ></v-list-item>
                </v-list>
            </v-menu>
        </v-app-bar>

        <v-main :class="theme.global.current.value.dark ? 'bg-slate-900' : 'bg-grey-lighten-4'" class="d-flex flex-column">
            <!-- Content Area -->
            <div class="flex-grow-1">
                <router-view></router-view>
            </div>

            <v-footer :color="theme.global.current.value.dark ? '#1e293b' : 'white'" class="border-t px-8 py-4" style="flex: 0 0 auto;">
                <div class="d-flex flex-column flex-sm-row justify-sm-space-between align-center w-100 text-caption text-grey-darken-1">
                    <div class="mb-2 mb-sm-0">
                        &copy; {{ new Date().getFullYear() }} <strong>{{ appSettings.school_name || 'MAN 1 Brebes' }}</strong>. 
                        Seluruh Hak Cipta Dilindungi.
                    </div>
                    <div class="d-flex align-center">
                        Transformasi Digital oleh 
                        <span class="font-weight-bold text-grey-darken-3 ml-1">{{ appSettings.app_developer || 'ObiDevOps' }}</span> 
                        <v-divider vertical class="mx-3 my-1"></v-divider>
                        <v-chip size="x-small" color="grey-lighten-3" variant="flat" class="font-weight-bold">v{{ appSettings.app_version || '1.0.0' }}</v-chip>
                    </div>
                </div>
            </v-footer>
        </v-main>
    </v-app>
</template>

<script setup>
const Laravel = window.Laravel;
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from 'vuetify';
import axios from 'axios';

const router = useRouter();
const theme = useTheme();
const drawer = ref(true);
const user = ref(null);
const appSettings = ref({});
const announcements = ref([]);

const currentThemeIcon = computed(() => {
    return theme.global.name.value === 'dark' ? 'mdi-moon-waning-crescent' : 'mdi-white-balance-sunny';
});

const setTheme = (val) => {
    theme.global.name.value = val;
    localStorage.setItem('theme', val);
};

const fetchSettings = async () => {
    try {
        const response = await axios.get('/api/settings');
        if (response.data.success) {
            appSettings.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching settings:', error);
    }
};

const fetchAnnouncements = async () => {
    try {
        const response = await axios.get('/api/announcements');
        if (response.data.success) {
            announcements.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching announcements:', error);
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
    fetchSettings();
    fetchAnnouncements();
    const userData = localStorage.getItem('user');
    if (userData) {
        user.value = JSON.parse(userData);
    }
    
    // Set initial theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        theme.global.name.value = savedTheme;
    }
});

const logout = async () => {
    try {
        await axios.post('/api/logout');
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('role');
        delete axios.defaults.headers.common['Authorization'];
        router.push('/login');
    }
};
</script>

<style scoped>
.sidebar-premium {
    border-right: 1px solid rgba(0,0,0,0.05) !important;
}

.sidebar-item {
    transition: all 0.2s ease;
    border-radius: 12px !important;
    margin-left: 12px !important;
    margin-right: 12px !important;
    padding-left: 16px !important;
}

.sidebar-item :deep(.v-icon) {
    color: #64748b !important;
}

.sidebar-item :deep(.v-list-item-title) {
    font-weight: 500;
    color: #475569 !important;
}

.sidebar-item:hover {
    background-color: #f1f5f9 !important;
}

.sidebar-item-active {
    background-color: #ecfdf5 !important;
    position: relative;
}

.sidebar-item-active::before {
    content: "";
    position: absolute;
    left: 0;
    top: 20%;
    bottom: 20%;
    width: 3px;
    background-color: #10b981;
    border-radius: 0 4px 4px 0;
}

.sidebar-item-active :deep(.v-icon) {
    color: #059669 !important;
}

.sidebar-item-active :deep(.v-list-item-title) {
    color: #047857 !important;
    font-weight: 700 !important;
}

.sidebar-over-line {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.075rem;
    color: #94a3b8;
    text-transform: uppercase;
}

/* Dark mode adjustments */
.v-theme--dark .sidebar-premium {
    border-right: 1px solid rgba(255,255,255,0.05) !important;
}

.v-theme--dark .sidebar-item :deep(.v-list-item-title) {
    color: #cbd5e1 !important;
}

.v-theme--dark .sidebar-item:hover {
    background-color: rgba(255,255,255,0.05) !important;
}

.v-theme--dark .sidebar-item-active {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

.v-theme--dark .sidebar-item-active :deep(.v-list-item-title) {
    color: #10b981 !important;
}

.text-emerald-darken-4 {
    color: #064e3b !important;
}

.v-theme--dark .text-emerald-darken-4 {
    color: #ecfdf5 !important;
}

.emerald-lighten-5 {
    background-color: #ecfdf5 !important;
}

.emerald-darken-2 {
    color: #059669 !important;
}

.border-b {
    border-bottom: 1px solid #e0e0e0;
}
</style>
