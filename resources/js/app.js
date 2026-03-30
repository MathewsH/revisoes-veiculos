import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'

// Configuração global do Axios
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

// Adiciona o token automaticamente em toda requisição
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Redireciona para login se token expirar
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            router.push('/login')
        }
        return Promise.reject(error)
    }
)

Vue.prototype.$http = axios

new Vue({
    router,
    store,
    render: h => h(App),
}).$mount('#app')