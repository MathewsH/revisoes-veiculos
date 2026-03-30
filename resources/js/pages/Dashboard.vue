<template>
    <div class="dashboard fade-in">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats.usuarios }}</span>
                    <span class="stat-label">Usuários</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">🚗</div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats.veiculos }}</span>
                    <span class="stat-label">Veículos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">🔩</div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats.revisoes }}</span>
                    <span class="stat-label">Revisões</span>
                </div>
            </div>
        </div>

        <div class="graficos-grid">
            <div class="card grafico-card">
                <h3 class="card-title">Veículos por Gênero</h3>
                <canvas ref="graficoPorGenero"></canvas>
            </div>
            <div class="card grafico-card">
                <h3 class="card-title">Idade Média por Gênero</h3>
                <canvas ref="graficoIdade"></canvas>
            </div>
            <div class="card grafico-card">
                <h3 class="card-title">Veículos por Combustível</h3>
                <canvas ref="graficoCombustivel"></canvas>
            </div>
        </div>

        <div class="card grafico-card grafico-largo">
            <h3 class="card-title">Veículos por Marca</h3>
            <div class="canvas-container">
                <canvas ref="graficoPorMarca"></canvas>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3 class="card-title">Revisões</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Proprietário</th>
                                <th>Data</th>
                                <th>Oficina</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in ultimasRevisoes" :key="r.id">
                                <td>{{ r.veiculo?.marca }} {{ r.veiculo?.modelo }}</td>
                                <td>{{ r.usuario?.nome }}</td>
                                <td>{{ formatarData(r.data_revisao) }}</td>
                                <td>{{ r.oficina || '—' }}</td>
                            </tr>
                            <tr v-if="!ultimasRevisoes.length">
                                <td colspan="4" class="vazio">Nenhuma revisão cadastrada</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 class="card-title">Veículos</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Placa</th>
                                <th>Proprietário</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="v in ultimosVeiculos" :key="v.id">
                                <td>{{ v.marca }} {{ v.modelo }}</td>
                                <td><span class="placa">{{ v.placa }}</span></td>
                                <td>{{ v.usuario?.nome }}</td>
                            </tr>
                            <tr v-if="!ultimosVeiculos.length">
                                <td colspan="3" class="vazio">Nenhum veículo cadastrado</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    Chart, BarController, DoughnutController, PieController,
    CategoryScale, LinearScale, BarElement, ArcElement,
    Tooltip, Legend
} from 'chart.js'

Chart.register(
    BarController, DoughnutController, PieController,
    CategoryScale, LinearScale, BarElement, ArcElement,
    Tooltip, Legend
)

