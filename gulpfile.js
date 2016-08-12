var gulp = require('gulp')

var merge = require('merge')

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

var del = require('del')

var runSequence = require('run-sequence')

var phpunit = require('gulp-phpunit')

var shell = require('gulp-shell')

var standard = require('gulp-standard')

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

gulp.task('copy:server', function () {
  return gulp.src('server/**/*')
    .pipe(gulp.dest('bin/'))
})

gulp.task('copy:app', function () {
  return gulp.src('build/rsc/**/*')
    .pipe(gulp.dest('bin/webroot/rsc'))
})

gulp.task('copy:data', function () {
  return gulp.src(['data/viewManifest.json', 'data/webpack-assets.json'])
    .pipe(gulp.dest('bin/data'))
})

gulp.task('copy:dust', function () {
  return gulp.src('app/dust/**/*')
    .pipe(gulp.dest('bin/dust'))
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

gulp.task('deploy', function (callback) {
  runSequence(
    'clean:bin',
    'lint',
    'test:app',
    'test:server',
    'build',
    'copy:server',
    ['copy:app', 'copy:data', 'copy:dust'],
    callback
  )
})

function throwError (taskName, msg) {
  throw new gutil.PluginError({
    plugin: taskName,
    message: msg
  })
}
