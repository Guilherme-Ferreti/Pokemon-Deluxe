<template>
  <Breadcrumb>
    <BreadcrumbList>
      <template
        v-for="(crumb, index) in breadcrumbs"
        :key="index"
      >
        <BreadcrumbItem>
          <BreadcrumbLink
            v-if="crumb.href"
            :href="crumb.href"
          >
            {{ crumb.title }}
          </BreadcrumbLink>
          <BreadcrumbPage v-else>
            {{ crumb.title }}
          </BreadcrumbPage>
        </BreadcrumbItem>
        <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
      </template>
    </BreadcrumbList>
  </Breadcrumb>
</template>

<script setup lang="ts">
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

const { breadcrumbs } = useBreadcrumbs();
</script>
