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

var distFolder = 'dist'

var inlineCss = require('gulp-inline-css')

function Server (serverKey) {
  var servers = require('./servers.json')
  var serverConfPath = argv.serverconf || path.normalize(path.join(process.env.HOME, '/jhm-vault/serverconf.json'))
  var privateServers
  try {
    privateServers = require(serverConfPath)
    gutil.log(gutil.colors.green('Using private server config: ' + gutil.colors.bold(serverConfPath)))
  } catch (e) {
    gutil.log(gutil.colors.red('Could not load server config path: ' + gutil.colors.bold(serverConfPath)))
    privateServers = {}
  }
  if (argv.envtest) {
    gutil.log(gutil.colors.red('Sending environment test file. Remember to delete after running.'))
  }
  servers = _.merge(servers, privateServers)
  serverKey = serverKey || ''
  var server
  if (_.has(servers, serverKey)) {
    server = servers[serverKey]
    server = _.defaultsDeep(server, servers._defaults)
  } else {
    if (serverKey === 'production') {
      throwError('server ', 'production definition not available.')
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

var server = new Server(argv.server || 'local')

gulp.task('server', function (cb) {
  gutil.log('server ID:', server.name)
  gutil.log('webroot: ', server.get('webroot'))
  gutil.log('server app: ', server.get('serverApp'))
  gutil.log('private key path: ', _.get(server.get('shell'), 'privateKey', ''))
  gutil.log(gutil.colors[server.get('color')]('Using Server ' + gutil.colors.bold(server.name) + ' definition.'))
  cb()
})

gulp.task('clean:dist', function () {
  return del([distFolder + '/**/*'])
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
  'composer install --no-dev --optimize-autoloader'
], {
  cwd: path.join(home, distFolder, path.basename(server.get('serverApp')))
}))

// clean up problematic vendor directories
gulp.task('composer:cleanup', function (callback) {
  var basepath = path.join(home, distFolder, path.basename(server.get('serverApp')))
  del.sync(basepath + '/**/examples')
  del.sync(basepath + '/**/.git')
  callback()
})

// remove index,php on remote server so it switched to maintenance mode
gulp.task('server:remove:index', shell.task('ssh -i ' + server.get('shell')['privateKey'] + ' -p ' + server.get('shell')['port'] + ' ' +
  server.get('shell')['username'] + '@' + server.get('shell')['hostname'] +
  " 'rm -f " + path.join(server.get('shell')['destination'], path.basename(server.get('webroot')), 'index.php') + "'")
)

// copy index.php to remote server so it is enabled again
gulp.task('server:copy:index', shell.task('scp -i ' + server.get('shell')['privateKey'] + ' -P ' +
  server.get('shell')['port'] + ' ' + path.join(home, distFolder, path.basename(server.get('webroot')), 'index.php') + ' ' +
  server.get('shell')['username'] + '@' + server.get('shell')['hostname'] + ':' +
  path.join(server.get('shell')['destination'], path.basename(server.get('webroot')), 'index.php'))
)

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

gulp.task('copy:local', function (callback) {
  runSequence('copy:local:server', 'copy:local:app', callback)
})

gulp.task('copy:local:server', function () {
  return gulp.src('dist/JHMAPP/**/*', {dot: true})
    .pipe(gulp.dest(path.join(process.env.HOME, 'www', 'JHMAPP')))
})

gulp.task('copy:local:app', function () {
  return gulp.src('dist/jhm/**/*', {dot: true})
    .pipe(gulp.dest(path.join(process.env.HOME, 'www', 'webroot', 'jhm')))
})

gulp.task('copy:webroot', function () {
  return gulp.src('server/webroot/**/*', {dot: true})
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('webroot')))))
})

gulp.task('copy:libs', function () {
  return gulp.src('server/libs/*')
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('serverApp')), 'libs')))
})

gulp.task('copy:includes', function () {
  return gulp.src('server/includes/*')
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('serverApp')), 'includes')))
})

gulp.task('copy:composer', function () {
  return gulp.src(['server/composer.json', 'server/composer.lock'])
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('serverApp')))))
})

gulp.task('copy:app', function () {
  return gulp.src('build/rsc/**/*')
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('webroot')), 'rsc')))
})

