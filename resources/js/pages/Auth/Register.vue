<template>
  <Head title="Register" />
  <Form
    v-slot="{ errors, validate }"
    method="post"
    class="flex flex-col gap-6"
    :action="route('web.auth.register.store')"
    disable-while-processing
    :reset-on-error="['password', 'password_confirmation']"
  >
    <FieldGroup>
      <AppAuthFormHeader
        title="Create an account"
        description="Enter your details below to create your account"
      />
      <AppInput
        label="Name"
        type="text"
        name="name"
        placeholder="John Doe"
        required
        :error-message="errors.name"
      />
      <AppInput
        label="Email"
        type="email"
        name="email"
        placeholder="test@example.com"
        required
        :error-message="errors.email"
        @blur="validate('email')"
      />
      <AppInput
        label="Password"
        type="password"
        name="password"
        required
        :error-message="errors.password"
      />
      <AppInput
        label="Confirm Password"
        type="password"
        name="password_confirmation"
        required
        :error-message="errors.password_confirmation"
      />
      <Button type="submit">Create account</Button>
      <FieldDescription class="text-center">
        Already have an account?
        <Link :href="route('web.auth.login.create')">Log in</Link>
      </FieldDescription>
    </FieldGroup>
  </Form>
</template>

<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AppAuthFormHeader from '@/components/AppAuthFormHeader.vue';
import AppInput from '@/components/AppInput.vue';
import { Button } from '@/components/ui/button';
import { FieldDescription, FieldGroup } from '@/components/ui/field';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { route } from '@/types/helpers/route';

defineOptions({
  layout: AuthLayout,
});
</script>
