import { ref } from 'vue';
import axios from 'axios';

/**
 * Polja izvestaja koja se u naprednoj pretrazi biraju iz sifarnika.
 * Izvestaji filtriraju po tekstualnoj koloni iz sifarnika (a ne po id-u),
 * pa se kao vrednost salje sam naziv iz sifarnika.
 */
const SIFARNIK_FIELDS = {
  osnov_zalbe: { url: '/sifarnik-osnov-zalbe', key: 'osnov_zalbe' },
  tip_resenja: { url: '/sifarnik-tipovi-resenja', key: 'tip_resenja' },
  status_zalbe: { url: '/sifarnik-status-zalbe', key: 'status_zalbe' },
  tip_presude: { url: '/sifarnik-tip-presude', key: 'tip_presude' }
};

/**
 * Composable za sifarnicka polja u naprednoj pretrazi izvestaja
 *
 * @param {Array<string>} fields - polja izvestaja koja se pune iz sifarnika
 * @returns {Object} - loadSifarnici, isSifarnikField i getSifarnikOptions
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
        const key = SIFARNIK_FIELDS[field].key;
        // Vrednosti ostavljamo onakve kakve su u bazi da bi poredjenje pogadjalo zapise
        options.value[field] = responses[index].data
          .map(item => item?.[key])
          .filter(value => value !== null && value !== undefined && value !== '');
      });

      loaded.value = true;
    } catch (error) {
      console.error('Error fetching sifarnici:', error);
    }
  };

  const isSifarnikField = (field) => usedFields.includes(field);

  const getSifarnikOptions = (field) => options.value[field] || [];

  return {
    loadSifarnici,
    isSifarnikField,
    getSifarnikOptions
  };
}
