<template>
  <Dialog
    v-model:open="isOpen"
    @update:open="resetDialog"
  >
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="pokemon-teach-tm-dialog__open-button"
      >
        Teach TM
      </Button>
    </DialogTrigger>
    <DialogScrollContent
      class="flex flex-col gap-4 max-sm:my-0 max-sm:min-h-dvh max-sm:max-w-none max-sm:rounded-none sm:max-w-3xl"
      data-test="pokemon-teach-tm-dialog"
    >
      <DialogHeader>
        <DialogTitle>Teach a TM</DialogTitle>
        <DialogDescription>
          The TM is used up. The move joins {{ page.props.pokemon.name }}'s
          known moves.
        </DialogDescription>
      </DialogHeader>
      <AppEmptyState
        v-if="page.props.tms.length === 0"
        title="You don't own any TMs"
        description="Buy TMs in the shop or win them in battles."
        data-test="pokemon-teach-tm-dialog__no-tms-empty-state"
        :icon="IconDisc"
      />
      <template v-else>
        <InputGroup class="bg-background shadow-xs">
          <InputGroupInput
            v-model="search"
            type="search"
            placeholder="Search by name or number"
            autocomplete="off"
            aria-label="Search by name or number"
            data-test="pokemon-teach-tm-dialog__search-input"
          />
          <InputGroupAddon>
            <IconSearch />
          </InputGroupAddon>
        </InputGroup>
        <div
          class="flex flex-wrap gap-1.5"
          role="group"
          aria-label="Filter by type"
        >
          <Badge
            as="button"
            type="button"
            class="cursor-pointer px-2.5 py-1"
            :variant="selectedType === null ? 'default' : 'outline'"
            :aria-pressed="selectedType === null"
            @click="selectedType = null"
          >
            All types
          </Badge>
          <Badge
            v-for="type in ownedTypes"
            :key="type"
            as="button"
            type="button"
            class="cursor-pointer px-2.5 py-1"
            :variant="selectedType === type ? 'default' : 'outline'"
            :aria-pressed="selectedType === type"
            :data-test="`pokemon-teach-tm-dialog__type-${type}-button`"
            @click="selectedType = type"
          >
            {{ page.props.labels.pokemonType[type] }}
          </Badge>
        </div>
        <ul
          v-if="visibleTms.length > 0"
          class="grid gap-1.5 sm:grid-cols-2"
          data-test="pokemon-teach-tm-dialog__tm-list"
        >
          <li
            v-for="tm in visibleTms"
            :key="tm.id"
          >
            <button
              type="button"
              class="flex w-full flex-col gap-1 rounded-lg border p-2 text-left transition-colors hover:bg-muted focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent aria-pressed:border-primary aria-pressed:ring-1 aria-pressed:ring-primary"
              :aria-pressed="selectedTm?.id === tm.id"
              :disabled="tm.isKnown || tm.needsMaxLevel"
              :data-test="`pokemon-teach-tm-dialog__tm-${tm.id}-button`"
              @click="selectedTm = tm"
            >
              <span class="text-sm font-medium">{{ tm.tmName }}</span>
              <span class="flex flex-wrap items-center gap-1.5">
                <PokemonTypeBadges :types="[tm.type]" />
                <span class="text-xs text-muted-foreground tabular-nums">
                  {{ describeTm(tm) }}
                </span>
              </span>
            </button>
          </li>
        </ul>
        <AppEmptyState
          v-else
          title="No TMs match your search"
          description="Try another name or number, or pick another type."
          data-test="pokemon-teach-tm-dialog__no-match-empty-state"
          :icon="IconSearch"
        />
        <p
          v-if="selectedTm"
          class="text-sm"
          data-test="pokemon-teach-tm-dialog__confirmation-text"
        >
          <strong>
            Teach {{ selectedTm.name }} to {{ page.props.pokemon.name }}?
          </strong>
          <span class="text-muted-foreground">
            {{
              hasEmptySlot
                ? ' It goes into the first empty slot.'
                : ' All 4 slots are full, so it goes to the known moves; swap it in when you want it.'
            }}
          </span>
        </p>
      </template>
      <DialogFooter>
        <DialogClose as-child>
          <Button
            type="button"
            variant="outline"
          >
            Cancel
          </Button>
        </DialogClose>
        <Button
          v-if="selectedTm"
          type="button"
          data-test="pokemon-teach-tm-dialog__teach-button"
          :disabled="isTeaching"
          @click="teachSelectedTm"
        >
          Teach {{ selectedTm.name }}
        </Button>
      </DialogFooter>
    </DialogScrollContent>
  </Dialog>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { IconDisc, IconSearch } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogScrollContent,
  DialogTitle,
  DialogTrigger,
} from '@/shared/components/ui/dialog';
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from '@/shared/components/ui/input-group';
import { PokemonType } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

type OwnedTm = BoxShowPageProps['tms'][number];

const MAX_ACTIVE_MOVES = 4;

const page = usePage<BoxShowPageProps>();

const isOpen = ref(false);

const isTeaching = ref(false);

const search = ref('');

const selectedType = ref<PokemonType | null>(null);

const selectedTm = ref<OwnedTm | null>(null);

const hasEmptySlot = computed(
  () => page.props.activeMoves.length < MAX_ACTIVE_MOVES,
);

const ownedTypes = computed(() => {
  const types = new Set(page.props.tms.map((tm) => tm.type));

  return Object.values(PokemonType).filter((type) => types.has(type));
});

const visibleTms = computed(() => {
  const searchTerm = search.value.trim().toLowerCase();

  return page.props.tms.filter(
    (tm) =>
      `${tm.tmName} ${tm.name}`.toLowerCase().includes(searchTerm) &&
      (selectedType.value === null || tm.type === selectedType.value),
  );
});

const describeTm = (tm: OwnedTm) => {
  if (tm.isKnown) {
    return 'Already known';
  }

  if (tm.needsMaxLevel) {
    return 'Needs level 100';
  }

  const power = tm.power === null ? 'Status' : `Power ${tm.power}`;

  return `${power} · you own ${tm.quantity.toLocaleString('en-US')}`;
};

const resetDialog = () => {
  search.value = '';
  selectedType.value = null;
  selectedTm.value = null;
};

const teachSelectedTm = () => {
  if (!selectedTm.value) {
    return;
  }

  router.post(
    route('pokemon.web.teach-tm', { pokemon: page.props.pokemon.id }),
    { move_id: selectedTm.value.id },
    {
      preserveScroll: true,
      onStart: () => (isTeaching.value = true),
      onSuccess: () => (isOpen.value = false),
      onFinish: () => (isTeaching.value = false),
    },
  );
};
</script>
