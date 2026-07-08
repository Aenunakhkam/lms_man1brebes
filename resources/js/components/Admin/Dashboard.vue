<template>
    <v-app>
        <!-- Premium Sidebar -->
        <v-navigation-drawer
            v-model="drawer"
            app
            :color="theme.global.current.value.dark ? '#1e293b' : 'primary'"
            width="280"
            class="elevation-4"
        >
            <div class="pa-6">
                <div class="d-flex align-center mb-6">
                    <v-avatar size="50" color="white" rounded="lg">
                        <v-img v-if="appSettings.school_logo" :src="Laravel.assetUrl + 'storage/' + appSettings.school_logo" contain></v-img>
                        <v-icon v-else size="30" color="primary">mdi-school</v-icon>
                    </v-avatar>
                    <div class="ml-3 overflow-hidden">
                        <div class="text-subtitle-1 font-weight-bold truncate" style="max-width: 170px;">{{ appSettings.school_name || 'LMS MAN 1' }}</div>
                        <div class="text-caption truncate">{{ appSettings.app_name || 'Admin Panel' }}</div>
                    </div>
                </div>

                <v-divider class="mb-4 border-opacity-25"></v-divider>

                <v-list density="compact" nav class="transparent px-0">
                    <!-- Dashboard Group -->
                    <v-list-item
                        prepend-icon="mdi-view-dashboard"
                        title="Dashboard"
                        to="/admin"
                        exact
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Data Master Group -->
                    <div class="text-overline grey--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">DATA MASTER</div>
                    
                    <v-list-item
                        prepend-icon="mdi-account-group"
                        title="Semua Pengguna"
                        to="/admin/users"
                        exact
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-account-star"
                        title="Data Guru"
                        to="/admin/users?role=guru"
                        :active="$route.path === '/admin/users' && $route.query.role === 'guru'"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-account-school"
                        title="Data Siswa"
                        to="/admin/users?role=siswa"
                        :active="$route.path === '/admin/users' && $route.query.role === 'siswa'"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>
                    
                    <v-list-item
                        prepend-icon="mdi-domain"
                        title="Jurusan / Prodi"
                        to="/admin/departments"
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Akademik Group -->
                    <div class="text-overline grey--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">AKADEMIK</div>
                    
                    <v-list-item
                        prepend-icon="mdi-google-classroom"
                        title="Manajemen Kelas"
                        to="/admin/classes"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>
                    
                    <v-list-item
                        prepend-icon="mdi-book-open-variant"
                        title="Mata Pelajaran"
                        to="/admin/subjects"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-calendar-clock"
                        title="Jadwal Pelajaran"
                        to="/admin/schedules"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-laptop-account"
                        title="CBT / Ujian"
                        to="/admin/cbt"
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Laporan Group -->
                    <div class="text-overline grey--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">LAPORAN & PRESENSI</div>

                    <v-list-item
                        prepend-icon="mdi-file-percent"
                        title="Ekspor Nilai"
                        to="/admin/grades"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-calendar-check"
                        title="Presensi"
                        to="/admin/attendance"
                        rounded="lg"
                        class="mb-4"
                        active-class="primary white--text"
                    ></v-list-item>

                    <!-- Settings Group -->
                    <div class="text-overline grey--text px-4 mb-2 opacity-60 font-weight-bold" style="letter-spacing: 1px !important;">SISTEM</div>
                    
                    <v-list-item
                        prepend-icon="mdi-bullhorn"
                        title="Pengumuman"
                        to="/admin/announcements"
                        rounded="lg"
                        class="mb-1"
                        active-class="primary white--text"
                    ></v-list-item>

                    <v-list-item
                        prepend-icon="mdi-cog"
                        title="Pengaturan"
                        to="/admin/settings"
                        rounded="lg"
                        class="mb-2"
                        active-class="primary white--text"
                    ></v-list-item>
                </v-list>
            </div>
        </v-navigation-drawer>

        <!-- App Bar -->
        <v-app-bar elevation="0" :color="theme.global.current.value.dark ? '#1e293b' : 'white'" class="border-b px-4">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            
            <v-toolbar-title class="text-h6 font-weight-bold">
                Dashboard Admin
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
                        <v-badge color="error" :content="unreadCount" :model-value="unreadCount > 0">
                            <v-icon>mdi-bell-outline</v-icon>
                        </v-badge>
                    </v-btn>
                </template>
                <v-card width="350" max-height="400" class="d-flex flex-column">
                    <v-card-title class="text-subtitle-1 px-4 py-3 bg-primary text-white d-flex justify-space-between align-center">
                        <span>Notifikasi</span>
                        <v-btn v-if="unreadCount > 0" variant="text" size="small" class="text-none px-0" color="white" @click="markAllAsRead">Tandai semua dibaca</v-btn>
                    </v-card-title>
                    <v-list lines="three" class="overflow-y-auto" style="flex: 1 1 auto;">
                        <template v-if="notifications.length > 0">
                            <v-list-item
                                v-for="notif in notifications"
                                :key="notif.id"
                                :title="notif.title"
                                :subtitle="notif.message"
                                :class="!notif.is_read ? 'bg-blue-lighten-5' : ''"
                                @click="markAsRead(notif)"
                            >
                                <template v-slot:prepend>
                                    <v-icon :color="notif.is_read ? 'grey' : 'primary'">mdi-bell-ring</v-icon>
                                </template>
                            </v-list-item>
                        </template>
                        <v-list-item v-else
                            prepend-icon="mdi-information"
                            title="Belum ada notifikasi"
                            subtitle="Anda telah membaca semua notifikasi."
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
                                <span v-else class="font-weight-bold" style="color: white !important; font-size: 1.2rem;">{{ user?.name ? String(user.name).charAt(0).toUpperCase() : 'A' }}</span>
                            </v-avatar>
                        </div>
                    </v-btn>
                </template>
                <v-list nav>
                    <v-list-item
                        prepend-icon="mdi-account-circle"
                        title="Profil Saya"
                        to="/admin/profile"
                    ></v-list-item>
                    <v-list-item
                        prepend-icon="mdi-cog"
                        title="Pengaturan"
                        to="/admin/settings"
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
            <div class="flex-grow-1 pa-6">
                <router-view></router-view>
            </div>
            
            <v-footer :color="theme.global.current.value.dark ? '#1a1a1a' : 'white'" class="border-t px-8 py-4" style="flex: 0 0 auto;">
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

const notifications = ref([]);
const unreadCount = ref(0);

const currentThemeIcon = computed(() => {
    return theme.global.name.value === 'dark' ? 'mdi-moon-waning-crescent' : 'mdi-white-balance-sunny';
});

const setTheme = (val) => {
    theme.global.name.value = val;
    localStorage.setItem('theme', val);
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

const markAsRead = async (notif) => {
    if (notif.is_read) {
        if (notif.link) router.push(notif.link);
        return;
    }
    try {
        await axios.post(`/api/notifications/${notif.id}/mark-as-read`);
        notif.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        if (notif.link) router.push(notif.link);
    } catch (error) {
        console.error('Error marking as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/api/notifications/mark-all-as-read');
        notifications.value.forEach(n => n.is_read = true);
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
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

onMounted(() => {
    fetchSettings();
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

.gradient-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.border-b {
    border-bottom: 1px solid #e0e0e0;
}

.chart-placeholder {
    background: #f5f7fa;
    border-radius: 12px;
}

.timeline-custom {
    max-height: 300px;
    overflow-y: auto;
}
</style>
