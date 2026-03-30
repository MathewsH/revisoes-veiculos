<template>
    <div class="page fade-in">
        <div class="page-header">
            <h2>Usuários</h2>
            <button class="btn btn-primary" @click="abrirModal()">+ Novo Usuário</button>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Sexo</th>
                            <th>Tipo</th>
                            <th>Veículos</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in usuarios" :key="u.id">
                            <td>{{ u.nome }}</td>
                            <td>{{ u.email }}</td>
                            <td>{{ u.cpf }}</td>
                            <td>
                                <span class="badge" :class="u.sexo === 'M' ? 'badge-m' : 'badge-f'">
                                    {{ u.sexo === 'M' ? 'Masculino' : 'Feminino' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" :class="'badge-' + u.tipo">{{ u.tipo }}</span>
                            </td>
                            <td>{{ u.veiculos_count || 0 }}</td>
                            <td class="acoes">
                                <button class="btn btn-info" @click="verVeiculos(u.id)" title="Ver Veículos" style="background: #3b82f6; border: none;">🚗</button>
                                
                                <button class="btn btn-secondary" @click="abrirModal(u)">✏️</button>
                                <button class="btn btn-danger" @click="excluir(u.id)">🗑️</button>
                            </td>
                        </tr>
                        <tr v-if="!usuarios.length">
                            <td colspan="7" class="vazio">Nenhum usuário encontrado</td>
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

        <!-- Modal -->
        <div v-if="modal" class="modal-bg" @click.self="modal = false">
            <div class="modal-box fade-in">
                <h3>{{ form.id ? 'Editar' : 'Novo' }} Usuário</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input v-model="form.nome" class="form-control"
                            :class="{ 'input-erro': erros.nome }" />
                        <span v-if="erros.nome" class="msg-erro">{{ erros.nome }}</span>
                    </div>
                    <div class="form-group">
                        <label>E-mail *</label>
                        <input v-model="form.email" type="text" class="form-control"
                            :class="{ 'input-erro': erros.email }"
                            @blur="validarEmail" />
                        <span v-if="erros.email" class="msg-erro">{{ erros.email }}</span>
                    </div>
                    <div class="form-group">
                        <label>CPF *</label>
                        <input v-model="form.cpf" class="form-control"
                            :class="{ 'input-erro': erros.cpf }"
                            placeholder="000.000.000-00"
                            @input="mascaraCpfSemValidar"
                            @blur="validarCpf" />
                        <span v-if="erros.cpf" class="msg-erro">{{ erros.cpf }}</span>
                    </div>
                    <!-- <div class="form-group">
                        <label>Senha {{ form.id ? '(deixe em branco para manter)' : '*' }}</label>
                        <input v-model="form.senha" type="password" class="form-control"
                            :class="{ 'input-erro': erros.senha }" />
                        <span v-if="erros.senha" class="msg-erro">{{ erros.senha }}</span>
                    </div> -->
                    <div class="form-group">
                        <label>Sexo *</label>
                        <div class="radio-group" style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: normal;">
                                <input type="radio" value="M" v-model="form.sexo" />
                                Masculino
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: normal;">
                                <input type="radio" value="F" v-model="form.sexo" />
                                Feminino
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipo *</label>
                        <select v-model="form.tipo" class="form-control">
                            <option value="admin">Administrador</option>
                            <option value="comum">Comum</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Data de Nascimento *</label>
                        <input v-model="form.data_nascimento" type="date" class="form-control"
                            :class="{ 'input-erro': erros.data_nascimento }"
                            @change="validarDataNascimento" />
                        <span v-if="erros.data_nascimento" class="msg-erro">{{ erros.data_nascimento }}</span>
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input v-model="form.telefone" class="form-control"
                            placeholder=""
                            @input="mascaraTelefone" />
                    </div>
                    <div class="form-group">
                        <label>CEP {{ buscandoCep ? '🔍 buscando...' : '' }}</label>
                        <input v-model="form.cep" class="form-control"
                            placeholder=""
                            @input="buscarCep" />
                        <span v-if="cepErro" class="msg-erro">{{ cepErro }}</span>
                    </div>
                    <div class="form-group">
                        <label>Rua</label>
                        <input v-model="form.rua" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Número</label>
                        <input v-model="form.numero" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Bairro</label>
                        <input v-model="form.bairro" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Cidade</label>
                        <input v-model="form.cidade" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <input v-model="form.estado" class="form-control" maxlength="2" />
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

const formVazio = () => ({
    id: null, nome: '', email: '', cpf: '', senha: '',
    sexo: 'M', tipo: 'comum', data_nascimento: '',
    telefone: '', cep: '', rua: '', numero: '',
    bairro: '', cidade: '', estado: '',
})

export default {
    name: 'Usuarios',
    data() {
        return {
            usuarios: [], modal: false, form: formVazio(),
            erro: '', salvando: false,
            buscandoCep: false, cepErro: '',
            erros: {},
            paginacao: { current_page: 1, last_page: 1 },
        }
    },
    watch: {
        modal(val) {
            document.body.classList.toggle('modal-aberto', val)
        },
        form: {
            deep: true,
            handler(val) {
                if (!val.id) localStorage.setItem('draft_usuario', JSON.stringify(val))
            }
        }
    },
    async created() { await this.carregar() },
    methods: {
        async carregar(page = 1) {
            const { data } = await this.$http.get(`/usuarios?page=${page}`)
            
            this.usuarios = data.data 
            
            this.paginacao = {
                current_page: data.current_page,
                last_page: data.last_page,
            }
        },
        abrirModal(u = null) {
            this.erro   = ''
            this.erros  = {}
            this.cepErro = ''
            
            if (u) {
                this.form = { ...u, senha: '' }
            } else {
                const rascunho = localStorage.getItem('draft_usuario')
                this.form = rascunho ? JSON.parse(rascunho) : formVazio()
            }
            this.modal  = true
        },
        limparRascunho() {
            if(confirm('Deseja apagar este rascunho e começar do zero?')) {
                localStorage.removeItem('draft_usuario')
                this.form = formVazio()
                this.erros = {}
            }
        },
        verVeiculos(usuarioId) {
            this.$router.push({ path: '/veiculos', query: { usuario_id: usuarioId } })
        },

        // ==================== MÁSCARAS ====================
        mascaraCpfSemValidar(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11)
            v = v.replace(/(\d{3})(\d)/, '$1.$2')
            v = v.replace(/(\d{3})(\d)/, '$1.$2')
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            this.form.cpf = v
        },
        mascaraTelefone(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11)
            v = v.replace(/^(\d{2})(\d)/, '($1) $2')
            v = v.replace(/(\d{5})(\d{1,4})$/, '$1-$2')
            this.form.telefone = v
        },
        async buscarCep(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 8)
            v = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2')
            this.form.cep = v

            const cep = v.replace(/\D/g, '')
            if (cep.length !== 8) return

            try {
                this.buscandoCep = true
                this.cepErro = ''
                const { data } = await axios.get(`https://viacep.com.br/ws/${cep}/json/`)
                if (data.erro) {
                    this.cepErro = 'CEP não encontrado.'
                    return
                }
                this.form.rua    = data.logradouro || ''
                this.form.bairro = data.bairro     || ''
                this.form.cidade = data.localidade || ''
                this.form.estado = data.uf         || ''
            } catch {
                this.cepErro = 'Erro ao buscar CEP.'
            } finally {
                this.buscandoCep = false
            }
        },

        // ==================== VALIDAÇÕES ====================
        async verificarCpfEmUso() {
            const cpf = this.form.cpf.replace(/\D/g, '')
            if (cpf.length !== 11) return false

            try {
                const params = this.form.id ? `?ignorar_id=${this.form.id}` : ''
                const { data } = await this.$http.get(`/verificar/cpf/${cpf}${params}`)
                if (data.em_uso) {
                    this.$set(this.erros, 'cpf', 'Este CPF já está sendo utilizado.')
                    return true // está em uso
                }
                return false
            } catch {
                return false
            }
        },
        validarCpf() {
            const cpf = this.form.cpf.replace(/\D/g, '')
            if (!cpf) { this.$delete(this.erros, 'cpf'); return true }
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                this.$set(this.erros, 'cpf', 'CPF inválido.')
                return false
            }
            for (let t = 9; t < 11; t++) {
                let sum = 0
                for (let c = 0; c < t; c++) sum += parseInt(cpf[c]) * (t + 1 - c)
                const d = ((10 * sum) % 11) % 10
                if (parseInt(cpf[t]) !== d) {
                    this.$set(this.erros, 'cpf', 'CPF inválido.')
                    return false
                }
            }
            this.$delete(this.erros, 'cpf')
            return true
        },
        validarEmail() {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (!this.form.email) { this.$delete(this.erros, 'email'); return true }
            if (!re.test(this.form.email)) {
                this.$set(this.erros, 'email', 'E-mail inválido.')
                return false
            }
            this.$delete(this.erros, 'email')
            return true
        },
        validarDataNascimento() {
            if (!this.form.data_nascimento) { this.$delete(this.erros, 'data_nascimento'); return true }
            const data  = new Date(this.form.data_nascimento)
            const hoje  = new Date()
            const minima = new Date('1800-01-01')
            if (data >= hoje) {
                this.$set(this.erros, 'data_nascimento', 'Data deve ser anterior à data atual.')
                return false
            }
            if (data <= minima) {
                this.$set(this.erros, 'data_nascimento', 'Data deve ser posterior a 01/01/1800.')
                return false
            }
            this.$delete(this.erros, 'data_nascimento')
            return true
        },
        validarTudo() {
            const novosErros = {}

            if (!this.form.nome.trim())
                novosErros.nome = 'Nome é obrigatório.'
            if (!this.form.email.trim())
                novosErros.email = 'E-mail é obrigatório.'
            if (!this.form.cpf.trim())
                novosErros.cpf = 'CPF é obrigatório.'
            if (!this.form.data_nascimento)
                novosErros.data_nascimento = 'Data de nascimento é obrigatória.'
            // if (!this.form.id && !this.form.senha.trim())
            //     novosErros.senha = 'Senha é obrigatória.'

            this.erros = { ...novosErros }

            // valida formato dos campos preenchidos
            if (this.form.cpf)             this.validarCpf()
            if (this.form.email)           this.validarEmail()
            if (this.form.data_nascimento) this.validarDataNascimento()

            return Object.keys(this.erros).length === 0
        },

        // ==================== CRUD ====================
        async salvar() {
            if (!this.validarTudo()) return

            // Verifica CPF duplicado antes de salvar
            const cpfEmUso = await this.verificarCpfEmUso()
            if (cpfEmUso) return

            this.salvando = true
            this.erro = ''
            try {
                const payload = { ...this.form }
                if (!payload.senha) delete payload.senha
                if (this.form.id) {
                    await this.$http.put(`/usuarios/${this.form.id}`, payload)
                } else {
                    await this.$http.post('/usuarios', payload)
                }
                localStorage.removeItem('draft_usuario')
                this.modal = false
                await this.carregar()
            } catch (e) {
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
        },
        async excluir(id) {
            if (!confirm('Deseja excluir este usuário?')) return
            try {
                await this.$http.delete(`/usuarios/${id}`)
                await this.carregar()
            } catch (e) {
                alert('Erro ao excluir usuário.')
            }
        },
    },
}
</script>

<style scoped>
.paginacao { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1.5rem 0 0.5rem 0; }
.input-erro { border-color: #dc2626 !important; }
.msg-erro { color: #dc2626; font-size: .78rem; margin-top: .2rem; display: block; }
.page {
    padding: 2rem;
    min-height: calc(100vh - 60px); /* 60px = altura do navbar */
    background: var(--bg);
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.3rem; font-weight: 700; }
.acoes { display: flex; gap: .4rem; }
.vazio { text-align: center; color: #94a3b8; padding: 2rem; }
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