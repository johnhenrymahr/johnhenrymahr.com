var path = require('path')
var parseArgs = require('minimist')
const args = parseArgs(process.argv.slice(2))
const webpack = require('webpack')
const merge = require('webpack-merge')

const TARGET = process.env.npm_lifecycle_event || process.env.gulp_target
console.log('target: ', TARGET)
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
    app: PATHS.app + '/bootstrap'
  },

  output: {
    path: PATHS.build,
    filename: 'bundle.js'
  },
  externals: {
    'jquery': 'jQuery',
    '$': 'jQuery'
  },
  resolve: {
    root: path.resolve(__dirname),
    modulesDirectories: ['node_modules', 'app'],
    extensions: ['', '.js', '.dust'],
    alias: {
      dustjs: PATHS.modules + '/dustjs-linkedin',
      'dust.core': PATHS.modules + '/dustjs-linkedin',
      sinon: 'sinon/pkg/sinon'
    }
  },
  module: {
    loaders: [
      {
        test: /sinon\.js$/,
        loader: 'imports?define=>false,require=>false'
      },
      {
        test: /\.json$/,
        loader: 'json',
        include: PATHS.data
      },
      {
        test: /\.dust$/,
        loader: 'dust-loader-complete',
        include: PATHS.app + '/dust'
      },
      {
        test: /\.(jpe?g|png|gif)$/i,
        loader: 'file-loader',
        query: {
          outputPath: 'img/',
          publicPath: '/rsc/'
        }

      },
      {
        test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        loader: 'file-loader',
        query: {
          name: '[name].[ext]',
          outputPath: 'fonts/',
          publicPath: '/rsc/'
        }
      }
    ],
    noParse: [/sinon/]
  }
}

// Default configuration
if (TARGET === 'start' || TARGET === 'test') {
  var scenarios = require(PATHS.data + '/scenarios.json')
  var scenarioKey = (args.s) ? args.s : 'default'
  var bootstrapData = scenarios[scenarioKey]

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
      // host: process.env.HOST || '0.0.0.0'
      //
      // 0.0.0.0 is available to all network devices unlike default
      // localhost
      host: '0.0.0.0',
      port: process.env.PORT
    },
    module: {
      loaders: [
        {
          test: /\.less$/,
          loader: 'style!css!less',
          include: PATHS.app + '/less'
        }]
    },
    plugins: [
      new webpack.HotModuleReplacementPlugin(),
      new webpack.DefinePlugin({
        'window.jhmData': JSON.stringify(bootstrapData),
        'window.appLogging': true,
        'window.localDev': true
      })
    ]
  })
}

if (TARGET === 'build') {
  var autoprefixer = require('autoprefixer')
  var ExtractTextPlugin = require('extract-text-webpack-plugin')
  var AssetsPlugin = require('assets-webpack-plugin')
  var CopyWebpackPlugin = require('copy-webpack-plugin')
  var CleanWebpackPlugin = require('clean-webpack-plugin')

  module.exports = merge(common, {
    devtook: 'source-map',
    output: {
      path: PATHS.build + '/rsc/',
      filename: 'js/[name]_[hash]bundle.js'
    },
    module: {
      loaders: [
        {
          test: /\.less$/,
          loader: ExtractTextPlugin.extract(
            // activate source maps via loader query
            'css?sourceMap!' +
            'postcss-loader!' +
            'less?sourceMap'
          ),
          include: PATHS.app + '/less'
        }]
    },
    postcss: [ autoprefixer({
      browsers: ['last 2 versions']
    }) ],
    plugins: [
      new ExtractTextPlugin('css/[hash]styles.css'),
      new webpack.optimize.UglifyJsPlugin(),
      new webpack.optimize.DedupePlugin(),
      new AssetsPlugin({
        path: PATHS.data,
        prettyPrint: true
      }),
      new CopyWebpackPlugin([{from: 'assets/', to: '', ignore: '*.'}], {copyUnmodified: true}),
      new CleanWebpackPlugin(['build'], {
        verbose: true,
        dry: false,
        exclude: ['index.html']
      })
    ]
  })
}
