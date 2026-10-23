
<template>
  <div class="app-page">
    <main class="app-card">
      <header class="app-header">
        <div class="app-icon">✓</div>

        <div>
          <h1>Minhas tarefas</h1>
          <p>Organize seu dia, uma tarefa de cada vez.</p>
        </div>
      </header>

    <!-- NOVO: resumo das tarefas -->
<section class="task-stats" aria-label="Resumo das tarefas">
  <div class="stat-item">
    <span class="stat-dot stat-dot-total"></span>
    <span class="stat-label">Total</span>
    <strong>{{ totalTasks }}</strong>
  </div>

  <div class="stat-item">
    <span class="stat-dot stat-dot-completed"></span>
    <span class="stat-label">Concluídas</span>
    <strong>{{ completedTasks }}</strong>
  </div>

  <div class="stat-item">
    <span class="stat-dot stat-dot-pending"></span>
    <span class="stat-label">Pendentes</span>
    <strong>{{ pendingTasks }}</strong>
  </div>
</section>

    <!-- Mensagem de erro -->
    <div
      v-if="errorMessage"
      class="alert alert-danger alert-dismissible app-alert"
      role="alert"
    >
      {{ errorMessage }}

      <button
        type="button"
        class="btn-close"
        aria-label="Fechar mensagem"
        @click="errorMessage = ''"
      ></button>
    </div>

    <!-- Formulário de criação -->
    <form @submit.prevent="addTask" class="task-form">
      <div class="input-group">
        <input
          v-model="newTask"
          type="text"
          class="form-control"
          placeholder="Digite uma nova tarefa..."
          aria-label="Nome da nova tarefa"
          :disabled="isBusy"
          maxlength="255"
        />

        <button
          type="submit"
          class="btn btn-add"
          :disabled="isBusy"
        >
          {{ isSaving ? 'Adicionando...' : '+ Adicionar tarefa' }}
        </button>
      </div>
    </form>

    <!-- Carregamento inicial -->
    <div v-if="isLoading" class="loading-state" role="status">
  <div class="spinner-border text-primary mb-3" aria-hidden="true"></div>
  <h5>Preparando tudo para você</h5>
  <p>Estamos carregando suas tarefas...</p>
</div>

    
<!-- Erro ao carregar a lista -->
<div v-else-if="loadError" class="empty-state"">
  <p class="text-danger mb-3">{{ loadError }}</p>
  <button class="btn btn-outline-primary" @click="loadTasks">
    Tentar novamente
  </button>
</div>

<!-- Lista vazia -->
<div
  v-else-if="tasks.length === 0"
  class="empty-state"
>
  <div class="empty-state-icon">✓</div>

  <h5>Seu dia começa aqui!</h5>

  <p>
    Você ainda não tem tarefas cadastradas.
    Que tal adicionar a primeira e começar a se organizar?
  </p>
