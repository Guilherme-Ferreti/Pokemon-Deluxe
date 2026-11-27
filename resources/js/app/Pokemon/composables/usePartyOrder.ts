import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from '@/shared/types/helpers/route';
import type { PartyPageProps } from '../types/page-props/PartyPageProps';

export function usePartyOrder() {
  const page = usePage<PartyPageProps>();

  const canReorder = computed(() => !page.props.isBattleOngoing);

  const movePokemon = (fromIndex: number, toIndex: number) => {
    const party = [...page.props.party];

    if (
      !canReorder.value ||
      fromIndex === toIndex ||
      toIndex < 0 ||
      toIndex >= party.length
    ) {
      return;
    }

    party.splice(toIndex, 0, ...party.splice(fromIndex, 1));

    router
      .optimistic<PartyPageProps>(() => ({ party }))
      .post(
        route('pokemon.web.party.reorder'),
        { pokemonIds: party.map((member) => member.id) },
        { preserveScroll: true, only: ['party', 'isBattleOngoing'] },
      );
  };

  return { canReorder, movePokemon };
}
