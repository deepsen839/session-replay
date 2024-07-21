module.exports = (api) => {
  api.cache(true);
  const presets = [
    [
      '@babel/preset-env',
      {
        targets: "last 2 years, not dead",
        useBuiltIns: 'usage',
        corejs: 3,
        debug: true,
      },
    ],
  ];

  const plugins = [
  ];

  return {
    presets,
    plugins,
  };
};
