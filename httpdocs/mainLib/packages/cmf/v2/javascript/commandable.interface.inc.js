var cmfcCommandable;
(function() {
	
	var cmfcCommandable = {

		commandHandlers: new Array(),
		
		addCommandHandler: function (cmd, commandHandler,parameters) {
			if (!this.commandHandlers[cmd]) {
				this.commandHandlers[cmd]=new Array();
			}
			this.commandHandlers[cmd].push(commandHandler);
		},
		
		removeCommandHandler: function (cmd, commandHandler) {
			if (this.commandHandlers[cmd]) {
				for (i in this.commandHandlers[cmd]) {
					var commandHandler=this.commandHandlers[cmd][i];
					if (typeof(commandHandler)!='function') {
						if (this.commandHandlers[cmd]==commandHandler) {
							this.commandHandlers[cmd].splice(i);
						}
					}
				}
			}
		},
		
		prependCommandHandler: function (cmd, commandHandler,parameters) {
			if (!this.commandHandlers[cmd]) {
				this.commandHandlers[cmd]=new Array();
			}
			this.commandHandlers[cmd].unshift(commandHandler);
		},
		
		hasCommandHandler: function (cmd) {
			if (this.commandHandlers[cmd]) {
				for (i in this.commandHandlers[cmd]) {
					var commandHandler=this.commandHandlers[cmd][i];
					if (typeof(commandHandler)!='function') {
						return true;
					}
				}
			} else {
				return false;
			}
		},
		
		//http://api.drupal.org/api/function/module_invoke_all/6
		runCommand: function (cmd,params) {
			if (this.commandHandlers[cmd])
			for (i in this.commandHandlers[cmd]) {
				var commandHandler=this.commandHandlers[cmd][i];
				if (typeof(commandHandler)!='function') {
					return cmfcFunction.callUserFunction(commandHandler,{'obj':this,'cmd':cmd,'params':params});
				}
			}
		},
		
		invokeCommand: function (cmd,params) {
			
		}
	};
	
	cmfImplementCommandable = function (subject) {
        for (var p in cmfcCommandable) {
            subject[p] = cmfcCommandable[p];
        }
    }
    
})();