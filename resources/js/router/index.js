import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

// Import components
import Login from '../components/Auth/Login.vue';
import AdminDashboard from '../components/Admin/Dashboard.vue';
import GuruDashboard from '../components/Guru/Dashboard.vue';
import SiswaDashboard from '../components/Siswa/Dashboard.vue';

// Set axios base URL and interceptor
// Set axios base URL and interceptor
axios.defaults.baseURL = window.Laravel ? window.Laravel.baseUrl : window.location.origin;

// Add token to requests if it exists
const token = localStorage.getItem('token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('../components/Home.vue'),
        meta: { guest: true }
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/admin',
        component: AdminDashboard,
        meta: { requiresAuth: true, role: 'admin' },
        children: [
            {
                path: '',
                name: 'AdminHome',
                component: () => import('../components/Admin/Home.vue')
            },
            {
                path: 'users',
                name: 'AdminUsers',
                component: () => import('../components/Admin/Users/Index.vue')
            },
            {
                path: 'departments',
                name: 'AdminDepartments',
                component: () => import('../components/Admin/Departments/Index.vue')
            },
            {
                path: 'classes',
                name: 'AdminClasses',
                component: () => import('../components/Admin/Classes/Index.vue')
            },
            {
                path: 'subjects',
                name: 'AdminSubjects',
                component: () => import('../components/Admin/Subjects/Index.vue')
            },
            {
                path: 'schedules',
                name: 'AdminSchedules',
                component: () => import('../components/Admin/Schedules/Index.vue')
            },
            {
                path: 'cbt',
                name: 'AdminCBT',
                component: () => import('../components/Admin/CBT/Index.vue')
            },
            {
                path: 'profile',
                name: 'AdminProfile',
                component: () => import('../components/Admin/Profile/Index.vue')
            },
            {
                path: 'settings',
                name: 'AdminSettings',
                component: () => import('../components/Admin/Settings/Index.vue')
            },
            {
                path: 'grades',
                name: 'AdminGrades',
                component: () => import('../components/Admin/Grades/Index.vue')
            },
            {
                path: 'attendance',
                name: 'AdminAttendance',
                component: () => import('../components/Admin/Attendance/Index.vue')
            },
            {
                path: 'announcements',
                name: 'AdminAnnouncements',
                component: () => import('../components/Admin/Announcements/Index.vue')
            }
        ]
    },
    {
        path: '/guru',
        component: GuruDashboard,
        meta: { requiresAuth: true, role: 'guru' },
        children: [
            {
                path: '',
                name: 'GuruHome',
                component: () => import('../components/Guru/Home.vue')
            },
            {
                path: 'classes',
                name: 'GuruClasses',
                component: () => import('../components/Guru/Classes/Index.vue')
            },
            {
                path: 'materials',
                name: 'GuruMaterials',
                component: () => import('../components/Guru/Materials/Index.vue')
            },
            {
                path: 'assignments',
                name: 'GuruAssignments',
                component: () => import('../components/Guru/Assignments/Index.vue')
            },
            {
                path: 'assignments/:id/submissions',
                name: 'GuruAssignmentSubmissions',
                component: () => import('../components/Guru/Assignments/Submissions.vue')
            },
            {
                path: 'attendance',
                name: 'GuruAttendance',
                component: () => import('../components/Guru/Attendance/Index.vue')
            },
            {
                path: 'grades',
                name: 'GuruGrades',
                component: () => import('../components/Guru/Grades/Index.vue')
            },
            {
                path: 'cbt',
                name: 'GuruCBT',
                component: () => import('../components/Admin/CBT/Index.vue')
            },
            {
                path: 'schedules',
                name: 'GuruSchedules',
                component: () => import('../components/Guru/Schedules/Index.vue')
            },
            {
                path: 'announcements',
                name: 'GuruAnnouncements',
                component: () => import('../components/Guru/Announcements/Index.vue')
            },
            {
                path: 'profile',
                name: 'GuruProfile',
                component: () => import('../components/Admin/Profile/Index.vue')
            }
        ]
    },
    {
        path: '/siswa',
        component: SiswaDashboard,
        meta: { requiresAuth: true, role: 'siswa' },
        children: [
            {
                path: '',
                name: 'SiswaHome',
                component: () => import('../components/Siswa/Home.vue')
            },
            {
                path: 'materials',
                name: 'SiswaMaterials',
                component: () => import('../components/Siswa/Materials/Index.vue')
            },
            {
                path: 'assignments',
                name: 'SiswaAssignments',
                component: () => import('../components/Siswa/Assignments/Index.vue')
            },
            {
                path: 'assignments/:id',
                name: 'SiswaAssignmentDetail',
                component: () => import('../components/Siswa/Assignments/Detail.vue')
            },
            {
                path: 'attendance',
                name: 'SiswaAttendance',
                component: () => import('../components/Siswa/Attendance/Index.vue')
            },
            {
                path: 'grades',
                name: 'SiswaGrades',
                component: () => import('../components/Siswa/Grades/Index.vue')
            },
            {
                path: 'schedules',
                name: 'SiswaSchedules',
                component: () => import('../components/Siswa/Schedules/Index.vue')
            },
            {
                path: 'cbt',
                name: 'SiswaCBT',
                component: () => import('../components/Siswa/CBT/Index.vue')
            },
            {
                path: 'cbt/quiz/:attemptId',
                name: 'SiswaQuizRoom',
                component: () => import('../components/Siswa/CBT/QuizRoom.vue')
            },
            {
                path: 'announcements',
                name: 'SiswaAnnouncements',
                component: () => import('../components/Siswa/Announcements/Index.vue')
            },
            {
                path: 'profile',
                name: 'SiswaProfile',
                component: () => import('../components/Admin/Profile/Index.vue')
            }
        ]
    },
];

const getBaseUrl = () => {
    if (window.Laravel && window.Laravel.baseUrl) {
        try {
            const path = new URL(window.Laravel.baseUrl).pathname;
            // If the path is just /public or ends with /public, treat as root for dev server
            if (path === '/public' || path === '/public/') return '/';
            return path.endsWith('/') ? path : path + '/';
        } catch (e) {
            return '/';
        }
    }
    return '/';
};

const router = createRouter({
    history: createWebHistory(getBaseUrl()),
    routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    const userRole = localStorage.getItem('role');

    if (to.meta.requiresAuth && !token) {
        next('/login');
    } else if (to.name === 'Login' && token) {
        // Only redirect from login page if already logged in
        if (userRole === 'admin') next('/admin');
        else if (userRole === 'guru') next('/guru');
        else if (userRole === 'siswa') next('/siswa');
        else next();
    } else if (to.meta.role && to.meta.role !== userRole) {
        // Role mismatch
        next('/login');
    } else {
        next();
    }
});

export default router;
