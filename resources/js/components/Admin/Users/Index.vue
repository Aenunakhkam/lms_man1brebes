<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-title class="d-flex align-center py-4 px-6 flex-wrap gap-4">
                <span class="text-h5 font-weight-bold">{{ pageTitle }}</span>
                <v-spacer></v-spacer>
                
                <div class="d-flex gap-2 flex-wrap">
                    <!-- Toggle only visible if NO role filter in query (General view) -->
                    <v-btn-toggle v-if="!route.query.role" v-model="roleFilter" mandatory color="primary" class="mr-4">
                        <v-btn value="">Semua</v-btn>
                        <v-btn value="guru">Guru</v-btn>
                        <v-btn value="siswa">Siswa</v-btn>
                    </v-btn-toggle>

                    <v-menu>
                        <template v-slot:activator="{ props }">
                            <v-btn color="success" prepend-icon="mdi-export" variant="outlined" v-bind="props">
                                Ekspor
                            </v-btn>
                        </template>
                        <v-list>
                            <v-list-item @click="exportExcel">
                                <v-list-item-title>
                                    <v-icon color="green" class="mr-2">mdi-microsoft-excel</v-icon> Ekspor Excel
                                </v-list-item-title>
                            </v-list-item>
                            <v-list-item @click="exportPDF">
                                <v-list-item-title>
                                    <v-icon color="red" class="mr-2">mdi-file-pdf-box</v-icon> Ekspor PDF (Landscape)
                                </v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    
                    <template v-if="roleFilter">
                        <v-btn color="info" prepend-icon="mdi-import" variant="outlined" @click="$refs.fileInput.click()">
                            Impor Excel
                        </v-btn>
                        
                        <v-btn color="grey-darken-1" prepend-icon="mdi-download" variant="text" size="small" @click="downloadTemplate">
                            Unduh Template
                        </v-btn>

                        <input type="file" ref="fileInput" hidden @change="handleImport" accept=".xlsx,.xls">
                    </template>

                    <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog()">
                        {{ buttonText }}
                    </v-btn>
                </div>
            </v-card-title>

            <v-data-table
                :headers="dynamicHeaders"
                :items="items"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.password_raw="{ item }">
                    <code class="px-2 py-1 rounded bg-yellow-lighten-4 text-orange-darken-4 font-weight-bold" v-if="item.password_raw">{{ item.password_raw }}</code>
                    <span class="text-grey italic text-caption" v-else>(tidak tersimpan)</span>
                </template>
                <template v-slot:item.role="{ item }">
                    <v-chip
                        :color="getRoleColor(item.role?.name)"
                        size="small"
                        class="text-uppercase"
                    >
                        {{ item.role?.display_name || item.role?.name }}
                    </v-chip>
                </template>
                <template v-slot:item.active="{ item }">
                    <v-icon :color="item.is_active ? 'success' : 'grey'">
                        {{ item.is_active ? 'mdi-check-circle' : 'mdi-minus-circle' }}
                    </v-icon>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-tooltip text="Reset Password">
                        <template v-slot:activator="{ props }">
                            <v-icon v-bind="props" size="small" class="me-2" color="warning" @click="resetPassword(item)">
                                mdi-lock-reset
                            </v-icon>
                        </template>
                    </v-tooltip>
                    <v-tooltip text="Edit">
                        <template v-slot:activator="{ props }">
                            <v-icon v-bind="props" size="small" class="me-2" color="primary" @click="openDialog(item)">
                                mdi-pencil
                            </v-icon>
                        </template>
                    </v-tooltip>
                    <v-tooltip text="Hapus">
                        <template v-slot:activator="{ props }">
                            <v-icon v-bind="props" size="small" color="error" @click="deleteItem(item)">
                                mdi-delete
                            </v-icon>
                        </template>
                    </v-tooltip>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Form -->
        <v-dialog v-model="dialog" max-width="700px">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ dialogTitle }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.name"
                                    label="Nama Lengkap"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Nama wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.email"
                                    label="Email"
                                    type="email"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Email wajib diisi']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.password"
                                    label="Password"
                                    type="password"
                                    variant="outlined"
                                    :hint="editedIndex > -1 ? 'Kosongkan jika tidak ingin mengubah password' : ''"
                                    :rules="editedIndex === -1 ? [v => !!v || 'Password wajib diisi'] : []"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6" v-if="!roleFilter">
                                <v-select
                                    v-model="editedItem.role_id"
                                    :items="roles"
                                    item-title="display_name"
                                    item-value="id"
                                    label="Role"
                                    variant="outlined"
                                    :rules="[v => !!v || 'Role wajib diisi']"
                                ></v-select>
                            </v-col>
                            
                            <!-- Conditional Fields based on Role -->
                            <v-col cols="12" md="6" v-if="isStudent">
                                <v-text-field
                                    v-model="editedItem.nis"
                                    label="NIS"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6" v-if="isTeacher">
                                <v-text-field
                                    v-model="editedItem.nip"
                                    label="NIP"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            
                            <v-col cols="12">
                                <v-textarea
                                    v-model="editedItem.address"
                                    label="Alamat"
                                    variant="outlined"
                                    rows="2"
                                ></v-textarea>
                            </v-col>
                            
                            <v-col cols="12">
                                <v-checkbox
                                    v-model="editedItem.is_active"
                                    label="Akun Aktif"
                                    color="primary"
                                ></v-checkbox>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="grey-darken-1" variant="text" @click="closeDialog">Batal</v-btn>
                    <v-btn color="primary" variant="text" @click="save">Simpan</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const route = useRoute();
