<template>
  <svg
    viewBox="0 0 600 300"
    preserveAspectRatio="xMidYMid slice"
    aria-hidden="true"
    focusable="false"
    :class="['battle-scene-background', `battle-scene-background--${scene}`]"
    :data-test="`battle-scene-background--${scene}`"
  >
    <rect
      class="battle-scene-background__sky"
      width="600"
      height="174"
    />
    <rect
      class="battle-scene-background__ground"
      y="174"
      width="600"
      height="126"
    />

    <g v-if="scene === BattleScene.GRASS_FIELD">
      <rect
        class="battle-scene-background__light"
        x="70"
        y="34"
        width="96"
        height="22"
        rx="11"
      />
      <rect
        class="battle-scene-background__light"
        x="380"
        y="58"
        width="128"
        height="26"
        rx="13"
      />
      <ellipse
        class="battle-scene-background__far"
        cx="130"
        cy="178"
        rx="210"
        ry="46"
      />
      <ellipse
        class="battle-scene-background__far"
        cx="470"
        cy="180"
        rx="230"
        ry="40"
      />
      <path
        class="battle-scene-background__detail"
        d="M60 236 l8 -16 l8 16 z M84 240 l7 -13 l7 13 z M300 262 l8 -16 l8 16 z M520 222 l8 -16 l8 16 z M542 226 l7 -13 l7 13 z M420 276 l8 -16 l8 16 z"
      />
    </g>

    <g v-else-if="scene === BattleScene.TRAINER_ARENA">
      <rect
        class="battle-scene-background__far"
        y="132"
        width="600"
        height="42"
      />
      <g class="battle-scene-background__line">
        <path d="M110 190 H490 L560 290 H40 Z" />
        <path d="M300 190 V290" />
        <ellipse
          cx="300"
          cy="238"
          rx="70"
          ry="22"
        />
      </g>
    </g>

    <g v-else-if="scene === BattleScene.GYM">
      <rect
        class="battle-scene-background__far"
        y="20"
        width="600"
        height="16"
      />
      <rect
        v-for="x in [40, 170, 300, 430, 560]"
        :key="x"
        class="battle-scene-background__far"
        y="36"
        width="28"
        height="138"
        :x="x - 14"
      />
      <g class="battle-scene-background__line">
        <path
          d="M0 210 H600 M0 252 H600 M100 174 L60 300 M240 174 L220 300 M360 174 L380 300 M500 174 L540 300"
        />
      </g>
    </g>

    <g v-else-if="scene === BattleScene.INDIGO_PLATEAU">
      <path
        class="battle-scene-background__far"
        d="M0 174 L90 82 L170 150 L260 60 L360 156 L450 92 L600 174 Z"
      />
      <path
        class="battle-scene-background__light"
        d="M260 60 L284 84 L270 80 L258 92 L246 80 L236 84 Z M90 82 L110 102 L96 99 L84 108 L72 100 Z"
      />
      <ellipse
        class="battle-scene-background__detail"
        cx="300"
        cy="238"
        rx="120"
        ry="34"
      />
      <ellipse
        class="battle-scene-background__line"
        cx="300"
        cy="238"
        rx="84"
        ry="22"
      />
    </g>

    <g v-else-if="scene === BattleScene.BATTLE_TOWER">
      <rect
        v-for="tower in towers"
        :key="tower.x"
        class="battle-scene-background__far"
        :x="tower.x"
        :y="174 - tower.height"
        :width="tower.width"
        :height="tower.height"
      />
      <rect
        v-for="towerWindow in towerWindows"
        :key="`${towerWindow.x}-${towerWindow.y}`"
        class="battle-scene-background__light"
        width="8"
        height="10"
        :x="towerWindow.x"
        :y="towerWindow.y"
      />
      <g class="battle-scene-background__line">
        <path
          d="M0 200 H600 M0 236 H600 M0 276 H600 M150 174 L90 300 M300 174 V300 M450 174 L510 300"
        />
      </g>
    </g>
  </svg>
</template>

<script setup lang="ts">
import { BattleScene } from '../../types/enums/BattleScene';

defineProps<{
  scene: BattleScene;
}>();

