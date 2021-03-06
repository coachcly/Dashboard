/**
 * FlashObject v1.3c: Flash detection and embed - http://blog.deconcept.com/flashobject/
 *
 * FlashObject is (c) 2006 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Modified by DJKenzie Feb 27, 2006 to customize it to handle Clover and FusionCharts
 * Modified by MDHunter Mar 23, 2006 to contain it more
 * Modified by DJKenzie Apr 24, 2006 to rename the object to xl_oFlashObject. 
 *  Also removed xl_Encode so it won't conflict with ESDIAPI.js, where the same function name is used.
 * Modified by RTC June 7, 2006 to allow a different task value to be specified optionally
 * Modified by TEH July 4, 2007 Added the function 'xl_AddWSParam' for passing WebSmart parameters when using method 'POST'.
 *  Also I formatted the file so it is readable and lined up correctly with our company standards.
 *  Note: 'wsparmstr' before appended to the 'swf' attribute can't be longer than 1910 characters.
 *         Flash code doesn't seem to set a limit on any variables so the limit must be in flash itself. 
 * Modified by RTC Feb 18, 2009 changed flash window transparency to correct content layering
 * Modified by TEH Nov 20, 2009 to support the new export feature which will allow the user to export the graph
 * 		as JPG, PNG or PDF. 3 items were added in total:
 * 		1) Added function 'getChartObject'
 *		2) Added variable 'registerWithJS' hardcoded to 1
 *		3) Added parameter 'allowScriptAccess' hardcoded to 'always'
 */

if(typeof com == "undefined")
	var com = new Object();
if(typeof com.deconcept == "undefined")
	com.deconcept = new Object();
if(typeof com.deconcept.util == "undefined")
	com.deconcept.util = new Object();
if(typeof com.deconcept.FlashObjectUtil == "undefined")
	com.deconcept.FlashObjectUtil = new Object();

com.deconcept.xl_oFlashObject = function(swf, id, pgm, w, h, ver, c, xmltask, useExpressInstall, quality, xiRedirectUrl, redirectUrl, detectKey)
{
	if (!document.createElement || !document.getElementById) 
		return;
	this.DETECT_KEY = detectKey ? detectKey : 'detectflash';
	this.skipDetect = com.deconcept.util.getRequestParameter(this.DETECT_KEY);
	this.params = new Object();
	this.wsparams = new Object();
	this.variables = new Object();
	this.attributes = new Array();
	this.useExpressInstall = useExpressInstall;

	if(pgm) 
		this.setAttribute("pgm", pgm);
	if(w) 
		this.setAttribute('width', w);
	if(h) 
		this.setAttribute('height', h);
		
	if (swf) 
	{ 
		this.swfurl = function()
		{
			var myswf = swf +  "?rnd="+ Math.round(100*Math.random());
			myswf += "&chartWidth=" + this.getAttribute("width") + "&chartHeight=" + this.getAttribute("height") +  "&dataUrl=" +this.getAttribute("pgm");

			var swfparms;
			if(xmltask != undefined) 
				swfparms = '?task='+xmltask;
			else 
				swfparms = "?task=RUN_REPORT";

			var query = window.location.search.substring(1);
			var qryparms  = query.split("&");
			for (var i=0;i< qryparms.length;i++)
			{
				var pair = qryparms[i].split("=");
				if (pair[0].toUpperCase() != "TASK")  
				{
					swfparms += "&" + pair[0] + "=" + pair[1]; 
				}
			}
			
    			if (encodeURIComponent)
			{
				swfparms =  encodeURIComponent(swfparms);
    			}
    			else if (escape)
			{
        			swfparms =  escape(swfparms);
    			}
  
			myswf += swfparms; 
			return myswf; 
		} 	
		this.setAttribute("swf", this.swfurl());  
	}
		
	if(id) 
		this.setAttribute('id', id);
		
		
	if(ver) 
		this.setAttribute('version', new com.deconcept.PlayerVersion(ver.toString().split(".")));
	this.installedVer = com.deconcept.FlashObjectUtil.getPlayerVersion(this.getAttribute('version'), useExpressInstall);
	if(c) 
		this.addParam('bgcolor', c);
	var q = quality ? quality : 'high';
	this.addParam('quality', q);
	this.addParam('wmode','transparent'); // RTC 20090218: allow zIndexed content to render on top 
	var xir = (xiRedirectUrl) ? xiRedirectUrl : window.location;
	this.setAttribute('xiRedirectUrl', xir);
	this.setAttribute('redirectUrl', '');
	if(redirectUrl) 
		this.setAttribute('redirectUrl', redirectUrl);
		
	//Register with JavaScript to make the export feature work
	this.addVariable('registerWithJS', 1);
	//Add scripting access parameter to make the export feature work
	this.addParam('allowScriptAccess', 'always');
}
	
