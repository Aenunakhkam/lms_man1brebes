<template>
    <v-app>
        <!-- Professional Structured Navbar - Fixed Position -->
        <v-app-bar elevation="0" color="transparent" height="80" style="z-index: 100;">
             <div class="glass-nav-pro d-flex align-center px-6 px-md-12 w-100" style="height: 80px;">
                <div class="d-flex align-center cursor-pointer" @click="$router.push('/')">
                     <v-avatar color="white" size="40" class="mr-3" rounded="0">
                         <v-img v-if="appSettings.school_logo" :src="Laravel?.assetUrl + 'storage/' + appSettings.school_logo" contain></v-img>
                         <v-icon v-else color="success" size="24">mdi-school</v-icon>
                     </v-avatar>
                    <div>
                        <div class="text-subtitle-1 font-weight-black text-white uppercase" style="line-height: 1.2; letter-spacing: 1px;">{{ appSettings.school_name || 'MAN 1 BREBES' }}</div>
                        <div class="text-caption text-white opacity-70 font-weight-bold" style="letter-spacing: 2px;">{{ appSettings.app_name || 'E-LEARNING' }}</div>
                    </div>
                </div>
                
                <v-spacer></v-spacer>
                
                <div class="d-none d-md-flex nav-links">
                    <v-btn variant="text" color="white" class="mx-2 text-none font-weight-bold" href="#beranda">BERANDA</v-btn>
                    <v-btn variant="text" color="white" class="mx-2 text-none font-weight-bold" href="#fitur">FITUR</v-btn>
                    <v-btn variant="text" color="white" class="mx-2 text-none font-weight-bold" href="#statistik">STATISTIK</v-btn>
                </div>

                <v-btn 
                    color="warning" 
                    variant="flat"
                    class="ml-8 text-none text-black font-weight-bold px-5" 
                    rounded="0" 
                    to="/login" 
                    height="34"
                    style="letter-spacing: 0.5px; font-size: 0.85rem;"
                >
                    MASUK
                </v-btn>
             </div>
        </v-app-bar>

        <v-main class="pa-0">
            <!-- Hero Section: Editorial Layout -->
            <section id="beranda" class="hero-section">
                <!-- Decorative Layer -->
                <div class="hero-decoration-layer">
                    <div class="hero-bg-gradient"></div>
                    <div class="honeycomb-pattern"></div>
                    <!-- Abstract Background Typography - Moved lower to avoid clipping -->
                    <div class="hero-floating-text">{{ appSettings.app_name?.split(' ')[0] || 'LMS' }}</div>
                    <div class="hero-floating-text secondary">{{ appSettings.school_name || 'MAN 1 BREBES' }}</div>
                </div>

                <v-container fluid class="h-100 position-relative px-6 px-md-12" style="z-index: 2;">
                    <v-row align="center" class="h-100">
                        <v-col cols="12" md="7" lg="7" class="text-white">
                            <div class="accent-line-home mb-6 slide-up"></div>
                            
                            <h1 class="hero-title mb-4 slide-up delay-1">
                                BELAJAR<br>TANPA <span class="text-gradient-gold">BATAS</span>
                            </h1>
                            <p class="text-h6 font-weight-light text-white opacity-80 mb-10 slide-up delay-2 hero-subtitle">
                                Selamat datang di {{ appSettings.app_name || 'LMS MAN 1 Brebes' }}. Mengintegrasikan teknologi modern dengan nilai-nilai Islami untuk mencetak generasi cerdas dan berkarakter.
                            </p>
                            
                            <div class="d-flex flex-wrap gap-4 slide-up delay-3">
                                <v-btn 
                                    size="x-large" 
                                    color="warning" 
                                    rounded="0" 
                                    to="/login" 
                                    class="text-none px-10 text-white font-weight-black elevation-0 sharp-btn-hero"
                                    height="64"
                                >
                                    MULAI SEKARANG
                                    <v-icon right class="ml-3">mdi-chevron-right</v-icon>
                                </v-btn>
                                <v-btn 
                                    size="x-large" 
                                    variant="outlined" 
                                    color="white" 
                                    rounded="0" 
                                    href="#fitur" 
                                    class="text-none px-10 font-weight-black border-2 sharp-btn-hero-outline"
                                    height="64"
                                >
                                    EKSPLORASI
                                </v-btn>
                            </div>
                        </v-col>
                        
                        <v-col cols="12" md="5" lg="5" class="position-relative d-none d-md-flex align-center justify-center">
                              <div class="hero-image-wrapper-pro reveal-right w-100">
                                  <v-img
                                    :src="Laravel?.assetUrl + 'images/student-hero.png'"
                                    max-height="600"
                                    contain
                                    class="hero-illustration-pro"
                                  ></v-img>
                              </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Services Section -->
            <section id="tentang" class="py-16 bg-white overflow-hidden">
                <v-container>
                    <v-row align="center" class="mb-16">
                        <v-col cols="12" md="6">
                            <div class="text-overline font-weight-black text-success mb-2" style="letter-spacing: 3px;">TENTANG KAMI</div>
                            <h2 class="text-h3 font-weight-black mb-6" style="line-height: 1.1;">{{ appSettings.app_name || 'LMS MAN 1 Brebes' }}:<br>Masa Depan Belajar</h2>
                        </v-col>
                        <v-col cols="12" md="6">
                            <p class="text-h6 text-grey-darken-1 font-weight-light">
                                {{ appSettings.school_name || 'MAN 1 Brebes' }} menghadirkan ekosistem digital yang dirancang khusus untuk memfasilitasi interaksi akademik yang lebih efisien, transparan, dan modern.
                            </p>
                        </v-col>
                    </v-row>
                    
                    <v-row>
                        <v-col cols="12" md="4">
                            <v-card class="pa-10 sharp-card h-100" elevation="0">
                                <v-icon size="48" color="success" class="mb-6">mdi-book-open-page-variant</v-icon>
                                <h3 class="text-h5 font-weight-black mb-4">Materi Digital</h3>
                                <p class="text-body-1 text-grey-darken-1 font-weight-medium">
                                    Akses library materi pembelajaran tanpa batas dalam format multimedia yang interaktif.
                                </p>
                            </v-card>
                        </v-col>
                        
                        <v-col cols="12" md="4">
                            <v-card class="pa-10 sharp-card h-100 active" elevation="0">
                                <v-icon size="48" color="white" class="mb-6">mdi-clipboard-check</v-icon>
                                <h3 class="text-h5 font-weight-black mb-4 text-white">Tugas & Kuis</h3>
                                <p class="text-body-1 text-white opacity-80 font-weight-medium">
                                    Evaluasi mandiri dengan sistem penilaian otomatis yang akurat dan real-time.
                                </p>
                            </v-card>
                        </v-col>
                        
                        <v-col cols="12" md="4">
                            <v-card class="pa-10 sharp-card h-100" elevation="0">
                                <v-icon size="48" color="warning" class="mb-6">mdi-chart-line</v-icon>
                                <h3 class="text-h5 font-weight-black mb-4">Monitoring</h3>
                                <p class="text-body-1 text-grey-darken-1 font-weight-medium">
                                    Pantau progres akademik siswa secara komprehensif melalui dashboard analitik.
                                </p>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Statistics Section -->
            <section id="statistik" class="py-16 bg-white overflow-hidden">
                <v-container>
                    <div class="text-left mb-16 relative">
                        <div class="accent-line-stat mb-4"></div>
                        <div class="text-overline font-weight-black text-success mb-2" style="letter-spacing: 5px;">DATA & STATISTIK</div>
                        <h2 class="text-h2 font-weight-black uppercase" style="line-height: 1; letter-spacing: -2px;">{{ appSettings.school_name || 'MAN 1 BREBES' }}<br><span class="text-grey-lighten-1">DALAM ANGKA</span></h2>
                    </div>
                    
                    <v-row>
                        <v-col cols="6" md="3" v-for="(stat, i) in statistics" :key="i">
                            <v-card class="pa-10 sharp-card-stat text-center h-100" elevation="0">
                                <v-icon size="40" color="black" class="mb-4">{{ stat.icon }}</v-icon>
                                <div class="text-h2 font-weight-black mb-1 counter-text">{{ stat.value }}</div>
                                <div class="text-overline font-weight-black text-grey-darken-1" style="line-height: 1.2;">{{ stat.label }}</div>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Features -->
            <section id="fitur" class="py-16 bg-grey-lighten-4">
                <v-container>
                    <div class="text-center mb-16">
                        <div class="text-overline font-weight-black text-success mb-2" style="letter-spacing: 3px;">FITUR UNGGULAN</div>
                        <h2 class="text-h3 font-weight-black">Teknologi Mutakhir</h2>
                    </div>
                    
                    <v-row>
                        <v-col cols="12" md="3" v-for="(feat, i) in features" :key="i">
                            <div class="feature-item-pro">
                                <v-icon size="40" color="black" class="mb-4">{{ feat.icon }}</v-icon>
                                <h4 class="text-h6 font-weight-black mb-2">{{ feat.title }}</h4>
                                <p class="text-body-2 text-grey-darken-2">{{ feat.desc }}</p>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Contact -->
            <section id="kontak" class="py-16 bg-white">
                <v-container>
                    <v-row align="center">
                        <v-col cols="12" md="6" class="pr-md-16">
                            <h2 class="text-h3 font-weight-black mb-8">Hubungi Kami</h2>
                            <div class="contact-grid">
                                <div class="d-flex mb-8">
                                    <v-icon size="28" color="success" class="mr-6 mt-1">mdi-map-marker</v-icon>
                                    <div>
                                        <div class="text-overline font-weight-black text-grey" style="line-height: 1;">LOKASI</div>
                                        <div class="text-h6 font-weight-black">{{ appSettings.school_address || 'Jl. Pendidikan No. 1, Brebes' }}</div>
                                    </div>
                                </div>
                                <div class="d-flex mb-8">
                                    <v-icon size="28" color="success" class="mr-6 mt-1">mdi-phone</v-icon>
                                    <div>
                                        <div class="text-overline font-weight-black text-grey" style="line-height: 1;">TELEPON</div>
                                        <div class="text-h6 font-weight-black">{{ appSettings.school_phone || '(0283) 123456' }}</div>
                                    </div>
                                </div>
                                <div class="d-flex mb-8">
                                    <v-icon size="28" color="success" class="mr-6 mt-1">mdi-email</v-icon>
                                    <div>
                                        <div class="text-overline font-weight-black text-grey" style="line-height: 1;">EMAIL</div>
                                        <div class="text-h6 font-weight-black">{{ appSettings.school_email || 'info@man1brebes.sch.id' }}</div>
                                    </div>
                                </div>
                            </div>
                        </v-col>
                        
                        <v-col cols="12" md="6">
                            <div class="pa-12 border-4-black">
                                <h3 class="text-h4 font-weight-black mb-8">Kirim Pesan</h3>
                                <v-form>
                                    <v-text-field label="Nama" variant="outlined" rounded="0" color="black" class="mb-4"></v-text-field>
                                    <v-text-field label="Email" variant="outlined" rounded="0" color="black" class="mb-4"></v-text-field>
                                    <v-textarea label="Pesan" variant="outlined" rounded="0" color="black" rows="4" class="mb-6"></v-textarea>
                                    <v-btn color="black" size="x-large" block rounded="0" class="text-none font-weight-black" height="64">
                                        KIRIM SEKARANG
                                    </v-btn>
                                </v-form>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>
        </v-main>

        <!-- Sharp Footer -->
        <v-footer class="footer-sharp py-16">
            <v-container>
                <v-row>
                    <v-col cols="12" md="5">
                        <div class="d-flex align-center mb-6">
                         <v-avatar color="white" size="44" class="mr-4" rounded="0">
                                <v-img v-if="appSettings.school_logo" :src="Laravel?.assetUrl + 'storage/' + appSettings.school_logo" contain></v-img>
                                <v-icon v-else size="32" color="success">mdi-school</v-icon>
                            </v-avatar>
                            <div class="text-h5 font-weight-black text-white uppercase">{{ appSettings.app_name || 'LMS MAN 1 Brebes' }}</div>
                        </div>
                        <p class="text-body-1 text-white opacity-60 pr-md-16">
                            Mewujudkan madrasah digital yang unggul, kompetitif, dan berakhlakul karimah melalui integrasi teknologi informasi dalam proses pembelajaran di {{ appSettings.school_name || 'MAN 1 Brebes' }}.
                        </p>
                    </v-col>
                    
                    <v-col cols="12" md="2" offset-md="1">
                        <div class="text-overline font-weight-black text-white mb-6" style="letter-spacing: 2px;">NAVIGASI</div>
                        <div class="footer-links-sharp">
                            <a href="#beranda">BERANDA</a>
                            <a href="#fitur">FITUR</a>
                            <a href="#statistik">STATISTIK</a>
                            <a href="#kontak">KONTAK</a>
                        </div>
                    </v-col>
                    
                    <v-col cols="12" md="4">
                        <div class="text-overline font-weight-black text-white mb-6" style="letter-spacing: 2px;">PENGEMBANG</div>
                        <div class="text-white mb-4">
                            <div class="text-body-2 font-weight-bold opacity-70">Sistem Dikembangkan Oleh:</div>
                            <div class="text-h6 font-weight-black">{{ appSettings.app_developer || 'Team IT MAN 1 Brebes' }}</div>
                        </div>
                        <div class="text-white opacity-50 text-caption font-weight-bold">VERSI {{ appSettings.app_version || '1.0.0' }}</div>
                    </v-col>
                </v-row>
                
                <v-divider class="my-12 border-opacity-25"></v-divider>
                
                <div class="d-flex flex-column flex-md-row justify-space-between align-center text-white opacity-50 text-caption">
                    <div class="mb-4 mb-md-0 uppercase font-weight-bold">© 2026 {{ appSettings.school_name || 'LMS MAN 1 BREBES' }}. ALL RIGHTS RESERVED.</div>
                    <div class="uppercase font-weight-bold">DESIGNED FOR EXCELLENCE</div>
                </div>
            </v-container>
        </v-footer>
    </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const statistics = ref([
    { label: 'SISWA AKTIF', value: '0', icon: 'mdi-account-group-outline' },
    { label: 'TENAGA PENGAJAR', value: '0', icon: 'mdi-account-tie-outline' },
    { label: 'MATA PELAJARAN', value: '0', icon: 'mdi-book-open-variant' },
    { label: 'RUANG KELAS', value: '0', icon: 'mdi-door-open' }
]);