const towers = [
  { x: 30, width: 70, height: 96 },
  { x: 130, width: 54, height: 140 },
  { x: 410, width: 60, height: 120 },
  { x: 500, width: 80, height: 84 },
];

const towerWindows = towers.flatMap((tower) =>
  Array.from({ length: Math.floor((tower.height - 20) / 24) }, (_, row) => [
    { x: tower.x + 12, y: 174 - tower.height + 14 + row * 24 },
    { x: tower.x + tower.width - 20, y: 174 - tower.height + 14 + row * 24 },
  ]).flat(),
);
</script>

<style scoped>
@reference '@css/app.css';

.battle-scene-background {
  @apply pointer-events-none absolute inset-0 size-full;
}

.battle-scene-background__sky {
  fill: var(--scene-sky);
}

.battle-scene-background__ground {
  fill: var(--scene-ground);
}

.battle-scene-background__far {
  fill: var(--scene-far);
}

.battle-scene-background__light {
  fill: var(--scene-light);
}

.battle-scene-background__detail {
  fill: var(--scene-detail);
}

.battle-scene-background__line {
  fill: none;
  stroke: var(--scene-line);
  stroke-width: 3;
}

.battle-scene-background--grass-field {
  --scene-sky: oklch(0.94 0.03 145);
  --scene-ground: oklch(0.79 0.07 145);
  --scene-far: oklch(0.86 0.06 145);
  --scene-light: oklch(0.98 0.01 145);
  --scene-detail: oklch(0.7 0.09 145);

  @variant dark {
    --scene-sky: oklch(0.27 0.03 145);
    --scene-ground: oklch(0.33 0.05 145);
    --scene-far: oklch(0.3 0.04 145);
    --scene-light: oklch(0.34 0.02 145);
    --scene-detail: oklch(0.4 0.06 145);
  }
}

.battle-scene-background--trainer-arena {
  --scene-sky: oklch(0.96 0 0);
  --scene-ground: oklch(0.87 0 0);
  --scene-far: oklch(0.91 0 0);
  --scene-line: oklch(0.97 0 0);

  @variant dark {
    --scene-sky: oklch(0.25 0 0);
    --scene-ground: oklch(0.31 0 0);
    --scene-far: oklch(0.28 0 0);
    --scene-line: oklch(0.38 0 0);
  }
}

.battle-scene-background--gym {
  --scene-sky: oklch(0.94 0.004 75);
  --scene-ground: oklch(0.72 0.01 60);
  --scene-far: oklch(0.88 0.008 75);
  --scene-line: oklch(0.66 0.01 60);

  @variant dark {
    --scene-sky: oklch(0.26 0.004 75);
    --scene-ground: oklch(0.33 0.01 60);
    --scene-far: oklch(0.3 0.008 75);
    --scene-line: oklch(0.28 0.01 60);
  }
}

.battle-scene-background--indigo-plateau {
  --scene-sky: oklch(0.93 0.02 300);
  --scene-ground: oklch(0.72 0.05 300);
  --scene-far: oklch(0.84 0.04 300);
  --scene-light: oklch(0.97 0.01 300);
  --scene-detail: oklch(0.66 0.06 300);
  --scene-line: oklch(0.8 0.05 300);

  @variant dark {
    --scene-sky: oklch(0.25 0.03 300);
    --scene-ground: oklch(0.32 0.05 300);
    --scene-far: oklch(0.3 0.04 300);
    --scene-light: oklch(0.4 0.03 300);
    --scene-detail: oklch(0.36 0.06 300);
    --scene-line: oklch(0.44 0.06 300);
  }
}

.battle-scene-background--battle-tower {
  --scene-sky: oklch(0.94 0.02 250);
  --scene-ground: oklch(0.8 0.04 250);
  --scene-far: oklch(0.87 0.03 250);
  --scene-light: oklch(0.96 0.01 250);
  --scene-line: oklch(0.86 0.03 250);

  @variant dark {
    --scene-sky: oklch(0.25 0.03 250);
    --scene-ground: oklch(0.32 0.04 250);
    --scene-far: oklch(0.3 0.03 250);
    --scene-light: oklch(0.38 0.03 250);
    --scene-line: oklch(0.37 0.04 250);
  }
}
</style>
