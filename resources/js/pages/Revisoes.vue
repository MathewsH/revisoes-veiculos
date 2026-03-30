<template>
    <div class="page fade-in">
        <div class="page-header">
            <h2>Revisões</h2>
            <button v-if="isAdmin" class="btn btn-primary" @click="abrirModal()">+ Nova Revisão</button>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Proprietário</th>
                            <th>Veículo</th>
                            <th>Km</th>
                            <th>Oficina</th>
                            <th>Valor Total</th>
                            <th v-if="isAdmin">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in revisoes" :key="r.id">
                            <td>{{ formatarData(r.data_revisao) }}</td>
                            <td>{{ r.usuario?.nome }}</td>
                            <td><strong>{{ r.veiculo?.marca }}</strong> {{ r.veiculo?.modelo }} <span class="placa">{{ r.veiculo?.placa }}</span></td>
                            <td>{{ Number(r.quilometragem_revisao).toLocaleString() }} km</td>
                            <td>{{ r.oficina || '—' }}</td>
                            <td>R$ {{ Number(r.valor_total).toFixed(2).replace('.', ',') }}</td>
                            <td v-if="isAdmin" class="acoes">
                                <button class="btn btn-secondary" @click="abrirModal(r)">✏️</button>
                                <button class="btn btn-danger" @click="excluir(r.id)">🗑️</button>
                            </td>
                        </tr>
                        <tr v-if="!revisoes.length">
                            <td colspan="7" class="vazio">Nenhuma revisão encontrada</td>
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
                <h3>{{ form.id ? 'Editar' : 'Nova' }} Revisão</h3>
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>Proprietário *</label>
                        <select v-model="form.usuario_id" class="form-control" :class="{ 'input-erro': erros.usuario_id }" @change="limparErros('usuario_id')">
                            <option value="">-- Selecione o Proprietário --</option>
                            <option v-for="u in usuarios" :key="u.id" :value="u.id">
                                {{ u.nome }}
                            </option>
                        </select>
                        <span v-if="erros.usuario_id" class="msg-erro">{{ erros.usuario_id }}</span>
                    </div>

                    <div class="form-group">
                        <label>Veículo *</label>
                        <select v-model="form.veiculo_id" class="form-control" :class="{ 'input-erro': erros.veiculo_id }" :disabled="!form.usuario_id" @change="limparErros('veiculo_id')">
                            <option value="">{{ form.usuario_id ? '-- Selecione o Veículo --' : 'Selecione um proprietário antes' }}</option>
                            <option v-for="v in veiculosFiltrados" :key="v.id" :value="v.id">
                                {{ v.marca }} {{ v.modelo }} - {{ v.placa }}
                            </option>
                        </select>
                        <span v-if="erros.veiculo_id" class="msg-erro">{{ erros.veiculo_id }}</span>
                    </div>

                    <div class="form-group">
                        <label>Data da Revisão *</label>
                        <input v-model="form.data_revisao" type="date" class="form-control" :class="{ 'input-erro': erros.data_revisao }" @blur="validarData" />
                        <span v-if="erros.data_revisao" class="msg-erro">{{ erros.data_revisao }}</span>
                    </div>

                    <div class="form-group">
                        <label>Quilometragem (Km) *</label>
                        <input v-model="form.quilometragem_revisao" type="text" inputmode="numeric" class="form-control" :class="{ 'input-erro': erros.quilometragem_revisao }" @input="formatarKm" />
                        <span v-if="erros.quilometragem_revisao" class="msg-erro">{{ erros.quilometragem_revisao }}</span>
                    </div>

                    <div class="form-group">
                        <label>Oficina</label>
                        <input v-model="form.oficina" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label>Valor Total</label>
                        <input v-model="form.valor_mascara" type="text" inputmode="numeric" class="form-control" @input="formatarMoeda" />
                    </div>

                    <div class="form-group" style="grid-column: span 2">
                        <label>Descrição</label>
                        <textarea v-model="form.descricao" class="form-control" rows="3"></textarea>
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
const formVazio = () => ({
    id: null, usuario_id: '', veiculo_id: '', data_revisao: '',
    quilometragem_revisao: '', descricao: '',
    valor_total: 0, valor_mascara: 'R$ 0,00', oficina: ''
})

