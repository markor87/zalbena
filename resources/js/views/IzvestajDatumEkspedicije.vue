<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-800">Датум експедиције решених жалби</h2>
      <p class="text-gray-600 mt-2">Преглед решених жалби са датумом експедиције</p>
    </div>

    <!-- Search, Filters and Export -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
      <div class="flex items-end gap-4 mb-4">
        <div class="w-80">
          <label class="block text-sm font-medium text-gray-700 mb-2">Претрага</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Име, презиме, пријемни број, број решења..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
          />
        </div>
        <div class="flex items-end gap-2">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200"
          >
            Resetuj
          </button>
          <button
            @click="openAdvancedSearch"
            class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition duration-200 flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Напредна претрага
          </button>
        </div>
      </div>

      <div class="flex gap-2">
        <button
          @click="exportExcel"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export Excel
        </button>
        <button
          @click="exportPdf"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          Export PDF
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Име и презиме</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Институција</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пријемни број</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Број решења</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Датум експедиције</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="data.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                Нема резултата
              </td>
            </tr>
            <tr v-for="(item, index) in data" :key="index" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.ime_i_prezime || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.institucija_podnosioca_zalbe || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.prijemni_broj || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.broj_resenja || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(item.datum_ekspedicije_ds_organu) }}
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

    <!-- Advanced Search Modal -->
    <teleport to="body">
      <div v-if="showAdvancedSearch" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-gray-800">Напредна претрага</h3>
            <button @click="closeAdvancedSearch" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div v-for="(filter, index) in advancedFilters" :key="index" class="flex gap-3 items-start">
              <!-- Field Select -->
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Поље</label>
                <select
                  v-model="filter.field"
                  @change="onFieldChange(index)"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                >
                  <option value="">Изаберите поље</option>
                  <option value="ime_i_prezime">Име и презиме</option>
                  <option value="institucija_podnosioca_zalbe">Институција</option>
                  <option value="prijemni_broj">Пријемни број</option>
                  <option value="broj_resenja">Број решења</option>
                  <option value="datum_ekspedicije_ds_organu">Датум експедиције</option>
                </select>
              </div>

              <!-- Operator Select -->
              <div class="flex-1" v-if="filter.field">
                <label class="block text-sm font-medium text-gray-700 mb-2">Оператор</label>
                <select
                  v-model="filter.operator"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                >
                  <!-- Text operators -->
                  <template v-if="getFieldType(filter.field) === 'text'">
                    <option value="equals">Једнак</option>
                    <option value="not_equals">Није једнак</option>
                    <option value="contains">Садржи</option>
                    <option value="starts_with">Почиње са</option>
                    <option value="ends_with">Завршава се са</option>
                  </template>
                  <!-- Date operators -->
                  <template v-else-if="getFieldType(filter.field) === 'date'">
                    <option value="equals">Једнак</option>
                    <option value="not_equals">Није једнак</option>
                    <option value="between">Између</option>
                    <option value="greater_than">Већи од</option>
                    <option value="less_than">Мањи од</option>
                    <option value="greater_or_equal">Већи или једнак</option>
                    <option value="less_or_equal">Мањи или једнак</option>
                    <option value="is_null">Празан</option>
                    <option value="is_not_null">Није празан</option>
                  </template>
                </select>
              </div>

              <!-- Value Input(s) -->
              <div class="flex-1" v-if="filter.field && filter.operator && !['is_null', 'is_not_null'].includes(filter.operator)">
                <label class="block text-sm font-medium text-gray-700 mb-2">Вредност</label>
                <VueDatePicker
                  v-if="getFieldType(filter.field) === 'date'"
                  v-model="filter.value"
                  format="dd.MM.yyyy"
                  :enable-time-picker="false"
                  text-input
                  auto-apply
                  :teleport="true"
                  input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
                <input
                  v-else
                  v-model="filter.value"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
              </div>

              <!-- Value2 for Between -->
              <div class="flex-1" v-if="filter.operator === 'between'">
                <label class="block text-sm font-medium text-gray-700 mb-2">До</label>
                <VueDatePicker
                  v-model="filter.value2"
                  format="dd.MM.yyyy"
                  :enable-time-picker="false"
                  text-input
                  auto-apply
                  :teleport="true"
                  input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
              </div>

              <!-- Remove Button -->
              <div class="pt-7">
                <button
                  @click="removeFilter(index)"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                  title="Уклони филтер"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Add Filter Button -->
            <button
              @click="addFilter"
              class="w-full py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-700 transition duration-200 flex items-center justify-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Додај филтер
            </button>
          </div>

          <!-- Footer -->
          <div class="p-6 border-t border-gray-200 flex justify-between">
            <button
              @click="resetAdvancedFilters"
              class="px-6 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition duration-200"
            >
              Ресетуј све
            </button>
            <div class="flex gap-3">
              <button
                @click="closeAdvancedSearch"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200"
              >
                Откажи
              </button>
              <button
                @click="applyAdvancedSearch"
                class="px-6 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition duration-200"
              >
                Примени
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
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const data = ref([]);
const searchQuery = ref('');
const showAdvancedSearch = ref(false);
const advancedFilters = ref([]);
const activeAdvancedFilters = ref([]);

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

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('sr-RS');
};

