<template>
    <v-app>
        <!-- Maintenance Screen -->
        <v-container v-if="isMaintenance" class="fill-height maintenance-bg pa-6" fluid>
            <div class="glow-orb glow-orb-1"></div>
            <div class="glow-orb glow-orb-2"></div>
            
            <v-row align="center" justify="center" class="position-relative z-index-1">
                <v-col cols="12" sm="10" md="8" lg="6" class="text-center">
                    
                    <!-- School Logo & Name Header -->
                    <div class="d-flex align-center justify-center mb-10 school-header">
                        <v-avatar v-if="schoolLogo" size="48" class="mr-3 border-glass">
                            <v-img :src="schoolLogo" contain></v-img>
                        </v-avatar>
                        <div class="text-left">
                            <div class="text-overline font-weight-bold text-cyan-accent-2 tracking-wide lh-1">PORTAL LMS</div>
                            <div class="text-subtitle-1 font-weight-bold text-white lh-1">{{ schoolName }}</div>
                        </div>
                    </div>
                    
                    <!-- Maintenance Icon -->
                    <div class="icon-container mb-8">
                        <div class="icon-pulse-glow"></div>
                        <v-icon size="80" color="cyan-accent-2">mdi-server-off</v-icon>
                    </div>
                    
                    <!-- Main Text -->
                    <h1 class="text-h3 font-weight-black text-white mb-4 tracking-tight title-gradient">
                        Pemeliharaan Sistem
                    </h1>
                    
                    <!-- Divider -->
                    <div class="custom-divider mx-auto mb-6"></div>
                    
                    <!-- Custom Message Display -->
                    <div class="message-box mb-8 mx-auto">
                        <p class="text-body-1 text-blue-grey-lighten-3 font-weight-medium lh-relaxed">
                            {{ maintenanceMessage || 'Mohon maaf, saat ini aplikasi sedang dalam pemeliharaan berkala untuk peningkatan sistem. Silakan coba beberapa saat lagi.' }}
                        </p>
                    </div>
                    
                    <!-- Button -->
                    <v-btn
                        v-if="hasToken"
                        color="cyan-accent-2"
                        variant="outlined"
                        rounded="xl"
                        size="large"
                        @click="logout"
                        prepend-icon="mdi-logout"
                        class="text-none btn-premium px-8"
                    >
                        Kembali ke Login
                    </v-btn>
                </v-col>
            </v-row>
        </v-container>
        
        <router-view v-else></router-view>
    </v-app>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isMaintenanceMode = ref(false);
const maintenanceMessage = ref('');
const schoolLogo = ref('');
const schoolName = ref('LMS MAN 1 Brebes');
const userRole = ref(localStorage.getItem('role'));
const hasToken = computed(() => !!localStorage.getItem('token'));

const isMaintenance = computed(() => {
    return isMaintenanceMode.value && userRole.value !== 'admin' && route.path !== '/login';
});

const logout = () => {
    localStorage.clear();
    userRole.value = null;
    router.push('/login');
};

// Keep role updated
watch(() => route.path, () => {
    userRole.value = localStorage.getItem('role');
});

onMounted(async () => {
    // Intercept 503 maintenance errors
    axios.interceptors.response.use(
        response => response,
        error => {
            if (error.response && error.response.status === 503 && error.response.data.maintenance) {
                isMaintenanceMode.value = true;
                if (error.response.data.maintenance_message) {
                    maintenanceMessage.value = error.response.data.maintenance_message;
                }
            }
            return Promise.reject(error);
        }
    );

    try {
        const response = await axios.get('/api/settings');
        if (response.data.success) {
            const settings = response.data.data;
            isMaintenanceMode.value = settings.is_maintenance;
            maintenanceMessage.value = settings.maintenance_message || '';
            schoolName.value = settings.school_name || 'LMS MAN 1 Brebes';
            
            // Update Favicon
            if (settings.school_logo) {
                schoolLogo.value = '/storage/' + settings.school_logo;
                const link = document.querySelector("link[rel*='icon']") || document.createElement('link');
                link.type = 'image/x-icon';
                link.rel = 'shortcut icon';
                link.href = '/storage/' + settings.school_logo;
                document.getElementsByTagName('head')[0].appendChild(link);
            }

            // Update Page Title
            if (settings.app_name) {
                document.title = settings.app_name;
            }
        }
    } catch (error) {
        console.error('Error fetching global settings:', error);
    }
});
</script>

<style>
.maintenance-bg {
    background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%) !important;
    position: relative;
    overflow: hidden;
}

.glow-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.15;
    pointer-events: none;
}

.glow-orb-1 {
    width: 400px;
    height: 400px;
    background: #00f2fe;
    top: -100px;
    left: -100px;
}

.glow-orb-2 {
    width: 500px;
    height: 500px;
    background: #4facfe;
    bottom: -150px;
    right: -150px;
}

.border-glass {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(5px);
}

.z-index-1 {
    position: relative;
    z-index: 1;
}

.lh-1 {
    line-height: 1 !important;
}

.tracking-wide {
    letter-spacing: 0.15em !important;
}

.tracking-tight {
    letter-spacing: -0.02em !important;
}

.school-header {
    animation: fadeInDown 1s ease-out;
}

.icon-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(0, 242, 254, 0.05);
    border: 1px solid rgba(0, 242, 254, 0.15);
    backdrop-filter: blur(10px);
    box-shadow: 0 0 40px rgba(0, 242, 254, 0.1);
    animation: scaleUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.icon-pulse-glow {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 2px solid rgba(0, 242, 254, 0.3);
    animation: pulseGlow 3s infinite;
}

.title-gradient {
    background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 2.5rem !important;
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.custom-divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
    border-radius: 2px;
    box-shadow: 0 0 10px rgba(0, 242, 254, 0.5);
    animation: fadeInUp 0.8s ease-out 0.4s both;
}

.message-box {
    max-width: 550px;
    padding: 1.5rem;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.btn-premium {
    border-color: rgba(0, 242, 254, 0.5) !important;
    color: #00f2fe !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    animation: fadeInUp 0.8s ease-out 0.8s both;
}

.btn-premium:hover {
    background: rgba(0, 242, 254, 0.1) !important;
    border-color: #00f2fe !important;
    box-shadow: 0 0 20px rgba(0, 242, 254, 0.3);
    transform: translateY(-2px);
}

@keyframes pulseGlow {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.15); opacity: 0; }
    100% { transform: scale(1); opacity: 0.8; }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes scaleUp {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}
</style>
