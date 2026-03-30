<template>
    <div class="page fade-in">
        <div class="page-header"><h2>Relatórios</h2></div>

        <div class="tabs">
            <button v-for="t in tabs" :key="t.key"
                    class="tab-btn" :class="{ active: tabAtiva === t.key }"
                    @click="mudarTab(t.key)">
                {{ t.label }}
            </button>
        </div>

        <div class="card mt">
            <div v-if="carregando" class="vazio">Carregando...</div>
            <div v-else>
                <div class="table-wrapper">
                    <table v-if="dados.length">
                        <thead>
                            <tr>
                                <th v-for="col in colunas" :key="col">{{ col }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in dados" :key="i">
                                <td v-for="col in colunasKey" :key="col">{{ formatarValor(row[col]) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="vazio">Nenhum dado encontrado</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Relatorios',
    data() {
        return {
            tabAtiva: 'veiculos_todos',
            carregando: false,
            dados: [],
            tabs: [
                { key: 'veiculos_todos',    label: 'Todos os Veículos' },
                { key: 'veiculos_pessoa',   label: 'Veículos por Pessoa' },
                { key: 'veiculos_genero',   label: 'Veículos por Gênero' },
                { key: 'marcas',            label: 'Marcas' },
                { key: 'marcas_genero',     label: 'Marcas por Gênero' },
                { key: 'pessoas_todas',     label: 'Todas as Pessoas' },
                { key: 'pessoas_genero',    label: 'Pessoas por Gênero' },
                { key: 'revisoes_marcas',   label: 'Revisões por Marca' },
                { key: 'revisoes_pessoas',  label: 'Revisões por Pessoa' }
            ],
        }
    },
    computed: {
        colunas() {
            if (!this.dados.length) return []
            return Object.keys(this.dados[0]).map(k =>
                k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
            )
        },
        colunasKey() {
            if (!this.dados.length) return []
            return Object.keys(this.dados[0])
        },
    },
    async created() { await this.carregar() },
    methods: {
        async mudarTab(key) {
            this.tabAtiva = key
            await this.carregar()
        },
        
        formatarValor(valor) {
            if (valor === null || valor === undefined) return '—';

            if (Array.isArray(valor)) {
                if (valor.length === 0) return 'Nenhum';
                
                if (valor[0].modelo || valor[0].placa) {
                    return valor.map(v => `${v.marca || ''} ${v.modelo || ''}`.trim()).join(', ');
                }
                return valor.join(', ');
            }

            if (typeof valor === 'object') {
                if (valor.nome) {
                    return valor.nome;
                }
                
                if (valor.modelo || valor.placa) {
                    return `${valor.marca || ''} ${valor.modelo || ''} - ${valor.placa || ''}`.trim();
                }
                
                return JSON.stringify(valor);
            }

            if (typeof valor === 'string' && valor.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/)) {
                return new Date(valor).toLocaleDateString('pt-BR');
            }

            return valor;
        },

        async carregar() {
            this.carregando = true
            const rotas = {
                veiculos_todos:   '/relatorios/veiculos/todos',
                veiculos_pessoa:  '/relatorios/veiculos/por-pessoa',
                veiculos_genero:  '/relatorios/veiculos/genero',
                marcas:           '/relatorios/veiculos/marcas',
                marcas_genero:    '/relatorios/veiculos/marcas-genero',
                pessoas_todas:    '/relatorios/pessoas/todas',
                pessoas_genero:   '/relatorios/pessoas/genero',
                revisoes_marcas:  '/relatorios/revisoes/marcas',
                revisoes_pessoas: '/relatorios/revisoes/pessoas',
                media_tempo:      '/relatorios/revisoes/media-tempo',
                proximas:         '/relatorios/revisoes/proximas',
            }
            try {
                const { data } = await this.$http.get(rotas[this.tabAtiva])
                this.dados = Array.isArray(data) ? data : [data]
            } catch {
                this.dados = []
            } finally {
                this.carregando = false
            }
        },
    },
}
</script>

<style scoped>
.page {
    padding: 2rem;
    min-height: calc(100vh - 60px);
    background: var(--bg);
}
.page-header { margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.3rem; font-weight: 700; }
.tabs { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
.tab-btn { padding: .5rem 1rem; border-radius: 7px; border: 1.5px solid var(--border); background: var(--white); cursor: pointer; font-size: .85rem; transition: all .2s; }
.tab-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.mt { margin-top: .5rem; }
.vazio { text-align: center; color: #94a3b8; padding: 2rem; }
</style>