const resetFilters = () => {
  searchQuery.value = '';
  activeAdvancedFilters.value = [];
  advancedFilters.value = [];
  fetchData(1);
};

// Advanced Search functions
const openAdvancedSearch = () => {
  advancedFilters.value = [{ field: '', operator: '', value: '', value2: '' }];
  showAdvancedSearch.value = true;
};

const closeAdvancedSearch = () => {
  showAdvancedSearch.value = false;
};

const addFilter = () => {
  advancedFilters.value.push({ field: '', operator: '', value: '', value2: '' });
};

const removeFilter = (index) => {
  advancedFilters.value.splice(index, 1);
};

const getFieldType = (field) => {
  const dateFields = ['datum_ekspedicije_ds_organu'];
  return dateFields.includes(field) ? 'date' : 'text';
};

const onFieldChange = (index) => {
  advancedFilters.value[index].operator = '';
  advancedFilters.value[index].value = '';
  advancedFilters.value[index].value2 = '';
};

const applyAdvancedSearch = () => {
  activeAdvancedFilters.value = advancedFilters.value.filter(f => f.field && f.operator);
  showAdvancedSearch.value = false;
  fetchData(1);
};

const resetAdvancedFilters = () => {
  advancedFilters.value = [{ field: '', operator: '', value: '', value2: '' }];
  activeAdvancedFilters.value = [];
};

const showToastNotification = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  setTimeout(() => {
    showToast.value = false;
  }, 3000);
};

const fetchData = async (page = 1) => {
  try {
    const params = {
      page,
      search: searchQuery.value
    };

    // Add advanced filters if they exist
    if (activeAdvancedFilters.value.length > 0) {
      params.advanced_filters = activeAdvancedFilters.value;
    }

    const response = await axios.get('/izvestaj-datum-ekspedicije', { params });
    data.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    total.value = response.data.total;
    from.value = response.data.from;
    to.value = response.data.to;
  } catch (error) {
    console.error('Error fetching data:', error);
    showToastNotification('Грешка приликом учитавања података.', 'error');
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchData(page);
  }
};

const exportExcel = () => {
  const params = new URLSearchParams({
    search: searchQuery.value || ''
  });

  if (activeAdvancedFilters.value.length > 0) {
    params.append('advanced_filters', JSON.stringify(activeAdvancedFilters.value));
  }

  const baseURL = window.axios.defaults.baseURL || '/api';
  window.location.href = `${baseURL}/izvestaj-datum-ekspedicije/export-excel?${params.toString()}`;
  showToastNotification('Excel извештај се преузима...', 'success');
};

const exportPdf = () => {
  const params = new URLSearchParams({
    search: searchQuery.value || ''
  });

  if (activeAdvancedFilters.value.length > 0) {
    params.append('advanced_filters', JSON.stringify(activeAdvancedFilters.value));
  }

  const baseURL = window.axios.defaults.baseURL || '/api';
  window.location.href = `${baseURL}/izvestaj-datum-ekspedicije/export-pdf?${params.toString()}`;
  showToastNotification('PDF извештај се преузима...', 'success');
};

// Watch search changes to reset to page 1
watch(searchQuery, () => {
  fetchData(1);
});

onMounted(() => {
  fetchData();
});
</script>
