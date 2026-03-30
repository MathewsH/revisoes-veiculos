<template>
    <div class="login-bg">
        <div class="login-card fade-in">
            <div class="login-header">
                <div class="login-logo">🔧</div>
                <h2>RevisaoCar</h2>
                <p>Sistema de Controle de Revisões</p>
            </div>

            <form @submit.prevent="entrar">
                <div class="form-group">
                    <label>E-mail</label>
                    <input v-model="form.email" type="email"
                           class="form-control" placeholder="seu@email.com" required />
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input v-model="form.senha" type="password"
                           class="form-control" placeholder="••••••" required />
                </div>

                <div v-if="erro" class="alert-erro">{{ erro }}</div>

                <button type="submit" class="btn btn-primary btn-full" :disabled="carregando">
                    {{ carregando ? 'Entrando...' : 'Entrar' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Login',
    data() {
        return {
            form: { email: '', senha: '' },
            erro: '',
            carregando: false,
        }
    },
    methods: {
        async entrar() {
            this.erro = ''
            this.carregando = true
            try {
                const { data } = await this.$http.post('/login', this.form)
                this.$store.commit('SET_AUTH', {
                    token: data.token,
                    usuario: data.usuario,
                })
                this.$router.push('/')
            } catch (e) {
                this.erro = e.response?.data?.message || 'Credenciais inválidas.'
            } finally {
                this.carregando = false
            }
        },
    },
}
</script>

<style scoped>
.login-bg {
    min-height: 100vh;
    width: 100%;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-card {
    background: var(--white);
    border-radius: 16px;
    padding: 2.5rem;
    width: 100%; max-width: 400px;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
}
.page {
    padding: 2rem;
    min-height: calc(100vh - 60px); /* 60px = altura do navbar */
    background: var(--bg);
}
.login-header { text-align: center; margin-bottom: 2rem; }
.login-logo { font-size: 3rem; margin-bottom: .5rem; }
.login-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text); }
.login-header p  { color: var(--text-light); font-size: .9rem; }
.btn-full { width: 100%; justify-content: center; padding: .75rem; font-size: 1rem; }
.alert-erro {
    background: #fee2e2; color: #991b1b;
    padding: .75rem; border-radius: 7px;
    margin-bottom: 1rem; font-size: .875rem;
}
</style>