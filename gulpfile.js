var gulp = require('gulp')

var _ = require('lodash')

var path = require('path')

var gutil = require('gulp-util')

var argv = require('minimist')(process.argv)

var del = require('del')

var runSequence = require('run-sequence')

var phpunit = require('gulp-phpunit')

var shell = require('gulp-shell')

var standard = require('gulp-standard')

var confirm = require('confirm-simple')

var rsync = require('gulp-rsync')

var phplint = require('phplint').lint

var fs = require('fs')

function Server (serverKey) {
  var servers = require('./servers.json')
  var serverConfPath = argv.serverconf || path.normalize(path.join(process.env.HOME, '/.serverconf.json'))
  var privateServers
  try {
    privateServers = require(serverConfPath)
    gutil.log(gutil.colors.green('Using private server config: ' + gutil.colors.bold(serverConfPath)))
  } catch (e) {
    gutil.log(gutil.colors.red('Could not load server config path: ' + gutil.colors.bold(serverConfPath)))
    privateServers = {}
  }

  servers = _.merge(servers, privateServers)
  serverKey = serverKey || ''
  var server
  if (_.has(servers, serverKey)) {
    server = servers[serverKey]
    _.defaults(server, servers._defaults)
  } else {
    if (serverKey === 'production') {
      throwError('server ', 'production deffinition not avaiable.')
    }
    serverKey = 'default'
    server = servers._defaults
  }

  function expand (list) {
    _.each(list, function (item, key, collection) {
      if (_.isString(item)) {
        collection[key] = item.replace('~', process.env.HOME)
      } else if (_.isObject(item) || _.isArray(item)) {
        expand(item)
      }
    })
  }

  expand(server)

  this.atts = server

  this.name = serverKey

  this.get = function (key) {
    if (_.has(this.atts, key)) {
      return this.atts[key]
    }
    return null
  }

  return this
}

var home = __dirname

var server = new Server(argv.server)

gulp.task('server', function (cb) {
  gutil.log('server ID:', server.name)
  gutil.log('webroot: ', server.get('webroot'))
  gutil.log('server app: ', server.get('serverApp'))
  gutil.log('private key path: ', server.get('privateKey'))
  gutil.log(gutil.colors[server.get('color')]('Using Server ' + gutil.colors.bold(server.name) + ' definition.'))
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

gulp.task('composer', shell.task([
  'composer install --no-dev'
], {
  cwd: path.join(home, 'bin', path.basename(server.get('serverApp')))
}))

gulp.task('confirm:push', function (callback) {
  gutil.log(gutil.colors.yellow('Pushing to ' + gutil.colors.bold(server.name)))
  if (server.name === 'production') {
    gutil.log(gutil.colors.red("Heads up. Push to production. Can't undo this!"))
    confirm('Sure?', function (ok) {
      if (ok) {
        callback()
      }
    })
  } else {
    callback()
  }
})

gulp.task('confirm:deploy', function (callback) {
  confirm('Run deployment script?', function (ok) {
    if (ok) {
      callback()
    }
  })
})

gulp.task('copy:webroot', function () {
  return gulp.src('server/webroot/*')
    .pipe(gulp.dest('bin/' + path.basename(server.get('webroot')) + '/'))
})

gulp.task('copy:libs', function () {
  return gulp.src('server/libs/*')
    .pipe(gulp.dest('bin/' + path.basename(server.get('serverApp')) + '/libs/'))
})

gulp.task('copy:includes', function () {
  return gulp.src('server/includes/*')
    .pipe(gulp.dest('bin/' + path.basename(server.get('serverApp')) + '/includes/'))
})

gulp.task('copy:composer', function () {
  return gulp.src(['server/composer.json', 'server/composer.lock'])
    .pipe(gulp.dest('bin/' + path.basename(server.get('serverApp')) + '/'))
})

gulp.task('copy:app', function () {
  return gulp.src('build/rsc/**/*')
    .pipe(gulp.dest('bin/' + path.basename(server.get('webroot')) + '/rsc'))
})

gulp.task('copy:data', function () {
  return gulp.src(['data/viewManifest.json', 'data/webpack-assets.json'])
    .pipe(gulp.dest('bin/' + path.basename(server.get('serverApp')) + '/data'))
})

gulp.task('copy:dust', function () {
  return gulp.src('app/dust/**/*')
    .pipe(gulp.dest('bin/' + path.basename(server.get('serverApp')) + '/dust'))
})

gulp.task('rsync', function () {
  var rsyncOpts = server.get('rsync')
  if (rsyncOpts.enabled === false) {
    throwError('rsync', 'Rsync is not enabled for this server: ' + gutil.colors.bold(server.name))
  }
  gutil.log('shell arg: ' + gutil.colors.bold(rsyncOpts.shell))
  return gulp.src('./bin/**/*')
    .pipe(rsync(rsyncOpts))
})

gulp.task('send', function (callback) {
  runSequence('confirm:push', 'rsync', callback)
})

gulp.task('update:index', function (callback) {
  try {
    var indexPath = path.join('bin', path.basename(server.get('webroot')), 'index.php')
    var index = fs.readFileSync(indexPath, 'utf8')
    var analytics = ''
    index = index.replace('{{serverApp}}', server.get('serverApp'))
    if (server.name === 'production') {
      analytics = fs.readFileSync('inc/analytics.html')
      index = index.replace("ini_set('display_errors', 1);\n", '')
    }
    index = index.replace('{{analytics}}', analytics)
    index = replaceComments(index)
    fs.writeFileSync(indexPath, index, 'utf8')
    callback()
  } catch (e) {
    throwError('update:index', e)
  }
})

gulp.task('update:config', function (callback) {
  try {
    var cfgPath = path.join('bin', path.basename(server.get('serverApp')), 'libs', 'Config.php')
    var config = fs.readFileSync(cfgPath, 'utf8')
    config = config.replace('{{serverApp}}', server.get('serverApp'))
    config = config.replace('{{webroot}}', server.get('webroot'))
    config = replaceComments(config)
    fs.writeFileSync(cfgPath, config, 'utf8')
    callback()
  } catch (e) {
    throwError('update:config', e)
  }
})

gulp.task('phplint', function (cb) {
  phplint([
    'bin/' + server.get('serverApp') + '/libs/*.php',
    'bin/' + server.get('serverApp') + '/includes/*.php',
    'bin/' + server.get('webroot') + '/index.php'
  ], {limit: 10}, function (err, stdout, stderr) {
    if (err) {
      throwError('phplint', err)
    }
    cb()
  })
})

gulp.task('package', function (callback) {
  runSequence(
    'clean:bin',
    'lint',
    'test:app',
    'test:server',
    'build',
    ['copy:composer', 'copy:libs', 'copy:includes', 'copy:data', 'copy:dust'],
    'composer',
    'copy:webroot',
    'copy:app',
    'update:index',
    'update:config',
    'phplint',
    callback
  )
})

gulp.task('deploy', function (callback) {
  runSequence(
    'server',
    'confirm:deploy',
    'package',
    'send',
    callback
  )
})

function throwError (taskName, msg) {
  throw new gutil.PluginError({
    plugin: taskName,
    message: gutil.colors.red(msg)
  })
}
function replaceComments (string) {
  return string.replace(/\/\*[\s\S]*?\*\/|([^:]|^)\/\/.*$/gm, '$1')
}
