<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-800">Подносиоци жалбе</h2>
      <p class="text-gray-600 mt-2">Управљајте подносиоцима жалби</p>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
      <div class="flex items-end gap-4">
        <button
          @click="openModal('create')"
          class="bg-blue-800 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-900 transition duration-200 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          <span>Додај подносиоца</span>
        </button>
        <div class="w-64">
          <label class="block text-sm font-medium text-gray-700 mb-2">Претрага</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Име, презиме или ЈМБГ..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
          />
        </div>
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200"
          >
            Ресетуј
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                @click="toggleSort('ime_podnosioca_zalbe')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Име и презиме
                  <span v-if="sortBy === 'ime_podnosioca_zalbe'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th
                @click="toggleSort('jmbg_podnosioca_zalbe')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  JMBG
                  <span v-if="sortBy === 'jmbg_podnosioca_zalbe'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th
                @click="toggleSort('institucija_podnosioca_zalbe')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Институција
                  <span v-if="sortBy === 'institucija_podnosioca_zalbe'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Напомена</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Акције</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="podnosilac in podnosioci" :key="podnosilac.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center text-white font-bold">
                    {{ getInitials(podnosilac) }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ podnosilac.ime_podnosioca_zalbe }} {{ podnosilac.prezime_podnosioca_zalbe }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ podnosilac.jmbg_podnosioca_zalbe }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ podnosilac.institucija_podnosioca_zalbe }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <span class="line-clamp-2">{{ podnosilac.napomena || '-' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="goToZalbe(podnosilac)"
                  class="bg-blue-800 text-white px-3 py-1 rounded-lg hover:bg-blue-900 transition duration-200 mr-2 text-xs"
                >
                  Жалбе
                </button>
                <button
                  @click="openModal('edit', podnosilac)"
                  class="text-purple-600 hover:text-purple-900 mr-3"
                >
                  Измени
                </button>
                <button
                  @click="deletePodnosilac(podnosilac.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Обриши
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
        <div class="text-sm text-gray-700">
          Приказано <span class="font-medium">{{ from }}-{{ to }}</span> од <span class="font-medium">{{ total }}</span> укупно
        </div>
        <div class="flex space-x-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Претходна
          </button>
          <span class="px-3 py-1 text-sm text-gray-700">
            Страна {{ currentPage }} од {{ lastPage }}
          </span>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Следећа
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <teleport to="body">
      <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800">
              {{ modalMode === 'create' ? 'Додај новог подносиоца' : 'Измени подносиоца' }}
            </h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Име *</label>
                <input
                  v-model="form.ime_podnosioca_zalbe"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Презиме *</label>
                <input
                  v-model="form.prezime_podnosioca_zalbe"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">JMBG</label>
                <input
                  v-model="form.jmbg_podnosioca_zalbe"
                  type="text"
                  maxlength="13"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Институција</label>
                <v-select
                  v-model="form.institucija_podnosioca_zalbe"
                  :options="organi"
                  :reduce="organ => organ.organ"
                  label="organ"
                  placeholder="Изаберите институцију"
                  class="vue-select-custom"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Напомена</label>
              <textarea
                v-model="form.napomena"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
              >
                Откажи
              </button>
              <button
                type="submit"
                class="px-6 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900"
              >
                {{ modalMode === 'create' ? 'Додај' : 'Сачувај' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>

    <!-- Delete Confirmation Modal -->
    <teleport to="body">
      <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
          <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Потврда брисања</h3>
            <p class="text-gray-600 text-center mb-6">Да ли сте сигурни да желите да обришете овог подносиоца?</p>
            <div class="flex gap-3">
              <button
                @click="cancelDelete"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200"
              >
                Откажи
              </button>
              <button
                @click="confirmDelete"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200"
              >
                Обриши
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Toast Notification -->
    <teleport to="body">
      <transition
        enter-active-class="transform transition duration-300 ease-out"
        enter-from-class="translate-x-full opacity-0"
        enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transform transition duration-200 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0"
      >
        <div
          v-if="showToast"
          class="fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg pointer-events-auto"
          :class="toastType === 'success' ? 'bg-green-500' : 'bg-red-500'"
        >
          <div class="flex items-center p-4">
            <div class="flex-shrink-0">
              <svg v-if="toastType === 'success'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-white">{{ toastMessage }}</p>
            </div>
            <button @click="showToast = false" class="ml-4 flex-shrink-0 text-white hover:text-gray-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { useSorting } from '../composables/useSorting';

const router = useRouter();

const podnosioci = ref([]);
const searchQuery = ref('');
const showModal = ref(false);
const modalMode = ref('create');
const organi = ref([]);

// Delete confirmation modal
const showDeleteConfirm = ref(false);
const deleteTargetId = ref(null);

// Toast notifications
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Pagination
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const from = ref(0);
const to = ref(0);

// Sorting
const { sortBy, sortDirection, toggleSort, getSortParams } = useSorting((page) => fetchPodnosioci(page));

const form = ref({
  ime_podnosioca_zalbe: '',
  prezime_podnosioca_zalbe: '',
  jmbg_podnosioca_zalbe: '',
  institucija_podnosioca_zalbe: '',
  napomena: ''
});

const filteredPodnosioci = computed(() => {
  let result = podnosioci.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(p =>
      p.ime_podnosioca_zalbe?.toLowerCase().includes(query) ||
      p.prezime_podnosioca_zalbe?.toLowerCase().includes(query) ||
      p.jmbg_podnosioca_zalbe?.includes(query)
    );
  }

  return result;
});

const getInitials = (podnosilac) => {
  const ime = podnosilac.ime_podnosioca_zalbe?.[0] || '';
  const prezime = podnosilac.prezime_podnosioca_zalbe?.[0] || '';
  return (ime + prezime).toUpperCase();
};

const openModal = (mode, podnosilac = null) => {
  modalMode.value = mode;
  if (mode === 'edit' && podnosilac) {
    form.value = { ...podnosilac };
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
    ime_podnosioca_zalbe: '',
    prezime_podnosioca_zalbe: '',
    jmbg_podnosioca_zalbe: '',
    institucija_podnosioca_zalbe: '',
    napomena: ''
  };
};

const resetFilters = () => {
  searchQuery.value = '';
  fetchPodnosioci(1);
};

const goToZalbe = (podnosilac) => {
  router.push({
    name: 'Zalbe',
    query: { podnosilac_id: podnosilac.id }
  });
};

const submitForm = async () => {
  try {
    const isCreate = modalMode.value === 'create';
    if (isCreate) {
      await axios.post('/podnosioci-zalbe', form.value);
    } else {
      await axios.put(`/podnosioci-zalbe/${form.value.id}`, form.value);
    }
    await fetchPodnosioci();
    closeModal();
    showToastNotification(isCreate ? 'Подносилац успешно додат!' : 'Подносилац успешно измењен!', 'success');
  } catch (error) {
    console.error('Error saving podnosilac:', error);
    showToastNotification('Грешка приликом чувања података.', 'error');
  }
};

const deletePodnosilac = (id) => {
  deleteTargetId.value = id;
  showDeleteConfirm.value = true;
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/podnosioci-zalbe/${deleteTargetId.value}`);
    await fetchPodnosioci();
    showDeleteConfirm.value = false;
    deleteTargetId.value = null;
    showToastNotification('Подносилац успешно обрисан!', 'success');
  } catch (error) {
    console.error('Error deleting podnosilac:', error);
    showToastNotification('Грешка приликом брисања.', 'error');
  }
};

const cancelDelete = () => {
  showDeleteConfirm.value = false;
  deleteTargetId.value = null;
};

const showToastNotification = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  setTimeout(() => {
    showToast.value = false;
  }, 3000);
};

const fetchPodnosioci = async (page = 1) => {
  try {
    const response = await axios.get('/podnosioci-zalbe', {
      params: {
        page,
        search: searchQuery.value,
        ...getSortParams()
      }
    });
    podnosioci.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    total.value = response.data.total;
    from.value = response.data.from;
    to.value = response.data.to;
  } catch (error) {
    console.error('Error fetching podnosioci:', error);
    showToastNotification('Грешка при учитавању подносилаца. Молимо покушајте поново.', 'error');
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchPodnosioci(page);
  }
};

const fetchOrgani = async () => {
  try {
    const response = await axios.get('/sifarnik-organi');
    organi.value = response.data;
  } catch (error) {
    console.error('Error fetching organi:', error);
    showToastNotification('Грешка при учитавању органа. Молимо освежите страницу.', 'error');
  }
};

// Watch search changes to reset to page 1
watch(searchQuery, () => {
  fetchPodnosioci(1);
});

onMounted(() => {
  fetchPodnosioci();
  fetchOrgani();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

:deep(.vue-select-custom .vs__dropdown-toggle) {
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
}

:deep(.vue-select-custom .vs__dropdown-toggle:hover) {
  border-color: #9333ea;
}

:deep(.vue-select-custom .vs__dropdown-toggle:focus-within) {
  border-color: transparent;
  ring: 2px;
  ring-color: #9333ea;
  box-shadow: 0 0 0 2px #9333ea40;
}
</style>
