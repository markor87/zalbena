import { ref } from 'vue';
import axios from 'axios';

/**
 * Polja izvestaja cija se vrednost u naprednoj pretrazi bira iz sifarnika.
 * Izvestaji filtriraju po tekstualnoj koloni (a ne po id-u), pa se kao
 * vrednost salje sam naziv iz sifarnika.
 *
 * key: null znaci da endpoint vraca obican niz stringova
 */
const SIFARNIK_FIELDS = {
  osnov_zalbe: { url: '/sifarnik-osnov-zalbe', key: 'osnov_zalbe' },
  tip_resenja: { url: '/sifarnik-tipovi-resenja', key: 'tip_resenja' },
  status_zalbe: { url: '/sifarnik-status-zalbe', key: 'status_zalbe' },
  tip_presude: { url: '/sifarnik-tip-presude', key: 'tip_presude' },
  institucija_podnosioca_zalbe: { url: '/podnosioci-zalbe/institucije', key: null },
  institucija: { url: '/podnosioci-zalbe/institucije', key: null }
};

/**
 * Composable za sifarnicka polja u naprednoj pretrazi izvestaja
 *
 * @param {Array<string>} fields - polja izvestaja koja se pune iz sifarnika
 * @returns {Object} - loadSifarnici, getListFieldType i getSifarnikOptions
 */
export function useSifarnikFilters(fields) {
  const usedFields = fields.filter(field => SIFARNIK_FIELDS[field]);
  const options = ref({});
  const loaded = ref(false);

  /**
   * Ucitava samo one sifarnike koje ovaj izvestaj koristi, i to jednom
   */
  const loadSifarnici = async () => {
    if (loaded.value) return;

    try {
      const responses = await Promise.all(
        usedFields.map(field => axios.get(SIFARNIK_FIELDS[field].url))
      );

      usedFields.forEach((field, index) => {
        const { key } = SIFARNIK_FIELDS[field];
        // Vrednosti ostavljamo onakve kakve su u bazi da bi poredjenje pogadjalo zapise
        options.value[field] = responses[index].data
          .map(item => (key ? item?.[key] : item))
          .filter(value => value !== null && value !== undefined && value !== '');
      });

      loaded.value = true;
    } catch (error) {
      console.error('Error fetching sifarnici:', error);
    }
  };

  const getListFieldType = (field) => (usedFields.includes(field) ? 'sifarnik' : null);

  const getSifarnikOptions = (field) => options.value[field] || [];

  return {
    loadSifarnici,
    getListFieldType,
    getSifarnikOptions
  };
}
