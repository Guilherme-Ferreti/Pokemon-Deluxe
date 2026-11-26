import type { Ref } from 'vue';
import { computed, ref } from 'vue';
import { toPokemonTypeOptions } from '@/shared/lib/utils';
import type { PokemonType } from '@/shared/types/enums';
import type { PokemonKnownMove } from '../types/page-props/BoxShowPageProps';

export type MoveSortOrder = 'name' | 'power';

export function compareMovesByName(
  first: PokemonKnownMove,
  second: PokemonKnownMove,
): number {
  return first.name.localeCompare(second.name);
}

export function compareMovesByPower(
  first: PokemonKnownMove,
  second: PokemonKnownMove,
): number {
  return (
    (second.power ?? -1) - (first.power ?? -1) ||
    compareMovesByName(first, second)
  );
}

export function useMoveFilters(moves: Ref<PokemonKnownMove[]>) {
  const sortOrder = ref<MoveSortOrder>('name');

  const selectedType = ref<PokemonType | null>(null);

  const typeOptions = computed(() =>
    toPokemonTypeOptions(moves.value.map((move) => move.type)),
  );

  const visibleMoves = computed(() =>
    moves.value
      .filter(
        (move) =>
          selectedType.value === null || move.type === selectedType.value,
      )
      .sort(
        sortOrder.value === 'power' ? compareMovesByPower : compareMovesByName,
      ),
  );

  const resetFilters = () => {
    sortOrder.value = 'name';
    selectedType.value = null;
  };

  return { sortOrder, selectedType, typeOptions, visibleMoves, resetFilters };
}
