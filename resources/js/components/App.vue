<template>
  <div class="container mt-5">
    <h1 class="mb-4">📝 To-Do List</h1>

    <form @submit.prevent="addTask" class="mb-3">
      <input
        v-model="newTask"
        class="form-control"
        placeholder="Nova tarefa"
      />
    </form>

    <ul class="list-group">
      <li
        v-for="task in tasks"
        :key="task.id"
        class="list-group-item d-flex justify-content-between align-items-center"
      >
        <span
          @click="toggleTask(task)"
          :class="{ 'text-decoration-line-through': task.completed }"
          style="cursor:pointer"
        >
          {{ task.title }}
        </span>

        <button
          class="btn btn-danger btn-sm"
          @click="deleteTask(task)"
        >
          X
        </button>
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
      newTask: ''
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
      if (!this.newTask) return
      await axios.post('/api/tasks', { title: this.newTask })
      this.newTask = ''
      this.loadTasks()
    },
    async toggleTask(task) {
      await axios.put(`/api/tasks/${task.id}`)
      this.loadTasks()
    },
    async deleteTask(task) {
      await axios.delete(`/api/tasks/${task.id}`)
      this.loadTasks()
    }
  }
}
</script>
