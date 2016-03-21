var path = require('path')
const webpack = require('webpack');
const merge = require('webpack-merge');

const TARGET = process.env.npm_lifecycle_event;
const PATHS = {
  app: path.join(__dirname, 'app'),
  build: path.join(__dirname, 'build'),
  data: path.join(__dirname, 'data'),
  modules: path.join(__dirname, 'node_modules')
}

const common = {
  context: __dirname,
  // Entry accepts a path or an object of entries. We'll be using the
  // latter form given it's convenient with more complex configurations.
  entry: {
    app: PATHS.app+'/bootstrap'
  },
  output: {
    path: PATHS.build,
    filename: 'bundle.js'
  },
  resolve: {
    root: path.resolve(__dirname),
    alias: {
          dustjs: PATHS.modules+'/dustjs-linkedin',
          'dust.core': PATHS.modules+'/dustjs-linkedin'
    }
  },
  module: {
    loaders: [
       {
        test: /\.json$/,
        loader: "json",
        include: PATHS.data
      },
      {
        test: /\.dust$/,
        loader: "dust-loader-complete",
        include: PATHS.app+'/dust'
      }
    ]
  }
};


// Default configuration
if(TARGET === 'start' || !TARGET) {
  module.exports = merge(common, {
    devServer: {
      contentBase: PATHS.build,

      // Enable history API fallback so HTML5 History API based
      // routing works. This is a good default that will come
      // in handy in more complicated setups.
      historyApiFallback: true,
      hot: true,
      inline: true,
      progress: true,
      devtool: 'eval-source-map',

      // Display only errors to reduce the amount of output.
      stats: 'errors-only',

      // Parse host and port from env so this is easy to customize.
      //
      // If you use Vagrant or Cloud9, set
      // host: process.env.HOST || '0.0.0.0';
      //
      // 0.0.0.0 is available to all network devices unlike default
      // localhost
      host: process.env.HOST,
      port: process.env.PORT
    },
    module: {
      loaders: [
       {
        test: /\.less$/,
        loader: "style!css!less",
        include: PATHS.app+'/less'
      }]
    },
    plugins: [
      new webpack.HotModuleReplacementPlugin()
    ]
  })
}

if(TARGET === 'build') {
  var ExtractTextPlugin = require('extract-text-webpack-plugin');

  module.exports = merge(common, {
    devtook: 'source-map',
    module: {
      loaders: [
        {
            test: /\.less$/,
            loader: ExtractTextPlugin.extract(
                // activate source maps via loader query
                'css?sourceMap!' +
                'less?sourceMap'
            ),
            include: PATHS.app+'/less'
        }]
    },
    plugins: [
      new ExtractTextPlugin('styles.css'),
      new webpack.optimize.UglifyJsPlugin(),
      new webpack.optimize.DedupePlugin()
    ]
  });
}
