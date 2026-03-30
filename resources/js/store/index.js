import Vue  from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        token:   localStorage.getItem('token')   || null,
        usuario: JSON.parse(localStorage.getItem('usuario') || 'null'),
    },
    mutations: {
        SET_AUTH(state, { token, usuario }) {
            state.token   = token
            state.usuario = usuario
            localStorage.setItem('token',   token)
            localStorage.setItem('usuario', JSON.stringify(usuario))
        },
        CLEAR_AUTH(state) {
            state.token   = null
            state.usuario = null
            localStorage.removeItem('token')
            localStorage.removeItem('usuario')
        },
    },
    getters: {
        logado:  state => !!state.token,
        isAdmin: state => state.usuario?.tipo === 'admin',
        usuario: state => state.usuario,
    },
})