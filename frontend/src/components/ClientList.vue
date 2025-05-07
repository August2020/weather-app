<template>
    <div class="container bg-white py-4">
        <h1 class="mb-4">Lista klientów</h1>

        <div v-if="loading" class="alert alert-info">Ładowanie klientów...</div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div class="mb-3">
            <button class="btn btn-primary" @click="showAddModal = true">Dodaj klienta</button>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Nazwa</th>
                    <th>Miasto</th>
                    <th>Temperatura</th>
                    <th>Opis</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="client in clients" :key="client.id">
                    <td>{{ client.name }}</td>
                    <td>{{ client.city }}</td>
                    <td>
                        <span v-if="client.loading">Ładowanie...</span>
                        <span v-else-if="client.error" class="text-danger">{{ client.error }}</span>
                        <span v-else-if="client.weather">{{ client.weather.temperature }}°C</span>
                        <span v-else>-</span>
                    </td>
                    <td>
                        <span v-if="client.weather">{{ client.weather.weather_description }}</span>
                        <span v-else>-</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info me-1" @click="fetchWeather(client)">🌦</button>
                        <button class="btn btn-sm btn-warning me-1" @click="editClient(client)">Edytuj</button>
                        <button class="btn btn-sm btn-danger" @click="deleteClient(client.id)">Usuń</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Modal dodawania klienta -->
        <div class="modal fade show d-block" tabindex="-1" v-if="showAddModal" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="addClient">
                        <div class="modal-header">
                            <h5 class="modal-title">Dodaj klienta</h5>
                            <button type="button" class="btn-close" @click="showAddModal = false"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nazwa</label>
                                <input v-model="newClient.name" type="text" class="form-control" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Miasto</label>
                                <input v-model="newClient.city" type="text" class="form-control" required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                @click="showAddModal = false">Anuluj</button>
                            <button type="submit" class="btn btn-primary">Dodaj</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal edycji klienta -->
        <div class="modal fade show d-block" tabindex="-1" v-if="showEditModal" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="updateClient">
                        <div class="modal-header">
                            <h5 class="modal-title">Edytuj klienta</h5>
                            <button type="button" class="btn-close" @click="showEditModal = false"></button>
                        </div>
                        <div class="modal-body" v-if="editingClient">
                            <div class="mb-3">
                                <label class="form-label">Nazwa</label>
                                <input v-model="editingClient.name" type="text" class="form-control" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Miasto</label>
                                <input v-model="editingClient.city" type="text" class="form-control" required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                @click="showEditModal = false">Anuluj</button>
                            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue'
import axios from 'axios'

interface Client {
    id: number
    name: string
    city: string
    weather?: {
        temperature: number
        weather_description: string
    }
    loading?: boolean
    error?: string
}

export default defineComponent({
    name: 'ClientList',
    setup() {
        const clients = ref<Client[]>([])
        const loading = ref(true)
        const error = ref<string | null>(null)

        const showAddModal = ref(false)
        const newClient = ref({ name: '', city: '' })

        const showEditModal = ref(false)
        const editingClient = ref<Client | null>(null)

        const fetchClients = async () => {
            try {
                const res = await axios.get('http://localhost:8000/api/clients')
                clients.value = res.data.map((c: Client) => ({
                    ...c,
                    weather: null,
                    loading: false,
                    error: '',
                }))
            } catch {
                error.value = 'Nie udało się pobrać listy klientów.'
            } finally {
                loading.value = false
            }
        }

        const fetchWeather = async (client: Client) => {
            client.loading = true
            client.error = ''

            try {
                const res = await axios.get(`http://localhost:8000/api/weather?city=${encodeURIComponent(client.city)}`)
                client.weather = {
                    temperature: res.data.temperature,
                    weather_description: res.data.weather_description,
                }
            } catch {
                client.error = 'Błąd pobierania pogody.'
            } finally {
                client.loading = false
            }
        }

        const addClient = async () => {
            try {
                const res = await axios.post('http://localhost:8000/api/clients', newClient.value)
                clients.value.push({ ...res.data, weather: null, loading: false, error: '' })
                showAddModal.value = false
                newClient.value = { name: '', city: '' }
            } catch {
                alert('Nie udało się dodać klienta.')
            }
        }

        const deleteClient = async (id: number) => {
            if (!confirm('Czy na pewno chcesz usunąć klienta?')) return

            try {
                await axios.delete(`http://localhost:8000/api/clients/${id}`)
                clients.value = clients.value.filter(c => c.id !== id)
            } catch {
                alert('Błąd przy usuwaniu klienta.')
            }
        }

        const editClient = (client: Client) => {
            editingClient.value = { ...client }
            showEditModal.value = true
        }

        const updateClient = async () => {
            if (!editingClient.value) return

            try {
                const res = await axios.put(`http://localhost:8000/api/clients/${editingClient.value.id}`, {
                    name: editingClient.value.name,
                    city: editingClient.value.city,
                })

                const index = clients.value.findIndex(c => c.id === editingClient.value?.id)
                if (index !== -1) {
                    clients.value[index] = {
                        ...res.data,
                        weather: clients.value[index].weather,
                        loading: false,
                        error: '',
                    }
                }

                showEditModal.value = false
                editingClient.value = null
            } catch {
                alert('Błąd przy aktualizacji klienta.')
            }
        }

        onMounted(fetchClients)

        return {
            clients,
            loading,
            error,
            fetchWeather,
            showAddModal,
            newClient,
            addClient,
            deleteClient,
            editClient,
            showEditModal,
            editingClient,
            updateClient,
        }
    },
})
</script>