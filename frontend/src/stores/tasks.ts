import { ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from '@/services/api'
import type { ApiDataResponse, Task, TaskFilters, TaskPayload } from '@/types/api'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const isLoading = ref(false)

  async function fetchTasks(filters: TaskFilters = {}): Promise<void> {
    isLoading.value = true

    try {
      const { data } = await api.get<ApiDataResponse<Task[]>>('/tasks', {
        params: filters,
      })
      tasks.value = data.data
    } finally {
      isLoading.value = false
    }
  }

  async function createTask(payload: TaskPayload): Promise<Task> {
    const { data } = await api.post<ApiDataResponse<Task>>('/tasks', payload)
    tasks.value = [data.data, ...tasks.value]
    return data.data
  }

  async function updateTask(taskId: number, payload: TaskPayload): Promise<Task> {
    const { data } = await api.put<ApiDataResponse<Task>>(`/tasks/${taskId}`, payload)
    tasks.value = tasks.value.map((task) => (task.id === taskId ? data.data : task))
    return data.data
  }

  async function deleteTask(taskId: number): Promise<void> {
    await api.delete(`/tasks/${taskId}`)
    tasks.value = tasks.value.filter((task) => task.id !== taskId)
  }

  return {
    tasks,
    isLoading,
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
  }
})
