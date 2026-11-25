<template>
  <svg
    viewBox="0 0 600 300"
    preserveAspectRatio="xMidYMid slice"
    aria-hidden="true"
    focusable="false"
    :class="[
      'pointer-events-none absolute inset-0 size-full',
      sceneColors[scene],
    ]"
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

const sceneColors: Record<BattleScene, string> = {
  [BattleScene.GRASS_FIELD]:
    '[--scene-sky:oklch(0.94_0.03_145)] [--scene-ground:oklch(0.79_0.07_145)] [--scene-far:oklch(0.86_0.06_145)] [--scene-light:oklch(0.98_0.01_145)] [--scene-detail:oklch(0.7_0.09_145)] dark:[--scene-sky:oklch(0.27_0.03_145)] dark:[--scene-ground:oklch(0.33_0.05_145)] dark:[--scene-far:oklch(0.3_0.04_145)] dark:[--scene-light:oklch(0.34_0.02_145)] dark:[--scene-detail:oklch(0.4_0.06_145)]',
  [BattleScene.TRAINER_ARENA]:
    '[--scene-sky:oklch(0.96_0_0)] [--scene-ground:oklch(0.87_0_0)] [--scene-far:oklch(0.91_0_0)] [--scene-line:oklch(0.97_0_0)] dark:[--scene-sky:oklch(0.25_0_0)] dark:[--scene-ground:oklch(0.31_0_0)] dark:[--scene-far:oklch(0.28_0_0)] dark:[--scene-line:oklch(0.38_0_0)]',
  [BattleScene.GYM]:
    '[--scene-sky:oklch(0.94_0.004_75)] [--scene-ground:oklch(0.72_0.01_60)] [--scene-far:oklch(0.88_0.008_75)] [--scene-line:oklch(0.66_0.01_60)] dark:[--scene-sky:oklch(0.26_0.004_75)] dark:[--scene-ground:oklch(0.33_0.01_60)] dark:[--scene-far:oklch(0.3_0.008_75)] dark:[--scene-line:oklch(0.28_0.01_60)]',
  [BattleScene.INDIGO_PLATEAU]:
    '[--scene-sky:oklch(0.93_0.02_300)] [--scene-ground:oklch(0.72_0.05_300)] [--scene-far:oklch(0.84_0.04_300)] [--scene-light:oklch(0.97_0.01_300)] [--scene-detail:oklch(0.66_0.06_300)] [--scene-line:oklch(0.8_0.05_300)] dark:[--scene-sky:oklch(0.25_0.03_300)] dark:[--scene-ground:oklch(0.32_0.05_300)] dark:[--scene-far:oklch(0.3_0.04_300)] dark:[--scene-light:oklch(0.4_0.03_300)] dark:[--scene-detail:oklch(0.36_0.06_300)] dark:[--scene-line:oklch(0.44_0.06_300)]',
  [BattleScene.BATTLE_TOWER]:
    '[--scene-sky:oklch(0.94_0.02_250)] [--scene-ground:oklch(0.8_0.04_250)] [--scene-far:oklch(0.87_0.03_250)] [--scene-light:oklch(0.96_0.01_250)] [--scene-line:oklch(0.86_0.03_250)] dark:[--scene-sky:oklch(0.25_0.03_250)] dark:[--scene-ground:oklch(0.32_0.04_250)] dark:[--scene-far:oklch(0.3_0.03_250)] dark:[--scene-light:oklch(0.38_0.03_250)] dark:[--scene-line:oklch(0.37_0.04_250)]',
};

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
</style>
