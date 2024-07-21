import path from 'path';

export default (env, args) => {
  const isProduction = args.mode === 'production';
  const mode = isProduction ? 'production' : 'development';
  const devtool = !isProduction && 'inline-source-map';
  const rules = [
    {
      test: /\.js$/,
      use: ['babel-loader'],
    },
  ];

  const plugins = [];

  return {
    mode,
    devtool,
    entry: './js/aurora-heatmap.js',
    output: {
      path: path.join(__dirname, './js'),
      filename: 'aurora-heatmap.min.js',
    },
    module: { rules },
    plugins,
  };
};
