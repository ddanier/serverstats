if (window.ActiveXObject) {

_DOM_PROGID = pickRecentProgID(["Msxml2.DOMDocument.5.0", "Msxml2.DOMDocument.4.0", "Msxml2.DOMDocument.3.0", "MSXML2.DOMDocument","MSXML.DOMDocument", "Microsoft.XMLDOM"]);
_XMLHTTP_PROGID = pickRecentProgID(["Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP"]);
_THREADEDDOM_PROGID = pickRecentProgID(["Msxml2.FreeThreadedDOMDocument.5.0", "MSXML2.FreeThreadedDOMDocument.4.0", "MSXML2.FreeThreadedDOMDocument.3.0"]);
_XSLTEMPLATE_PROGID = pickRecentProgID(["Msxml2.XSLTemplate.5.0", "Msxml2.XSLTemplate.4.0", "MSXML2.XSLTemplate.3.0"]);	

_IS_MSXML_5 = _DOM_PROGID == 'Msxml2.DOMDocument.5.0';

function pickRecentProgID(idList){
  // found progID flag
	while (idList.length > 0) {
			try {
  		 	new ActiveXObject(idList[0]);	
  		 	return idList[0];
			} catch (e) {
  			// Didn't work. Try the next program.
  			idList.shift();
			}
	}
 	throw new Exception("Could not retreive a valid progID of Class");
};

function XMLHttpRequest(){
    return new ActiveXObject(_XMLHTTP_PROGID);
};

function XSLTProcessor(){
        this.template = new ActiveXObject(_XSLTEMPLATE_PROGID);
        this.processor = null;
};

XSLTProcessor.prototype.importStylesheet = function(xslDoc){
    // convert stylesheet to free threaded
    var converted = new ActiveXObject(_THREADEDDOM_PROGID); 
    converted.loadXML(xslDoc.xml);
    
    this.template.stylesheet = converted;
    this.processor = this.template.createProcessor();
    // (re)set default param values
    this.paramsSet = new Array();
};


XSLTProcessor.prototype.transformToDocument = function(sourceDoc){
    this.processor.input = sourceDoc;
    var outDoc = new ActiveXObject(_THREADEDDOM_PROGID);
    this.processor.output = outDoc; 
    this.processor.transform();
    return outDoc;
};

XSLTProcessor.prototype.transformToFragment = function (sourceDoc, asFragmentOfDoc) {
		return this.transformToDocument(sourceDoc);
}

XSLTProcessor.prototype.setParameter = function(nsURI, name, value){
    /* nsURI is optional but cannot be null */
    if(nsURI){
        this.processor.addParameter(name, value, nsURI);
    }else{
        this.processor.addParameter(name, value);
    };
};

}
