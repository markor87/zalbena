import { ref } from 'vue';
import axios from 'axios';

/**
 * Polja izvestaja cija se vrednost u naprednoj pretrazi bira iz liste.
 * Izvestaji filtriraju po tekstualnoj koloni (a ne po id-u), pa se kao
 * vrednost salje sam naziv.
 *
 * type 'sifarnik' - vrednost mora biti iz sifarnika, pa su dozvoljeni samo
 *                   operatori jednakosti
 * type 'lista'    - lista je samo pomoc pri unosu (institucije nisu sifarnik),
 *                   dozvoljen je i slobodan unos i svi tekstualni operatori
 *
 * key: null znaci da endpoint vraca obican niz stringova
 */
const LIST_FIELDS = {
  osnov_zalbe: { url: '/sifarnik-osnov-zalbe', key: 'osnov_zalbe', type: 'sifarnik' },
  tip_resenja: { url: '/sifarnik-tipovi-resenja', key: 'tip_resenja', type: 'sifarnik' },
  status_zalbe: { url: '/sifarnik-status-zalbe', key: 'status_zalbe', type: 'sifarnik' },
  tip_presude: { url: '/sifarnik-tip-presude', key: 'tip_presude', type: 'sifarnik' },
  institucija_podnosioca_zalbe: { url: '/podnosioci-zalbe/institucije', key: null, type: 'lista' },
  institucija: { url: '/podnosioci-zalbe/institucije', key: null, type: 'lista' }
};

/**
 * Composable za polja napredne pretrage koja se biraju iz liste
 *
 * @param {Array<string>} fields - polja izvestaja koja se pune iz liste
 * @returns {Object} - loadSifarnici, getListFieldType i getSifarnikOptions
 */
export function useSifarnikFilters(fields) {
  const usedFields = fields.filter(field => LIST_FIELDS[field]);
  const options = ref({});
  const loaded = ref(false);

  /**
   * Ucitava samo one liste koje ovaj izvestaj koristi, i to jednom
   */
  const loadSifarnici = async () => {
    if (loaded.value) return;

    try {
      const responses = await Promise.all(
        usedFields.map(field => axios.get(LIST_FIELDS[field].url))
      );

      usedFields.forEach((field, index) => {
        const { key } = LIST_FIELDS[field];
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

  const getListFieldType = (field) =>
    usedFields.includes(field) ? LIST_FIELDS[field].type : null;

  const getSifarnikOptions = (field) => options.value[field] || [];

  return {
    loadSifarnici,
    getListFieldType,
    getSifarnikOptions
  };
}