export default {
    name: 'Revisoes',
    data() {
        return { 
            revisoes: [], veiculos: [], usuarios: [], 
            modal: false, form: formVazio(), 
            erro: '', erros: {}, salvando: false,
            paginacao: { current_page: 1, last_page: 1 }
        }
    },
    computed: {
        isAdmin() { return this.$store.getters.isAdmin },
        
        // Filtra a combo de veículos baseada no dono selecionado
        veiculosFiltrados() {
            if (!this.form.usuario_id) return [];
            return this.veiculos.filter(v => v.usuario_id === this.form.usuario_id);
        }
    },
    watch: {
        modal(val) {
            document.body.classList.toggle('modal-aberto', val)
        },
        'form.usuario_id'(novoDono) {
            const veiculoPertenceAoDono = this.veiculosFiltrados.find(v => v.id === this.form.veiculo_id);
            if (!veiculoPertenceAoDono) {
                this.form.veiculo_id = '';
            }
        },
        form: {
            deep: true,
            handler(val) {
                if (!val.id) localStorage.setItem('draft_revisao', JSON.stringify(val))
            }
        }
    },
    async created() { 
        await this.carregar();
        
        const query = this.$route.query;
        if (query.novo_agendamento) {
            
            this.abrirModal();
            
            this.form.usuario_id = Number(query.usuario_id);
            
            this.$nextTick(() => {
                this.form.veiculo_id = Number(query.veiculo_id);
                this.form.data_revisao = query.data;
                this.form.quilometragem_revisao = Number(query.km);
            });
            
            this.$router.replace('/revisoes');
        }
    },
    methods: {
        async carregar(page = 1) {
            const [r, v, u] = await Promise.all([
                this.$http.get(`/revisoes?page=${page}`),
                this.$http.get('/veiculos'),
                this.$http.get('/usuarios')
            ])
            
            this.revisoes = r.data.data || r.data;
            
            this.veiculos = v.data.data || v.data;
            this.usuarios = u.data.data || u.data;
            
            this.paginacao = {
                current_page: r.data.current_page || 1,
                last_page: r.data.last_page || 1,
            }
        },

        abrirModal(r = null) {
            this.erro = '';
            this.erros = {};
            if (r) {
                let mascara = Number(r.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                this.form = { ...r, valor_mascara: mascara };
            } else {
                const rascunho = localStorage.getItem('draft_revisao')
                this.form = rascunho ? JSON.parse(rascunho) : formVazio()
            }
            this.modal = true;
        },

        limparRascunho() {
            if(confirm('Deseja apagar este rascunho e começar do zero?')) {
                localStorage.removeItem('draft_revisao')
                this.form = formVazio()
                this.erros = {}
            }
        },

        // ================= MÁSCARAS E VALIDAÇÕES =================

        formatarMoeda(event) {
            // Remove tudo que não for número
            let valor = event.target.value.replace(/\D/g, '');
            if (!valor) valor = '0';
            
            // Transforma "25" em 0.25
            let numeroFloat = parseInt(valor, 10) / 100;

            // Trava no teto de 2 milhões
            if (numeroFloat > 2000000) numeroFloat = 2000000;

            // Guarda o número real para o banco e a máscara para a tela
            this.form.valor_total = numeroFloat;
            this.form.valor_mascara = numeroFloat.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        },

        formatarKm(event) {
            this.limparErros('quilometragem_revisao');
            let valor = String(event.target.value).replace(/\D/g, '');
            this.form.quilometragem_revisao = valor ? parseInt(valor, 10) : '';
        },

        validarData() {
            if (!this.form.data_revisao) {
                this.limparErros('data_revisao');
                return true;
            }

            // Pega a data ignorando o fuso horário para evitar bugs de dia anterior
            const dataEscolhida = new Date(this.form.data_revisao + 'T00:00:00');
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0); // Zera a hora para comparar apenas o dia

            if (dataEscolhida < hoje) {
                this.$set(this.erros, 'data_revisao', 'A data da revisão não pode ser no passado.');
                return false;
            }
            
            this.limparErros('data_revisao');
            return true;
        },

        formatarData(d) {
            if (!d) return '—';
            
            const dataLimpa = d.substring(0, 10);
            
            return new Date(dataLimpa + 'T00:00:00').toLocaleDateString('pt-BR');
        },

        limparErros(campo) {
            this.$delete(this.erros, campo);
        },

        validarTudo() {
            const novosErros = {};

            if (!this.form.usuario_id) novosErros.usuario_id = 'Proprietário é obrigatório.';
            if (!this.form.veiculo_id) novosErros.veiculo_id = 'Veículo é obrigatório.';
            if (!this.form.data_revisao) novosErros.data_revisao = 'Data é obrigatória.';
            if (this.form.quilometragem_revisao === '') novosErros.quilometragem_revisao = 'Quilometragem é obrigatória.';

            this.erros = { ...novosErros };

            if (this.form.data_revisao) this.validarData();

            return Object.keys(this.erros).length === 0;
        },

        // ================= CRUD =================

        async salvar() {
            if (!this.validarTudo()) return;

            this.salvando = true;
            this.erro = '';
            
            try {
                const payload = { ...this.form };
                delete payload.valor_mascara; // Não enviamos o texto "R$ 0,00" para o banco

                if (this.form.id) {
                    await this.$http.put(`/revisoes/${this.form.id}`, payload);
                } else {
                    await this.$http.post('/revisoes', payload);
                }
                localStorage.removeItem('draft_revisao')
                this.modal = false;
                await this.carregar();
            } catch (e) {
                const errosApi = e.response?.data?.errors;
                if (errosApi) {
                    Object.keys(errosApi).forEach(campo => {
                        this.$set(this.erros, campo, errosApi[campo][0]);
                    });
                } else {
                    this.erro = e.response?.data?.message || 'Erro ao salvar.';
                }
            } finally {
                this.salvando = false;
            }
        },

        async excluir(id) {
            if (!confirm('Deseja excluir esta revisão?')) return;
            await this.$http.delete(`/revisoes/${id}`);
            await this.carregar();
        },
    },
}
</script>

<style scoped>
.input-erro { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
.msg-erro { color: #dc2626; font-size: .78rem; margin-top: .2rem; display: block; font-weight: 500; }

.page {
    padding: 2rem;
    min-height: calc(100vh - 60px);
    background: var(--bg);
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.3rem; font-weight: 700; }
.acoes { display: flex; gap: .4rem; }
.vazio { text-align: center; color: #94a3b8; padding: 2rem; }
.placa { background: #f1f5f9; padding: .2rem .5rem; border-radius: 4px; font-family: monospace; font-weight: 600; font-size: .8rem; }
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