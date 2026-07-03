<template>
    <v-app>
        <!-- Mobile-Optimized Bottom Navigation -->
        <v-bottom-navigation
            v-model="bottomNav"
            grow
            class="d-md-none"
            :color="theme.global.current.value.dark ? 'primary' : 'primary'"
            :bg-color="theme.global.current.value.dark ? '#1e293b' : 'white'"
        >
            <v-btn value="dashboard">
                <v-icon>mdi-view-dashboard</v-icon>
                <span>Dashboard</span>
            </v-btn>
            
            <v-btn value="classes">
                <v-icon>mdi-google-classroom</v-icon>
                <span>Kelas</span>
            </v-btn>
            
            <v-btn value="assignments">
                <v-icon>mdi-clipboard-text</v-icon>
                <span>Tugas</span>
            </v-btn>
            
            <v-btn value="grades">
                <v-icon>mdi-chart-box</v-icon>
                <span>Nilai</span>
            </v-btn>
        </v-bottom-navigation>

        <!-- Desktop Sidebar -->
        <v-navigation-drawer
            v-model="drawer"
            app
            :color="theme.global.current.value.dark ? '#1e293b' : 'info'"
            width="280"
            class="elevation-4 d-none d-md-block"
        >
            <div class="pa-6">
                <div class="d-flex align-center mb-6">
                    <v-avatar size="50" color="white" rounded="lg">
                        <v-img v-if="appSettings.school_logo" :src="Laravel.assetUrl + 'storage/' + appSettings.school_logo" contain></v-img>
                        <v-icon v-else size="30" color="info">mdi-school</v-icon>
                    </v-avatar>
                    <div class="ml-3 overflow-hidden">
                        <div class="text-subtitle-1 font-weight-bold truncate" style="max-width: 170px;">{{ appSettings.school_name || 'LMS MAN 1' }}</div>
                        <div class="text-caption truncate">{{ appSettings.app_name || 'Portal Siswa' }}</div>
                    </div>
                </div>

                <v-divider class="mb-4 border-opacity-25"></v-divider>

                <v-list density="compact" nav class="transparent px-0">
                    <!-- Dashboard Group -->
                    <v-list-item
                        prepend-icon="mdi-view-dashboard"
                        title="Dashboard"
                        to="/siswa"
                        exact
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Akademik Group -->
                    <div class="text-overline white--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">AKADEMIK</div>
                    
                    <v-list-item
                        prepend-icon="mdi-calendar-clock"
                        title="Jadwal Pelajaran"
                        to="/siswa/schedules"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-calendar-check"
                        title="Presensi Saya"
                        to="/siswa/attendance"
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Pembelajaran Group -->
                    <div class="text-overline white--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">PEMBELAJARAN</div>
                    
                    <v-list-item
                        prepend-icon="mdi-book-open-page-variant"
                        title="Materi Pelajaran"
                        to="/siswa/materials"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-clipboard-text"
                        title="Tugas Saya"
                        to="/siswa/assignments"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-laptop-account"
                        title="Ujian / CBT"
                        to="/siswa/cbt"
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Laporan Group -->
                    <div class="text-overline white--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">LAPORAN</div>

                    <v-list-item
                        prepend-icon="mdi-chart-box"
                        title="Nilai Saya"
                        to="/siswa/grades"
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Sistem Group -->
                    <div class="text-overline white--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">SISTEM</div>
                    
                    <v-list-item
                        prepend-icon="mdi-bullhorn"
                        title="Pengumuman"
                        to="/siswa/announcements"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-account-circle"
                        title="Profil Saya"
                        to="/siswa/profile"
                        rounded="lg"
                        class="mb-2"
                        active-class="primary white--text"
                    ></v-list-item>
                </v-list>
            </div>
        </v-navigation-drawer>

        <!-- App Bar -->
        <v-app-bar elevation="0" :color="theme.global.current.value.dark ? '#1e293b' : 'white'" class="border-b px-4">
            <v-app-bar-nav-icon @click="drawer = !drawer" class="d-none d-md-flex"></v-app-bar-nav-icon>
            
            <v-toolbar-title class="text-h6 font-weight-bold">
                Dashboard Siswa
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
            <v-menu min-width="300px" max-height="400px" rounded="lg">
                <template v-slot:activator="{ props }">
                    <v-btn icon variant="text" class="mr-2" v-bind="props">
                        <v-badge color="error" :content="unreadCount" :model-value="unreadCount > 0" dot>
                            <v-icon>mdi-bell-outline</v-icon>
                        </v-badge>
                    </v-btn>
                </template>
                <v-card>
                    <v-card-title class="text-subtitle-1 px-4 py-2 bg-primary text-white d-flex justify-space-between align-center">
                        Notifikasi
                        <v-btn v-if="unreadCount > 0" size="x-small" variant="text" @click="markAllNotificationsAsRead">
                            Tandai semua dibaca
                        </v-btn>
                    </v-card-title>
                    <v-list lines="two" v-if="notifications.length > 0">
                        <v-list-item
                            v-for="notif in notifications"
                            :key="notif.id"
                            :prepend-icon="'mdi-bell'"
                            :title="notif.title"
                            :subtitle="notif.message"
                            :class="{'bg-blue-grey-lighten-5': !notif.is_read}"
                            @click="markNotificationAsRead(notif)"
                        >
                            <template v-slot:append>
                                <v-icon v-if="!notif.is_read" color="primary" size="small">mdi-circle</v-icon>
                            </template>
                        </v-list-item>
                    </v-list>
                    <v-list lines="two" v-else>
                        <v-list-item
                            prepend-icon="mdi-information"
                            title="Belum ada notifikasi"
                            subtitle="Anda tidak memiliki notifikasi baru."
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
                            <v-avatar size="36" color="primary">
                                <v-img v-if="user?.photo" :src="Laravel.assetUrl + 'storage/' + user.photo" cover></v-img>
                                <span v-else class="font-weight-bold" style="color: white !important; font-size: 1.2rem;">{{ user?.name ? String(user.name).charAt(0).toUpperCase() : 'S' }}</span>
                            </v-avatar>
                        </div>
                    </v-btn>
                </template>
                <v-list nav>
                    <v-list-item
                        prepend-icon="mdi-account-circle"
                        title="Profil Saya"
                        to="/siswa/profile"
                    ></v-list-item>
                    <v-list-item
                        prepend-icon="mdi-cog"
                        title="Pengaturan"
                        to="/siswa/settings"
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

            <!-- Footer -->
            <v-footer :color="theme.global.current.value.dark ? '#1e293b' : 'white'" class="border-t px-8 py-4 mb-4 mb-md-0" style="flex: 0 0 auto;">
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
const bottomNav = ref('dashboard');
const user = ref(null);
const announcements = ref([]);
const appSettings = ref({});
const notifications = ref([]);
const unreadCount = ref(0);

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

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unread_count;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

const markNotificationAsRead = async (notif) => {
    if (notif.is_read) return;
    try {
        await axios.post(`/api/notifications/${notif.id}/mark-as-read`);
        notif.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        
        // Optional: redirect to link
        if (notif.link) {
            router.push(notif.link);
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllNotificationsAsRead = async () => {
    try {
        await axios.post('/api/notifications/mark-all-as-read');
        fetchNotifications();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
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
    fetchNotifications();
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
.bg-slate-900 {
    background-color: #0f172a !important;
}

.bg-slate-800 {
    background-color: #1e293b !important;
}

.border-card-dark {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.gradient-card-siswa {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.border-b {
    border-bottom: 1px solid #e0e0e0;
}
</style>
