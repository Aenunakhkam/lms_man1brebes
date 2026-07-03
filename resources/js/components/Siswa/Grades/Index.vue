<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Nilai Saya</h1>
            <p class="text-subtitle-1 text-grey">Pantau perkembangan hasil belajar Anda</p>
        </div>

        <!-- Tabs: Nilai Manual & Nilai Tugas -->
        <v-tabs v-model="tab" color="primary" class="mb-6" rounded="lg">
            <v-tab value="all">Semua Nilai</v-tab>
            <v-tab value="assignment">Nilai Tugas</v-tab>
            <v-tab value="manual">Nilai Ujian / Lainnya</v-tab>
        </v-tabs>

        <v-card rounded="xl" elevation="2">
            <v-data-table
                :headers="activeHeaders"
                :items="filteredGrades"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>

                <template v-slot:item.grade_type="{ item }">
                    <v-chip
                        :color="item.grade_type === 'assignment' ? 'warning' : 'primary'"
                        size="small"
                        rounded="lg"
                        variant="tonal"
                    >
                        <v-icon start size="14">{{ item.grade_type === 'assignment' ? 'mdi-clipboard-text' : 'mdi-school' }}</v-icon>
                        {{ item.grade_type === 'assignment' ? 'Tugas' : (item.category || item.grade_type) }}
                    </v-chip>
                </template>

                <template v-slot:item.score="{ item }">
                    <div class="d-flex align-center gap-2">
                        <v-chip
                            :color="getGradeColor(item.percentage)"
                            variant="flat"
                            size="small"
                            class="font-weight-bold"
                            rounded="lg"
                        >
                            {{ item.score }}
                        </v-chip>
                        <span class="text-caption text-grey" v-if="item.max_score">/ {{ item.max_score }}</span>
                    </div>
                </template>

                <template v-slot:item.percentage="{ item }">
                    <div class="d-flex align-center" style="min-width: 120px;">
                        <v-progress-linear
                            :model-value="item.percentage"
                            :color="getGradeColor(item.percentage)"
                            height="8"
                            rounded
                            class="mr-2 flex-grow-1"
                        ></v-progress-linear>
                        <span class="text-caption font-weight-bold" :class="`text-${getGradeColor(item.percentage)}`">
                            {{ item.percentage }}%
                        </span>
                    </div>
                </template>

                <template v-slot:item.notes="{ item }">
                    <span class="text-caption text-grey">{{ item.notes || item.note || '-' }}</span>
                </template>

                <template v-slot:no-data>
                    <div class="pa-12 text-center text-grey">
                        <v-icon size="64" class="mb-4">mdi-chart-areaspline-variant</v-icon>
                        <div class="text-h6">Belum ada nilai</div>
                        <p>Tetap semangat belajar, hasil tidak akan mengkhianati usaha!</p>
                    </div>
                </template>
            </v-data-table>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const grades = ref([]);
const loading = ref(false);
const tab = ref('all');

const commonHeaders = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Mata Pelajaran', key: 'subject.name' },
    { title: 'Jenis', key: 'grade_type', sortable: false },
    { title: 'Judul / Keterangan', key: 'title' },
    { title: 'Nilai', key: 'score', align: 'center' },
    { title: 'Persentase', key: 'percentage', sortable: false, width: '160px' },
    { title: 'Catatan Guru', key: 'notes' },
];

const activeHeaders = computed(() => commonHeaders);

const filteredGrades = computed(() => {
    if (tab.value === 'assignment') return grades.value.filter(g => g.grade_type === 'assignment');
    if (tab.value === 'manual') return grades.value.filter(g => g.grade_type !== 'assignment');
    return grades.value;
});

const fetchGrades = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/siswa/grades');
        if (response.data.success) {
            grades.value = response.data.data.map(g => ({
                ...g,
                percentage: g.max_score ? Math.round((g.score / g.max_score) * 100) : g.score,
                title: g.gradable?.title || g.gradable?.name || g.category || '-'
            }));
        }
    } catch (error) {
        console.error('Error fetching grades:', error);
    } finally {
        loading.value = false;
    }
};

const getGradeColor = (pct) => {
    if (pct >= 85) return 'success';
    if (pct >= 70) return 'primary';
    if (pct >= 60) return 'warning';
    return 'error';
};

onMounted(fetchGrades);
</script>
