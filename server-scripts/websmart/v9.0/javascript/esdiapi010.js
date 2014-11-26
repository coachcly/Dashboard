// Except where otherwise noted, these functions are copyrighted by ESDI  2005-2006

// Version 1.00 20051028
// Version 1.01 20051116 
// Version 1.20 20060307  - Code for handling creation of Flash object HTML code. *SEE NOTE OF 20060424. 
// Version 1.30 20060323:	Reorganized some of the code, changed some naming conventions
//									Also changed the file name, so as to not affect existing
//									applications.
// NOTE 20060424 - FlashObject object is not in this file- it is now in a file called ESDIFlashObject (DJK)
// See the following ESDI Wiki page for Documentation of these functions:
// http://wiki.excelsystems.com/wiki/index.php/Javascript_Library_Of_Functions
// Version 1.40 20060704 - xl_GetObj now instantiates a global xl_oDOMFeats object called xl_DOM if one does not exist. 
// Version 1.50 20061103 - added function xl_BuildURLWithFormElements to build a url with form elements (!)-typically used in AJAX apps. 
// Version 1.60 20061220 - added optional formName to xl_GetFormElementByName 
//			 - changed xl_BuildURLWithFormElements to check for form name instead of by id
//			 - added leading zeros to xl_GetShortDate when month or day < 10
// 			 - added optional parameter to automatically append random number to xl_AjaxUpdate request	
// Version 1.65 20070209 - added xl_DisableFormElementByName to do what you would expect
//           - corrected a few comments
//           - Style corrections to some functions (correcting indention and bracket format)
// MDH 20070215: Version 1.68 2007
//			 - Adding xl_FocusFirstElement()
//			 - Addling xl_EnableDisabledFormElements()
// Version 1.70 20070417 - DJK  added xl_HiliteRows() function. Adds custom color to table rows 
//              when hovering. 
// Version 1.80 20070604 - DJK added xl_EnableDrag 
// Version 1.90 20070920 - DJK fix to xl_GetPosX and xl_GetPosY as per PFC's code. Previously, IE was not calculating values correctly.
//				           This version is esdiapi010.js
/** Object:		xl_oDomFeats
 *  Purpose:  	http://wiki.excelsystems.com/wiki/index.php/Javascript_Library_Of_Functions#xl_GetDOMFeats
 */ 
function xl_oDOMFeats( ) 
{
	if (document.images) 
	{
		this.isCSS = (document.body && document.body.style) ? true : false;
		this.isW3C = (this.isCSS && document.getElementById) ? true : false;
		this.isIE4 = (this.isCSS && document.all) ? true : false;
		this.isNN4 = (document.layers) ? true : false;
		this.isIE6CSS = (document.compatMode && document.compatMode.indexOf("CSS1") >= 0) ? 
		true : false;
	}
}

/** Function:			xl_GetObj
 *  Purpose:   		Return a reference to an HTML element based on its id property (either as a string or a pointer) 
 *  Convert object name string or object reference into a valid element object reference
 *   @return  				ObjID 		Reference to an HTML element 
*/ 
function xl_GetObj(obj) 
{
	// If xl_DOM object not attached to document, then do it: 
	if(!document.xl_DOM) document.xl_DOM = new xl_oDOMFeats();
	var xl_DOM = document.xl_DOM; 
	var ObjID;
	if (typeof obj == "string") 
	{
		if (xl_DOM.isW3C) 
		{
			ObjID = document.getElementById(obj);
		} 
		else if (xl_DOM.isIE4) 
		{
			ObjID = document.all(obj);
		} 
		else if (xl_DOM.isNN4) 
		{
			ObjID = xl_SeekLayer(document, obj);
		}
	} 
	else 
		// pass through object reference
	return obj;
	
	return ObjID; 
}


// Seek nested NN4 layer from string name
function xl_SeekLayer(doc, name) 
{
	var ObjID;
	var LayersLength = doc.layers.length;
	for (var i = 0; i < LayersLength; i++) {
		if (doc.layers[i].name == name) {
			return doc.layers[i];
		}
		// dive into nested layers if necessary
		if (doc.layers[i].document.layers.length > 0) {
			// Could this simply return the result of the function?
			ObjID = xl_SeekLayer(document.layers[i].document, name);
		}
	}
	return ObjID;
}


