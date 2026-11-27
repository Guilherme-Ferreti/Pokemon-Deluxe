import { useForm } from '@inertiajs/vue3';
import type { MaybeRefOrGetter } from 'vue';
import { ref, toValue } from 'vue';
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import { route } from '@/shared/types/helpers/route';

export function useAddToParty(
  pokemonId: MaybeRefOrGetter<string>,
  isPartyFull: MaybeRefOrGetter<boolean>,
) {
  const isSwapDialogOpen = ref(false);

  const form = useForm({});

  const isRequestRunning = useIsRequestRunning();

  const addToParty = () => {
    if (isRequestRunning.value) {
      return;
    }

    if (toValue(isPartyFull)) {
      isSwapDialogOpen.value = true;

      return;
    }

    form.post(route('pokemon.web.party.add', { pokemon: toValue(pokemonId) }), {
      preserveScroll: true,
    });
  };

  return { isSwapDialogOpen, addToParty };
}
