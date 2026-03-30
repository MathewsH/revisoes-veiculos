<template>
    <div class="page fade-in">
        <div class="page-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <h2>Veículos</h2>
                
                <select v-if="isAdmin" v-model="filtroUsuarioId" class="form-control" style="width: 250px;">
                    <option value="">Todos os Proprietários</option>
                    <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nome }}</option>
                </select>
            </div>

            <button v-if="isAdmin" class="btn btn-primary" @click="abrirModal()">+ Novo Veículo</button>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Marca/Modelo</th>
                            <th>Placa</th>
                            <th>Ano</th>
                            <th>Cor</th>
                            <th>Combustível</th>
                            <th>Quilometragem</th>
                            <th @click="alternarOrdenacao('proprietario')" style="cursor: pointer; user-select: none;" title="Clique para ordenar">
                                Proprietário
                                <span v-if="ordenacaoCampo === 'proprietario'" style="font-size: 0.8em; margin-left: 4px;">
                                    {{ ordenacaoDirecao === 'asc' ? '▲' : '▼' }}
                                </span>
                                <span v-else style="color: #cbd5e1; font-size: 0.8em; margin-left: 4px;">↕</span>
                            </th>
                            <th v-if="isAdmin">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in veiculosOrdenados" :key="v.id">
                            <td><strong>{{ v.marca }}</strong> {{ v.modelo }}</td>
                            <td><span class="placa">{{ v.placa }}</span></td>
                            <td>{{ v.ano_fabricacao }}/{{ v.ano_modelo }}</td>
                            <td>{{ v.cor?.nome || '—' }}</td>
                            <td>{{ v.combustivel }}</td>
                            <td>{{ Number(v.quilometragem).toLocaleString() }} km</td>
                            <td>{{ v.usuario?.nome }}</td>
                            <td v-if="isAdmin" class="acoes">
                                <button class="btn btn-success" @click="agendarRevisao(v)" title="Agendar Revisão" style="background: #10b981; border: none;">🔧</button>
                                <button class="btn btn-secondary" @click="abrirModal(v)">✏️</button>
                                <button class="btn btn-danger" @click="excluir(v.id)">🗑️</button>
                            </td>
                        </tr>
                        <tr v-if="!veiculos.length">
                            <td colspan="8" class="vazio">Nenhum veículo encontrado</td>
                        </tr>
                    </tbody>
                </table>
                <div class="paginacao" v-if="paginacao.last_page > 1">
                    <button class="btn btn-secondary" :disabled="paginacao.current_page === 1" @click="carregar(paginacao.current_page - 1)">⬅️ Anterior</button>
                    <span>Página {{ paginacao.current_page }} de {{ paginacao.last_page }}</span>
                    <button class="btn btn-secondary" :disabled="paginacao.current_page === paginacao.last_page" @click="carregar(paginacao.current_page + 1)">Próxima ➡️</button>
                </div>
            </div>
        </div>

        <div v-if="modal" class="modal-bg" @click.self="modal = false">
            <div class="modal-box fade-in">
                <h3>{{ form.id ? 'Editar' : 'Novo' }} Veículo</h3>
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>Proprietário *</label>
                        <select v-model="form.usuario_id" class="form-control" :class="{ 'input-erro': erros.usuario_id }">
                            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nome }}</option>
                        </select>
                        <span v-if="erros.usuario_id" class="msg-erro">{{ erros.usuario_id }}</span>
                    </div>

                    <div class="form-group" style="grid-column: span 2">
                        <label>Marca (FIPE) {{ carregandoMarcas ? ' carregando...' : '' }}</label>
                        <select v-model="form.marcaFipeId" class="form-control"
                                @change="aoSelecionarMarcaFipe">
                            <option value="">-- Selecione pela FIPE --</option>
                            <option v-for="m in marcasFipe" :key="m.codigo" :value="m.codigo">
                                {{ m.nome }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group" v-if="form.marcaFipeId === 'outro'">
                        <label>Nova Marca *</label>
                        <input v-model="form.marcaOutro" class="form-control"
                            placeholder="Digite a marca..." />
                    </div>

                    <div class="form-group" v-if="modelosFipe.length" style="grid-column: span 2">
                        <label>Modelo {{ carregandoModelos ? ' carregando...' : '' }}</label>
                        <select v-model="form.modeloFipe" class="form-control"
                                @change="aoSelecionarModeloFipe">
                            <option value="">-- Selecione o modelo --</option>
                            <option v-for="m in modelosFipe" :key="m.codigo" :value="m.codigo">
                                {{ m.nome }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Placa *</label>
                        <input 
                            v-model="form.placa" 
                            @input="formatarPlaca"
                            @blur="validarPlaca"
                            class="form-control" 
                            :class="{ 'input-erro': erros.placa }"
                            placeholder="ABC-1234" 
                            maxlength="8"
                        />
                        <span v-if="erros.placa" class="msg-erro">{{ erros.placa }}</span>
                    </div>

                    <div class="form-group">
                        <label>Ano Fabricação *</label>
                        <input 
                            v-model="form.ano_fabricacao" 
                            @blur="validarAnos"
                            type="number" 
                            class="form-control" 
                            :class="{ 'input-erro': erros.ano_fabricacao }"
                        />
                        <span v-if="erros.ano_fabricacao" class="msg-erro">{{ erros.ano_fabricacao }}</span>
                    </div>

                    <div class="form-group">
                        <label>Ano Modelo *</label>
                        <input 
                            v-model="form.ano_modelo" 
                            @blur="validarAnos"
                            type="number" 
                            class="form-control" 
                            :class="{ 'input-erro': erros.ano_modelo }"
                        />
                        <span v-if="erros.ano_modelo" class="msg-erro">{{ erros.ano_modelo }}</span>
                    </div>

                    <div class="form-group">
                        <label>Cor</label>
                        <select v-model="form.cor_id" class="form-control select-auto">
                            <option value="">Sem cor</option>
                            <option v-for="c in cores" :key="c.id" :value="c.id">{{ c.nome }}</option>
                            <option value="outro">➕ Outra cor...</option>
                        </select>
                    </div>

                    <div class="form-group" v-if="corSelecionada">
                        <label>Nova Cor *</label>
                        <input v-model="form.corOutro" class="form-control"
                            placeholder="Digite a cor..." />
                    </div>

                    <div class="form-group">
                        <label>Combustível</label>
                        <select v-model="form.combustivel" class="form-control">
                            <option>gasolina</option>
                            <option>etanol</option>
                            <option>flex</option>
                            <option>diesel</option>
                            <option>elétrico</option>
                            <option>híbrido</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quilometragem</label>
                        <input 
                            v-model="form.quilometragem" 
                            @input="formatarQuilometragem"
                            type="text" 
                            inputmode="numeric"
                            class="form-control" 
                        />
                    </div>
                </div>
                
                <div v-if="erro" class="alert-erro">{{ erro }}</div>
                
                <div class="modal-footer">
                    <button v-if="!form.id" class="btn btn-danger" @click="limparRascunho" style="margin-right: auto; background-color: #ef4444; border: none;">🗑️ Limpar Rascunho</button>
                    <button class="btn btn-secondary" @click="modal = false">Cancelar</button>
                    <button class="btn btn-primary" @click="salvar">
                        {{ salvando ? 'Salvando...' : 'Salvar' }}
                    </button>
                </div>  
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

const fipeApi = axios.create({ baseURL: 'https://parallelum.com.br/fipe/api/v1/carros' })

const formVazio = () => ({
    id: null, usuario_id: '', marca: '', modelo: '',
    marcaFipeId: '', modeloFipe: '', marcaOutro: '',
    placa: '', ano_fabricacao: '', ano_modelo: '',
    cor_id: '', corOutro: '', combustivel: 'flex', quilometragem: 0,
})

export default {
    name: 'Veiculos',
    data() {
        return {
            veiculos: [], usuarios: [], marcas: [], cores: [],
            marcasFipe: [], modelosFipe: [],
            carregandoMarcas: false, carregandoModelos: false,
            modal: false, 
            form: formVazio(), 
            erro: '', 
            erros: {}, 
            salvando: false,
            charts: {},
            paginacao: { current_page: 1, last_page: 1 },
            
            ordenacaoCampo: 'id', 
            ordenacaoDirecao: 'desc',
            filtroUsuarioId: '',
        }
    },
    computed: {
        isAdmin()         { return this.$store.getters.isAdmin },
        corSelecionada()  { return this.form.cor_id === 'outro' },

        veiculosOrdenados() {

            let listaFiltrada = this.veiculos;
            if (this.filtroUsuarioId) {
                listaFiltrada = listaFiltrada.filter(v => v.usuario_id === this.filtroUsuarioId);
            }

            return [...listaFiltrada].sort((a, b) => {
                if (this.ordenacaoCampo === 'id') {
                    return this.ordenacaoDirecao === 'desc' ? b.id - a.id : a.id - b.id;
                } 
                else if (this.ordenacaoCampo === 'proprietario') {
                    const nomeA = a.usuario?.nome || '';
                    const nomeB = b.usuario?.nome || '';
                    return this.ordenacaoDirecao === 'asc' 
                        ? nomeA.localeCompare(nomeB) 
                        : nomeB.localeCompare(nomeA);
                }
                return 0;
            });
        }
    },
    async created() { 
        await this.carregar();
        
        if (this.$route.query.usuario_id) {
            this.filtroUsuarioId = Number(this.$route.query.usuario_id);
        }
    },
    watch: {
        modal(val) {
            document.body.classList.toggle('modal-aberto', val)
        },
        form: {
            deep: true,
            handler(val) {
                if (!val.id) localStorage.setItem('draft_veiculo', JSON.stringify(val))
            }
        }
    },
    methods: {
        async carregar(page = 1) {
            const [v, u, c] = await Promise.all([
                this.$http.get(`/veiculos?page=${page}`),
                this.isAdmin ? this.$http.get('/usuarios') : Promise.resolve({ data: [] }),
                this.$http.get('/cores'),
            ])
            this.veiculos = v.data.data

            this.paginacao = {
                current_page: v.data.current_page,
                last_page: v.data.last_page,
            }

            this.usuarios = u.data.data || u.data
            this.cores    = c.data
            await this.carregarMarcasFipe()
        },

        alternarOrdenacao(campo) {
            if (this.ordenacaoCampo === campo) {
                this.ordenacaoDirecao = this.ordenacaoDirecao === 'asc' ? 'desc' : 'asc';
            } else {
                this.ordenacaoCampo = campo;
                this.ordenacaoDirecao = campo === 'proprietario' ? 'asc' : 'desc';
            }
        },

        async carregarMarcasFipe() {
            try {
                this.carregandoMarcas = true
                const { data } = await fipeApi.get('/marcas')
                this.marcasFipe = data
            } catch {
                this.marcasFipe = []
            } finally {
                this.carregandoMarcas = false
            }
        },

        async aoSelecionarMarcaFipe() {
            this.form.modelo    = ''
            this.form.modeloFipe = ''
            this.modelosFipe    = []
            if (!this.form.marcaFipeId || this.form.marcaFipeId === 'outro') return

            const marcaFipe = this.marcasFipe.find(m => m.codigo == this.form.marcaFipeId)
            if (marcaFipe) this.form.marca = marcaFipe.nome

            try {
                this.carregandoModelos = true
                const { data } = await fipeApi.get(`/marcas/${this.form.marcaFipeId}/modelos`)
                this.modelosFipe = data.modelos || []
            } catch {
                this.modelosFipe = []
            } finally {
                this.carregandoModelos = false
            }
        },

        aoSelecionarModeloFipe() {
            const m = this.modelosFipe.find(m => m.codigo == this.form.modeloFipe)
            if (m) this.form.modelo = m.nome
        },

        async abrirModal(v = null) {
            this.erro = ''
            this.erros = {} 
            this.modelosFipe = []
            
            if (v) {
                this.form = { ...v, marcaFipeId: '', modeloFipe: '', marcaOutro: '', corOutro: '' }
            } else {
                const rascunho = localStorage.getItem('draft_veiculo')
                this.form = rascunho ? JSON.parse(rascunho) : formVazio()
                
                if (this.form.marcaFipeId && this.form.marcaFipeId !== 'outro') {
                    this.aoSelecionarMarcaFipe();
                }
            }
            this.modal = true
        },

        limparRascunho() {
            if(confirm('Deseja apagar este rascunho e começar do zero?')) {
                localStorage.removeItem('draft_veiculo')
                this.form = formVazio()
                this.erros = {}
            }
        },

        agendarRevisao(veiculo) {
            const dataDaquiUmAno = new Date();
            dataDaquiUmAno.setFullYear(dataDaquiUmAno.getFullYear() + 1);
            const dataFormatada = dataDaquiUmAno.toISOString().split('T')[0]; 

            const kmAtual = Number(veiculo.quilometragem) || 0;
            const proximoKm = kmAtual + 10000;

            this.$router.push({
                path: '/revisoes',
                query: {
                    novo_agendamento: true, 
                    usuario_id: veiculo.usuario_id,
                    veiculo_id: veiculo.id,
                    data: dataFormatada,
                    km: proximoKm
                }
            });
        },

        // ==================== MÁSCARAS E VALIDAÇÕES (Estilo Usuarios.vue) ====================

        formatarPlaca(event) {
            this.$delete(this.erros, 'placa') // Apaga erro visual enquanto digita
            let valor = event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '')
            if (valor.length > 3) {
                valor = valor.substring(0, 3) + '-' + valor.substring(3, 7)
            }
            this.form.placa = valor
        },

        formatarQuilometragem(event) {
            let valorLimpado = String(event.target.value).replace(/\D/g, '')
            
            this.form.quilometragem = valorLimpado ? parseInt(valorLimpado, 10) : 0
        },

        validarPlaca() {
            if (!this.form.placa) { 
                this.$delete(this.erros, 'placa')
                return true 
            }
            const regexPlaca = /^[A-Z]{3}-[0-9][A-Z0-9][0-9]{2}$/
            if (!regexPlaca.test(this.form.placa)) {
                this.$set(this.erros, 'placa', 'Placa inválida. Ex: ABC-1234 ou ABC-1A23')
                return false
            }
            this.$delete(this.erros, 'placa')
            return true
        },

        validarAnos() {
            let anoAtual = new Date().getFullYear() + 1 // Permite até o ano que vem (ex: comprar carro 2024 modelo 2025)
            
            // Valida Ano Fabricação
            if (this.form.ano_fabricacao) {
                if (this.form.ano_fabricacao < 1900 || this.form.ano_fabricacao > anoAtual) {
                    this.$set(this.erros, 'ano_fabricacao', `Ano inválido (1900 - ${anoAtual}).`)
                } else {
                    this.$delete(this.erros, 'ano_fabricacao')
                }
            }

            // Valida Ano Modelo
            if (this.form.ano_modelo) {
                 if (this.form.ano_modelo < 1900 || this.form.ano_modelo > anoAtual) {
                    this.$set(this.erros, 'ano_modelo', `Ano inválido (1900 - ${anoAtual}).`)
                } else {
                    this.$delete(this.erros, 'ano_modelo')
                }
            }
        },

        validarTudo() {
            const novosErros = {}

            if (!this.form.usuario_id) novosErros.usuario_id = 'Proprietário é obrigatório.'
            if (!this.form.placa.trim()) novosErros.placa = 'Placa é obrigatória.'
            if (!this.form.ano_fabricacao) novosErros.ano_fabricacao = 'Ano de fabricação é obrigatório.'
            if (!this.form.ano_modelo) novosErros.ano_modelo = 'Ano do modelo é obrigatório.'

            this.erros = { ...novosErros }

            // Chama as validações secundárias se os campos estiverem preenchidos
            if (this.form.placa) this.validarPlaca()
            if (this.form.ano_fabricacao || this.form.ano_modelo) this.validarAnos()

            return Object.keys(this.erros).length === 0
        },

        // ==================== SALVAR ====================

        async resolverCor() {
            if (this.form.cor_id !== 'outro') return this.form.cor_id || null
            const nome = this.form.corOutro.trim()
            if (!nome) return null
            const { data } = await this.$http.post('/cores', { nome })
            this.cores.push(data)
            return data.id
        },

        async salvar() {
            if (!this.validarTudo()) return // Interrompe se houver erros de validação

            this.salvando = true
            this.erro = ''
            try {
                const cor_id_resolvida = await this.resolverCor()
                
                // Se o usuário digitou uma "Nova Marca", usamos ela. Se não, usamos a da FIPE.
                const marcaFinal = this.form.marcaOutro ? this.form.marcaOutro : this.form.marca;

                const payload = {
                    usuario_id:     this.form.usuario_id,
                    marca:          marcaFinal,
                    cor_id:         cor_id_resolvida,
                    modelo:         this.form.modelo,
                    placa:          this.form.placa,
                    ano_fabricacao: this.form.ano_fabricacao,
                    ano_modelo:     this.form.ano_modelo,
                    combustivel:    this.form.combustivel,
                    quilometragem:  this.form.quilometragem || 0,
                }
                
                if (this.form.id) {
                    await this.$http.put(`/veiculos/${this.form.id}`, payload)
                } else {
                    await this.$http.post('/veiculos', payload)
                }

                localStorage.removeItem('draft_veiculo')
                this.modal = false
                await this.carregar()
            } catch (e) {
                // Trata erros vindos da API (ex: Placa já cadastrada no banco)
                const errosApi = e.response?.data?.errors
                if (errosApi) {
                    Object.keys(errosApi).forEach(campo => {
                        this.$set(this.erros, campo, errosApi[campo][0])
                    })
                } else {
                    this.erro = e.response?.data?.message || 'Erro ao salvar.'
                }
            } finally {
                this.salvando = false
            }
            localStorage.removeItem('draft_veiculo')
            this.modal = false
        },

        async excluir(id) {
            if (!confirm('Deseja excluir este veículo?')) return
            try {
                await this.$http.delete(`/veiculos/${id}`)
                await this.carregar()
            } catch {
                alert('Erro ao excluir veículo.')
            }
        },
    },
}
</script>

<style scoped>
/* Classes padronizadas de Erro (Iguais ao Usuarios.vue) */
.input-erro { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
.msg-erro { color: #dc2626; font-size: .78rem; margin-top: .2rem; display: block; font-weight: 500; }

.select-auto { height: auto !important; size: 1; }
.page {
    padding: 2rem;
    min-height: calc(100vh - 60px); /* 60px = altura do navbar */
    background: var(--bg);
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.3rem; font-weight: 700; }
.acoes { display: flex; gap: .4rem; }
.vazio { text-align: center; color: #94a3b8; padding: 2rem; }
.placa { background: #f1f5f9; padding: .2rem .5rem; border-radius: 4px; font-family: monospace; font-weight: 600; }
.modal-bg {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,.5);
    z-index: 999;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    overflow-y: auto;
    padding: 2rem 1rem;
    box-sizing: border-box;
}
.modal-box {
    background: var(--white);
    border-radius: 12px;
    padding: 2rem;
    width: 90%;
    max-width: 700px;
    box-sizing: border-box;
    flex-shrink: 0;
    margin-bottom: 2rem;
}
.modal-box h3 { margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 600; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: .75rem; margin-top: 1.5rem; }
.alert-erro { background: #fee2e2; color: #991b1b; padding: .75rem; border-radius: 7px; margin-top: 1rem; font-size: .875rem; }
</style>