export default {
    name: 'Dashboard',
    data() {
        return {
            stats: { usuarios: 0, veiculos: 0, revisoes: 0 },
            ultimasRevisoes: [],
            ultimosVeiculos: [],
            usuarios: [],
            veiculos: [],
            charts: {},
        }
    },
    async created() { await this.carregar() },
    methods: {
        async carregar() {
            try {
                const [rev, vei] = await Promise.all([
                    this.$http.get('/revisoes'),
                    this.$http.get('/veiculos'),
                ])
                
                const arrayRevisoes = rev.data.data || rev.data;
                const arrayVeiculos = vei.data.data || vei.data;
                
                this.ultimasRevisoes = arrayRevisoes.slice(0, 4);
                this.ultimosVeiculos = arrayVeiculos.slice(0, 4);
                
                this.stats.revisoes  = rev.data.total || arrayRevisoes.length;
                this.stats.veiculos  = vei.data.total || arrayVeiculos.length;
                this.veiculos        = arrayVeiculos;

                if (this.$store.getters.isAdmin) {
                    const usu = await this.$http.get('/usuarios')
                    const arrayUsuarios = usu.data.data || usu.data;
                    
                    this.usuarios       = arrayUsuarios;
                    this.stats.usuarios = usu.data.total || arrayUsuarios.length;
                }

                this.$nextTick(() => this.renderGraficos())
            } catch(e) {
                console.error(e)
            }
        },

        destruirGraficos() {
            Object.values(this.charts).forEach(c => c.destroy())
            this.charts = {}
        },

        renderGraficos() {
            this.destruirGraficos()

            // Veículos por Marca (Gráfico Extenso)
            const porMarca = {}
            this.veiculos.forEach(v => {

                const nome = v.marca || 'Sem marca'
                porMarca[nome] = (porMarca[nome] || 0) + 1
            })
            
            if (Object.keys(porMarca).length && this.$refs.graficoPorMarca) {
                const valores = Object.values(porMarca);
                const valorMaximo = Math.max(...valores, 1);
                
                this.charts.marca = new Chart(this.$refs.graficoPorMarca, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(porMarca),
                        datasets: [{
                            label: 'Veículos',
                            data: valores,
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { 
                                beginAtZero: true,
                                max: valorMaximo + 1, 
                                ticks: { 
                                    stepSize: 1,
                                    callback: function(value) {
                                        
                                        return value <= valorMaximo ? value : '';
                                    }
                                } 
                            } 
                        }
                    }
                })
            }

            // Veículos por Gênero
            const homens   = this.veiculos.filter(v => v.usuario?.sexo === 'M').length
            const mulheres = this.veiculos.filter(v => v.usuario?.sexo === 'F').length
            if (this.$refs.graficoPorGenero) {
                this.charts.genero = new Chart(this.$refs.graficoPorGenero, {
                    type: 'doughnut',
                    data: {
                        labels: ['Homens', 'Mulheres'],
                        datasets: [{
                            data: [homens, mulheres],
                            backgroundColor: ['#3b82f6', '#ec4899'],
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                })
            }

            // Idade média por gênero
            const calcIdade = nasc => {
                if (!nasc) return 0
                return Math.floor((new Date() - new Date(nasc)) / (365.25 * 24 * 3600 * 1000))
            }
            const usuH  = this.usuarios.filter(u => u.sexo === 'M')
            const usuF  = this.usuarios.filter(u => u.sexo === 'F')
            const medH  = usuH.length ? Math.round(usuH.reduce((s, u) => s + calcIdade(u.data_nascimento), 0) / usuH.length) : 0
            const medF  = usuF.length ? Math.round(usuF.reduce((s, u) => s + calcIdade(u.data_nascimento), 0) / usuF.length) : 0
            if (this.$refs.graficoIdade) {
                this.charts.idade = new Chart(this.$refs.graficoIdade, {
                    type: 'bar',
                    data: {
                        labels: ['Homens', 'Mulheres'],
                        datasets: [{
                            label: 'Idade Média (anos)',
                            data: [medH, medF],
                            backgroundColor: ['#3b82f6', '#ec4899'],
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                })
            }

            // Veículos por Combustível
            const porComb = {}
            this.veiculos.forEach(v => {
                const c = v.combustivel || 'outro'
                porComb[c] = (porComb[c] || 0) + 1
            })
            if (this.$refs.graficoCombustivel) {
                this.charts.combustivel = new Chart(this.$refs.graficoCombustivel, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(porComb),
                        datasets: [{
                            data: Object.values(porComb),
                            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#6366f1','#ec4899','#14b8a6'],
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                })
            }
        },

        formatarData(d) {
            if (!d) return '—';
            const dataLimpa = String(d).substring(0, 10);
            return new Date(dataLimpa + 'T00:00:00').toLocaleDateString('pt-BR');
        },
    },
}
</script>

<style scoped>
.dashboard { padding: 2rem; }


.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 1.5rem;
    display: flex; align-items: center; gap: 1rem;
    box-shadow: var(--shadow);
    transition: transform .2s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.blue   { background: #dbeafe; }
.green  { background: #dcfce7; }
.orange { background: #ffedd5; }
.stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: var(--text); }
.stat-label { font-size: .85rem; color: var(--text-light); }


.graficos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}


.grafico-largo {
    margin-bottom: 2rem;
}
.canvas-container {
    position: relative;
    height: 320px; 
    width: 100%;
}

.grafico-card { padding: 1.5rem; }
.card-title { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--text); }

/* Tabelas */
.dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.vazio { text-align: center; color: #94a3b8; padding: 2rem; }
.placa { background: #f1f5f9; padding: .2rem .5rem; border-radius: 4px; font-family: monospace; font-weight: 600; font-size: 0.85rem; }

@media (max-width: 1024px) { 
    .dashboard-grid { grid-template-columns: 1fr; } 
}
</style>