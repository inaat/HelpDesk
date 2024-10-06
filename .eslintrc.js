module.exports = {
  extends: ['eslint:recommended', 'plugin:vue/recommended'],
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module',
  },
  env: {
    amd: true,
    browser: true,
    es6: true,
  },
  rules: {
    // Turn off all rules
    quotes: 'off',
    semi: 'off',
    'no-unused-vars': 'off',
    'comma-dangle': 'off',
    'vue/max-attributes-per-line': 'off',
    'vue/require-default-prop': 'off',
    'vue/singleline-html-element-content-newline': 'off',
    'vue/html-self-closing': 'off',
  },
}
