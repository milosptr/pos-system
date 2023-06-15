module.exports = {
  parser: '@typescript-eslint/parser',
  env: {
    commonjs: true,
    node: true,
    browser: true,
    es6: true
  },
  plugins: ['@typescript-eslint', 'react', 'react-hooks'],
  extends: [
    'plugin:react/recommended',
    'prettier',
    'plugin:@typescript-eslint/eslint-recommended',
    'plugin:@typescript-eslint/recommended'
  ],
  rules: {
    '@typescript-eslint/no-unused-vars': [
      'error',
      {
        argsIgnorePattern: '^_',
        varsIgnorePattern: '^_',
        caughtErrorsIgnorePattern: '^_'
      }
    ],
    'react/react-in-jsx-scope': 'off',
    'react-hooks/rules-of-hooks': 'error', // Checks rules of Hooks
    '@typescript-eslint/no-explicit-any': 'off',
    '@typescript-eslint/no-var-requires': 'off',
    '@typescript-eslint/explicit-module-boundary-types': ['off'],
    '@typescript-eslint/ban-types': 'error'
  },
  settings: {
    react: {
      version: 'detect'
    },
    typescript: {},
    'import/resolver': {
      node: {
        paths: ['src'],
        extensions: ['.js', '.jsx', '.json', '.ts', '.tsx']
      }
    }
  }
}