com.deconcept.xl_oFlashObject.prototype =
{
	setAttribute: function(name, value)
	{
		this.attributes[name] = value;
	},
	getAttribute: function(name)
	{
		return this.attributes[name];
	},
	addParam: function(name, value)
	{
		this.params[name] = value;
	},
	getParams: function(){
		return this.params;
	},
	xl_AddWSParam: function(name, value)
	{
		this.wsparams[name] = value;
	},
	getWSParams: function()
	{
		return this.wsparams;
	},
	addVariable: function(name, value)
	{
		this.variables[name] = value;
	},
	getVariable: function(name)
	{
		return this.variables[name];
	},
	getVariables: function()
	{
		return this.variables;
	},
	createParamTag: function(n, v)
	{
		var p = document.createElement('param');
		p.setAttribute('name', n);
		p.setAttribute('value', v);
		return p;
	},
	getVariablePairs: function()
	{
		var variablePairs = new Array();
		var key;
		var variables = this.getVariables();
		for(key in variables){
			variablePairs.push(key +"="+ variables[key]);
		}
		return variablePairs;
	},
	getFlashHTML: function()
	{
		var flashNode = "";
		if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) // netscape plugin architecture
		{
			var wsparmstr = "";
			var wsparams = this.getWSParams();
			for(var key in wsparams)
			{
				wsparmstr += '&' + [key] +'='+ wsparams[key];
			} 
			if (encodeURIComponent)
			{
				wsparmstr =  encodeURIComponent(wsparmstr);
    			}
    			else if (escape)
			{
        			wsparmstr =  escape(wsparmstr);
    			}
			wsparmstr = this.getAttribute("swf") + wsparmstr;
			this.setAttribute("swf", wsparmstr);


			if (this.getAttribute("doExpressInstall"))
				this.addVariable("MMplayerType", "PlugIn");
			flashNode = '<embed type="application/x-shockwave-flash" src="'+ this.getAttribute('swf') +'" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'"';
			flashNode += ' id="'+ this.getAttribute('id') +'" name="'+ this.getAttribute('id') +'" ';
			var params = this.getParams();
			for(var key in params)
			{
				flashNode += [key] +'="'+ params[key] +'" ';
			}
			var pairs = this.getVariablePairs().join("&");
			if (pairs.length > 0)
			{
				flashNode += 'flashvars="'+ pairs +'"';
			}
			flashNode += '/>';
		}
		else // PC IE
		{
			var wsparmstr = "";
			var wsparams = this.getWSParams();
			for(var key in wsparams)
			{
				wsparmstr += '&' + [key] +'='+ wsparams[key];
			} 
			if (encodeURIComponent)
			{
				wsparmstr =  encodeURIComponent(wsparmstr);
    			}
    			else if (escape)
			{
        			wsparmstr =  escape(wsparmstr);
    			}

			wsparmstr = this.getAttribute("swf") + wsparmstr;
			this.setAttribute("swf", wsparmstr);

			if (this.getAttribute("doExpressInstall"))
				this.addVariable("MMplayerType", "ActiveX");
			flashNode = '<object id="'+ this.getAttribute('id') +'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'" viewastext>';
			flashNode += '<param name="movie" value="'+ this.getAttribute('swf') +'" />';
			var params = this.getParams();
			for(var key in params)
			{
				flashNode += '<param name="'+ key +'" value="'+ params[key] +'" />';
			}
			var pairs = this.getVariablePairs().join("&");
			if(pairs.length > 0)
				flashNode += '<param name="flashvars" value="'+ pairs +'" />';
			flashNode += "</object>";
		}
		return flashNode;
	},
	write: function(elementId)
	{
		if(this.useExpressInstall)
		{
			// check to see if we need to do an express install
			var expressInstallReqVer = new com.deconcept.PlayerVersion([6,0,65]);
			if (this.installedVer.versionIsValid(expressInstallReqVer) && !this.installedVer.versionIsValid(this.getAttribute('version')))
			{
				this.setAttribute('doExpressInstall', true);
				this.addVariable("MMredirectURL", escape(this.getAttribute('xiRedirectUrl')));
				document.title = document.title.slice(0, 47) + " - Flash Player Installation";
				this.addVariable("MMdoctitle", document.title);
			}
		}
		else
		{
			this.setAttribute('doExpressInstall', false);
		}
		if(this.skipDetect || this.getAttribute('doExpressInstall') || this.installedVer.versionIsValid(this.getAttribute('version')))
		{
			var n = (typeof elementId == 'string') ? document.getElementById(elementId) : elementId;
			n.innerHTML = this.getFlashHTML();
		}
		else
		{
			if(this.getAttribute('redirectUrl') != "")
			{
				document.location.replace(this.getAttribute('redirectUrl'));
			}
		}
	}
}