/** Function:			xl_GetEvent
 *  Purpose:   		Get Event - returns an Event object. To see its type, for example, use e.type
 *  @param        e - Event name
 *  Cross-browser notes:    FireFox passes variable e to the function, while IE doesn't - the event is window.event.
 *  Usage notes:  See Wiki 
*/  
function xl_GetEvent(e) 
{
	// e gives access to the event in all browsers
	if (!e) var e = window.event; 
	
	return e; 
} 


/** Function:			xl_GetEventTarg
 *  Purpose:   		Get object reference to target of an Event such as onClick 
 *  @return    EventObj = element upon which the event occurred. 
 *  Cross-browser notes:    FireFox passes variable e to the function, while IE doesn't - the event is window.event.
 *  Usage notes:  See Wiki  (includes info on how to register an event handler) 
*/  
function xl_GetEventTarg(e) 
{
	var EventObj; 
	var e = xl_GetEvent(e); 
	
	if (e.target) EventObj  = e.target;
	else if (e.srcElement) EventObj = e.srcElement;
	
	return EventObj; 
} 


/** Function:			xl_AttachEvent
 *  Purpose:   		Attach a custom event handler to an object (such as document). Allows you to add multiple event handlers to objects.   
 *  @return       true -  it attached it ok.  false - it didn't, meaning browser doesn't support native methods used. 
 *  Cross-browser notes:    NS6 and Mozilla (FireFox) use addEventListener, IE uses attachEvent. 
 *                          in addition, Mozilla supports both event capturing and event bubbling while IE supports only event bubbling. 
 *  Usage notes:  See Wiki  
*/  
function xl_AttachEvent(obj, evType, fn, useCapture)
{
	if (!useCapture) var useCapture = true; 
	if (obj.addEventListener){
		obj.addEventListener(evType, fn, useCapture);
		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	} else {
		return false; 
	}
} 


/** Function:			xl_DetachEvent
 *  Purpose:   		Detach a custom event handler that was added with xl_AttachEvent  
 *  @return 		  true -  it attached it ok.  false - it didn't, meaning browser doesn't support native methods used. 
*/ 
function xl_DetachEvent(obj, evType, fn, useCapture)
{
	if (!useCapture) var useCapture = true; 
	if (obj.removeEventListener){
		obj.removeEventListener(evType, fn, useCapture);
		return true;
	} else if (obj.detachEvent){
		var r = obj.detachEvent("on"+evType, fn);
		return r;
	} else {
		return false;
	}
} 


/** Function:  	 xl_FindPosX 
 *  Purpose: 		 Find left position (x coordinate) of an object on a page. 
 *  @param			 Object reference (can be got with xl_GetObj) 
/*  @return			 curleft  left position of object
*/  

function xl_FindPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft;
			obj = obj.offsetParent;
		}
		curleft += obj.offsetLeft;  // added in Version 1.90 - ESDIAPI010.js
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
	
}


/** Function:  	 xl_FindPosY 
 *  Purpose: 		 Find top position (y coordinate) of an object on a page. 
 *  @param			 Object reference (can be got with xl_GetObj) 
/*  @return			 curleft  left position of object
*/  
function xl_FindPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop;
			obj = obj.offsetParent;
		}
		curtop += obj.offsetTop;  // added in Version 1.90 - ESDIAPI010.js
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

/** Function: 	   xl_Encode 
 *  Purpose: 		 	 URL-encode a string to ensure compatibility with all character sets
 *  @param				 uri - raw string
 *  @return 			 uri - encoded string
*/
function xl_Encode( uri ) {
	if (encodeURIComponent) {
		return encodeURIComponent(uri);
	}
	
	if (escape) {
		return escape(uri);
	}
} 


/** Function: 	   xl_Decode 
 *  Purpose: 		 	 Decode a URL-encoded string to ensure compatibility with all character sets
 *  @param				 uri - raw string
 *  @return        uri - decoded string
*/
function xl_Decode( uri ) {
	uri = uri.replace(/\+/g, ' ');
	
	if (decodeURIComponent) {
		return decodeURIComponent(uri);
	}
	
	if (unescape) {
		return unescape(uri);
	}
	
	return uri;
}


// Public domain cookie code written by: Bill Dortch, hIdaho Design (bdortch@netw.com)
// Modified by ESDI to conform to naming conventions


/** Function:	xl_GetCookieVal 
 *  Purpose: 	
 */
function xl_GetCookieVal (offset) 
{
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1)
		endstr = document.cookie.length;
	return xl_Decode(document.cookie.substring(offset, endstr));
}


