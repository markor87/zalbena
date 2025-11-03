<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-800">Извештај: Списак нерешених жалби</h2>
      <p class="text-gray-600 mt-2">Преглед свих жалби са статусом Нерешен или Упућен на допуну"</p>
    </div>

    <!-- Search and Export -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
      <div class="flex items-center gap-4">
        <div class="w-80">
          <label class="block text-sm font-medium text-gray-700 mb-2">Pretraga</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Ime, prezime, prijemni broj, broj rešenja..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
          />
        </div>
        <div class="flex items-end gap-2">
          <button
            @click="resetSearch"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200"
          >
            Resetuj
          </button>
          <button
            @click="exportExcel"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2"
            title="Izvezi u Excel"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Excel
          </button>
          <button
            @click="exportPdf"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 flex items-center gap-2"
            title="Izvezi u PDF"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            PDF
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institucija</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ime</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prezime</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prijemni broj</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Broj rešenja</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum prijema</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum rešavanja</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum isticanja</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Osnov žalbe</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-for="(group, institucija) in groupedResults" :key="institucija">
              <tr
                v-for="(item, index) in group"
                :key="item.prijemni_broj"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span v-if="index === 0">
                    {{ institucija || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.ime_podnosioca_zalbe || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ item.prezime_podnosioca_zalbe || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">
                  {{ item.prijemni_broj }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.broj_resenja || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(item.datum_prijema_zalbe) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(item.datum_resavanja_na_zk) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(item.datum_isticanja_donosenje) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                  {{ item.osnov_zalbe || '-' }}
                </td>
              </tr>

              <!-- Total row for this institution -->
              <tr class="bg-gray-50">
                <td class="px-6 py-3 font-bold text-gray-900">
                  Укупно: {{ group.length }}
                </td>
                <td colspan="8"></td>
              </tr>
            </template>

            <tr v-if="results.length === 0">
              <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                Nema rezultata za prikaz
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const results = ref([]);
const searchQuery = ref('');

// Group results by institution
const groupedResults = computed(() => {
  const groups = {};
  results.value.forEach(item => {
    const institucija = item.institucija_podnosioca_zalbe || '-';
    if (!groups[institucija]) {
      groups[institucija] = [];
    }
    groups[institucija].push(item);
  });
  return groups;
});

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('sr-RS');
};

const getStatusClass = (status) => {
  if (status === 'Нерешен') {
    return 'bg-red-100 text-red-800';
  } else if (status === 'Упућен на допуну') {
    return 'bg-orange-100 text-orange-800';
  }
  return 'bg-gray-100 text-gray-800';
};

const fetchResults = async () => {
  try {
    const response = await axios.get('/izvestaj-neresene-zalbe', {
      params: {
        search: searchQuery.value
      }
    });
    results.value = response.data;
  } catch (error) {
    console.error('Error fetching report:', error);
  }
};

const resetSearch = () => {
  searchQuery.value = '';
};

const exportExcel = () => {
  const params = new URLSearchParams();
  if (searchQuery.value) {
    params.append('search', searchQuery.value);
  }
  const url = `/api/izvestaj-neresene-zalbe/export-excel?${params.toString()}`;
  window.open(url, '_blank');
};

const exportPdf = () => {
  const params = new URLSearchParams();
  if (searchQuery.value) {
    params.append('search', searchQuery.value);
  }
  const url = `/api/izvestaj-neresene-zalbe/export-pdf?${params.toString()}`;
  window.open(url, '_blank');
};

// Watch search query changes
watch(searchQuery, () => {
  fetchResults();
});

onMounted(() => {
  fetchResults();
});
</script>
