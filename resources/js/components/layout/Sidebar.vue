<template>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">🔧</div>
            <div>
                <div class="logo-text">RevisaoCar</div>
                <div class="logo-version">v1.0.0</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Principal</div>
            <router-link to="/" class="nav-item" exact>
                <div class="nav-icon">📊</div> Dashboard
            </router-link>

            <div class="nav-section">Gestão</div>
            <router-link to="/veiculos" class="nav-item">
                <div class="nav-icon">🚗</div> Veículos
            </router-link>
            <router-link to="/revisoes" class="nav-item">
                <div class="nav-icon">🔩</div> Revisões
            </router-link>

            <template v-if="isAdmin">
                <div class="nav-section">Administração</div>
                <router-link to="/usuarios" class="nav-item">
                    <div class="nav-icon">👥</div> Usuários
                </router-link>
                <router-link to="/relatorios" class="nav-item">
                    <div class="nav-icon">📈</div> Relatórios
                </router-link>
            </template>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ iniciais }}</div>
                <div>
                    <div class="user-name">{{ usuario.nome }}</div>
                    <div class="user-role">{{ usuario.tipo }}</div>
                </div>
            </div>
            <button class="btn-logout" @click="logout">🚪 Sair do sistema</button>
        </div>
    </aside>
</template>

<script>
export default {
    name: 'Sidebar',
    computed: {
        usuario() { return this.$store.getters.usuario || {} },
        isAdmin()  { return this.$store.getters.isAdmin },
        iniciais() {
            return (this.usuario.nome || 'U')
                .split(' ').slice(0, 2)
                .map(n => n[0]).join('').toUpperCase()
        },
    },
    methods: {
        async logout() {
            try { await this.$http.post('/logout') } catch {}
            this.$store.commit('CLEAR_AUTH')
            this.$router.push('/login')
        },
    },
}
</script>

<style scoped>
.sidebar {
    width: 270px;
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    left: 0; top: 0;
    display: flex;
    flex-direction: column;
    z-index: 100;
    box-shadow: 4px 0 24px rgba(30,27,75,.25);
}
.sidebar-logo {
    padding: 1.75rem 1.5rem 1.25rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    border-bottom: 1px solid rgba(165,180,252,.1);
}
.logo-icon {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, var(--primary), #818cf8);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    box-shadow: 0 4px 12px rgba(99,102,241,.4);
}
.logo-text {
    color: #fff;
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: -.01em;
}
.logo-version {
    font-size: .65rem;
    color: var(--sidebar-text);
    margin-top: .1rem;
}
.sidebar-nav { flex: 1; padding: 1rem .75rem; overflow-y: auto; }
.nav-section {
    font-size: .65rem;
    font-weight: 700;
    color: rgba(165,180,252,.5);
    text-transform: uppercase;
    letter-spacing: .1em;
    padding: .75rem .75rem .4rem;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .75rem 1rem;
    color: var(--sidebar-text);
    text-decoration: none;
    font-size: .875rem;
    font-weight: 500;
    border-radius: 10px;
    margin-bottom: .2rem;
    transition: all .2s;
}
.nav-item:hover {
    background: var(--sidebar-hover);
    color: #fff;
}
.nav-item.router-link-active {
    background: linear-gradient(135deg, rgba(99,102,241,.3), rgba(99,102,241,.15));
    color: #fff;
    font-weight: 700;
    box-shadow: inset 3px 0 0 var(--primary);
}
.nav-icon {
    width: 32px; height: 32px;
    background: rgba(165,180,252,.1);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    transition: all .2s;
}
.nav-item.router-link-active .nav-icon {
    background: rgba(99,102,241,.3);
}
.sidebar-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid rgba(165,180,252,.1);
}
.user-info {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: .85rem;
    padding: .75rem;
    background: rgba(165,180,252,.08);
    border-radius: 10px;
}
.user-avatar {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, var(--primary), #818cf8);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 800; font-size: .8rem;
    box-shadow: 0 2px 8px rgba(99,102,241,.3);
    flex-shrink: 0;
}
.user-name { color: #fff; font-size: .85rem; font-weight: 600; }
.user-role {
    color: var(--sidebar-text);
    font-size: .7rem;
    text-transform: capitalize;
    font-weight: 500;
}
.btn-logout {
    width: 100%;
    padding: .6rem;
    background: rgba(239,68,68,.1);
    border: 1px solid rgba(239,68,68,.2);
    color: #fca5a5;
    border-radius: 10px;
    cursor: pointer;
    font-size: .85rem;
    font-weight: 600;
    font-family: inherit;
    transition: all .2s;
}
.btn-logout:hover {
    background: rgba(239,68,68,.25);
    color: #fff;
    border-color: rgba(239,68,68,.4);
}
</style>