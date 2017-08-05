/**
 * _view
 *
 * base view, contains render functionality
 *
 * @Backbone View
 *
 * note to self: LifeCycle methods/events
 *
 *  onRender() | view:render  -- called after dust rendering completes, before attached to DOM
 *  onAttach() | view:atatch -- called  immeditaly after element attached to DOM
 *  onPostRender() | view:postRender -- deferred execution until current DOM manipulation has finished
 *
 * NOTE: onRender and onAttach are ONLY called when a view is actually rendered, so NOT in server rendered context
 *       onPostRender is ALWAYS called, for rendering, server rendered or even if a view has no template
 *       if render() is called onPostRender() will be called.
 *       all methods/events will be passed view options
 */
var _ = require('lodash')
var app = require('../app')
var Backbone = require('backbone')
module.exports = Backbone.View.extend({
  /**
   * view class
   * id from manifest
   *
   * @type {String}
   */
  viewClass: null,

  /**
  *
  * childViewContainer
  *
  * if there are child views, a selector
  * for the container to append them to
  * @type {string}
  */
  childViewContainer: null,

  /**
   *  sanity check
   *
   * @type {Boolean}
   */
  _destroyed: false,

  /**
   *  has this view been rendered on the server
   *
   * @type {Boolean}
   */
  _serverRendered: false,

  /**
  * child views
  # @type {Array}
  */
  _children: null,

  /**
   * handle el property intelegently
   *
   *
   * @constructor
   * @param  {object}
   */
  constructor: function (options) {
    options = options || {}
    if (_.isFunction(options.template)) {
      this.template = options.template
    }
    if (_.has(options, 'viewClass')) {
      this.viewClass = options.viewClass
    }
    if (_.has(options, 'childViewContainer')) {
      this.childViewContainer = options.childViewContainer
    }
    this._children = [] // set to array here so does not become part of protype
    if (_.has(options, 'el')) {
      var $el = options.el instanceof Backbone.$ ? options.el : Backbone.$(options.el)
      if (this._isServerRendered($el) && $el.children().length) { // if the el is not in the DOM already remove the option
        this._serverRendered = true // will be set to false after first render
        this.preRendered = true // will always remain true
      } else {
        options = _.omit(options, 'el')
        this.preRendered = false
      }
    }
    if (_.isFunction(this.onAppReady)) {
      this.listenToOnce(app.vent, 'app:ready', _.bind(this.onAppReady, this))
    }
    Backbone.View.call(this, options)
  },
  /**
   * _isServerRendered
   *
   * test if template has been rendered
   * server side already
   *
   * @param  {jQuery} $el
   * @return {boolean}
   */
  _isServerRendered: function ($el) {
    return $el.length && Backbone.$.contains(document, $el[0])
  },
  /**
   *  has view been rendered on server
   *  returns true ONLY the first time it is called
   *
   * @private
   * @return {Boolean}
   */
  serverRendered: function () {
    if (this._serverRendered) {
      this._serverRendered = false
      return true
    }
    return false
  },
  /**
   *  return serialized model representation
   *
   * @return {Object}
   */
  serializeModel: function () {
    return (this.model) ? this.model.toJSON() : {}
  },
  /**
   * optional noop
   * return object of default options for render
   *
   * @return object
   */
  renderDefaults: {
    _data: {}
  },

  /**
   * @private
   * @options {object||null} render options
   * @return {object} template data
   */
  _getData: function (options) {
    options = options || {}
    var data = this.serializeModel()
    _.merge(data, _.result(this, 'templateHelpers', {}), options._data || {})
    return data
  },
  /**
   * attach method
   * override to controll how element atatched
   *
   * @param  {string} html
   * @param {object} options
   * @return {undefined}
   */
  attach: function (html, options) {
    this.$el.empty().append(html)
  },
  /**
   * attach html to the view container
   * @param  {string} html
   * @param  {object} options
   * @return {undefined}
   */
  _attach: function (html, options) {
    options = options || {}
    this.attach(html, options)
    if (_.isFunction(this.onAttach)) {
      this.onAttach(options)
    }
    this.trigger('view:attach', html, options)
    _.defer(_.bind(this._postRender, this)) // wait for DOM manipulation to finish
  },
  /**
   *post render handler
   * @param  {object} options
   * @return {undefined}
   */
  _postRender: function (options) {
    if (this._children.length) {
      this._renderChildViews(options)
    }
    if (_.isFunction(this.onPostRender)) {
      this.onPostRender(options)
    }
    this.trigger('view:postRender', options)
  },
  /**
  * _renderChildViews
  *  loop through _children and render views
  *  @param {Object} render options
  *  @return {object} this
  */
  _renderChildViews: function (options) {
    var $container = this._getChildViewContainer()
    _.each(this._children, _.bind(function (child) {
      $container.append(child.render(options).el)
      this.trigger('view:attachChild', child)
    }, this))
    return this
  },
  /**
  * _getChildViewContainer
  *  get a container object
  *
  *  @return {object} jQuery wrapped container
  *  @thow error if container not found in DOM
  */
  _getChildViewContainer: function () {
    if (_.isString(this.childViewContainer) && this.childViewContainer.length) {
      var $container = this.$(this.childViewContainer)
      if ($container.length) {
        return $container
      } else {
        throw new Error('Child view container not found in context.')
      }
    } else {
      return this.$el
    }
  },
  /**
   * basic render method
   * @param  {object} options
   * @return {object} this
   */
  render: function (options) {
    _.defaults(options || {}, _.result(this, 'renderDefaults'))
    if (this._destroyed) {
      throw new Error('The destroyed view: ' + this.cid + ' cannot be rendered.')
    }
    if (!_.isFunction(this.template)) {
      this._postRender(options)
      return this
    }
    if (this.serverRendered() === false) {
      this.template(this._getData(options), _.wrap(options, _.bind(this._templateCallback, this)))
    } else {
      this._postRender(options)
    }
    return this
  },
  /**
   * _templateCallback: provided to dust function
   * @param  {object} options [options passed to render]
   * @param  {null|string} err     [error message if render failed, null otherwise]
   * @param  {string|null} html    [rendered html]
   * @return {undefined}
   */
  _templateCallback: function (options, err, html) {
    if (err === null) {
      if (_.isFunction(this.onRender)) {
        this.onRender(options)
      }
      this.trigger('view:render', html, options)
      this._attach(html, options)
    }
  },
  /**
   * transition end callback with failsafe
   * @param  {object} $el  jQuery wrapped element
   * @param  {number} duration in mciro-seconds
   * @return {object}  instance
   */
  transitionEnd: function ($el, duration, cb) {
    var called = false
    if (!_.isNull(app._transition)) {
      $el.one(app._transition, function () {
        called = true
        cb($el)
      })
    }
    var callback = function () {
      if (!called) {
        cb($el)
      }
    }
    setTimeout(callback, (duration + 100))
    return this
  },
  /**
   * clean up  handlers
   *  and destroy view
   *
   * @return {object} this
   */
  destroy: function () {
    if (_.isFunction(this.onBeforeDestroy)) {
      this.onBeforeDestroy()
    }
    this.trigger('view:beforeDestroy')
    if (this._children.length) {
      _.each(this._children, function (child) {
        child.destroy()
      })
    }
    this.undelegateEvents()
    this.$el.removeData().unbind()
    this._destroyed = true
    if (_.isFunction(this.onDestroy)) {
      this.onDestroy()
    }
    this.trigger('view:destroy')
    return this.remove()
  },
  log: function () {
    if (console && _.isFunction(console.log) && window.appLogging) {
      console.log.apply(this, arguments)
    }
  }
})
