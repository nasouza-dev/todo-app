<template>
  <div class="container mt-5">
    <h1 class="mb-4">📝 To-Do List</h1>

    <form @submit.prevent="addTask" class="mb-3">
  <div class="input-group">
    <input
      v-model="newTask"
      type="text"
      class="form-control"
      placeholder="Digite uma nova tarefa..."
      aria-label="Nome da nova tarefa"
    />

    <button
      type="submit"
      class="btn btn-primary"
    >
      + Adicionar tarefa
    </button>
  </div>
</form>

    <div
  v-if="tasks.length === 0"
  class="text-center py-5 my-4 border rounded"
>
  <div class="fs-1 mb-3">📝</div>

  <h5 class="mb-2">Tudo pronto para começar?</h5>

  <p class="text-secondary mb-0">
    Você ainda não tem tarefas.
    Adicione sua primeira tarefa acima!
  </p>
</div>

  <ul v-else class="list-unstyled d-flex flex-column gap-2">
  <li
    v-for="task in tasks"
    :key="task.id"
    class="border rounded p-3 d-flex justify-content-between align-items-center"
    :class="{ 'bg-light': task.completed }"
  >
        <template v-if="editingTask?.id === task.id">
  <input
    v-model="editingTask.title"
    class="form-control me-2"
  />
</template>

<template v-else>
  <div class="d-flex align-items-center flex-grow-1 me-3">
    <input
      type="checkbox"
      class="form-check-input me-3"
      :checked="task.completed"
      @change="toggleTask(task)"
      :aria-label="`Concluir tarefa: ${task.title}`"
    />

    <span
      :class="{ 'text-decoration-line-through text-secondary': task.completed }"
    >
      {{ task.title }}
    </span>
  </div>
</template>
        
        <template v-if="editingTask?.id === task.id">
  <button
    class="btn btn-success btn-sm me-2"
    @click="saveTask"
  >
    Salvar
  </button>

  <button
    class="btn btn-secondary btn-sm me-2"
    @click="cancelEdit"
  >
    Cancelar
  </button>
</template>

<template v-else>
  <button
    class="btn btn-outline-warning btn-sm me-2"
    @click="editTask(task)"
  >
    ✏️
  </button>

  <button
    class="btn btn-outline-danger btn-sm"
    @click="deleteTask(task)"
  >
    X
  </button>
</template>

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
        originalTitle: ''
    }
},
  mounted() {
    this.loadTasks()
  },
  methods: {
    async loadTasks() {
        const res = await axios.get('/api/tasks')
        this.tasks = res.data
    },

    async addTask() {
    const title = this.newTask.trim()

    if (!title) {
        alert('Digite uma tarefa antes de adicionar.')
        return
    }

    await axios.post('/api/tasks', {
        title: title
    })

    this.newTask = ''
    await this.loadTasks()
},

    async toggleTask(task) {
        await axios.put(`/api/tasks/${task.id}`, {
            completed: !task.completed
        })

        this.loadTasks()
    },

    async deleteTask(task) {
        await axios.delete(`/api/tasks/${task.id}`)
        this.loadTasks()
    },

editTask(task) {
    this.editingTask = { ...task }
    this.originalTitle = task.title
},

async saveTask() {
    const title = this.editingTask.title.trim()

    if (!title) {
        alert('O título da tarefa não pode ficar vazio.')
        return
    }

    await axios.put(`/api/tasks/${this.editingTask.id}`, {
        title: title
    })

    this.editingTask = null
    this.originalTitle = ''
    await this.loadTasks()
},

cancelEdit() {
    this.editingTask = null
    this.originalTitle = ''
}

    }
}
</script>