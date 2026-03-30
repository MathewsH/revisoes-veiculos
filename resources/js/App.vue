<template>
    <div id="app">
        <template v-if="logado">
            <layout-sidebar />
            <div class="main-content">
                <layout-navbar />
                <router-view />
            </div>
        </template>
        <template v-else>
            <router-view />
        </template>
    </div>
</template>

<script>
import LayoutSidebar from './components/layout/Sidebar.vue'
import LayoutNavbar from './components/layout/NavBar.vue'

export default {
    name: 'App',
    components: { LayoutSidebar, LayoutNavbar },
    computed: {
        logado() {
            return this.$store.getters.logado
        },
    },
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --primary:       #6366f1;
    --primary-dark:  #4f46e5;
    --primary-light: #e0e7ff;
    --success:       #10b981;
    --danger:        #ef4444;
    --warning:       #f59e0b;
    --bg:            #f8faff;
    --sidebar-bg:    #1e1b4b;
    --sidebar-hover: #2d2a5e;
    --sidebar-text:  #a5b4fc;
    --white:         #ffffff;
    --border:        #e8eaf6;
    --text:          #1e1b4b;
    --text-light:    #6b7280;
    --radius:        14px;
    --radius-sm:     8px;
    --shadow:        0 1px 3px rgba(99,102,241,.08);
    --shadow-md:     0 4px 20px rgba(99,102,241,.12);
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
}

html, body { width: 100%; min-height: 100vh; }

#app {
    display: flex;
    min-height: 100vh;
    width: 100%;
}

.main-content {
    flex: 1;
    margin-left: 270px;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    width: calc(100% - 270px);
    background: var(--bg);
}

.full-content {
    flex: 1;
    width: 100%;
    min-height: 100vh;
}

.card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    border: 1px solid var(--border);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem 1.25rem;
    border-radius: var(--radius-sm);
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .2s ease;
}
.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.35);
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.45); }
.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
}
.btn-danger:hover { opacity: .9; }
.btn-secondary {
    background: var(--primary-light);
    color: var(--primary-dark);
    border: 1px solid #c7d2fe;
}
.btn-secondary:hover { background: #c7d2fe; }
.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}

.table-wrapper { overflow-x: auto; border-radius: var(--radius-sm); }
table { width: 100%; border-collapse: collapse; font-size: .875rem; }
thead th {
    background: linear-gradient(135deg, #f0f0ff, #e8eaf6);
    padding: .85rem 1.1rem;
    text-align: left;
    font-weight: 700;
    font-size: .78rem;
    color: var(--primary-dark);
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 2px solid #c7d2fe;
}
tbody td {
    padding: .9rem 1.1rem;
    border-bottom: 1px solid #f0f0ff;
    color: var(--text);
    vertical-align: middle;
}
tbody tr:hover { background: #fafafe; }
tbody tr:last-child td { border-bottom: none; }

.form-group { margin-bottom: 1.1rem; }
.form-group label {
    display: block;
    font-size: .78rem;
    font-weight: 700;
    margin-bottom: .4rem;
    color: var(--primary-dark);
    text-transform: uppercase;
    letter-spacing: .05em;
}
.form-control {
    width: 100%;
    padding: .6rem .9rem;
    border: 2px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: .9rem;
    font-family: inherit;
    transition: all .2s;
    background: var(--white);
    color: var(--text);
}
.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,.1);
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: .25rem .75rem;
    border-radius: 99px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.badge-admin   { background: linear-gradient(135deg,#e0e7ff,#c7d2fe); color: #3730a3; }
.badge-comum   { background: #f1f5f9; color: #475569; }
.badge-m       { background: linear-gradient(135deg,#dbeafe,#bfdbfe); color: #1e40af; }
.badge-f       { background: linear-gradient(135deg,#fce7f3,#fbcfe8); color: #9d174d; }
.badge-success { background: linear-gradient(135deg,#d1fae5,#a7f3d0); color: #065f46; }
.badge-warning { background: linear-gradient(135deg,#fef3c7,#fde68a); color: #92400e; }
.badge-danger  { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #991b1b; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-in { animation: fadeInUp .4s ease forwards; }

body.modal-aberto { overflow: hidden; }

@media (max-width: 768px) {
    .main-content { margin-left: 0; width: 100%; }
}
</style>