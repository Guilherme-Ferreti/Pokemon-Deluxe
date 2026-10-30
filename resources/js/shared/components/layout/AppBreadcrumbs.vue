<template>
  <Breadcrumb>
    <BreadcrumbList>
      <BreadcrumbItem
        v-for="(item, index) in breadcrumbs"
        :key="item.name"
      >
        <BreadcrumbLink
          as-child
          v-if="item.href"
        >
          <AppNavLink
            :title="item.name"
            :url="item.href"
          />
        </BreadcrumbLink>
        <BreadcrumbPage v-else>{{ item.name }}</BreadcrumbPage>
        <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
      </BreadcrumbItem>
    </BreadcrumbList>
  </Breadcrumb>
</template>

<script setup lang="ts">
import AppNavLink from '@/shared/components/layout/AppNavLink.vue';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/shared/components/ui/breadcrumb';

defineProps<{
  breadcrumbs: { name: string; href?: string }[];
}>();
</script>