</div>


    <!-- Lista de tarefas -->
    <ul v-else class="list-unstyled d-flex flex-column gap-2">
      <li
  v-for="task in tasks"
  :key="task.id"
  class="task-card"
  :class="{ 'task-completed': task.completed }"

      >
        <template v-if="editingTask?.id === task.id">
          <input
            v-model="editingTask.title"
            type="text"
            class="form-control"
            aria-label="Editar título da tarefa"
            maxlength="255"
            :disabled="isBusy"
            @keyup.enter="saveTask"
            @keyup.esc="cancelEdit"
          />
        </template>

        <template v-else>
          <div class="d-flex align-items-center flex-grow-1 me-2">
            <input
              type="checkbox"
              class="form-check-input me-3 flex-shrink-0"
              :checked="task.completed"
              :disabled="isBusy"
              @change="toggleTask(task)"
              :aria-label="`Concluir tarefa: ${task.title}`"
            />

            <span
              class="text-break"
              :class="{
                'text-decoration-line-through text-secondary': task.completed
              }"
            >
              {{ task.title }}
            </span>
          </div>
        </template>

        <div class="d-flex align-items-center flex-shrink-0">
          <template v-if="editingTask?.id === task.id">
            <button
              class="btn btn-success btn-sm me-2"
              :disabled="isBusy"
              @click="saveTask"
            >
              {{ isSaving ? 'Salvando...' : 'Salvar' }}
            </button>

            <button
              class="btn btn-secondary btn-sm"
              :disabled="isBusy"
              @click="cancelEdit"
            >
              Cancelar
            </button>
          </template>

          <template v-else>
            <button
              class="btn btn-icon btn-edit me-2"
              :disabled="isBusy"
              :aria-label="`Editar tarefa: ${task.title}`"
              @click="editTask(task)"
            >
              ✎
            </button>

            <button
              class="btn btn-icon btn-delete"
              :disabled="isBusy"
              :aria-label="`Excluir tarefa: ${task.title}`"
              @click="deleteTask(task)"
            >
              {{ deletingTaskId === task.id ? '...' : '⌫' }}
            </button>
          </template>
        </div>
      </li>
        </ul>
    </main>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      tasks: [],
      newTask: '',
      editingTask: null,
      isLoading: true,
      isSaving: false,
      updatingTaskId: null,
      deletingTaskId: null,
      errorMessage: '',
      loadError: '',
    }
  },

  computed: {
  isBusy() {
    return (
      this.isSaving ||
      this.updatingTaskId !== null ||
      this.deletingTaskId !== null
    )
  },

  totalTasks() {
    return this.tasks.length
  },

  completedTasks() {
    return this.tasks.filter(task => task.completed).length
  },

  pendingTasks() {
    return this.tasks.filter(task => !task.completed).length
  },
},

  mounted() {
  this.loadTasks(true)
},

  methods: {
    
async loadTasks(showLoader = false) {
  if (showLoader) {
    this.isLoading = true
  }

  this.loadError = ''

  try {
    const response = await axios.get('/api/tasks')
    this.tasks = response.data
  } catch (error) {
    this.loadError =
      'Não foi possível carregar as tarefas. Tente novamente.'
  } finally {
    if (showLoader) {
      this.isLoading = false
    }
  }
},

    async addTask() {
      const title = this.newTask.trim()

      if (!title) {
        this.errorMessage = 'Digite uma tarefa antes de adicionar.'
        return
      }

      if (this.isBusy) return

      this.isSaving = true
      this.errorMessage = ''

      try {
        await axios.post('/api/tasks', { title })
        this.newTask = ''
        await this.loadTasks()
      } catch (error) {
        this.errorMessage = error.response?.status === 422
          ? 'O título da tarefa é inválido. Confira e tente novamente.'
          : 'Não foi possível adicionar a tarefa. Tente novamente.'
      } finally {
        this.isSaving = false
      }
    },

    async toggleTask(task) {
      if (this.isBusy) return

      this.updatingTaskId = task.id
      this.errorMessage = ''

      try {
        await axios.put(`/api/tasks/${task.id}`, {
          completed: !task.completed
        })

        task.completed = !task.completed
      } catch (error) {
        this.errorMessage = 'Não foi possível atualizar a tarefa.'
      } finally {
        this.updatingTaskId = null
      }
    },

    async deleteTask(task) {
      if (this.isBusy) return

      const confirmed = window.confirm(
        `Tem certeza que deseja excluir a tarefa "${task.title}"?`
      )

      if (!confirmed) return

      this.deletingTaskId = task.id
      this.errorMessage = ''

      try {
        await axios.delete(`/api/tasks/${task.id}`)
        this.tasks = this.tasks.filter(item => item.id !== task.id)
      } catch (error) {
        this.errorMessage = 'Não foi possível excluir a tarefa.'
      } finally {
        this.deletingTaskId = null
      }
    },

    editTask(task) {
      if (this.isBusy) return
      this.errorMessage = ''
      this.editingTask = { ...task }
    },

    async saveTask() {
      if (!this.editingTask || this.isBusy) return

      const title = this.editingTask.title.trim()

      if (!title) {
        this.errorMessage = 'O título da tarefa não pode ficar vazio.'
        return
      }

      this.isSaving = true
      this.errorMessage = ''

      try {
        const taskId = this.editingTask.id

        const response = await axios.put(`/api/tasks/${taskId}`, {
          title
        })

        const index = this.tasks.findIndex(task => task.id === taskId)

        if (index !== -1) {
          this.tasks[index] = response.data
        }

        this.editingTask = null
      } catch (error) {
        this.errorMessage = error.response?.status === 422
          ? 'O título da tarefa é inválido. Confira e tente novamente.'
          : 'Não foi possível salvar as alterações.'
      } finally {
        this.isSaving = false
      }
    },

    cancelEdit() {
      if (this.isBusy) return

      this.editingTask = null
      this.errorMessage = ''
    }
  }
}
</script>

<style scoped>
.app-page {
  min-height: 100vh;
  padding: 56px 20px;
  background: #f4f6fa;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  font-family: Arial, Helvetica, sans-serif;
}

.app-card {
  width: 100%;
  max-width: 760px;
  padding: 36px;
  background: #ffffff;
  border: 1px solid #e9edf3;
  border-radius: 20px;
  box-shadow: 0 12px 35px rgba(31, 45, 61, 0.06);
}

.app-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 30px;
}

.app-icon {
  width: 54px;
  height: 54px;
  flex-shrink: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #ffffff;
  background: #4f7df3;
  border-radius: 16px;
  font-size: 28px;
  font-weight: bold;
  box-shadow: 0 5px 12px rgba(79, 125, 243, 0.2);
}

.app-header h1 {
  margin: 0 0 6px;
  color: #263247;
  font-size: 30px;
  font-weight: 700;
  letter-spacing: -0.7px;
}

.app-header p {
  margin: 0;
  color: #8993a4;
  font-size: 14px;
}

.task-form {
  margin-bottom: 26px;
}

.task-form .input-group {
  gap: 10px;
}

.task-form .form-control {
  min-height: 48px;
  padding: 12px 16px;
  border: 1px solid #e1e6ef;
  border-radius: 12px !important;
  color: #263247;
  box-shadow: none;
}

