concentre = new Object();
concentre.ajax = new Object();

concentre.ajax = function(surl,method, vars) {
	this.location = surl;
	this.method = method;
	this.vars = vars;
};


concentre.ajax.prototype.send = function() {
	this.http = new XMLHttpRequest();
	
	var _self = this;
	_self.http.onreadystatechange = function () {  _self.process();  };

	this.httpTimer = setTimeout ( function () { _self.timeoutHandler(_self.http); }, 30000);
	
	this.http.open(this.method, this.location, true);
	this.http.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	this.progressIndicator();
	
	this.http.send( this.serializeVariables() );
};

concentre.ajax.prototype.serializeVariables = function(node) {
	var serialized='';
	for (var i in this.vars) {
		serialized+= i + '=' + escape(this.vars[i]) + '&';
	}

	return serialized.substr(0,serialized.length-1);
}

concentre.ajax.prototype.process = function () {
	switch (this.http.readyState) {
		default:
			
			break;
		case 3:
       break; 
	
		case 4:
			clearTimeout(this.httpTimer);

			switch (this.http.status) {
				
				case 0:
				case 200: // url retrivied
				        this.callBack();
					break;
				default:
						alert(this.http.status + ' ' + this.http.statusText);
						
			}
		break;
	}
};

concentre.ajax.prototype.callBack= function() {
};

concentre.ajax.prototype.progressIndicator= function() {
};

	
concentre.ajax.prototype.timeoutHandler= function(o) {
	 try {
		 		switch (o.readyState)
		 			{
		 				case 1:
		 				case 2:
		 				case 3:
		 					o.abort();
		 					break;
		 				case 4:
		 				default:
						break;		 						 				
		
		 			}
			 } catch (err) {}
};