const fileInput = ref(null);
const { showSuccess, showError, showConfirm } = useAlert();

const items = ref([]);
const roles = ref([
    { id: 1, name: 'admin', display_name: 'Administrator' },
    { id: 2, name: 'guru', display_name: 'Guru' },
    { id: 3, name: 'siswa', display_name: 'Siswa' }
]);
const loading = ref(false);
const dialog = ref(false);
const roleFilter = ref(route.query.role || '');
const editedIndex = ref(-1);
const editedItem = ref({
    name: '',
    email: '',
    password: '',
    role_id: null,
    nis: '',
    nip: '',
    address: '',
    is_active: true
});
const defaultItem = {
    name: '',
    email: '',
    password: '',
    role_id: null,
    nis: '',
    nip: '',
    address: '',
    is_active: true
};

const dynamicHeaders = computed(() => {
    let base = [
        { title: 'No.', key: 'index', sortable: false, width: '50px' },
        { title: 'Nama', align: 'start', key: 'name' },
        { title: 'Email', key: 'email' },
        { title: 'Password', key: 'password_raw', sortable: false },
    ];

    if (roleFilter.value === 'siswa') {
        base.push({ title: 'NIS', key: 'nis' });
    } else if (roleFilter.value === 'guru') {
        base.push({ title: 'NIP', key: 'nip' });
    } else {
        base.push({ title: 'Role', key: 'role' });
    }

    base.push({ title: 'Status', key: 'active' });
    base.push({ title: 'Aksi', key: 'actions', sortable: false });
    
    return base;
});

const isStudent = computed(() => {
    const role = roles.value.find(r => r.id === editedItem.value.role_id);
    return role?.name === 'siswa';
});

const isTeacher = computed(() => {
    const role = roles.value.find(r => r.id === editedItem.value.role_id);
    return role?.name === 'guru';
});

const pageTitle = computed(() => {
    if (roleFilter.value === 'guru') return 'Data Guru';
    if (roleFilter.value === 'siswa') return 'Data Siswa';
    return 'Data Pengguna';
});

const buttonText = computed(() => {
    if (roleFilter.value === 'guru') return 'Tambah Guru';
    if (roleFilter.value === 'siswa') return 'Tambah Siswa';
    return 'Tambah User';
});

const dialogTitle = computed(() => {
    const action = editedIndex.value === -1 ? 'Tambah' : 'Edit';
    if (roleFilter.value === 'guru') return `${action} Guru`;
    if (roleFilter.value === 'siswa') return `${action} Siswa`;
    return `${action} User`;
});

const loadData = async () => {
    loading.value = true;
    try {
        const params = {};
        if (roleFilter.value) params.role = roleFilter.value;
        const response = await axios.get('/api/admin/users', { params });
        items.value = response.data.data;
    } catch (error) {
        console.error('Error loading data:', error);
        showError('Gagal memuat data');
    } finally {
        loading.value = false;
    }
};

