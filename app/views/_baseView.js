/**
 * _view
 *
 * base view, contains render functionality
 *
 * @Backbone View
 */
var _ = require('lodash')
var Backbone = require('backbone')
module.exports = Backbone.View.extend({
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
   * handle el property intelegently
   *
   *
   * @constructor
   * @param  {object}
   */
  constructor: function (options) {
    options = options || {}
    if (_.has(options, 'el')) {
      var $el = options.el instanceof Backbone.$ ? options.el : Backbone.$(options.el)
      if (Backbone.$.contains(document, $el[0])) { // if the el is not in the DOM already remove the option
        if ($el.children().length) {
          this._serverRendered = true
        }
      } else {
        options = _.omit(options, 'el')
      }
    }
    Backbone.View.call(this, options)
  },
  /**
   *  has view been rendered on server
   *  returns true ONLY FIrst tiem it is called
   *
   * @private
   * @return {Boolean}
   */
  _isServerRendered: function () {
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
  renderDefaults: {},

  /**
   * @private
   * @return {object} template data
   */
  _getData: function () {
    var data = this.serializeModel()
    if (_.has(this, 'templateHelpers')) {
      _.merge(data, _.result(this, 'templateHelpers'))
    }
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
  },
  /**
   * basic render method
   * @param  {object} options
   * @return {object} this
   */
  render: function (options) {
    _.defaults(options || {}, _.result(this, 'renderDefaults'))
    if (this._destroyed) {
      throw new Error('destroyed view: ' + this.cid + ' cannot be rendered.')
    }
    if (_.isFunction(this.template) && this._isServerRendered() === false) {
      this.template(this._getData(), function (err, html) {
        if (err === null) {
          if (_.isFunction(this.onRender)) {
            this.onRender(options)
          }
          this._attach(html, options)
        }
      }.bind(this))
    }
    return this
  },
  /**
   * clean up  handlers
   *  and destroy view
   *
   * @return {object} this
   */
  destroy: function () {
    this.undelegateEvents()
    this.$el.removeData().unbind()
    this._destroyed = true
    return this.remove()
  }
})