const appSettings = ref({});
const Laravel = window.Laravel;

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

const features = [
    { icon: 'mdi-cellphone', title: 'Responsive', desc: 'Akses optimal dari perangkat mobile mana pun.' },
    { icon: 'mdi-cloud-upload', title: 'Cloud Data', desc: 'Penyimpanan data yang aman dan terpusat.' },
    { icon: 'mdi-bell-ring', title: 'Notifikasi', desc: 'Informasi akademik langsung ke perangkat Anda.' },
    { icon: 'mdi-shield-check', title: 'Keamanan', desc: 'Proteksi data pengguna dengan standar enkripsi.' }
];

const contactInfo = [
    { icon: 'mdi-map-marker', label: 'LOKASI', value: 'Jl. Pendidikan No. 1, Brebes' },
    { icon: 'mdi-phone', label: 'TELEPON', value: '(0283) 123456' },
    { icon: 'mdi-email', label: 'EMAIL', value: 'info@man1brebes.sch.id' }
];

onMounted(async () => {
    fetchSettings();
    try {
        const response = await axios.get('/api/public-stats');
        if (response.data.success) {
            const data = response.data.data;
            statistics.value[0].value = data.students;
            statistics.value[1].value = data.teachers;
            statistics.value[2].value = data.subjects;
            statistics.value[3].value = data.classes;
        }
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
});
</script>

<style scoped>
/* Navbar Pro */
.glass-nav-pro {
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}

/* Hero Section */
.hero-section {
    position: relative;
    min-height: 100vh;
    background: #0f4d19;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding-top: 80px; /* Space for fixed navbar */
}

.hero-decoration-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.hero-bg-gradient {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #09300f 0%, #1b5e20 50%, #2e7d32 100%);
}

.hero-floating-text {
    position: absolute;
    top: 15%; /* Shifted lower to avoid navbar overlap */
    left: 5%;
    font-size: clamp(4rem, 12vw, 15rem);
    font-weight: 900;
    color: transparent;
    -webkit-text-stroke: 1px rgba(255, 255, 255, 0.04);
    line-height: 0.8;
    user-select: none;
    pointer-events: none;
    white-space: nowrap;
}

.hero-floating-text.secondary {
    top: auto;
    bottom: 5%;
    right: 5%;
    left: auto;
    font-size: clamp(3rem, 8vw, 10rem);
}

.hero-title {
    font-size: clamp(2.2rem, 7vw, 5.5rem);
    line-height: 1;
    font-weight: 900;
    letter-spacing: -1px;
}

.hero-subtitle {
    line-height: 1.7;
    max-width: 650px;
    font-size: clamp(1rem, 1.5vw, 1.2rem);
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.honeycomb-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
    background-size: 50px 50px;
    z-index: 1;
}

.accent-line-home {
    width: 80px;
    height: 4px;
    background: #ffd700;
}

.accent-line-stat {
    width: 60px;
    height: 4px;
    background: #2e7d32;
}

.hero-illustration-pro {
    filter: drop-shadow(0 30px 60px rgba(0,0,0,0.5));
    animation: float-pro 8s ease-in-out infinite;
    transform: translateY(-20px); /* Conservative lift */
}

@keyframes float-pro {
    0%, 100% { transform: translateY(-20px); }
    50% { transform: translateY(-50px); }
}

/* Text Gradient */
.text-gradient-gold {
    background: linear-gradient(to right, #FFD700, #FDB931);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Cards & Buttons Sharp */
.sharp-card {
    border: 1px solid #eee;
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: 0 !important;
    position: relative;
    overflow: hidden;
}

.sharp-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 4px;
    background: #ffd700;
    transition: width 0.3s ease;
}

.sharp-card:hover {
    transform: translateY(-16px);
    border-color: #333;
    box-shadow: 0 30px 60px rgba(0,0,0,0.1) !important;
}

.sharp-card:hover::after {
    width: 100%;
}

.sharp-card.active {
    background: #2e7d32;
    border-color: #2e7d32;
}

.sharp-card-stat {
    border: 1px solid #111;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: 0 !important;
}

.sharp-card-stat:hover {
    background: #111;
    color: white !important;
}

.sharp-card-stat:hover .v-icon,
.sharp-card-stat:hover .counter-text,
.sharp-card-stat:hover .text-overline {
    color: white !important;
}

.counter-text {
    line-height: 1;
    letter-spacing: -2px;
}

.feature-item-pro {
    padding: 40px;
    border: 1px solid transparent;
    transition: all 0.3s ease;
}

.feature-item-pro:hover {
    background: white;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.border-4-black {
    border: 4px solid #000;
}

.footer-sharp {
    background: #111;
}

.footer-links-sharp a {
    display: block;
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-weight: 800;
    margin-bottom: 12px;
    letter-spacing: 1px;
    font-size: 0.8rem;
    transition: color 0.3s ease;
}

.footer-links-sharp a:hover {
    color: white;
}

/* Animations */
.slide-up {
    animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

.slide-left {
    animation: slideLeft 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(60px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideLeft {
    from { opacity: 0; transform: translateX(60px); }
    to { opacity: 1; transform: translateX(0); }
}

.delay-1 { animation-delay: 0.2s; }
.delay-2 { animation-delay: 0.4s; }
.delay-3 { animation-delay: 0.6s; }

/* Responsive Adjustments */
@media (max-width: 960px) {
    .text-h1 { font-size: 3.5rem !important; }
    .hero-section { text-align: left; }
    .px-12 { padding-left: 20px !important; padding-right: 20px !important; }
    .hero-floating-text { display: none; }
}
</style>
