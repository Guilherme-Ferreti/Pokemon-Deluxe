<template>
  <Table v-if="page.props.speciesList.data.length > 0">
    <TableHeader>
      <TableRow>
        <TableHead class="w-12 whitespace-normal">Dex No.</TableHead>
        <TableHead>Portrait</TableHead>
        <AppTableHeadSortableLink
          class="w-50"
          column-name="Name"
          :sort-by="SearchPokedexSortBy.NAME"
          :default-sort-by="SearchPokedexSortBy.POKEDEX_NUMBER"
          :search-form="searchForm"
          :route-url="route('dex.web.pokedex.index')"
          :sort-asc-icon="IconSortAscendingLetters"
          :sort-desc-icon="IconSortDescendingLetters"
        />
        <TableHead>Type(s)</TableHead>
        <TableHead>Generation</TableHead>
        <TableHead>Regular</TableHead>
        <TableHead>Shiny</TableHead>
        <TableHead></TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow
        v-for="species in page.props.speciesList.data"
        :key="species.id"
      >
        <TableCell class="text-muted-foreground">
          {{ species.pokedexNumber }}
        </TableCell>
        <TableCell>
          <SpeciesPortrait
            :name="species.name"
            :url="species.portrait"
          />
        </TableCell>
        <TableCell class="font-medium whitespace-normal">
          {{ species.name }}
        </TableCell>
        <TableCell>
          <ElementalTypeBadges :types="species.types" />
        </TableCell>
        <TableCell class="text-muted-foreground">
          {{ $page.props.labels.generation[species.generation] }}
        </TableCell>
        <TableCell>
          <InBoxIcon v-if="species.regularInBox" />
          <NotInBoxIcon v-else />
        </TableCell>
        <TableCell>
          <InBoxIcon v-if="species.shinyInBox" />
          <NotInBoxIcon v-else />
        </TableCell>
        <TableCell>
          <Link
            class="flex items-center gap-1 hover:underline"
            data-test="pokemon-box-card__view-species-link"
            :href="route('dex.web.pokedex.show', { species: species.slug })"
          >
            <IconEye class="size-4" />
            <span aria-hidden="true">View</span>
            <span class="sr-only">View {{ species.name }}</span>
          </Link>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
  <AppEmptyState
    v-else
    title="No Pokémon found"
    description="Change your search or filters to find some."
    data-test="pokedex__empty-state"
    :icon="IconXMark"
  />
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
  IconEye,
  IconSortAscendingLetters,
  IconSortDescendingLetters,
  IconXMark,
} from '@tabler/icons-vue';
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import ElementalTypeBadges from '@/shared/components/game/ElementalTypeBadges.vue';
import InBoxIcon from '@/shared/components/game/InBoxIcon.vue';
import NotInBoxIcon from '@/shared/components/game/NotInBoxIcon.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import type { AppTableHeadSortableLinkProps } from '@/shared/components/list/AppTableHeadSortableLink.vue';
import AppTableHeadSortableLink from '@/shared/components/list/AppTableHeadSortableLink.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/shared/components/ui/table';
import { route } from '@/shared/types/helpers/route';
import { SearchPokedexSortBy } from '../../../types/enums/SearchPokedexSortBy';
import type { PokedexIndexPageProps } from '../../../types/page-props/PokedexIndexPageProps';

const page = usePage<PokedexIndexPageProps>();

defineProps<{
  searchForm: AppTableHeadSortableLinkProps<SearchPokedexSortBy>['searchForm'];
}>();
</script>