/** Function:	xl_GetCookie 
 *  Purpose: 	Get a browser cookie
 */
function xl_GetCookie (name) 
{
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen) 
	{
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)
			return xl_GetCookieVal (j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break; 
	}
	
	return null;
}

/** Function:	xl_SetCookie 
 *  Purpose: 	Set a browser cookie
 */
function xl_SetCookie (name, value) 
{
	var argv = xl_SetCookie.arguments;
	var argc = xl_SetCookie.arguments.length;
	var expires = (argc > 2) ? argv[2] : null;
	var path = (argc > 3) ? argv[3] : null;
	var domain = (argc > 4) ? argv[4] : null;
	var secure = (argc > 5) ? argv[5] : false;
	document.cookie = name + "=" + xl_Encode(value) +
		((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
	((path == null) ? "" : ("; path=" + path)) +
	((domain == null) ? "" : ("; domain=" + domain)) +
	((secure == true) ? "; secure" : "");
}

//      - - - - - - - - End of  Bill Dortch code - - - - - - - -      


/** Function:	xl_GetDay
 *  Purpose: 	Get the text for a given day of week index
 */
function xl_GetDay(intDay)
{
	var DayArray = new Array("Sunday", "Monday", "Tuesday", "Wednesday", 
	"Thursday", "Friday", "Saturday")
	return DayArray[intDay]
}

/** Function:	xl_Getmonth
 *  Purpose: 	Get the text for a given month index
 */
function xl_GetMonth(intMonth)
{
	var MonthArray = new Array("January", "February", "March",
	"April", "May", "June",
	"July", "August", "September",
	"October", "November", "December") 
	return MonthArray[intMonth] 	  	 
}

/** Function:	xl_GetDateStr
 *  Purpose: 	Format and return the current date
 */
// Example: September 21, 2004		
function xl_GetDateStr()
{
	var today = new Date()
	var year = today.getYear()
	if(year<1000) year+=1900
		var todayStr = GetMonth(today.getMonth()) + " " + today.getDate()
	todayStr += ", " + year
	return todayStr
}

/** Function:	xl_GetDateStr
 *  Purpose: 	Format and return the current date
 */
// Example: Monday September 21, 2004
function xl_GetDateStrWithDOW()
{
	var today = new Date()
	var year = today.getYear()
	if(year<1000) year+=1900
		var todayStr = GetDay(today.getDay()) + ", "
	todayStr += GetMonth(today.getMonth()) + " " + today.getDate()
	todayStr += ", " + year
	return todayStr
}

/** Function:	xl_GetShortDate
 *  Purpose: 	Format and return the current date
 */
// Example:  09/21/2004		
function xl_GetShortDate()
{
	var today = new Date();
	var year = today.getYear();
	// 2006-12-20 Tyson Gilberstad - added leading zeros for < 10 case for month & day
	var month = today.getMonth() + 1;
	if (month < 10) month = "0" + month;
	var day = today.getDate();
	if (day < 10) day = "0" + day;
	if(year<1000) year+=1900
		var shortdate=    month + "/" +  day + "/" + year;
	return shortdate;
}


/** Function:	xl_AjaxUpdate
 *  Purpose: 	Submit an Ajax call and when complete call the third parm (the
 *					function) with the second parm (possibly a div to be populated
 *					but really it could be anything which identifies which response
 *					it is)
 */
// 2006-12-20 Tyson Gilberstad - added optional parameter to automatically append random number to request	
var _ms_AJAX_Request_ActiveX = ""; // Holds type of ActiveX to instantiate
function xl_AjaxUpdate(url, obj, func, random)
{
	if (!url) return false;  // Don't run if missing the url parm. 
	
	// code for Mozilla, etc.
	if (window.XMLHttpRequest)
	{
		var xmlhttp=new XMLHttpRequest();
	}
	
	// code for IE
	else if (window.ActiveXObject)
	{
		
		// Instantiate the latest MS ActiveX Objects
		if (_ms_AJAX_Request_ActiveX) 
		{
			xmlhttp = new ActiveXObject(_ms_AJAX_Request_ActiveX);
		} 
		else 
		{
			// loops through the various versions of XMLHTTP to ensure we're using the latest
			var versions = ["Msxml2.XMLHTTP.7.0", "Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP",
			"Microsoft.XMLHTTP"];
			for (var i = 0; i < versions.length ; i++)
			{
				try 
				{
					// try to create the object
					// if it doesn't work, we'll try again
					// if it does work, we'll save a reference to the proper one to speed up future instantiations
					xmlhttp  = new ActiveXObject(versions[i]);
					if (xmlhttp) 
					{
						_ms_AJAX_Request_ActiveX = versions[i];
						break;
					}
				}
				catch (objException) 
				{
					// trap -  try next one
				} 
			}
		}
	}
	
	if (!xmlhttp) return false;
	
	if (func) 
		xmlhttp.onreadystatechange = 
	function()
	{
		if (xmlhttp.readyState != 4) return;
		if (xmlhttp.status == 200)
			func(obj, xmlhttp.responseText);
		//else 
		//  alert("An error occurred" + xmlhttp.status);
	};
	else
	xmlhttp.onreadystatechange = function() { return; }
	
	// 2006-12-20 Tyson Gilberstad - added optional parameter to automatically append random number to request	
	if (random)
	{
		url = xl_AddParmToURLString(url, 'rand', Math.floor(Math.random() * 1000000000 + 1));
	}
	
	
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	
	if (func != null)
		if (func) 
	{
	} 
	else 
		return xmlhttp.responseText;
	return false;
}


/** Function:	xl_SetLangs
 *  Purpose: 	This function is used to initialize the esdi multi-language
 *				engine in order to tell it what alternate languages are valid.
 *				The order of the languages provided to this function equates
 *				to the order of the alternate strings provided to the 
 *				xl_AddLangEntry function, and they also equate to the value
 *				passed to the xl_SwitchLang function
 */
function xl_SetLangs()
{
	var argv = xl_SetLangs.arguments;
	var argc = xl_SetLangs.arguments.length;
	var x = 0;
	lang_array = new Array();
	while(x < argc)
	{
		// Convert the parms into entries in the array
		lang_array[x] = argv[x];
		x++;
	}
	
	// Store the array in a new property of the document so that
	// we can retrieve it later
	document.xl_oSupportedLangs = lang_array;
}


/** Function:	xl_AddLangEntry
 *  Purpose: 	This function is used to create the 'translatation'
 *				table between the native language (the first parm)
 *				and each alternate language (as defined in the
 *				xl_SetLangs call)
 */
function xl_AddLangEntry(native_lang)
{
	var argv = xl_AddLangEntry.arguments;
	var argc = xl_AddLangEntry.arguments.length;
	var x = 1;
	
	// If the table (array) of language entries has not been
	// attached to the document yet, do it now.
	if(!document.xl_oMultiLangArr)
		document.xl_oMultiLangArr = new Array();
	
	// Construct a new language object for this 'record' in the table
	// and assign it the various translational equivalents (this assumes
	// the parms are in the same order as specified in xl_SetLangs
	langstr = new Object();
	while(x < argc)
	{
		langstr[document.xl_oSupportedLangs[x - 1]] = argv[x];
		x++;
	}
	
	// Add the entry to the array
	document.xl_oMultiLangArr[native_lang] = langstr;
	
}


/** Function:	xl_SwitchLang
 *  Purpose: 	This function is used to switch all string within
 *				spans identified as language specific (with lang="Y")
 *				to the equivalent string in the language specified
 *				as a parm.  If no match is found, the text is left as-is.
 *				If the same string needs to be translated differently
 *				then use an 'id' tag in the span, and also use the same
 *				id in the AddLangEntry call.
 */
function xl_SwitchLang(target_lang)
{
	var i=0; 
	var tokens = document.getElementsByTagName("SPAN"); 
	// Cycle through all spans
	for (i=0; i < tokens.length; i++) 
	{
		// Trap for ones with a lang attribute set to "Y"
		lang = tokens[i].getAttribute("lang");
		if (lang == "Y") 
		{ 
			// If there is an ID attribute, we want to use that
			// to look it up in our replacement table, otherwise
			// use the string itself
			id = tokens[i].getAttribute("id");
			if((id == "") || (id ==null))
				newstr = document.xl_oMultiLangArr[tokens[i].innerHTML];
			else
				newstr = document.xl_oMultiLangArr[id];
			
			// If we found a replacement, replace it.
			if((newstr != null) && (newstr[target_lang] != null))
				tokens[i].innerHTML = newstr[target_lang];
		}
	} 
} 


/** Function:	  xl_GetFormElementByName
 *  Purpose:   	  GetFormElementByName - returns the first Element object by name found within any page form 
 *  			(or within optionally-specified page form).
 *  @param        elementName - String Element name
 *  @param        formName - Specific form name (optional)
 *  Usage notes:  See Wiki 
*/  
function xl_GetFormElementByName(elementName, formName)
{
	var result;
	
	for(formIndex = 0; formIndex < document.forms.length; formIndex++)
	{ 
		frm = document.forms[formIndex];
		
		// 2006-12-20 Tyson Gilberstad - added optional formName, if specified, 
		//				 only check elements from specified form
		if ((formName == undefined) || (formName == frm.name))
		{
			for(elementIndex = 0; elementIndex < frm.elements.length; elementIndex++)
			{ 		
				if(frm.elements[elementIndex].name == elementName)
				{ 
					result = frm.elements[elementIndex]; 
					return result;
				}
			}
		}
	} 
	return result;
}

/** Function:	  xl_DisableFormElementByName
 *  Purpose:   	  Disable the first Element object by name found within any page form 
 *  			(or within optionally-specified page form).
 *  @param        elementName - String Element name
 *  @param        formName - Specific form name (optional)
 *  Usage notes:  See Wiki 
*/  
function xl_DisableFormElementByName(elementName, formName)
{
	var element = xl_GetFormElementByName(elementName, formName);
	if(element)
	{
		element.disabled = true;
		element.className = element.className + " disabled";	
	}
}

/** Function:	  xl_BuildURLWithFormElements
 *  Purpose:   	 Constructs a string that (usually) comprises a URL, to emulate the format provided by a conventional html form submission. It finds every element name and value for a specified form and appends them to the specified string in the format ?name1=value1&name2=value2 or &name1=value1&name2=&value2, depending on whether an initial parm is present.
 *  @param        frmname - Name of form on page containing elements to use 
 *                string - Starting string to contain the URL. 
 * 		            boolean - Flag if disabled elements should be added to string (optional: default true)
 *  @return       string - Resulting constructed string.
 *  Usage notes:  See Wiki 
*/  
function xl_BuildURLWithFormElements(frmname, str, addDisabled)
{
	// Look for '?' in string - if found, the first parm in a url exists- no need to add it. 
	if (str == undefined)
	{
		str = "";
	}
	
	var x=str.indexOf('?');
	var frm = xl_GetObj(frmname);
	// 2006-12-20 Tyson Gilberstad - check for form name if form not found by id
	if (frm == undefined) 
		frm = document.forms[frmname];
	var elem = frm.elements;
	var parmAdded = ( x != -1);
	
	if (addDisabled == undefined)
	{
		addDisabled = true;
	}
	
	for (var i = 0; i < elem.length; i++)
	{
		if (!addDisabled && elem[i].disabled) 
		{
			// do nothing
		}
		
		else if ((elem[i].tagName == 'INPUT') && ((elem[i].type == 'radio') || (elem[i].type == 'checkbox')) && (!elem[i].checked)) 
		{
			// do nothing
		}
		
		else if ((elem[i].tagName == 'SELECT') && (elem[i].multiple))
		{
			// need to iterate through the items in the select and add all that are selected
			
			for (j=0; j< elem[i].options.length; j++)
			{	
				if ( elem[i].options[j].selected == true )
				{
					
					str = xl_AddParmToURLString(str, elem[i].name, elem[i].options[j].value);
					
				}
			}
			
		}
		
		else 
		{
			str = xl_AddParmToURLString(str, elem[i].name, elem[i].value);
		}
	} 
	return str;
}

/** Function:	  xl_AddParmToURLString
 *  Purpose:   	  Adds a parameter to a URL string
 *  @param        string - Starting string to contain the URL.  
 *                string - Parameter name
 *                string - Parameter value
 *  @return       string - Resulting constructed string.
*/  
function xl_AddParmToURLString(str, name, value)
{
	if(str.indexOf('?') == -1) 
		str += "?";
	else 
		str += "&";
	return str + xl_Encode(name) + "=" + xl_Encode(value);
}

/** Function:    xl_EnableDisabledElements
 *  Purpose:    Enable the disabled fields
 *  @param      oForm - the form to be changed
 *  Usage notes:  See Wiki 
 *  Usage notes <form action="$pf_scriptname" method="get" onsubmit="return enableDisabledFields(this)"> >
*/    
function xl_EnableDisabledElements(oForm)
{
	for(nIndex = 0; nIndex < oForm.elements.length; nIndex++)
	if( oForm.elements[nIndex].disabled == true)
		oForm.elements[nIndex].disabled = false;
	return true;
}


/** Function:   xl_FocusFirstElement
 *  Purpose:    Set the focus to the first valid element we can find (regardless of form)
 * 	Source:		http://www.codeproject.com/jscript/FocusFirstInput.asp
 *  Usage notes:  See Wiki 
*/    
function xl_FocusFirstElement()
{
	var bFound = false;
	
	// for each form
	for (f=0; f < document.forms.length; f++)
	{
		// for each element in each form
		for(i=0; i < document.forms[f].length; i++)
		{
			// if it's not a hidden element
			if (document.forms[f][i].type != "hidden")
			{
				// and it's not disabled
				if (document.forms[f][i].disabled != true)
				{
					// set the focus to it
					document.forms[f][i].focus();
					var bFound = true;
				}
			}
			
			// if found in this element, stop looking
			if (bFound == true)
				break;
		}
		
		// if found in this form, stop looking
		if (bFound == true)
			break;
	}
}

/** Function:	  xl_HiLiteRows
 *  Purpose:   	  Adds custom highlighting of rows in a table when hovering over them.
 *  @param        string - Table id (id attribute value of <table> tag)  
 *                string - Hover color if next parm is omitted or not true, class id if next parm is true.
 *                string - Optional. Value of true= prev parm is a class id instead of a color code.
 *  @return       false  
 */  
function xl_HiliteRows(tblid, hovercolor, hvrclass)
{
	var tbl=xl_GetObj(tblid); 
	
	var trs=tbl.getElementsByTagName('tr');
	for(var j=0;j<trs.length;j++)
	{
		if(trs[j].parentNode.nodeName=='TBODY'  && trs[j].parentNode.nodeName!='TFOOT')
		{
			trs[j].onmouseover=function() 
			{
				if (hvrclass != undefined && hvrclass == true) 
				{
					this.svclass = this.className; 
					this.className = hovercolor;  
					return false;	
					
				}
				else 
				{
					this.svbgcolor = this.style.backgroundColor; 
					this.style.backgroundColor = hovercolor;  
					return false;
				} 
			}
			trs[j].onmouseout=function() 
			{
				if (hvrclass != undefined && hvrclass == true) 
				{
					this.className = this.svclass;  
					return false;	
				} 	
				else 
				{
					this.style.backgroundColor = this.svbgcolor;
					return false;
				}
			}
		}
	}
}

/** Function:	  xl_EnableDrag
 *  Purpose:   	  Enables any element to be draggable, usually DIVs. 
 *  @param        object - Obj -  Handle to Object eg: xl_GetObj(id) using  id attribute value of <div> tag)  
 */  


function xl_EnableDrag(Obj)
{ 
	var ObjCurrStyle; 
	// Actually, allow use of id also: 
	
	if (Obj instanceof Object == false) Obj = xl_GetObj(Obj); 
	
	// IE method of getting computed style for this element (combines classes and inline CSS)
	if (Obj.currentStyle) 
		ObjCurrStyle = Obj.currentStyle; 
	// Mozilla method: 
	else 
		ObjCurrStyle  = window.getComputedStyle(Obj, null); 
	
	// Make sure the style sheet setting is position:absolute -required for drag and drop.
	// Use computed style to determine this, but change actual style (computed style is read-only)
	if (ObjCurrStyle.position != "absolute") Obj.style.position = "absolute"; 
	// If no z-index value, make sure our div sits above rest of page: 
	
	// Check for zIndex being 0 or auto (FireFox) . If so, make it sit above rest of page: 
	
	if (ObjCurrStyle.zIndex == 0 || ObjCurrStyle.zIndex == "auto") Obj.style.zIndex=999;  
	
	xl_AttachEvent(Obj, "mousedown", xl_DragMe); 
}   

/** Function:	  xl_DragMe
 *  Purpose:      Implementation for xl_EnableDrag - drag absolutely positioned HTML elements.
 *				  Drags element that is the direct target of the current event object. Uses xl_Drag, which requires the element id in addition to the event object.
 *  @param        event-  the Event object for the mousedown event.
 */
function xl_DragMe(event) 
{
	var target = xl_GetEventTarg(event); 
	xl_Drag(target, event); 
} 	

/** Function:	  xl_Drag

 *  Purpose:      Called from an onmousedown event handler. Subsequent mousemove events will
 *                move the specified element. A mouseup event will terminate the drag.
 *                If the element is dragged off the screen, the window does not scroll.
 *                This implementation works with both the DOM Level 2 event model and the IE event model.
 *
 *  @param 	      me - the element that received the mousedown event or
 *                  some containing element. It must be absolutely positioned. Its
 *                  style.left and style.top values will be changed based on the user's
 *                  drag.
 *                event-  the Event object for the mousedown event.
 */
function xl_Drag(me, event) 
{
	
	me.style.cursor = "pointer"; 
	// The mouse position (in window coordinates)
	// at which the drag begins
	var startX = event.clientX, startY = event.clientY;
	
	// The original position (in document coordinates) of the
	// element that is going to be dragged. Since me is
	// absolutely positioned, we assume that its offsetParent is the
	// document body.
	var origX = me.offsetLeft, origY = me.offsetTop;
	
	// Even though the coordinates are computed in different
	// coordinate systems, we can still compute the difference between them
	// and use it in the moveHandler( ) function. This works because
	// the scrollbar position never changes during the drag.
	var deltaX = startX - origX, deltaY = startY - origY;
	
	// Register the event handlers that will respond to the mousemove events
	// and the mouseup event that follow this mousedown event.
	if (document.addEventListener) {  // DOM Level 2 event model
		// Register capturing event handlers
		document.addEventListener("mousemove", moveHandler, true);
		document.addEventListener("mouseup", upHandler, true);
	}
	else if (document.attachEvent) {  // IE 5+ Event Model
		// In the IE event model, we capture events by calling
		// setCapture( ) on the element to capture them.
		me.setCapture( );
		me.attachEvent("onmousemove", moveHandler);
		me.attachEvent("onmouseup", upHandler);
		// Treat loss of mouse capture as a mouseup event.
		me.attachEvent("onlosecapture", upHandler);
	}
	else {  // IE 4 Event Model
		// In IE 4 we can't use attachEvent( ) or setCapture( ), so we set
		// event handlers directly on the document object and hope that the
		// mouse events we need will bubble up.
		var oldmovehandler = document.onmousemove; // used by upHandler( )
		var olduphandler = document.onmouseup;
		document.onmousemove = moveHandler;
		document.onmouseup = upHandler;
	}
	
	// We've handled this event. Don't let anybody else see it.
	if (event.stopPropagation) event.stopPropagation( );  // DOM Level 2
	else event.cancelBubble = true;                      // IE
	
	// Now prevent any default action.
	if (event.preventDefault) event.preventDefault( );   // DOM Level 2
	else event.returnValue = false;                     // IE
	
	/**
	* This is the handler that captures mousemove events when an element
	* is being dragged. It is responsible for moving the element.
	**/
	function moveHandler(e) 
	{
		
		me.style.cursor = "move"; 
		
		if (!e) e = window.event;  // IE Event Model
		
		// Move the element to the current mouse position, adjusted as
		// necessary by the offset of the initial mouse-click.
		me.style.left = (e.clientX - deltaX) + "px";
		me.style.top = (e.clientY - deltaY) + "px";
		
		// And don't let anyone else see this event.
		if (e.stopPropagation) e.stopPropagation( );  // DOM Level 2
		else e.cancelBubble = true;                  // IE
	}
	
	/**
	* This is the handler that captures the final mouseup event that
	* occurs at the end of a drag.
	**/
	function upHandler(e) 
	{
		me.style.cursor = "default"; 
		
		if (!e) e = window.event;  // IE Event Model
		
		// Unregister the capturing event handlers.
		if (document.removeEventListener) {  // DOM event model
			document.removeEventListener("mouseup", upHandler, true);
			document.removeEventListener("mousemove", moveHandler, true);
		}
		else if (document.detachEvent) {  // IE 5+ Event Model
			me.detachEvent("onlosecapture", upHandler);
			me.detachEvent("onmouseup", upHandler);
			me.detachEvent("onmousemove", moveHandler);
			me.releaseCapture( );
		}
		else {  // IE 4 Event Model
			// Restore the original handlers, if any
			document.onmouseup = olduphandler;
			document.onmousemove = oldmovehandler;
		}
		
		// And don't let the event propagate any further.
		if (e.stopPropagation) e.stopPropagation( );  // DOM Level 2
		else e.cancelBubble = true;                  // IE
	}
}
