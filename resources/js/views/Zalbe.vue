<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-800">Жалбе</h2>
      <p class="text-gray-600 mt-2">Управљајте жалбама</p>
    </div>

    <!-- Podnosilac Context Card -->
    <div v-if="selectedPodnosilacData" class="bg-slate-50 rounded-xl shadow-md p-6 mb-6 border-2 border-slate-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 bg-blue-700 rounded-full flex items-center justify-center text-white font-bold text-xl">
            {{ selectedPodnosilacData.ime_podnosioca_zalbe?.[0] }}{{ selectedPodnosilacData.prezime_podnosioca_zalbe?.[0] }}
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-800">
              {{ selectedPodnosilacData.ime_podnosioca_zalbe }} {{ selectedPodnosilacData.prezime_podnosioca_zalbe }}
            </h3>
            <div class="flex gap-4 mt-2 text-sm text-gray-600">
              <div v-if="selectedPodnosilacData.jmbg_podnosioca_zalbe">
                <span class="font-medium">JMBG:</span> {{ selectedPodnosilacData.jmbg_podnosioca_zalbe }}
              </div>
              <div v-if="selectedPodnosilacData.institucija_podnosioca_zalbe">
                <span class="font-medium">Институција:</span> {{ selectedPodnosilacData.institucija_podnosioca_zalbe }}
              </div>
            </div>
          </div>
        </div>
        <button
          @click="clearPodnosilacFilter"
          class="text-gray-600 hover:text-gray-800 transition duration-200"
          title="Прикажи све жалбе"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
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
          <span>Додај жалбу</span>
        </button>
        <div class="w-64">
          <label class="block text-sm font-medium text-gray-700 mb-2">Брза претрага</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Пријемни број, број решења..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
          />
        </div>
        <div class="flex items-end gap-2">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200"
          >
            Ресетуј
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
          <button
            @click="exportExcel"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2"
            title="Извези у Excel"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Excel
          </button>
          <button
            @click="exportPdf"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 flex items-center gap-2"
            title="Извези у PDF"
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Акције</th>
              <th
                @click="toggleSort('prijemni_broj')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Пријемни број
                  <span v-if="sortBy === 'prijemni_broj'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Подносилац жалбе</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Институција</th>
              <th
                @click="toggleSort('datum_prijema_zalbe')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Датум пријема
                  <span v-if="sortBy === 'datum_prijema_zalbe'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Основ жалбе</th>
              <th
                @click="toggleSort('broj_resenja')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Број решења
                  <span v-if="sortBy === 'broj_resenja'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th
                @click="toggleSort('status_zalbe')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Статус
                  <span v-if="sortBy === 'status_zalbe'" class="text-blue-800">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="zalba in zalbe" :key="zalba.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="viewDetails(zalba)"
                  class="text-blue-600 hover:text-blue-700 mr-3"
                >
                  Детаљи
                </button>
                <button
                  @click="openModal('edit', zalba)"
                  class="text-purple-600 hover:text-purple-900 mr-3"
                >
                  Измени
                </button>
                <button
                  @click="deleteZalba(zalba.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Обриши
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ zalba.prijemni_broj }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ zalba.podnosilac ? `${zalba.podnosilac.ime_podnosioca_zalbe} ${zalba.podnosilac.prezime_podnosioca_zalbe}` : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ zalba.podnosilac?.institucija_podnosioca_zalbe || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(zalba.datum_prijema_zalbe) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{
                  typeof zalba.osnov_zalbe === 'object' && zalba.osnov_zalbe !== null
                    ? zalba.osnov_zalbe.osnov_zalbe
                    : zalba.osnov_zalbe || '-'
                }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ zalba.broj_resenja || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(zalba.status_zalbe)]">
                  {{ zalba.status_zalbe }}
                </span>
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

    <!-- Modal for Create/Edit -->
    <teleport to="body">
      <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800">
              {{ modalMode === 'create' ? 'Додај нову жалбу' : 'Измени жалбу' }}
            </h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            <!-- SEKCIJA 1: Основни подаци жалбе -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Основни подаци жалбе</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Подносилац жалбе *</label>
                  <v-select
                    v-model="form.podnosioci_zalbe"
                    :options="podnosioci"
                    :reduce="p => p.id"
                    :get-option-label="p => `${p.ime_podnosioca_zalbe} ${p.prezime_podnosioca_zalbe}`"
                    @search="searchPodnosioci"
                    placeholder="Изаберите подносиоца"
                    class="vue-select-custom"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Пријемни број *</label>
                  <input
                    v-model="form.prijemni_broj"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Датум пријема жалбе *</label>
                  <VueDatePicker
                    v-model="form.datum_prijema_zalbe"
                    format="dd.MM.yyyy"
                    :enable-time-picker="false"
                    text-input
                    auto-apply
                    :required="true"
                    input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Број решења</label>
                  <input
                    v-model="form.broj_resenja"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Основ жалбе</label>
                  <v-select
                    v-model="form.osnov_zalbe"
                    :options="osnoviZalbe"
                    :reduce="o => o.id"
                    label="osnov_zalbe"
                    placeholder="Изаберите основ"
                    class="vue-select-custom"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Датум враћања на допуну</label>
                  <VueDatePicker
                    v-model="form.datum_vracanja_na_dopunu"
                    format="dd.MM.yyyy"
                    :enable-time-picker="false"
                    text-input
                    auto-apply
                    input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Рок за допуну</label>
                  <VueDatePicker
                    v-model="form.rok_za_dopunu"
                    format="dd.MM.yyyy"
                    :enable-time-picker="false"
                    text-input
                    auto-apply
                    input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Датум пријема допуне</label>
                  <VueDatePicker
                    v-model="form.datum_prijema_dopune"
                    format="dd.MM.yyyy"
                    :enable-time-picker="false"
                    text-input
                    auto-apply
                    input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>
              </div>

              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Напомена</label>
                <textarea
                  v-model="form.napomena"
                  rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                ></textarea>
              </div>
            </div>

            <!-- EDIT MODE ONLY SECTIONS -->
            <template v-if="modalMode === 'edit'">
              <!-- SEKCIJA 2: Komisija i upravni sud -->
              <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Komisija i upravni sud</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум предаје комисији</label>
                    <VueDatePicker
                      v-model="form.datum_predaje_komisiji"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум решавања на ЗК</label>
                    <VueDatePicker
                      v-model="form.datum_resavanja_na_zk"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум експедиције ДС органу</label>
                    <VueDatePicker
                      v-model="form.datum_ekspedicije_ds_organu"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Известилац са жалбама</label>
                    <v-select
                      v-model="form.izvestilac_sa_zalbama"
                      :options="clanoviKomisije"
                      :reduce="c => c.id"
                      :get-option-label="c => `${c.ime} ${c.prezime}`"
                      placeholder="Изаберите известиоца"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Комисија ЗКБ</label>
                    <v-select
                      v-model="form.komisije_zkv"
                      :options="clanoviKomisije"
                      :reduce="c => c.id"
                      :get-option-label="c => `${c.ime} ${c.prezime}`"
                      placeholder="Изаберите комисију"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Типови решења</label>
                    <v-select
                      v-model="form.tipovi_resenja"
                      :options="tipoviResenja"
                      :reduce="t => t.id"
                      label="tip_resenja"
                      placeholder="Изаберите тип"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Члан комисије 1</label>
                    <v-select
                      v-model="form.clanovi_komisije1"
                      :options="clanoviKomisije"
                      :reduce="c => `${c.ime} ${c.prezime}`"
                      :get-option-label="c => `${c.ime} ${c.prezime}`"
                      placeholder="Изаберите члана"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Члан комисије 2</label>
                    <v-select
                      v-model="form.clanovi_komisije2"
                      :options="clanoviKomisije"
                      :reduce="c => `${c.ime} ${c.prezime}`"
                      :get-option-label="c => `${c.ime} ${c.prezime}`"
                      placeholder="Изаберите члана"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Статус жалбе</label>
                    <v-select
                      v-model="form.status_zalbe"
                      :options="statusiZalbe"
                      :reduce="s => s.status_zalbe"
                      label="status_zalbe"
                      placeholder="Изаберите статус"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум истицања доношење</label>
                    <VueDatePicker
                      v-model="form.datum_isticanja_donosenje"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      :disabled="true"
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent bg-gray-100"
                    />
                  </div>
                </div>
              </div>

              <!-- SEKCIJA 3: Tužba upućena Upravnom sudu Srbije -->
              <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Tužba upućena Upravnom sudu Srbije</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум пријема тужбе од УС</label>
                    <VueDatePicker
                      v-model="form.datum_prijema_tuzbe_od_us"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум експедиције одговора ЗК</label>
                    <VueDatePicker
                      v-model="form.datum_ekspedicije_odgovora_zk"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум пријема одлуке УС</label>
                    <VueDatePicker
                      v-model="form.datum_prijema_odluke_us"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Тип пресуде УС</label>
                    <v-select
                      v-model="form.tipovi_presude_us"
                      :options="tipoviPresude"
                      :reduce="t => t.id"
                      label="tip_presude"
                      placeholder="Изаберите тип"
                      class="vue-select-custom"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Број одлуке УС</label>
                    <input
                      v-model="form.broj_odluke_us"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум доношења одлуке УС</label>
                    <VueDatePicker
                      v-model="form.datum_donosenja_odluke_us"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Датум решења ЗК по пресуди УС</label>
                    <VueDatePicker
                      v-model="form.datum_resenja_zk_po_presudi_us"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Број решења ZK po presudi US</label>
                    <input
                      v-model="form.broj_resenja_zk_po_presudi_us"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Накнада</label>
                    <input
                      v-model="form.naknada"
                      type="number"
                      step="0.01"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Достављeница</label>
                    <VueDatePicker
                      v-model="form.dostavnica"
                      format="dd.MM.yyyy"
                      :enable-time-picker="false"
                      text-input
                      auto-apply
                      input-class-name="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>
            </template>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
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

    <!-- Advanced Search Modal -->
    <teleport to="body">
      <div v-if="showAdvancedSearch" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
          <!-- Header -->
          <div class="p-6 border-b flex items-center justify-between bg-purple-50">
            <div>
              <h3 class="text-2xl font-bold text-gray-800">Napredna pretraga</h3>
              <p class="text-sm text-gray-600 mt-1">Kreirajte složene filtere za pretragu žalbi</p>
            </div>
            <button @click="closeAdvancedSearch" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto p-6">
            <!-- Filters -->
            <div class="space-y-4">
              <div v-for="(filter, index) in advancedFilters" :key="index" class="flex items-end gap-3 p-4 bg-gray-50 rounded-lg">
                <!-- Field Selection -->
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Поље</label>
                  <select
                    v-model="filter.field"
                    @change="onFieldChange(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  >
                    <option value="">Izaberite polje</option>
                    <option value="prijemni_broj">Пријемни број</option>
                    <option value="broj_resenja">Број решења</option>
                    <option value="podnosioci_zalbe">Подносилац жалбе</option>
                    <option value="institucija">Institucija</option>
                    <option value="datum_prijema_zalbe">Датум пријема жалбе</option>
                    <option value="datum_vracanja_na_dopunu">Датум враћања на допуну</option>
                    <option value="rok_za_dopunu">Рок за допуну</option>
                    <option value="datum_prijema_dopune">Датум пријема допуне</option>
                    <option value="osnov_zalbe">Основ жалбе</option>
                    <option value="napomena">Напомена</option>
                    <option value="datum_predaje_komisiji">Датум предаје комисији</option>
                    <option value="datum_resavanja_na_zk">Датум решавања на ЗК</option>
                    <option value="datum_ekspedicije_ds_organu">Датум експедиције ДС органу</option>
                    <option value="izvestilac_sa_zalbama">Известилац са жалбама</option>
                    <option value="komisije_zkv">Комисија ЗКБ</option>
                    <option value="tipovi_resenja">Типови решења</option>
                    <option value="clanovi_komisije1">Члан комисије 1</option>
                    <option value="clanovi_komisije2">Члан комисије 2</option>
                    <option value="datum_isticanja_donosenje">Датум истицања доношење</option>
                    <option value="status_zalbe">Статус жалбе</option>
                    <option value="datum_prijema_tuzbe_od_us">Датум пријема тужбе од УС</option>
                    <option value="datum_ekspedicije_odgovora_zk">Датум експедиције одговора ЗК</option>
                    <option value="datum_prijema_odluke_us">Датум пријема одлуке УС</option>
                    <option value="tipovi_presude_us">Тип пресуде УС</option>
                    <option value="broj_odluke_us">Број одлуке УС</option>
                    <option value="datum_donosenja_odluke_us">Датум доношења одлуке УС</option>
                    <option value="datum_resenja_zk_po_presudi_us">Датум решења ЗК по пресуди УС</option>
                    <option value="broj_resenja_zk_po_presudi_us">Број решења ZK po presudi US</option>
                    <option value="naknada">Накнада</option>
                    <option value="dostavnica">Достављeница</option>
                  </select>
                </div>

                <!-- Оператор Selection -->
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Оператор</label>
                  <select
                    v-model="filter.operator"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  >
                    <option value="">Izaberite operator</option>
                    <template v-if="getFieldType(filter.field) === 'text'">
                      <option value="equals">Jednak</option>
                      <option value="not_equals">Nije jednak</option>
                      <option value="contains">Sadrži</option>
                      <option value="starts_with">Počinje sa</option>
                      <option value="ends_with">Završava se sa</option>
                    </template>
                    <template v-if="getFieldType(filter.field) === 'date'">
                      <option value="equals">Jednak</option>
                      <option value="not_equals">Nije jednak</option>
                      <option value="between">Između</option>
                      <option value="greater_than">Veći od</option>
                      <option value="less_than">Manji od</option>
                      <option value="greater_or_equal">Veći ili jednak</option>
                      <option value="less_or_equal">Manji ili jednak</option>
                      <option value="is_null">Prazan</option>
                      <option value="is_not_null">Nije prazan</option>
                    </template>
                  </select>
                </div>

                <!-- Value Input -->
                <div class="flex-1" v-if="!['is_null', 'is_not_null'].includes(filter.operator)">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Вредност</label>
                  <VueDatePicker
                    v-if="getFieldType(filter.field) === 'date'"
                    v-model="filter.value"
                    format="dd.MM.yyyy"
                    :enable-time-picker="false"
                    text-input
                    auto-apply
                    :teleport="true"
                    input-class-name="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                  <input
                    v-else
                    v-model="filter.value"
                    type="text"
                    placeholder="Унесите вредност"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <!-- Second Value for "Between" -->
                <div class="flex-1" v-if="filter.operator === 'between'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Do</label>
                  <VueDatePicker
                    v-model="filter.value2"
                    format="dd.MM.yyyy"
                    :enable-time-picker="false"
                    text-input
                    auto-apply
                    :teleport="true"
                    input-class-name="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                  />
                </div>

                <!-- Remove Button -->
                <button
                  @click="removeFilter(index)"
                  class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                  title="Ukloni filter"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Add Filter Button -->
            <button
              @click="addFilter"
              class="mt-4 w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-purple-500 hover:text-blue-700 transition flex items-center justify-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Додај filter
            </button>
          </div>

          <!-- Footer -->
          <div class="p-6 border-t bg-gray-50 flex items-center justify-between">
            <button
              @click="resetAdvancedFilters"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
            >
              Resetuj filtere
            </button>
            <div class="flex gap-3">
              <button
                @click="closeAdvancedSearch"
                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
              >
                Откажи
              </button>
              <button
                @click="applyAdvancedSearch"
                class="px-6 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition"
              >
                Primeni pretragu
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Details Modal -->
    <teleport to="body">
      <div v-if="showDetailsModal && selectedZalba" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
            <h3 class="text-2xl font-bold text-gray-800">Detalji žalbe</h3>
            <button @click="closeDetailsModal" class="text-gray-500 hover:text-gray-700 transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-6">
            <!-- SEKCIJA 1: Основни подаци жалбе -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Основни подаци жалбе</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Подносилац жалбе</label>
                  <p class="text-gray-900 font-medium">
                    {{ selectedZalba.podnosilac ? `${selectedZalba.podnosilac.ime_podnosioca_zalbe} ${selectedZalba.podnosilac.prezime_podnosioca_zalbe}` : '-' }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Пријемни број</label>
                  <p class="text-gray-900 font-medium">{{ selectedZalba.prijemni_broj || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум пријема жалбе</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_prijema_zalbe) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Број решења</label>
                  <p class="text-gray-900">{{ selectedZalba.broj_resenja || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Основ жалбе</label>
                  <p class="text-gray-900">
                    {{
                      typeof selectedZalba.osnov_zalbe === 'object' && selectedZalba.osnov_zalbe !== null
                        ? selectedZalba.osnov_zalbe.osnov_zalbe
                        : selectedZalba.osnov_zalbe || '-'
                    }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум враћања на допуну</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_vracanja_na_dopunu) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Рок за допуну</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.rok_za_dopunu) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум пријема допуне</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_prijema_dopune) }}</p>
                </div>
              </div>

              <div class="mt-4 bg-white p-4 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-500 mb-2">Напомена</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ selectedZalba.napomena || 'Nema napomene' }}</p>
              </div>
            </div>

            <!-- SEKCIJA 2: Komisija i upravni sud -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Komisija i upravni sud</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум предаје комисији</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_predaje_komisiji) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум решавања на ЗК</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_resavanja_na_zk) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум експедиције ДС органу</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_ekspedicije_ds_organu) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Известилац са жалбама</label>
                  <p class="text-gray-900">{{ selectedZalba.izvestilac_sa_zalbama || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Комисија ЗКБ</label>
                  <p class="text-gray-900">{{ selectedZalba.komisije_zkv || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Типови решења</label>
                  <p class="text-gray-900">{{ selectedZalba.tipResenja?.tip_resenja || selectedZalba.tipovi_resenja || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Члан комисије 1</label>
                  <p class="text-gray-900">{{ selectedZalba.clanovi_komisije1 || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Члан комисије 2</label>
                  <p class="text-gray-900">{{ selectedZalba.clanovi_komisije2 || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Статус жалбе</label>
                  <span v-if="selectedZalba.status_zalbe" :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(selectedZalba.status_zalbe)]">
                    {{ selectedZalba.status_zalbe }}
                  </span>
                  <p v-else class="text-gray-900">-</p>
                </div>
              </div>
            </div>

            <!-- SEKCIJA 3: Tužba upućena Upravnom sudu Srbije -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Tužba upućena Upravnom sudu Srbije</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум пријема тужбе од УС</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_prijema_tuzbe_od_us) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум експедиције одговора ЗК</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_ekspedicije_odgovora_zk) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум пријема одлуке УС</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_prijema_odluke_us) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Тип пресуде УС</label>
                  <p class="text-gray-900">{{ selectedZalba.tipPresude?.tip_presude || selectedZalba.tipovi_presude_us || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Број одлуке УС</label>
                  <p class="text-gray-900">{{ selectedZalba.broj_odluke_us || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум доношења одлуке УС</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_donosenja_odluke_us) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Датум решења ЗК по пресуди УС</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.datum_resenja_zk_po_presudi_us) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Број решења ZK po presudi US</label>
                  <p class="text-gray-900">{{ selectedZalba.broj_resenja_zk_po_presudi_us || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Накнада</label>
                  <p class="text-gray-900">{{ selectedZalba.naknada || '-' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Достављeница</label>
                  <p class="text-gray-900">{{ formatDate(selectedZalba.dostavnica) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-200 flex justify-end sticky bottom-0 bg-white">
            <button
              @click="closeDetailsModal"
              class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
            >
              Zatvori
            </button>
          </div>
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
            <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Potvrda brisanja</h3>
            <p class="text-gray-600 text-center mb-6">Da li ste sigurni da želite da obrišete ovu žalbu?</p>
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
                Obriši
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
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { useSorting } from '../composables/useSorting';

const route = useRoute();
const router = useRouter();

const zalbe = ref([]);
const searchQuery = ref('');
const showModal = ref(false);
const modalMode = ref('create');
const showAdvancedSearch = ref(false);
const advancedFilters = ref([]);
const activeAdvancedFilters = ref([]);

// Podnosilac context
const selectedPodnosilacId = ref(null);
const selectedPodnosilacData = ref(null);

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
const { sortBy, sortDirection, toggleSort, getSortParams } = useSorting((page) => fetchZalbe(page));

// Sifarnici
const podnosioci = ref([]);
const osnoviZalbe = ref([]);
const clanoviKomisije = ref([]);
const tipoviResenja = ref([]);
const statusiZalbe = ref([]);
const tipoviPresude = ref([]);

// Details modal
const showDetailsModal = ref(false);
const selectedZalba = ref(null);

const form = ref({
  // Add mode fields
  podnosioci_zalbe: null,
  prijemni_broj: '',
  institucija: '',
  datum_prijema_zalbe: '',
  broj_resenja: '',
  osnov_zalbe: '',
  datum_vracanja_na_dopunu: '',
  rok_za_dopunu: '',
  datum_prijema_dopune: '',
  napomena: '',
  // Edit mode only fields
  datum_predaje_komisiji: '',
  datum_resavanja_na_zk: '',
  datum_ekspedicije_ds_organu: '',
  izvestilac_sa_zalbama: null,
  komisije_zkv: null,
  clanovi_komisije1: '',
  clanovi_komisije2: '',
  tipovi_resenja: null,
  status_zalbe: '',
  datum_isticanja_donosenje: '',
  datum_prijema_tuzbe_od_us: '',
  datum_ekspedicije_odgovora_zk: '',
  datum_prijema_odluke_us: '',
  tipovi_presude_us: null,
  broj_odluke_us: '',
  datum_donosenja_odluke_us: '',
  datum_resenja_zk_po_presudi_us: '',
  broj_resenja_zk_po_presudi_us: '',
  naknada: '',
  dostavnica: ''
});

const filteredZalbe = computed(() => {
  let result = zalbe.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(z =>
      z.prijemni_broj?.toLowerCase().includes(query) ||
      z.broj_resenja?.toLowerCase().includes(query)
    );
  }

  return result;
});

const getStatusClass = (status) => {
  const classes = {
    'U obradi': 'bg-yellow-100 text-yellow-800',
    'Rešeno': 'bg-green-100 text-green-800',
    'Novo': 'bg-blue-100 text-blue-800',
    'Na dopuni': 'bg-orange-100 text-orange-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('sr-RS');
};

const openModal = (mode, zalba = null) => {
  modalMode.value = mode;
  if (mode === 'edit' && zalba) {
    form.value = { ...zalba };

    // Ensure podnosioci_zalbe is just the ID, not the full object
    if (zalba.podnosilac) {
      form.value.podnosioci_zalbe = zalba.podnosilac.id;

      // Make sure the podnosilac is in the podnosioci array for v-select to display
      const existingPodnosilac = podnosioci.value.find(p => p.id === zalba.podnosilac.id);
      if (!existingPodnosilac) {
        podnosioci.value.unshift(zalba.podnosilac);
      }
    } else if (zalba.podnosioci_zalbe && typeof zalba.podnosioci_zalbe === 'number') {
      form.value.podnosioci_zalbe = zalba.podnosioci_zalbe;
    } else if (typeof zalba.podnosioci_zalbe === 'object') {
      form.value.podnosioci_zalbe = zalba.podnosioci_zalbe.id;
    }

    // Extract ID from osnov_zalbe relationship if it's an object
    if (typeof zalba.osnov_zalbe === 'object' && zalba.osnov_zalbe !== null) {
      form.value.osnov_zalbe = zalba.osnov_zalbe.id;
    } else if (typeof zalba.osnov_zalbe === 'number') {
      form.value.osnov_zalbe = zalba.osnov_zalbe;
    }

    // Convert all date fields from ISO format to yyyy-MM-dd
    const dateFields = [
      'datum_prijema_zalbe', 'datum_vracanja_na_dopunu', 'rok_za_dopunu',
      'datum_prijema_dopune', 'datum_predaje_komisiji', 'datum_resavanja_na_zk',
      'datum_ekspedicije_ds_organu', 'datum_isticanja_donosenje', 'datum_prijema_tuzbe_od_us',
      'datum_ekspedicije_odgovora_zk', 'datum_prijema_odluke_us',
      'datum_donosenja_odluke_us', 'datum_resenja_zk_po_presudi_us', 'dostavnica'
    ];

    dateFields.forEach(field => {
      if (form.value[field]) {
        // Extract yyyy-MM-dd from ISO format (e.g., "2025-10-30T00:00:00.000000Z" -> "2025-10-30")
        form.value[field] = form.value[field].split('T')[0];
      }
    });
  } else {
    resetForm();
    // Pre-select podnosilac if filtering by specific podnosilac
    if (selectedPodnosilacId.value && selectedPodnosilacData.value) {
      form.value.podnosioci_zalbe = selectedPodnosilacId.value;
      form.value.institucija = selectedPodnosilacData.value.institucija_podnosioca_zalbe || '';

      // Make sure the podnosilac is in the podnosioci array for v-select to display
      const existingPodnosilac = podnosioci.value.find(p => p.id === selectedPodnosilacId.value);
      if (!existingPodnosilac) {
        podnosioci.value.unshift(selectedPodnosilacData.value);
      }
    }
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    // Add mode fields
    podnosioci_zalbe: null,
    prijemni_broj: '',
    institucija: '',
    datum_prijema_zalbe: '',
    broj_resenja: '',
    osnov_zalbe: '',
    datum_vracanja_na_dopunu: '',
    rok_za_dopunu: '',
    datum_prijema_dopune: '',
    napomena: '',
    // Edit mode only fields
    datum_predaje_komisiji: '',
    datum_resavanja_na_zk: '',
    datum_ekspedicije_ds_organu: '',
    izvestilac_sa_zalbama: null,
    komisije_zkv: null,
    clanovi_komisije1: '',
    clanovi_komisije2: '',
    tipovi_resenja: null,
    status_zalbe: 'Нерешен',
    datum_isticanja_donosenje: '',
    datum_prijema_tuzbe_od_us: '',
    datum_ekspedicije_odgovora_zk: '',
    datum_prijema_odluke_us: '',
    tipovi_presude_us: null,
    broj_odluke_us: '',
    datum_donosenja_odluke_us: '',
    datum_resenja_zk_po_presudi_us: '',
    broj_resenja_zk_po_presudi_us: '',
    naknada: '',
    dostavnica: ''
  };
};

const resetFilters = () => {
  searchQuery.value = '';
  activeAdvancedFilters.value = [];
  advancedFilters.value = [];
  fetchZalbe(1);
};

const exportExcel = () => {
  const params = new URLSearchParams();
  if (searchQuery.value) {
    params.append('search', searchQuery.value);
  }
  if (activeAdvancedFilters.value.length > 0) {
    params.append('advanced_filters', JSON.stringify(activeAdvancedFilters.value));
  }
  if (selectedPodnosilacId.value) {
    params.append('podnosilac_id', selectedPodnosilacId.value);
  }
  const url = `/api/zalbe/export-excel?${params.toString()}`;
  window.location.href = url;
};

const exportPdf = () => {
  const params = new URLSearchParams();
  if (searchQuery.value) {
    params.append('search', searchQuery.value);
  }
  if (activeAdvancedFilters.value.length > 0) {
    params.append('advanced_filters', JSON.stringify(activeAdvancedFilters.value));
  }
  if (selectedPodnosilacId.value) {
    params.append('podnosilac_id', selectedPodnosilacId.value);
  }
  const url = `/api/zalbe/export-pdf?${params.toString()}`;
  window.open(url, '_blank');
};

const clearPodnosilacFilter = () => {
  selectedPodnosilacId.value = null;
  selectedPodnosilacData.value = null;
  router.push({ name: 'Zalbe' });
  fetchZalbe(1);
};

const loadPodnosilacData = async (podnosilacId) => {
  try {
    const response = await axios.get(`/podnosioci-zalbe/${podnosilacId}`);
    selectedPodnosilacData.value = response.data;
  } catch (error) {
    console.error('Error loading podnosilac data:', error);
  }
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
  const dateFields = [
    'datum_prijema_zalbe',
    'datum_vracanja_na_dopunu',
    'rok_za_dopunu',
    'datum_prijema_dopune',
    'datum_resavanja_na_zk'
  ];
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
  fetchZalbe(1);
};

const resetAdvancedFilters = () => {
  advancedFilters.value = [{ field: '', operator: '', value: '', value2: '' }];
  activeAdvancedFilters.value = [];
};

const viewDetails = (zalba) => {
  selectedZalba.value = zalba;
  showDetailsModal.value = true;
};

const closeDetailsModal = () => {
  showDetailsModal.value = false;
  selectedZalba.value = null;
};

// Helper function to format date for backend
const formatDateForBackend = (date) => {
  if (!date || date === '') return null;

  // If it's already a string in yyyy-MM-dd format, return it
  if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
    return date;
  }

  let dateObj;

  // If it's dd.MM.yyyy format (display format from VueDatePicker)
  if (typeof date === 'string' && /^\d{2}\.\d{2}\.\d{4}$/.test(date)) {
    const [day, month, year] = date.split('.');
    dateObj = new Date(year, month - 1, day);
  }
  // If it's a Date object
  else if (date instanceof Date) {
    dateObj = date;
  }
  // If it's a string (ISO or other format)
  else if (typeof date === 'string') {
    dateObj = new Date(date);
  }

  // Check if valid date
  if (!dateObj || isNaN(dateObj.getTime())) {
    return null;
  }

  // Format as yyyy-MM-dd
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
};

const submitForm = async () => {
  try {
    // Format all date fields to yyyy-MM-dd before sending
    const formData = { ...form.value };
    const dateFields = [
      'datum_prijema_zalbe', 'datum_vracanja_na_dopunu', 'rok_za_dopunu',
      'datum_prijema_dopune', 'datum_predaje_komisiji', 'datum_resavanja_na_zk',
      'datum_ekspedicije_ds_organu', 'datum_isticanja_donosenje',
      'datum_prijema_tuzbe_od_us', 'datum_ekspedicije_odgovora_zk',
      'datum_prijema_odluke_us', 'datum_donosenja_odluke_us',
      'datum_resenja_zk_po_presudi_us', 'dostavnica'
    ];

    dateFields.forEach(field => {
      if (formData[field]) {
        formData[field] = formatDateForBackend(formData[field]);
      }
    });

    // Debug logging
    console.log('Original form data:', form.value);
    console.log('Formatted form data:', formData);

    const isCreate = modalMode.value === 'create';
    if (isCreate) {
      await axios.post('/zalbe', formData);
    } else {
      await axios.put(`/zalbe/${formData.id}`, formData);
    }
    await fetchZalbe();
    closeModal();
    showToastNotification(isCreate ? 'Žalba uspešno dodata!' : 'Žalba uspešno izmenjena!', 'success');
  } catch (error) {
    console.error('Error saving zalba:', error);

    // Log detailed validation errors
    if (error.response && error.response.data) {
      console.error('Backend validation errors:', error.response.data);
      if (error.response.data.errors) {
        console.error('Validation details:', error.response.data.errors);
      }
    }

    showToastNotification('Greška prilikom čuvanja podataka.', 'error');
  }
};

const deleteZalba = (id) => {
  deleteTargetId.value = id;
  showDeleteConfirm.value = true;
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/zalbe/${deleteTargetId.value}`);
    await fetchZalbe();
    showDeleteConfirm.value = false;
    deleteTargetId.value = null;
    showToastNotification('Žalba uspešno obrisana!', 'success');
  } catch (error) {
    console.error('Error deleting zalba:', error);
    showToastNotification('Greška prilikom brisanja.', 'error');
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

const fetchZalbe = async (page = 1) => {
  try {
    const params = {
      page,
      search: searchQuery.value,
      ...getSortParams()
    };

    // Add podnosilac filter if active
    if (selectedPodnosilacId.value) {
      params.podnosilac_id = selectedPodnosilacId.value;
    }

    // Add advanced filters if they exist
    if (activeAdvancedFilters.value.length > 0) {
      params.advanced_filters = activeAdvancedFilters.value;
    }

    const response = await axios.get('/zalbe', { params });
    zalbe.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    total.value = response.data.total;
    from.value = response.data.from;
    to.value = response.data.to;
  } catch (error) {
    console.error('Error fetching zalbe:', error);
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchZalbe(page);
  }
};

const searchPodnosioci = async (searchQuery, loading) => {
  if (loading) {
    loading(true);
  }
  try {
    const response = await axios.get('/podnosioci-zalbe/search', {
      params: { q: searchQuery }
    });
    podnosioci.value = response.data;
  } catch (error) {
    console.error('Error searching podnosioci:', error);
  } finally {
    if (loading) {
      loading(false);
    }
  }
};

const fetchSifarnici = async () => {
  try {
    const [
      podnosioziRes,
      osnoviRes,
      clanoviRes,
      tipoviResenjaRes,
      statusiRes,
      tipoviPresudeRes
    ] = await Promise.all([
      axios.get('/podnosioci-zalbe/search'),  // Load initial set without search
      axios.get('/sifarnik-osnov-zalbe'),
      axios.get('/sifarnik-clanovi-komisije'),
      axios.get('/sifarnik-tipovi-resenja'),
      axios.get('/sifarnik-status-zalbe'),
      axios.get('/sifarnik-tip-presude')
    ]);

    podnosioci.value = podnosioziRes.data;
    osnoviZalbe.value = osnoviRes.data;
    clanoviKomisije.value = clanoviRes.data;
    tipoviResenja.value = tipoviResenjaRes.data;
    statusiZalbe.value = statusiRes.data;
    tipoviPresude.value = tipoviPresudeRes.data;
  } catch (error) {
    console.error('Error fetching sifarnici:', error);
  }
};

// Watch search changes to reset to page 1
watch(searchQuery, () => {
  fetchZalbe(1);
});

// Watch for route query parameter changes
watch(() => route.query.podnosilac_id, async (newPodnosilacId) => {
  if (newPodnosilacId) {
    selectedPodnosilacId.value = parseInt(newPodnosilacId);
    await loadPodnosilacData(selectedPodnosilacId.value);
    fetchZalbe(1);
  } else {
    selectedPodnosilacId.value = null;
    selectedPodnosilacData.value = null;
  }
}, { immediate: true });

// Watch podnosioci_zalbe field to auto-update institucija
watch(() => form.value.podnosioci_zalbe, (newPodnosilacId) => {
  if (newPodnosilacId && podnosioci.value.length > 0) {
    const selectedPodnosilac = podnosioci.value.find(p => p.id === newPodnosilacId);
    if (selectedPodnosilac) {
      form.value.institucija = selectedPodnosilac.institucija_podnosioca_zalbe || '';
    }
  }
});

// Trigger 1 & 2: Auto-calculate datum_isticanja_donosenje when datum_prijema_zalbe changes
watch(() => form.value.datum_prijema_zalbe, (newDatumPrijema) => {
  if (newDatumPrijema) {
    // Parse the date
    const datumPrijema = new Date(newDatumPrijema);

    // Add 1 month
    const datumIsticanja = new Date(datumPrijema);
    datumIsticanja.setMonth(datumIsticanja.getMonth() + 1);

    // Convert back to yyyy-MM-dd format for the backend
    const year = datumIsticanja.getFullYear();
    const month = String(datumIsticanja.getMonth() + 1).padStart(2, '0');
    const day = String(datumIsticanja.getDate()).padStart(2, '0');

    form.value.datum_isticanja_donosenje = `${year}-${month}-${day}`;
  }
});

// Trigger 3: Set status to 'Упућен на допуну' when datum_vracanja_na_dopunu is set
watch(() => form.value.datum_vracanja_na_dopunu, (newDatumVracanja) => {
  if (newDatumVracanja) {
    form.value.status_zalbe = 'Упућен на допуну';
  }
});

// Trigger 4: Set status to 'Решен' when both dates are set
watch([() => form.value.datum_resavanja_na_zk, () => form.value.datum_ekspedicije_ds_organu],
  ([newDatumResavanja, newDatumEkspedicije]) => {
    if (newDatumResavanja && newDatumEkspedicije) {
      form.value.status_zalbe = 'Решен';
    }
  }
);

onMounted(() => {
  fetchZalbe();
  fetchSifarnici();
});
</script>

<style scoped>
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
