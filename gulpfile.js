/** *****************************************************************************
 * https://gist.github.com/plasticbrain/b98b5c3b97e7226353ce
 * http://mikeeverhart.net/2016/01/deploy-code-to-remote-servers-with-gulp-js/
 * Description:
 *
 *   Gulp file to push changes to remote servers (eg: staging/production)
 *
 * Usage:
 *
 *   gulp deploy --target
 *
 * Examples:
 *
 *   gulp deploy --production   // push to production
 *   gulp deploy --staging      // push to staging
 *
 ******************************************************************************/
var gulp = require('gulp')

var path = require('path')

var merge = require('merge')

var exec = require('child_process').exec

// gulp-util - https://www.npmjs.com/package/gulp-util
var gutil = require('gulp-util')

// Minimist - https://www.npmjs.com/package/minimist
var argv = require('minimist')(process.argv)

// gulp-rsync - https://www.npmjs.com/package/gulp-rsync
var rsync = require('gulp-rsync')

// gulp-prompt - https://www.npmjs.com/package/gulp-prompt
var prompt = require('gulp-prompt')

// gulp-if - https://www.npmjs.com/package/gulp-if
var gulpif = require('gulp-if')

var webpack = require('webpack')

var KarmaServer = require('karma').Server

gulp.task('test', function (done) {
  process.env.gulp_target = 'test'
  new KarmaServer({
    configFile: path.join(__dirname, '/karma.conf.js'),
    singleRun: true
  }, done).start({port: 9876})
})

gulp.task('build', function (done) {
  process.env.gulp_target = 'build'

  var config = require('./webpack.config')

  webpack(config, function (err, stats) {
    if (err) {
      throwError('webpack', err)
    }
    gutil.log('[webpack]', stats.toString({
      // output options
    }))
    done()
  })
})

gulp.task('copy-server', function () {
  return gulp.src('server/**/*')
    .pipe(gulp.dest('bin/'))
})

gulp.task('copy-app', function () {
  return gulp.src('build/rsc/**/*')
    .pipe(gulp.dest('bin/webroot/rsc'))
})

gulp.task('copy-data', function () {
  return gulp.src(['data/viewManifest.json', 'data/webpack-assets.json'])
    .pipe(gulp.dest('bin/data'))
})

gulp.task('copy-dust', function () {
  return gulp.src('app/dust/**/*')
    .pipe(gulp.dest('bin/dust'))
})

gulp.task('version-bump', function (done) {
  done()
})

gulp.task('rsync', function () {
  // Dirs and Files to sync
  var rsyncPaths = ['bin/**/*']

  // Default options for rsync
  var rsyncConf = {
    progress: true,
    hostname: '',
    username: '',
    destination: '',
    incremental: true,
    relative: true,
    emptyDirectories: true,
    recursive: true,
    clean: true,
    exclude: []
  }

  var servers = {
    production: {},
    staging: {}
  }

  // Staging
  if (argv.staging) {
    rsyncConf = merge(rsyncConf, servers.staging)
  // Production
  } else if (argv.production) {
    rsyncConf = merge(rsyncConf, servers.production)
  // Missing/Invalid Target
  } else {
    throwError('deploy', gutil.colors.red('Missing or invalid target'))
  }

  // Use gulp-rsync to sync the files
  return gulp.src(rsyncPaths)
    .pipe(gulpif(
      argv.production,
      prompt.confirm({
        message: 'Heads Up! Are you SURE you want to push to PRODUCTION?',
        default: false
      })
    ))
    .pipe(rsync(rsyncConf))
})

function throwError (taskName, msg) {
  throw new gutil.PluginError({
    plugin: taskName,
    message: msg
  })
}
