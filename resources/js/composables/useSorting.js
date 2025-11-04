import { ref } from 'vue';

/**
 * Composable za sortiranje tabela
 *
 * @param {Function} fetchCallback - Funkcija koja se poziva nakon promene sortiranja
 * @returns {Object} - Objekat sa sortBy, sortDirection, toggleSort i getSortParams
 */
export function useSorting(fetchCallback) {
  const sortBy = ref(null);
  const sortDirection = ref('asc');

  /**
   * Toggle sortiranje po određenom polju
   * Ako je polje već sortirano, menja se smer (asc/desc)
   * Ako je novo polje, postavlja se na ascending
   */
  const toggleSort = (field) => {
    if (sortBy.value === field) {
      // Ako je isto polje, menjamo smer
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      // Novo polje, postavljamo na ascending
      sortBy.value = field;
      sortDirection.value = 'asc';
    }

    // Pozivamo callback funkciju (obično fetchData sa page=1)
    if (fetchCallback) {
      fetchCallback(1);
    }
  };

  /**
   * Vraća parametre za slanje na backend
   * Ako sortiranje nije aktivno, vraća prazne vrednosti
   */
  const getSortParams = () => {
    if (!sortBy.value) {
      return {};
    }

    return {
      sort_by: sortBy.value,
      sort_direction: sortDirection.value
    };
  };

  /**
   * Resetuje sortiranje na default stanje
   */
  const resetSort = () => {
    sortBy.value = null;
    sortDirection.value = 'asc';
  };

  return {
    sortBy,
    sortDirection,
    toggleSort,
    getSortParams,
    resetSort
  };
}
