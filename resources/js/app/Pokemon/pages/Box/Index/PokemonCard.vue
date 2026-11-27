<template>
  <Card
    class="relative h-full gap-2 p-4 transition-shadow hover:shadow-md"
    :data-test="`pokemon-${pokemon.id}-card`"
  >
    <Badge
      v-if="pokemon.inParty"
      variant="secondary"
      class="absolute -top-2 -right-2 border-border shadow-sm"
      :data-test="`pokemon-${pokemon.id}-card__in-party-indicator`"
    >
      In party
    </Badge>
    <div class="grid grid-cols-[auto_1fr] gap-2">
      <SpeciesSprite
        :url="pokemon.frontSprite"
        :name="pokemon.name"
      />
      <div class="flex flex-col">
        <div class="flex justify-between gap-1">
          <p
            class="self-center text-sm font-semibold text-foreground"
            :data-test="`pokemon-${pokemon.id}-card__name`"
          >
            {{ pokemon.name }}
          </p>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                class="text-ring"
                :data-test="`pokemon-${pokemon.id}-card__open-dropdown-menu-button`"
                :aria-label="`Actions for ${pokemon.name}`"
              >
                <IconDotsVertical />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem as-child>
                <AppNavLink
                  title="View"
                  :data-test="`pokemon-${pokemon.id}-card__view-pokemon-link`"
                  :url="route('pokemon.web.box.show', { pokemon: pokemon.id })"
                  :icon="IconEye"
                />
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <AppNavLink
                  title="Pokédex"
                  :data-test="`pokemon-${pokemon.id}-card__view-species-link`"
                  :url="
                    route('dex.web.pokedex.show', {
                      species: pokemon.species.slug,
                    })
                  "
                  :icon="IconBook"
                />
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                v-if="pokemon.inParty"
                :data-test="`pokemon-${pokemon.id}-card__remove-from-party-button`"
                :disabled="isLastInParty"
                @select="isRemoveDialogOpen = true"
              >
                <IconCircleMinus />
                <span class="flex flex-col">
                  Remove from party
                  <span
                    v-if="isLastInParty"
                    class="text-xs text-muted-foreground"
                  >
                    Your party needs at least one Pokémon
                  </span>
                </span>
              </DropdownMenuItem>
              <DropdownMenuItem
                v-else
                :data-test="`pokemon-${pokemon.id}-card__add-to-party-button`"
                :disabled="form.processing"
                @select="addToParty"
              >
                <IconCirclePlus />
                Add to party
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
        <PokemonTypeBadges
          class="mt-1.5"
          :data-test="`pokemon-${pokemon.id}-card__types`"
          :types="pokemon.species.types"
        />
      </div>
    </div>
    <div
      class="mt-auto flex items-center justify-between rounded bg-blue-100/40 px-2 py-1 text-xs text-muted-foreground dark:bg-transparent"
    >
      <div>
        <span
          class="flex items-center gap-0.5"
          :data-test="`pokemon-${pokemon.id}-card__gender`"
        >
          Gender: <PokemonGenderIcon :gender="pokemon.gender" />
        </span>
      </div>
      <div>
        <span aria-hidden="true">Lv.</span>
        <span class="sr-only">Level</span>
        <span
          class="font-medium text-foreground"
          :data-test="`pokemon-${pokemon.id}-card__level`"
        >
          {{ pokemon.level }}
        </span>
      </div>
      <span :data-test="`pokemon-${pokemon.id}-card__experience`">
        {{ pokemon.experience }} XP
      </span>
    </div>
    <div class="flex flex-col gap-0.5">
      <p
        class="text-xxs font-medium tracking-wide text-muted-foreground uppercase"
      >
        Moves
      </p>
      <ul class="grid grid-cols-2 gap-0.5">
        <li
          v-for="(move, index) in pokemon.moves"
          :key="move.name"
          class="flex items-center gap-1"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-300"></span>
          <span
            class="truncate text-xs text-foreground"
            :title="move.name"
            :data-test="`pokemon-${pokemon.id}-card__move-${index + 1}`"
          >
            {{ move.name }}
          </span>
        </li>
      </ul>
    </div>
    <PartySwapDialog
      v-if="!pokemon.inParty"
      v-model:open="isSwapDialogOpen"
      :pokemon="pokemon"
      :party="page.props.party"
    />
    <PartyRemoveDialog
      v-else
      v-model:open="isRemoveDialogOpen"
      :pokemon="pokemon"
    />
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  IconBook,
  IconCircleMinus,
  IconCirclePlus,
  IconDotsVertical,
  IconEye,
} from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import AppNavLink from '@/shared/components/layout/AppNavLink.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '@/shared/components/ui/dropdown-menu';
import { route } from '@/shared/types/helpers/route';
import PartyRemoveDialog from '../../../components/PartyRemoveDialog.vue';
import PartySwapDialog from '../../../components/PartySwapDialog.vue';
import { useAddToParty } from '../../../composables/useAddToParty';
import type { BoxIndexPageProps } from '../../../types/page-props/BoxIndexPageProps';

const props = defineProps<{
  pokemon: BoxIndexPageProps['pokemonList']['data'][number];
}>();

const page = usePage<BoxIndexPageProps>();

const isRemoveDialogOpen = ref(false);

const isLastInParty = computed(() => page.props.party.length <= 1);

const { isSwapDialogOpen, form, addToParty } = useAddToParty(
  () => props.pokemon.id,
  () => page.props.party.length >= page.props.maxPartySize,
);
</script>
