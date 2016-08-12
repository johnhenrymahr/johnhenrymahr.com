var gulp = require('gulp')

var _ = require('lodash')

var gutil = require('gulp-util')

// Minimist - https://www.npmjs.com/package/minimist
var argv = require('minimist')(process.argv)

// gulp-rsync - https://www.npmjs.com/package/gulp-rsync
var rsync = require('gulp-rsync')

// gulp-prompt - https://www.npmjs.com/package/gulp-prompt
var prompt = require('gulp-prompt')

// gulp-if - https://www.npmjs.com/package/gulp-if
var gulpif = require('gulp-if')

var del = require('del')

var runSequence = require('run-sequence')

var phpunit = require('gulp-phpunit')

var shell = require('gulp-shell')

var standard = require('gulp-standard')

var servers = require('./servers.json')

function Server (servers, serverKey) {
  serverKey = serverKey || ''
  var server
  if (_.has(servers, serverKey)) {
    server = servers.serverKey
    _.defaults(server, servers._defaults)
  } else {
    server = servers._defaults
  }
  this.atts = server

  this.name = serverKey

  this.get = function (key) {
    if (_.has(server, key)) {
      return server[key]
    }
    return null
  }

  return this
}

var server = new Server(servers, argv.server)

gulp.task('server', function (cb) {
  gutil.log('server ID:', server.name)
  gutil.log('webroot: ', server.get('webroot'))
  gutil.log('server app: ', server.get('serverApp'))
  cb()
})

gulp.task('clean:bin', function () {
  return del(['bin/**/*'])
})

gulp.task('lint', function () {
  return gulp.src(['app/*.js', 'app/**/*.js'])
    .pipe(standard())
    .pipe(standard.reporter('default', {
      breakOnError: true
    }))
})

gulp.task('test:server', function (callback) {
  gulp.src('server/phpunit.xml')
    .pipe(phpunit('./server/vendor/bin/phpunit', {
      notify: false,
      stopOnFailure: true
    }, callback))
})

gulp.task('test:app', shell.task([
  'npm test'
]))

gulp.task('build', shell.task([
  'npm run build'
]))

gulp.task('copy:webroot', function () {
  return gulp.src('server/webroot/*')
    .pipe(gulp.dest('bin/' + server.get('webroot') + '/'))
})

gulp.task('copy:libs', function () {
  return gulp.src('server/libs/*')
    .pipe(gulp.dest('bin/' + server.get('serverApp') + '/libs/'))
})

gulp.task('copy:includes', function () {
  return gulp.src('server/includes/*')
    .pipe(gulp.dest('bin/' + server.get('serverApp') + '/includes/'))
})

gulp.task('copy:vendor', function () {
  return gulp.src('server/vendor/**/*')
    .pipe(gulp.dest('bin/' + server.get('serverApp') + '/vendor/'))
})

gulp.task('copy:app', function () {
  return gulp.src('build/rsc/**/*')
    .pipe(gulp.dest('bin/' + server.get('webroot') + '/rsc'))
})

gulp.task('copy:data', function () {
  return gulp.src(['data/viewManifest.json', 'data/webpack-assets.json'])
    .pipe(gulp.dest('bin/' + server.get('serverApp') + '/data'))
})

gulp.task('copy:dust', function () {
  return gulp.src('app/dust/**/*')
    .pipe(gulp.dest('bin/' + server.get('serverApp') + '/dust'))
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

  if (server.get('supportsRsync') === false) {
    throwError('gulp-rsync', 'server does not support rsync.')
  }

  rsyncConf = _.merge(rsyncConf, server.get('rsync'))

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

gulp.task('deploy', function (callback) {
  runSequence(
    'server',
    'clean:bin',
    'lint',
    'test:app',
    'test:server',
    'build',
    ['copy:libs', 'copy:includes', 'copy:data', 'copy:dust', 'copy:vendor'],
    'copy:webroot',
    'copy:app',
    callback
  )
})

function throwError (taskName, msg) {
  throw new gutil.PluginError({
    plugin: taskName,
    message: msg
  })
}