// ---- detection functions ---- 
com.deconcept.FlashObjectUtil.getPlayerVersion = function(reqVer, xiInstall)
{
	var PlayerVersion = new com.deconcept.PlayerVersion(0,0,0);
	if(navigator.plugins && navigator.mimeTypes.length)
	{
		var x = navigator.plugins["Shockwave Flash"];
		if(x && x.description)
		{
			PlayerVersion = new com.deconcept.PlayerVersion(x.description.replace(/([a-z]|[A-Z]|\s)+/, "").replace(/(\s+r|\s+b[0-9]+)/, ".").split("."));
		}
	}
	else
	{
		try
		{
			var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
			for (var i=3; axo!=null; i++)
			{
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i);
				PlayerVersion = new com.deconcept.PlayerVersion([i,0,0]);
			}
		}
		catch(e){}
		if (reqVer && PlayerVersion.major > reqVer.major)
			return PlayerVersion; // version is ok, skip minor detection
		// this only does the minor rev lookup if the user's major version 
		// is not 6 or we are checking for a specific minor or revision number
		// see http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
		if (!reqVer || ((reqVer.minor != 0 || reqVer.rev != 0) && PlayerVersion.major == reqVer.major) || PlayerVersion.major != 6 || xiInstall)
		{
			try
			{
				PlayerVersion = new com.deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));
			}
			catch(e){}
		}
	}
	return PlayerVersion;
}

com.deconcept.PlayerVersion = function(arrVersion)
{
	this.major = parseInt(arrVersion[0]) || 0;
	this.minor = parseInt(arrVersion[1]) || 0;
	this.rev = parseInt(arrVersion[2]) || 0;
}

com.deconcept.PlayerVersion.prototype.versionIsValid = function(fv)
{
	if(this.major < fv.major)
		return false;
	if(this.major > fv.major)
		return true;
	if(this.minor < fv.minor)
		return false;
	if(this.minor > fv.minor)
		return true;
	if(this.rev < fv.rev)
		return false;
	return true;
}

// ---- get value of query string param ----
com.deconcept.util =
{
	getRequestParameter: function(param){
		var q = document.location.search || document.location.href.hash;
		if(q)
		{
			var startIndex = q.indexOf(param +"=");
			var endIndex = (q.indexOf("&", startIndex) > -1) ? q.indexOf("&", startIndex) : q.length;
			if (q.length > 1 && startIndex > -1)
			{
				return q.substring(q.indexOf("=", startIndex)+1, endIndex);
			}
		}
		return "";
	},
	removeChildren: function(n)
	{
		while (n.hasChildNodes())
			n.removeChild(n.firstChild);
	}
}

// add Array.push if needed (ie5) 
if (Array.prototype.push == null)
{
	Array.prototype.push = function(item)
	{
		this[this.length] = item; 
		return this.length;
	}
}

/* Function to return Flash Object from ID */
com.deconcept.FlashObjectUtil.getChartObject = function(id)
{
  var chartRef=null;
  if (navigator.appName.indexOf("Microsoft Internet")==-1) {
    if (document.embeds && document.embeds[id])
      chartRef = document.embeds[id]; 
	else
	chartRef  = window.document[id];
  }
  else {
    chartRef = window[id];
  }
  if (!chartRef)
	chartRef  = document.getElementById(id);
  
  return chartRef;
}

// add some aliases for ease of use/backwards compatibility 
var getQueryParamValue = com.deconcept.util.getRequestParameter;
var xl_oFlashObject = com.deconcept.xl_oFlashObject;
var getChartFromId = com.deconcept.FlashObjectUtil.getChartObject;