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
        <AppFilterChipGroup
          v-model="selectedType"
          label="Filter by type"
          all-label="All types"
          test-id-prefix="pokemon-teach-tm-dialog__type"
          :options="ownedTypeOptions"
        />
        <RadioGroup
          v-if="visibleTms.length > 0"
          v-model="selectedTmId"
          class="gap-2 sm:grid-cols-2"
          aria-label="TMs"
          data-test="pokemon-teach-tm-dialog__tm-list"
        >
          <AppChoiceCard
            v-for="tm in visibleTms"
            :key="tm.id"
            :data-test="`pokemon-teach-tm-dialog__tm-${tm.id}-radio`"
            :value="tm.id"
            :disabled="tm.isKnown || tm.needsMaxLevel"
          >
            <span class="flex min-w-0 flex-col gap-1">
              <span class="text-sm font-medium">{{ tm.tmName }}</span>
              <span class="flex flex-wrap items-center gap-1.5">
                <PokemonTypeBadges :types="[tm.type]" />
                <span class="text-xs text-muted-foreground tabular-nums">
                  {{ describeTm(tm) }}
                </span>
              </span>
            </span>
          </AppChoiceCard>
        </RadioGroup>
        <AppEmptyState
          v-else
          title="No TMs match your search"
          description="Try another name or number, or pick another type."
          data-test="pokemon-teach-tm-dialog__no-match-empty-state"
          :icon="IconSearch"
        />
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
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import AppFilterChipGroup from '@/shared/components/list/AppFilterChipGroup.vue';
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
import { RadioGroup } from '@/shared/components/ui/radio-group';
import { toPokemonTypeOptions } from '@/shared/lib/utils';
import type { PokemonType } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

type OwnedTm = BoxShowPageProps['tms'][number];

const page = usePage<BoxShowPageProps>();

const isOpen = ref(false);

const isTeaching = ref(false);

const search = ref('');

const selectedType = ref<PokemonType | null>(null);

const selectedTmId = ref<string | null>(null);

const selectedTm = computed(
  () => page.props.tms.find((tm) => tm.id === selectedTmId.value) ?? null,
);

const ownedTypeOptions = computed(() =>
  toPokemonTypeOptions(page.props.tms.map((tm) => tm.type)),
);

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
  selectedTmId.value = null;
};

const teachSelectedTm = () => {
  if (!selectedTm.value) {
    return;
  }

  router.post(
    route('pokemon.web.teach-tm', { pokemon: page.props.pokemon.id }),
    { moveId: selectedTm.value.id },
    {
      preserveScroll: true,
      onStart: () => (isTeaching.value = true),
      onSuccess: () => (isOpen.value = false),
      onFinish: () => (isTeaching.value = false),
    },
  );
};
</script>
