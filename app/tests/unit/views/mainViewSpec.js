var Backbone = require('backbone')
var App = require('app/app')
var MainView = require('app/views/mainView')
var chai = require('chai')
var $ = require('jquery')
var views = {
  testView: Backbone.View.extend({})
}
var manifest = {
  template: 'mainTpl.dust',
  selector: 'main.mainView',
  attributes: {
    className: 'mainClass',
    tagName: 'main'
  },
  sections: [ {
    container: {
      attributes: {
        className: 'content'
      }
    },
    children: [
      {
        id: 'test',
        selector: 'section.title',
        template: 'titleTpl.dust',
        attributes: {
          className: 'title container-fluid',
          tagName: 'section'
        }
      }
    ]
  }]
}
App.model = {
  getModel: function () {}
}

describe('MainView spec', function () {
  var view
  beforeEach(function () {
    view = new MainView()
    view._manifest = manifest
    view._views = views
  })
  afterEach(function () {
    view = null
  })
  context('instantiation', function () {
    it('can be instantiated', function () {
      chai.expect(view).to.be.a('object')
    })
  })

  context('render', function () {
    it('can render its base template', function () {
      chai.expect(view.render().$el.is(':empty')).to.be.false
    })
  })

  context(' getSections method', function () {
    it('returns  array', function () {
      var secs = view._getSections()
      chai.expect(secs).to.be.a('array')
    })
    it('returns jQUERY obejcts', function () {
      var secs = view._getSections()
      chai.expect(secs[0]).to.be.an.instanceof($)
    })
  })

  context('_getViewInstance', function () {
    it('returns undefined if no id provided', function () {
      chai.expect(view._getViewInstance({})).to.be.undefined
    })
    it('returns undefined if no view class found', function () {
      chai.expect(view._getViewInstance({id: 'nothing'})).to.be.undefined
    })
    it('returns view instance if requried atts provided', function () {
      chai.expect(view._getViewInstance({id: 'test'})).to.be.instanceof(Backbone.View)
    })
  })
})
