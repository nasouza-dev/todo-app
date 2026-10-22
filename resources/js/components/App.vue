
<template>
  <div class="container mt-5 mb-5">
    <h1 class="mb-4">📝 To-Do List</h1>

    <!-- Mensagem de erro -->
    <div
      v-if="errorMessage"
      class="alert alert-danger alert-dismissible"
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
    <form @submit.prevent="addTask" class="mb-3">
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
          class="btn btn-primary"
          :disabled="isBusy"
        >
          {{ isSaving ? 'Adicionando...' : '+ Adicionar tarefa' }}
        </button>
      </div>
    </form>

    <!-- Carregamento inicial -->
    <div v-if="isLoading" class="text-center py-5" role="status">
      <div class="spinner-border text-primary mb-3" aria-hidden="true"></div>
      <p class="text-secondary mb-0">Carregando tarefas...</p>
    </div>

    
<!-- Erro ao carregar a lista -->
<div v-else-if="loadError" class="text-center py-5 my-4 border rounded">
  <p class="text-danger mb-3">{{ loadError }}</p>
  <button class="btn btn-outline-primary" @click="loadTasks">
    Tentar novamente
  </button>
</div>

<!-- Lista vazia -->
<div
  v-else-if="tasks.length === 0"
  class="text-center py-5 my-4 border rounded"
>
  <div class="fs-1 mb-3">📝</div>
  <h5 class="mb-2">Tudo pronto para começar?</h5>
  <p class="text-secondary mb-0">
    Você ainda não tem tarefas. Adicione sua primeira tarefa acima!
  </p>
</div>


    <!-- Lista de tarefas -->
    <ul v-else class="list-unstyled d-flex flex-column gap-2">
      <li
        v-for="task in tasks"
        :key="task.id"
        class="border rounded p-3 d-flex justify-content-between align-items-center gap-2"
        :class="{ 'bg-light': task.completed }"
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
              class="btn btn-outline-warning btn-sm me-2"
              :disabled="isBusy"
              :aria-label="`Editar tarefa: ${task.title}`"
              @click="editTask(task)"
            >
              ✏️
            </button>

            <button
              class="btn btn-outline-danger btn-sm"
              :disabled="isBusy"
              :aria-label="`Excluir tarefa: ${task.title}`"
              @click="deleteTask(task)"
            >
              {{ deletingTaskId === task.id ? '...' : 'X' }}
            </button>
          </template>
        </div>
      </li>
    </ul>
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