gulp.task('copy:setup', function () {
  try {
    var setupPath = 'server/bin/setup.sh'
    var setup = fs.readFileSync(setupPath, 'utf8')
    if (setup) {
      if (server.get('cacheEnabled')) {
        var cacheChunk = fs.readFileSync('server/bin/__setup__cache.sh', 'utf8')
        setup += '\n' + cacheChunk
      }
      fs.writeFileSync(path.join(distFolder, path.basename(server.get('serverApp')), 'setup.sh'), setup, 'utf8')
    }
  } catch (e) {
    throwError('update:index', e)
  }
})
gulp.task('copy:data', function () {
  return gulp.src('data/*.json')
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('serverApp')), 'data')))
})

gulp.task('copy:dust', function () {
  return gulp.src('app/dust/**/*')
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('serverApp')), 'app/dust')))
})

gulp.task('copy:mail', function () {
  return gulp.src('./server/mailTpl/*.html')
    .pipe(inlineCss())
    .pipe(gulp.dest(path.join(distFolder, path.basename(server.get('serverApp')), 'mailTpl')))
})

gulp.task('rsync', function () {
  var rsyncOpts = _.merge({}, server.get('shell'), server.get('rsync'))

  rsyncOpts.shell = 'ssh -p ' + rsyncOpts.port + ' -i ' + rsyncOpts.privateKey
  rsyncOpts.exclude = 'index.php .DS_Store'
  rsyncOpts.root = distFolder
  rsyncOpts.command = Boolean(argv.rsyncdebug || false)
  delete rsyncOpts.port // port will cause shell opt to be ignore

  if (rsyncOpts.enabled === false) {
    throwError('rsync', 'Rsync is not enabled for this server: ' + gutil.colors.bold(server.name))
  }
  if (rsyncOpts.local) {
    gutil.log('local copy mode')
    return gulp.src(distFolder + '/**/*', {dot: true})
      .pipe(gulp.dest(rsyncOpts.destination))
  } else {
    gutil.log('shell arg: ' + gutil.colors.bold(rsyncOpts.shell))
    gutil.log('rsync options ', rsyncOpts)
    return gulp.src('./' + distFolder + '/**/*', {dot: true})
    .pipe(rsync(rsyncOpts))
  }
})

gulp.task('send', function (callback) {
  var isLocal = server.get('shell')['local'] || false
  var args = ['confirm:push']
  if (!isLocal) {
    args.push('server:remove:index')
  }
  args.push('rsync')
  if (!isLocal) {
    args.push('server:copy:index')
  }
  args.push(callback)
  runSequence.apply(global, args)
})

gulp.task('update:index', function (callback) {
  try {
    var indexPath = path.join(distFolder, path.basename(server.get('webroot')), 'index.php')
    var index = fs.readFileSync(indexPath, 'utf8')
    var analytics = ''
    index = index.replace('{{serverApp}}', server.get('serverApp'))
    index = index.replace('{{webroot}}', server.get('webroot'))
    if (server.name === 'production') {
      analytics = fs.readFileSync('./inc/analytics.html', 'utf8')
      analytics = analytics.replace('{{property-id}}', server.get('ga_property_id'))
      index = index.replace("ini_set('display_errors', 1);\n", '')
    }
    index = index.replace('{{analytics}}', analytics)
    var auth = ''
    if (server.get('auth')) {
      auth = fs.readFileSync('./inc/auth.php', 'utf8')
      if (_.isString(auth) && auth.length) {
        auth += '?>'
      }
    }
    index = index.replace('{{auth}}', auth)
    index = replaceComments(index)
    fs.writeFileSync(indexPath, index, 'utf8')
    callback()
  } catch (e) {
    throwError('update:index', e)
  }
})

gulp.task('update:api', function (callback) {
  try {
    var indexPath = path.join(distFolder, path.basename(server.get('webroot')), 'api', 'index.php')
    var index = fs.readFileSync(indexPath, 'utf8')
    var app = server.get('serverApiApp') || server.get('serverApp')
    index = index.replace('{{serverApp}}', app)
    index = replaceComments(index)
    fs.writeFileSync(indexPath, index, 'utf8')
    callback()
  } catch (e) {
    throwError('update:api', e)
  }
})

gulp.task('update:env', function (callback) {
  try {
    var envPath = path.join(distFolder, path.basename(server.get('webroot')), 'environmentTest.php')
    if (argv.envtest) {
      var index = fs.readFileSync(envPath, 'utf8')
      var app = server.get('serverApiApp') || server.get('serverApp')
      index = index.replace('{{serverApp}}', app)
      fs.writeFileSync(envPath, index, 'utf8')
    } else {
      fs.unlinkSync(envPath)
    }
    callback()
  } catch (e) {
    throwError('update:env', e)
  }
})