const exportExcel = async () => {
    loading.value = true;
    try {
        const role = roleFilter.value;
        const response = await axios.get(`/api/admin/users/export?role=${role}&format=excel`, {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${role || 'users'}_export_${new Date().toISOString().slice(0,10)}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        showError('Gagal ekspor Excel');
    } finally {
        loading.value = false;
    }
};

const exportPDF = async () => {
    loading.value = true;
    try {
        const role = roleFilter.value;
        const response = await axios.get(`/api/admin/users/export?role=${role}&format=pdf`, {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${role || 'users'}_export_${new Date().toISOString().slice(0,10)}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        showError('Gagal ekspor PDF');
    } finally {
        loading.value = false;
    }
};

const downloadTemplate = async () => {
    loading.value = true;
    try {
        const role = roleFilter.value || 'siswa';
        const response = await axios.get(`/api/admin/users/template?role=${role}`, {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `template_import_${role}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        showError('Gagal mengunduh template');
    } finally {
        loading.value = false;
    }
};

const handleImport = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!roleFilter.value) {
        showError('Pilih tab Guru atau Siswa sebelum mengimpor');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('role', roleFilter.value);

    loading.value = true;
    try {
        const response = await axios.post('/api/admin/users/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        if (response.data.success) {
            showSuccess('Berhasil!', response.data.message);
            await loadData();
        }
    } catch (error) {
        showError('Gagal impor', error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        loading.value = false;
        event.target.value = '';
    }
};

watch(roleFilter, () => {
    loadData();
});

watch(() => route.query.role, (newRole) => {
    roleFilter.value = newRole || '';
});

onMounted(() => {
    loadData();
});

const getRoleColor = (roleName) => {
    if (roleName === 'admin') return 'purple';
    if (roleName === 'guru') return 'success';
    if (roleName === 'siswa') return 'info';
    return 'grey';
};

const openDialog = (item = null) => {
    if (item) {
        editedIndex.value = items.value.indexOf(item);
        editedItem.value = Object.assign({}, item);
        editedItem.value.password = ''; 
    } else {
        editedIndex.value = -1;
        editedItem.value = Object.assign({}, defaultItem);
        
        if (roleFilter.value) {
            const activeRole = roles.value.find(r => r.name === roleFilter.value);
            if (activeRole) editedItem.value.role_id = activeRole.id;
        }
    }
    dialog.value = true;
};

const closeDialog = () => {
    dialog.value = false;
    setTimeout(() => {
        editedItem.value = Object.assign({}, defaultItem);
        editedIndex.value = -1;
    }, 300);
};

const save = async () => {
    try {
        const payload = { ...editedItem.value };
        if (!payload.password) delete payload.password;

        if (editedIndex.value > -1) {
            await axios.put(`/api/admin/users/${editedItem.value.id}`, payload);
            showSuccess('Berhasil!', 'Data telah diperbarui');
        } else {
            await axios.post('/api/admin/users', payload);
            showSuccess('Berhasil!', 'Data baru telah ditambahkan');
        }
        await loadData();
        closeDialog();
    } catch (error) {
        showError('Gagal!', error.response?.data?.message || 'Gagal menyimpan data');
    }
};

const deleteItem = async (item) => {
    if (item.id === JSON.parse(localStorage.getItem('user'))?.id) {
        showError('Gagal!', 'Tidak dapat menghapus akun sendiri');
        return;
    }

    const confirmed = await showConfirm(
        'Hapus Pengguna?',
        `Apakah Anda yakin ingin menghapus ${item.name}?`
    );
    
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/users/${item.id}`);
            await showSuccess('Terhapus!', 'Pengguna berhasil dihapus');
            await loadData();
        } catch (error) {
            showError('Gagal!', 'Terjadi kesalahan saat menghapus data');
        }
    }
};

const resetPassword = async (item) => {
    const confirmed = await showConfirm(
        'Reset Password?',
        `Apakah Anda yakin ingin mereset password pengguna ${item.name} menjadi "password123"?`
    );
    
    if (confirmed) {
        try {
            await axios.post(`/api/admin/users/${item.id}/reset-password`);
            showSuccess('Berhasil!', `Password untuk ${item.name} telah direset menjadi "password123"`);
        } catch (error) {
            showError('Gagal!', error.response?.data?.message || 'Terjadi kesalahan saat mereset password');
        }
    }
};
</script>
