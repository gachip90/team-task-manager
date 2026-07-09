export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user'
}

export type TaskStatus = 'todo' | 'in_progress' | 'done'

export interface Task {
  id: number
  title: string
  description: string
  status: TaskStatus
  assigned_to: number
  due_date: string | null
  created_at: string
  updated_at: string
  assignee?: User
}

export interface LoginPayload {
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  token_type: 'Bearer'
  user: User
}

export interface TaskPayload {
  title: string
  description: string
  status: TaskStatus
  assigned_to?: number
  due_date: string | null
}

export interface ApiDataResponse<T> {
  data: T
  message?: string
}

export interface TaskFilters {
  status?: TaskStatus
  assigned_to?: number
}
