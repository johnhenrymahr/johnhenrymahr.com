#Building the modern web client

 With the advent of html5, css3, and the modern javascript ecosystem the client-server architecture of the web now has a true client that can stand on its own as a full-fledged application. This change in paradigm has both put more preasure on the front-end developer and challenged the way the industry must think about ui development and the division of responsabilities within the development team. 
 
 In the past the font-end was little more that a decorator for an 'application' being assembled on the server; providing styles and possibly some limited functionality or ui effects through client side scripting. The bulk of the application and business logic was kept (safely) on the server, making abundant 'round tripping' from client to server and back again a common practice. The font end was just decoration for the most part, it was the frosting on the cake but not the cake itself.
 
 Things have changed -- we are at the forefront of a new era in web development. It is no longer so much about 'web pages', but web delivered applications. Yes, the server still provdes essential services, but much more of the responsibility for the handling of user interaction is being handled directly by the client. The service apis are, to a large degree, once removed from direct interaction with the client. The front end application can implement much of the business logic and only go to the server when it is truly necessary. 
 
There is much to be gained from this approach: the load on the network is lessened, latency has less fo an affect, and teh end user gets an overall more responsive experiece. But this also creates new challenges for the ui developer. Broken front end code has a much larger downside than in the past  -- it can render the entire application unusable, or worse produce and inconsistent experience. Not to mention the potential security issues from a more autonomous ui application. In addition, the fact that there is just a lot more code that needs to be written and maintained for a modern application means that th eoppertunity for human error goes up greatly. To counter this new tools and approaches are required.

#The toolset of the modern ui developer.

A UI developer is tasked with bringing togethor the many varied pieces of a application into one (hopefully) unified and coherant whole. Business requirements, service apis, user experiece flows, graphic design materials, copy, analytics must all come togethor into an applciation that actually works and is performant. It is a difficult task that requires new architectures, frameworks, and toolsets.

The world of front end technologies is in a period of extremely rapid advancement, adn that advancement seems to be accelerating. To list all of the tools available would be an impossibility as I am only awrae of teh smallslice of what exists that I use on a daily basisi.  The best I can provide is a list of the tools that I am currently using.

##enhanced language features and linting
Writting good code without errors takes more than dicipline (that does help too though). The help of a toolset can make all of difference and point out errors rigth in fron of you that you are just not seeing. Mostly setting aup a good js linter helps a great deal. 

##Module systems: AMD, CommonJS, UMD; probably ES2015
As the amount and complexity grows breaking it down into consumable and reusable components becomes essential. Trying to read through a 3000 line long file is a nightmare. There have a been a few differnt module system that have grown up, but likely the ES2015 format will win as it will eventually be built into the language. 

##framework: React / Redux
I used Backbone.js along with jQuery for many years. I still use it in some circumstances (like this website). But React has caught my attention, and now my devotion, as the framework for me. There is a certainly a learning curve and it is, of course, not a perfect system. But the declarative/functional approach of its architecture simply produces a more readable, easily maintainable code base. This is most true when delaing with a large code base where the event-driven system backbone uses can become quite tedious. 
Redux add another layer of complexity on top of react, and is really not necessary for simpler apps. But when state becomes more complex as the application grows it can offer a true benefit. The code becomes more strictly logically organized which makes testing easier. It also provides a very usefull developer extension for Chrome that makes analyzing the current state when debugging an issue simple.

#Testing Frameworks: Jest, Sinon, Enzyme
As the role of the ui application grows the need for better testing becomes more essential. Unit tests form the basic foundation and in my opinion must be integrated into the development process. I am not necessaroy an advocate of test-first development for the ui, but I believe the process of writing unit tests must be integrated into the process of coding -- not tacked on at the end. It does slow down development, but I do not see that as necessarily a bad thing.
As for testing frameworks. I consider whatever works for the individual developer or team to probably be fine. Anything is certainly better than nothing in this case. But I have spent a fair bit of time with the karma, chai, mocha, sinon, phantomjs approach. Athough it does work once setup, the process of getting all of those parts to work properly togethor, and to keep working togethor, can often lead to a lot of frustration. 

Lately I have been using the Jest framework for testing react code and really appreciate its very low configuration approach. It probably works the best with React code as it generally much less dependant on having access to some sort of DOM when executing tests. Jest has its own mocking system but I fidn that sinon adds additional capabilites for stubbing object methods that are really usefull. And the shallow rendering capabilites of enzyme for testing react components is just really usefull.


build systems
It may seem strange to 


responsive design, mobile, css frameworks



