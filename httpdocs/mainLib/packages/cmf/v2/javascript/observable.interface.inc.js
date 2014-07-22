var cmfImplementObservable;
(function() {

    var cmfcObservable = { 

    		observers: new Array(),
    		
    		addObserver: function (event, observer,parameters) {
    			if (!this.observers[event]) {
    				this.observers[event]=new Array();
    			}
    			this.observers[event].push(observer);
    		},
    		
    		removeObserver: function (event, observer) {
    			if (this.observers[event]) {
    				for (i in this.observers[event]) {
    					var observer=this.observers[event][i];
    					if (typeof(observer)!='function') {
    						if (this.observers[event]==observer) {
    							this.observers[event].splice(i);
    						}
    					}
    				}
    			}
    		},
    		
    		prependObserver: function (event, observer,parameters) {
    			if (!this.observers[event]) {
    				this.observers[event]=new Array();
    			}
    			this.observers[event].unshift(observer);
    		},
    		
    		hasObserver: function (event) {
    			if (this.observers[event]) {
    				for (i in this.observers[event]) {
    					var observer=this.observers[event][i];
    					if (typeof(observer)!='function') {
    						return true;
    					}
    				}
    			} else {
    				return false;
    			}
    		},
    		
    		notifyObservers: function (event,params) {
    			if (this.observers[event])
    			for (i in this.observers[event]) {
    				var observer=this.observers[event][i];
    				if (typeof(observer)!='function') {
    					return cmfcFunction.callUserFunction(observer,{'obj':this,'event':event,'params':params});
    				}
    			}
    		}
      
    };
    
    cmfImplementObservable = function (subject) {
        for (var p in cmfcObservable) {
            subject[p] = cmfcObservable[p];
        }
    }
    
})();