import { NodeTypes } from '@vue/compiler-dom';
import type { NodeTransform } from '@vue/compiler-dom';

const TEST_ID_ATTRIBUTES = new Set(['data-testid', 'data-test']);

export const stripTestIds: NodeTransform = (node) => {
  if (node.type !== NodeTypes.ELEMENT) {
    return;
  }

  node.props = node.props.filter((prop) => {
    if (prop.type === NodeTypes.ATTRIBUTE) {
      return !TEST_ID_ATTRIBUTES.has(prop.name);
    }

    if (prop.type === NodeTypes.DIRECTIVE && prop.name === 'bind') {
      const arg = prop.arg;

      return !(
        arg?.type === NodeTypes.SIMPLE_EXPRESSION &&
        arg.isStatic &&
        TEST_ID_ATTRIBUTES.has(arg.content)
      );
    }

    return true;
  });
};
