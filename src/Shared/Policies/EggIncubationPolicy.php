<?php

declare(strict_types=1);

namespace Shared\Models\Policies;

use Illuminate\Auth\Access\Response;
use Shared\Models\EggIncubation;
use Shared\Models\User;

final class EggIncubationPolicy
{
    public function hatchEgg(User $user, EggIncubation $eggIncubation): Response
    {
        return $user->id === $eggIncubation->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
