<template>
  <div class="space-y-6">
    <!-- Header with Add button -->
    <div class="bg-white rounded-lg shadow-sm p-6">
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Корисници</h2>
        <button
          @click="openModal('add')"
          class="bg-blue-800 text-white px-4 py-2 rounded-lg hover:bg-blue-900 transition duration-200"
        >
          Додај корисника
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Претрага</label>
          <input
            v-model="filters.search"
            @input="fetchКорисници(1)"
            type="text"
            placeholder="Име или емаил..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Улога</label>
          <select
            v-model="filters.role"
            @change="fetchКорисници(1)"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
          >
            <option value="">Све улоге</option>
            <option value="admin">Admin</option>
            <option value="user">Korisnik</option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition duration-200"
          >
            Ресетуј филтере
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-purple-50 to-pink-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                Име
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                Улога
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                Датум креирања
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                Акције
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="korisnik in korisnici.data" :key="korisnik.id" class="hover:bg-gray-50 transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ korisnik.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-600">{{ korisnik.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="korisnik.role === 'admin'
                    ? 'bg-slate-100 text-purple-800'
                    : 'bg-gray-100 text-gray-800'"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                  {{ korisnik.role === 'admin' ? 'Admin' : 'Korisnik' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                {{ formatDate(korisnik.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button
                  @click="openModal('edit', korisnik)"
                  class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 transition duration-200 mr-2 text-xs"
                >
                  Измени
                </button>
                <button
                  @click="confirmDelete(korisnik)"
                  class="bg-gradient-to-r from-red-600 to-pink-600 text-white px-3 py-1 rounded-lg hover:from-red-700 hover:to-pink-700 transition duration-200 text-xs"
                  :disabled="korisnik.id === currentUser.id"
                  :class="{ 'opacity-50 cursor-not-allowed': korisnik.id === currentUser.id }"
                >
                  Обриши
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Prikazano <span class="font-medium">{{ korisnici.from || 0 }}</span> do
            <span class="font-medium">{{ korisnici.to || 0 }}</span> od
            <span class="font-medium">{{ korisnici.total || 0 }}</span> rezultata
          </div>
          <div class="flex gap-2">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="fetchКорисници(page)"
              :class="page === korisnici.current_page
                ? 'bg-purple-600 text-white'
                : 'bg-white text-gray-700 hover:bg-gray-100'"
              class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium transition duration-150"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <teleport to="body">
      <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-blue-800 text-white p-6 rounded-t-xl">
            <h3 class="text-2xl font-bold">
              {{ modalMode === 'add' ? 'Додај новог корисника' : 'Измени корисника' }}
            </h3>
          </div>

          <div class="p-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Име *</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  placeholder="Унесите име"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  placeholder="primer@email.com"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Шифра {{ modalMode === 'edit' ? '(оставите празно да не мењате)' : '*' }}
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  placeholder="••••••••"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Улога *</label>
                <select
                  v-model="form.role"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                >
                  <option value="">Изаберите улогу</option>
                  <option value="admin">Admin</option>
                  <option value="user">Korisnik</option>
                </select>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                @click="closeModal"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200"
              >
                Откажи
              </button>
              <button
                @click="saveKorisnik"
                class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition duration-200"
              >
                {{ modalMode === 'add' ? 'Додај' : 'Сачувај' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Delete Confirmation Modal -->
    <teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Потврда брисања</h3>
            <p class="text-gray-600 mb-6">
              Да ли сте сигурни да желите да обришете корисника <strong>{{ korisnikToDelete?.name }}</strong>?
              Ова акција се не може поништити.
            </p>
            <div class="flex justify-end space-x-3">
              <button
                @click="showDeleteModal = false"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200"
              >
                Откажи
              </button>
              <button
                @click="deleteKorisnik"
                class="px-4 py-2 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 transition duration-200"
              >
                Обриши
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const currentUser = computed(() => authStore.user);

const korisnici = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

const filters = ref({
  search: '',
  role: ''
});

const showModal = ref(false);
const modalMode = ref('add');
const form = ref({
  name: '',
  email: '',
  password: '',
  role: ''
});

const showDeleteModal = ref(false);
const korisnikToDelete = ref(null);

const fetchКорисници = async (page = 1) => {
  try {
    const params = {
      page,
      search: filters.value.search || undefined,
      role: filters.value.role || undefined
    };
    const response = await axios.get('/korisnici', { params });
    korisnici.value = response.data;
  } catch (error) {
    console.error('Error fetching korisnici:', error);
    if (error.response?.status === 403) {
      alert('Nemate pristup ovoj stranici. Potrebne su admin privilegije.');
    }
  }
};

const resetFilters = () => {
  filters.value = {
    search: '',
    role: ''
  };
  fetchКорисници(1);
};

const openModal = (mode, korisnik = null) => {
  modalMode.value = mode;
  if (mode === 'edit' && korisnik) {
    form.value = {
      id: korisnik.id,
      name: korisnik.name,
      email: korisnik.email,
      password: '',
      role: korisnik.role
    };
  } else {
    resetForm();
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    password: '',
    role: ''
  };
};

const saveKorisnik = async () => {
  try {
    if (modalMode.value === 'add') {
      await axios.post('/korisnici', form.value);
    } else {
      await axios.put(`/korisnici/${form.value.id}`, form.value);
    }
    closeModal();
    fetchКорисници(korisnici.value.current_page);
  } catch (error) {
    console.error('Error saving korisnik:', error);
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      alert(errors.join('\n'));
    } else {
      alert('Greška pri čuvanju korisnika.');
    }
  }
};

const confirmDelete = (korisnik) => {
  korisnikToDelete.value = korisnik;
  showDeleteModal.value = true;
};

const deleteKorisnik = async () => {
  try {
    await axios.delete(`/korisnici/${korisnikToDelete.value.id}`);
    showDeleteModal.value = false;
    korisnikToDelete.value = null;
    fetchКорисници(korisnici.value.current_page);
  } catch (error) {
    console.error('Error deleting korisnik:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    } else {
      alert('Greška pri brisanju korisnika.');
    }
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('sr-RS');
};

const visiblePages = computed(() => {
  const pages = [];
  const current = korisnici.value.current_page;
  const last = korisnici.value.last_page;

  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i);
  }

  return pages;
});

onMounted(() => {
  fetchКорисници();
});
</script>