.task-form .form-control:focus {
  border-color: #7d9cf8;
  box-shadow: 0 0 0 3px rgba(79, 125, 243, 0.12);
}

.task-form .form-control::placeholder {
  color: #a0a8b5;
}

.btn-add {
  min-height: 48px;
  padding: 0 20px;
  border: none;
  border-radius: 12px !important;
  color: #ffffff;
  background: #4f7df3;
  font-weight: 600;
  transition: background 0.2s ease, transform 0.2s ease;
}

.btn-add:hover:not(:disabled) {
  color: #ffffff;
  background: #3868df;
  transform: translateY(-1px);
}

.btn-add:disabled {
  opacity: 0.65;
}

.task-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 18px;
  border: 1px solid #e9edf3;
  border-left: 4px solid #a3aab7;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 3px 10px rgba(31, 45, 61, 0.025);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.task-card:hover {
  box-shadow: 0 6px 16px rgba(31, 45, 61, 0.06);
  transform: translateY(-1px);
}

.task-card .form-check-input {
  width: 19px;
  height: 19px;
  margin-top: 0;
  cursor: pointer;
}

.task-card .form-check-input:checked {
  background-color: #18b978;
  border-color: #18b978;
}

.task-completed {
  border-left-color: #18b978;
  background: #fcfefd;
}

.task-card > div:first-of-type {
  min-width: 0;
}

.task-card span {
  color: #354052;
  font-size: 14px;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.task-completed span {
  color: #9aa3af;
}

.btn-icon {
  width: 36px;
  height: 36px;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 0;
  border: 1px solid transparent;
  border-radius: 10px;
  background: transparent;
  font-size: 16px;
  transition: background 0.2s ease;
}

.btn-edit {
  color: #4f7df3;
  background: #f3f6ff;
  border-color: #e0e8ff;
  font-size: 21px;
}

.btn-edit:hover:not(:disabled) {
  color: #3868df;
  background: #e7eeff;
  border-color: #d4e0ff;
}

.btn-delete {
  color: #e36b75;
  background: #fff8f8;
  border-color: #f5dfe2;
  font-size: 19px;
}

.btn-delete:hover:not(:disabled) {
  color: #c73e4c;
  background: #fff0f1;
  border-color: #f0c8cd;
}

.empty-state {
  margin-top: 24px;
  padding: 44px 24px;
  border: 1px dashed #dce2ec;
  border-radius: 16px;
  background: #fafbfe;
  text-align: center;
}

.empty-state-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  background: #edf2ff;
  color: #4f7df3;
  font-size: 30px;
  font-weight: 700;
}

/* NOVOS ESTILOS: título */
.empty-state h5 {
  margin-bottom: 10px;
  color: #354052;
  font-size: 18px;
  font-weight: 700;
}

/* NOVOS ESTILOS: descrição */
.empty-state p {
  max-width: 380px;
  margin: 0 auto;
  color: #8993a4;
  font-size: 14px;
  line-height: 1.7;
}

.empty-state h5 {
  color: #354052;
  font-weight: 700;
}

.empty-state p {
  color: #8993a4;
  font-size: 14px;
}

.app-alert {
  border-radius: 12px;
}

.loading-state {
  padding: 48px 20px;
  text-align: center;
  background: #fafbfe;
  border: 1px solid #edf0f6;
  border-radius: 16px;
}

.loading-state .spinner-border {
  width: 2rem;
  height: 2rem;
}

.loading-state h5 {
  margin-bottom: 8px;
  color: #354052;
  font-size: 16px;
  font-weight: 700;
}

.loading-state p {
  margin-bottom: 0;
  color: #8993a4;
  font-size: 14px;
}

.task-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  margin-bottom: 26px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #8993a4;
  font-size: 13px;
}

.stat-item strong {
  color: #354052;
  font-weight: 700;
}

.stat-dot {
  width: 8px;
  height: 8px;
  flex-shrink: 0;
  border-radius: 50%;
}

.stat-dot-total {
  background: #4f7df3;
}

.stat-dot-completed {
  background: #18b978;
}

.stat-dot-pending {
  background: #a3aab7;
}

@media (max-width: 576px) {
  .app-page {
    padding: 24px 12px;
  }

  .app-card {
    padding: 22px 16px;
    border-radius: 16px;
  }

  .app-header {
    gap: 12px;
    margin-bottom: 24px;
  }

  .app-icon {
    width: 46px;
    height: 46px;
    border-radius: 13px;
  }

  .app-header h1 {
    font-size: 25px;
  }

  .task-form .input-group {
    flex-direction: column;
  }

  .task-form .form-control,
  .task-form .btn-add {
    width: 100%;
    border-radius: 12px !important;
  }

  .task-card {
    padding: 14px 10px;
    gap: 8px;
  }

  .task-card .form-check-input {
    margin-right: 10px !important;
  }

  .btn-icon {
    width: 32px;
    height: 32px;
  }

.task-stats {
  gap: 14px 20px;
  margin-bottom: 22px;
}

}
</style>