gulp.task('update:download', function (callback) {
  try {
    var indexPath = path.join(distFolder, path.basename(server.get('webroot')), 'download', 'index.php')
    var index = fs.readFileSync(indexPath, 'utf8')
    index = index.replace('{{serverApp}}', server.get('serverApp'))
    index = replaceComments(index)
    fs.writeFileSync(indexPath, index, 'utf8')
    callback()
  } catch (e) {
    throwError('update:download', e)
  }
})

gulp.task('update:config', function (callback) {
  try {
    var cfgPath = path.join(distFolder, path.basename(server.get('serverApp')), 'libs', 'Config.php')
    var config = fs.readFileSync(cfgPath, 'utf8')
    config = config.replace('{{serverApp}}', server.get('serverApp'))
    config = config.replace('{{webroot}}', server.get('webroot'))
    config = config.replace('{{mailToAddress}}', server.get('mailToAddress'))
    config = config.replace('{{mailToName}}', server.get('mailToName'))
    config = config.replace('{{logfile}}', server.get('logfile'))
    config = config.replace('"{{cacheEnabled}}"', server.get('cacheEnabled') || false)
    config = config.replace('"{{sendMail}}"', server.get('sendMail') || false)
    config = config.replace('"{{sendThankYou}}"', server.get('sendThankYou') || false)
    // smtp settings
    config = config.replace('"{{smtp__enabled}}"', server.atts.smtp.enabled)
    config = config.replace('{{smtp__hostname}}', server.atts.smtp.hostname)
    config = config.replace('{{smtp__username}}', server.atts.smtp.username)
    config = config.replace('{{smtp__password}}', server.atts.smtp.password)
    // mysql settings
    config = config.replace('"{{mysql__enabled}}"', server.atts.mysql.enabled)
    config = config.replace('{{mysql__host}}', server.atts.mysql.host)
    config = config.replace('"{{mysql__port}}"', server.atts.mysql.port)
    config = config.replace('{{mysql__db}}', server.atts.mysql.db)
    config = config.replace('{{mysql__user}}', server.atts.mysql.user)
    config = config.replace('{{mysql__password}}', server.atts.mysql.password)
    config = config.replace('{{mysql__prefix}}', server.atts.mysql.prefix)

    if (server.name === 'production') {
      config = config.replace('{{property-id}}', server.get('ga_property_id'))
    }
    // remove comments
    config = replaceComments(config)
    fs.writeFileSync(cfgPath, config, 'utf8')
    callback()
  } catch (e) {
    throwError('update:config', e)
  }
})

gulp.task('phplint', function (cb) {
  phplint([
    path.join(distFolder, server.get('serverApp'), 'libs', '*.php'),
    path.join(distFolder, server.get('serverApp'), 'includes', '*.php'),
    path.join(distFolder, server.get('webroot'), 'index.php')
  ], {limit: 10}, function (err, stdout, stderr) {
    if (err) {
      throwError('phplint', err)
    }
    cb()
  })
})

gulp.task('package', function (callback) {
  runSequence(
    'server',
    'clean:dist',
    'lint',
    'test:app',
    'test:server',
    'build',
    ['copy:composer', 'copy:libs', 'copy:includes', 'copy:data', 'copy:dust', 'copy:mail'],
    'composer',
    'composer:cleanup',
    'copy:webroot',
    'copy:app',
    'copy:setup',
    'update:index',
    'update:api',
    'update:env',
    'update:download',
    'update:config',
    'phplint',
    callback
  )
})
gulp.task('local', function (callback) {
  runSequence(
    'package',
    'copy:local'
  )
})

gulp.task('local:update', function (callback) {
  runSequence(
    ['copy:libs', 'copy:includes', 'copy:data', 'copy:dust', 'copy:webroot'],
    'update:index',
    'update:config',
    'copy:local'
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

/**
 * usage
 * server=[stage|beta|hexcore|production]
 *
 *  stage -- local network instance (if it is up)
 *  beta -- beta.johnhenrymahr.com
 *  production -- johnhenrymahr.com
 *  hexcore -- local mac pro instance
 *
 * gulp package --server={server_key} -- package but do not push anywhere
 * gulp deploy --server={server_key}
 *
 * extra args
 * --envtest=true | send the environmentTest.php file to server
 */
