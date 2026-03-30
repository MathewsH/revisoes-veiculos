import Vue      from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: { auth: true },
    },
    {
        path: '/usuarios',
        name: 'usuarios',
        component: () => import('../pages/Usuarios.vue'),
        meta: { auth: true, admin: true },
    },
    {
        path: '/veiculos',
        name: 'veiculos',
        component: () => import('../pages/Veiculos.vue'),
        meta: { auth: true },
    },
    {
        path: '/revisoes',
        name: 'revisoes',
        component: () => import('../pages/Revisoes.vue'),
        meta: { auth: true },
    },
    {
        path: '/relatorios',
        name: 'relatorios',
        component: () => import('../pages/Relatorios.vue'),
        meta: { auth: true, admin: true },
    },
]

const router = new VueRouter({
    mode: 'history',
    routes,
})

// Guard de navegação
router.beforeEach((to, from, next) => {
    const token   = localStorage.getItem('token')
    const usuario = JSON.parse(localStorage.getItem('usuario') || '{}')

    if (to.meta.auth && !token) return next('/login')
    if (to.meta.guest && token) return next('/')
    if (to.meta.admin && usuario.tipo !== 'admin') return next('/')

    next()
})

export default router