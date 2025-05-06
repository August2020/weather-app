<template>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Lista klientów</h1>

        <div v-if="loading">Ładowanie klientów...</div>
        <div v-else-if="error" class="text-red-500">{{ error }}</div>

        <div v-else>
            <table class="min-w-full table-auto border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Nazwa</th>
                        <th class="border px-4 py-2 text-left">Miasto</th>
                        <th class="border px-4 py-2 text-left">Temperatura</th>
                        <th class="border px-4 py-2 text-left">Opis</th>
                        <th class="border px-4 py-2 text-left">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="client in clients" :key="client.id">
                        <td class="border px-4 py-2">{{ client.name }}</td>
                        <td class="border px-4 py-2">{{ client.city }}</td>
                        <td class="border px-4 py-2">
                            <span v-if="client.loading">Ładowanie...</span>
                            <span v-else-if="client.error" class="text-red-500">{{ client.error }}</span>
                            <span v-else-if="client.weather">{{ client.weather.temperature }}°C</span>
                            <span v-else>-</span>
                        </td>
                        <td class="border px-4 py-2">
                            <span v-if="client.weather">{{ client.weather.weather_description }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="border px-4 py-2">
                            <button @click="fetchWeather(client)"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Odśwież pogodę
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
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

        const fetchClients = async () => {
            try {
                const res = await axios.get('http://localhost:8000/api/clients')
                clients.value = res.data.map((c: Client) => ({
                    ...c,
                    weather: null,
                    loading: false,
                    error: '',
                }))
            } catch (err) {
                error.value = 'Nie udało się pobrać listy klientów.'
            } finally {
                loading.value = false
            }
        }

        const fetchWeather = async (client: Client) => {
            client.loading = true
            client.error = ''

            try {
                const res = await axios.get(
                    `http://localhost:8000/api/weather?city=${encodeURIComponent(client.city)}`
                )

                client.weather = {
                    temperature: res.data.temperature,
                    weather_description: res.data.weather_description,
                }
            } catch (err) {
                client.error = 'Błąd pobierania pogody.'
            } finally {
                client.loading = false
            }
        }

        onMounted(fetchClients)

        return {
            clients,
            loading,
            error,
            fetchWeather,
        }
    },
})
</script>