import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from '@/shared/types/helpers/route';
import type {
  BoxShowPageProps,
  PokemonKnownMove,
} from '../types/page-props/BoxShowPageProps';

export const MAX_ACTIVE_MOVES = 4;

export type ActiveMoveSlot = {
  number: number;
  move: PokemonKnownMove | null;
};

export function toActiveMoves(
  knownMoves: PokemonKnownMove[],
): PokemonKnownMove[] {
  return knownMoves
    .filter((knownMove) => knownMove.slot !== null)
    .sort((first, second) => (first.slot ?? 0) - (second.slot ?? 0));
}

export function withMoveInSlot(
  knownMoves: PokemonKnownMove[],
  moveId: string,
  slot: number,
): PokemonKnownMove[] {
  const previousSlot =
    knownMoves.find((knownMove) => knownMove.id === moveId)?.slot ?? null;

  return knownMoves.map((knownMove) => {
    if (knownMove.id === moveId) {
      return { ...knownMove, slot };
    }

    return knownMove.slot === slot
      ? { ...knownMove, slot: previousSlot }
      : knownMove;
  });
}

export function withActiveMovesInOrder(
  knownMoves: PokemonKnownMove[],
  moveIds: string[],
): PokemonKnownMove[] {
  return knownMoves.map((knownMove) => {
    const index = moveIds.indexOf(knownMove.id);

    return index === -1 ? knownMove : { ...knownMove, slot: index + 1 };
  });
}

export function useActiveMoves() {
  const page = usePage<BoxShowPageProps>();

  const activeMoves = computed(() => toActiveMoves(page.props.knownMoves));

  const activeMoveIds = computed(() =>
    activeMoves.value.map((activeMove) => activeMove.id),
  );

  const slots = computed<ActiveMoveSlot[]>(() =>
    Array.from({ length: MAX_ACTIVE_MOVES }, (_, index) => ({
      number: index + 1,
      move:
        page.props.knownMoves.find(
          (knownMove) => knownMove.slot === index + 1,
        ) ?? null,
    })),
  );

  const hasEmptySlot = computed(
    () => activeMoves.value.length < MAX_ACTIVE_MOVES,
  );

  const updateKnownMoves = (
    url: string,
    data: Parameters<typeof router.post>[1],
    nextKnownMoves: (knownMoves: PokemonKnownMove[]) => PokemonKnownMove[],
  ) => {
    router
      .optimistic<BoxShowPageProps>((props) => ({
        knownMoves: nextKnownMoves(props.knownMoves),
      }))
      .post(url, data, { preserveScroll: true, only: ['knownMoves'] });
  };

  const setActiveMove = (moveId: string, slot: number) => {
    updateKnownMoves(
      route('pokemon.web.set-active-move', { pokemon: page.props.pokemon.id }),
      { move_id: moveId, slot },
      (knownMoves) => withMoveInSlot(knownMoves, moveId, slot),
    );
  };

  const reorderActiveMoves = (moveIds: string[]) => {
    updateKnownMoves(
      route('pokemon.web.reorder-active-moves', {
        pokemon: page.props.pokemon.id,
      }),
      { move_ids: moveIds },
      (knownMoves) => withActiveMovesInOrder(knownMoves, moveIds),
    );
  };

  const moveActiveMoveTo = (moveId: string, index: number) => {
    const moveIds = activeMoveIds.value.filter(
      (activeMoveId) => activeMoveId !== moveId,
    );

    moveIds.splice(index, 0, moveId);

    reorderActiveMoves(moveIds);
  };

  const moveUp = (moveId: string) => {
    moveActiveMoveTo(moveId, activeMoveIds.value.indexOf(moveId) - 1);
  };

  const moveDown = (moveId: string) => {
    moveActiveMoveTo(moveId, activeMoveIds.value.indexOf(moveId) + 1);
  };

  const moveToPlaceOf = (moveId: string, targetMoveId: string) => {
    moveActiveMoveTo(moveId, activeMoveIds.value.indexOf(targetMoveId));
  };

  const isFirstActiveMove = (moveId: string) =>
    activeMoveIds.value[0] === moveId;

  const isLastActiveMove = (moveId: string) =>
    activeMoveIds.value[activeMoveIds.value.length - 1] === moveId;

  return {
    activeMoves,
    slots,
    hasEmptySlot,
    setActiveMove,
    moveUp,
    moveDown,
    moveToPlaceOf,
    isFirstActiveMove,
    isLastActiveMove,
  };
}
