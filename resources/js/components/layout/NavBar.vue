<template>
    <header class="navbar">
        <div class="navbar-left">
            <span class="breadcrumb">RevisaoCar</span>
            <span class="breadcrumb-sep">›</span>
            <h1 class="page-title">{{ titulo }}</h1>
        </div>
        <div class="navbar-right">
            <span class="badge" :class="isAdmin ? 'badge-admin' : 'badge-comum'">
                {{ isAdmin ? '⭐ Administrador' : '👤 Usuário' }}
            </span>
        </div>
    </header>
</template>

<script>
const titulos = {
    '/'           : 'Dashboard',
    '/usuarios'   : 'Usuários',
    '/veiculos'   : 'Veículos',
    '/revisoes'   : 'Revisões',
    '/relatorios' : 'Relatórios',
}
export default {
    name: 'Navbar',
    computed: {
        titulo()  { return titulos[this.$route.path] || 'RevisaoCar' },
        isAdmin() { return this.$store.getters.isAdmin },
    },
}
</script>

<style scoped>
.navbar {
    background: var(--white);
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid var(--border);
    box-shadow: 0 2px 12px rgba(99,102,241,.06);
    position: sticky;
    top: 0;
    z-index: 50;
}
.navbar-left { display: flex; align-items: center; gap: .5rem; }
.breadcrumb { font-size: .8rem; color: var(--text-light); font-weight: 500; }
.breadcrumb-sep { color: var(--border); font-size: .9rem; }
.page-title { font-size: 1.1rem; font-weight: 800; color: var(--text); }
</style>